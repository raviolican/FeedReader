<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of db
 *
 * @author Simon
 */
class Model {
    //put your code here
    private $database;
    function __construct($dbh) {
        
        $this->dbh = $dbh;
        $this->sessionInit();
    }
    /**
     * Initializes the session
     */
    private function sessionInit(){
        session_name("FERE_SESSION");
        session_set_cookie_params(0, "/", "localhost"); // cookie delete on browser close
        session_start();
        // Prevents session fixation
        if(!isset($_SESSION["initiated"])){
            session_regenerate_id();
             $_SESSION["language"] = "de_AT";
            $_SESSION["initiated"] = TRUE; 
        }
        // Check for valid user agent to prevent session hijacking
        if(isset($_SESSION["HTTP_USER_AGENT"])){
            if($_SESSION["HTTP_USER_AGENT"] != md5($_SERVER["HTTP_USER_AGENT"])){
                exit;
            }
        }
        else{
            $_SESSION["HTTP_USER_AGENT"] = md5($_SERVER["HTTP_USER_AGENT"]);
        }
    }
    public static function setSessionLanguage($language){
        $_SESSION["language"] = $language;
        clearstatcache();
    }
    private function __clone() {
    }
    /**
     * Querien the site's configurations from DB 
     * @return array Site Settings
     */
    public function getSiteConfiguration(){
        try {
            $sth = $this->dbh->prepare("SELECT * FROM site_settings");
            $sth->execute();
            $result = (array)$sth->fetchAll(PDO::FETCH_CLASS)[0];
            
        } catch (PDOException  $e) { 
            echo "Database Error: Couldn't retrieve settings.<br>".$e->getMessage();
        } catch (Exception  $e) {
            echo "General Error: Couldn't retrieve settings.<br>".$e->getMessage();
        }
        return $result;
    }
    /**
     * Checks if user EXISTS in DB
     * @param type $email
     * @return boolean
     */
    private function checkUserMailExists($email){
        try {
            $sth = $this->dbh->prepare("SELECT * FROM `users` WHERE `email` = '$email'");
            $sth->execute();
            $result = $sth->rowCount();
            if ($result === 1) {
                return TRUE;
            }
            else {
                return FALSE;
            }
        } catch (PDOException $ex) {
            echo $ex->getMessage();
        }
    }
    /**
     * Registers new user when credentials match
     * @param array $userdata [regInputEmail], [regInputPWD] and [regInputPWD_re]
     * @throws Exception
     */
    public function registerNewUser($userdata) {
        if(!$this->checkUserMailExists($userdata["regInputEmail"])){
            try {
                $hashedPWD = password_hash($userdata["regInputPWD"], PASSWORD_BCRYPT, ['cost' => 11]);
                $sth = $this->dbh->prepare("INSERT INTO `users`"
                        . "(`id`, `categories`, `email`, `np_feeds`, `password`, `privatefeed`) "
                        . "VALUES('','hi','$userdata[regInputEmail]','NULL','$hashedPWD','NULL')");
                $sth->execute();
                echo "Registration done.";
            } catch (PDOException $ex) {
                echo "Database Error: Couldn't Insert User: " . $ex->getMessage();
            }
        } else {
            echo _("E-Mail is already in use by another user");
        }
    }
    /**
     * Does an API call to check captchas validity
     * @param string $response
     * @return boolean
     */
    public function reCaptcha($response){
        $secret = "6LcK5CQTAAAAAFSkYFGPsN4oCTKuRz4_LpjNQb-3";
        $result = json_decode(file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$response),TRUE);
        if($result["success"] === TRUE){
            return TRUE;
        } else {
            return FALSE;
        }
    }
    /**
     * Checks login credentials for true or fals
     * @param type $credentials
     * @return boolean  credentials right/wrong
     * @throws Exception
     */
    public function checkLogin($credentials){   
        try {
            $sth = $this->dbh->prepare("SELECT password FROM users WHERE email ='".$credentials["loginInputEmail"]."'");
            $sth->execute();
            if($sth->rowCount() === 1){ 
                $result = $sth->fetchAll()[0];
                if(password_verify($credentials["loginInputPWD"], $result["password"])){
                    return TRUE;
                }
                else{
                    return FALSE;
                }
            }
            else{
                throw new Exception("User not found");
            }
        } catch (Exception $exc) {
            echo "General Error: ".$exc->getTraceAsString();
        } catch (PDOException $exc) {
            echo "Database Error: ".$exc->getMessage();
        }
    }
    /**
     * Defining session params
     * @todo maybe use param as array and set session vars by loop
     *       improved re-use-ability
     */
    public function defineSessionParams($param){
        $_SESSION["email"] = $param;
        echo "success";
    }
    /**
     * Starting a logout by destroying session and unset $_session vars
     * @throws LogOut exception
     */
    public function performLougout(){
        try {
            session_destroy();
            unset($_SESSION);
            echo "success";
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
        }
    /**
     * Performing a redirect, IF the user is logged in
     */
    public static function isLoggedInPerformRedirect(){
        if(isset($_SESSION["email"])){
            header("location: ../");
        }
    }
    /**
     * Creates a sorted array with users feeds
     * @return array Users feeds with category names as keys and feeds as value
     */
    public function getUserFeeds(){
        $FEED_CATEGORIZED = ARRAY(); // We return this, after we builT it
        // Selecting user's feeds from the DB
        $sth = $this->dbh->prepare("SELECT privatefeed FROM users WHERE email ='".$_SESSION["email"]."'");
        $sth->execute();
        // Creating an assoc _ARRAY_ with the feteched result
        $result = json_decode($sth->fetchAll()[0]["privatefeed"],true);
        
        // Defining a new array to srore it like
        // [category_1] => [FEED_NAME] =>category_1,[FEED_NAME] => category_1
        // [category_2] => [FEED_NAME] =>category_2,[FEED_NAME] => category_2
        $categorizedArray = array();
        foreach ($result as $res => $val) {
                $categorizedArray[$val["category"]][$res] = $val["category"];
        }
        
        // Now query the users categories
        $sth = $this->dbh->prepare("SELECT categories FROM users WHERE email ='".$_SESSION["email"]."'");
        $sth->execute();
        $result = json_decode($sth->fetchAll()[0]["categories"],true); // same above
        
        // Looping throught each category
        foreach($result AS $ID => $NAME){
            // Checks if the user has feed names defined in the category["CATEGORY_NAME" => $NAME]
            if(isset($categorizedArray[$ID])){
                // If so, create a new array key and put the feednames inside.
                $FEED_CATEGORIZED[$NAME] = ARRAY();
                $FEED_CATEGORIZED[$NAME] = array_merge($FEED_CATEGORIZED[$NAME],$categorizedArray[$ID]);
            } else {
                // DEBUG echo "NAN";
                // User has no feeds defined in the category
            }
        }
        // there we go
        return $FEED_CATEGORIZED;
    }
}
