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
    public  function is_NOT_LoggedInPerformRedirect(){
        if(!isset($_SESSION["email"])){
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
        
        foreach ($result as $res => $val) {// </editor-fold>
                            $categorizedArray[$val["category"]][$res] = $val["category"];
        }
        
        // Now query the users categories
        $result = $this->db_selectCategories();
        
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
    /**
     * Deletes a feed and updates table
     * @param string $feedName Name of the feed
     * @throws Exception
     */
    public function deleteUserFeed($feedName) {
        // First get the feeds and create an assoc array
        $result = $this->db_selectUserFeeds();
        // unset the perefered feedname in the array
        unset($result[$feedName]);
        // creating json
        $newJSON = json_encode($result);
        
        // updatiing the clolumn
        try {
            $sth = $this->dbh->prepare("UPDATE users SET privatefeed=? WHERE email=?");
            $affected = $sth->execute(array($newJSON,$_SESSION["email"]));
            if($affected === 1){
                echo "success";
            }
            else{
                throw new Exception("Error: Couldn't update userdata");
            }
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        } catch (PDOException $exc){
            echo $exc->getTraceAsString();
        }
    }
    
    /**
     * 
     * @param array $feedData Entered feed data from user
     * @return type
     */
    public function addUserFeed($feedData){
        // validade the feet by API
        $valid = $this->validateFeed($feedData["feedUrl"]);
        if($valid){ // $valid
            $result = $this->db_selectUserFeeds();
            
            // Check for duplicates
            foreach ($result as $key => $value) {
                if($key === $feedData["feedName"]){
                    echo "A feed with given name already exists";
                    return;
                }
                if($value["url"] === $feedData["feedUrl"]){
                    echo "A feed with given URL already exists.";
                    return;
                }
            }
            // Assign the new feed to the old
            $result[$feedData["feedName"]] = [
                "url" => $feedData["feedUrl"],
                "category" => $feedData["category"]
            ];
            // generate the JSON
            $newJSON = json_encode($result);
            
            try { // Update the table
                $sth = $this->dbh->prepare("UPDATE users SET privatefeed=? WHERE email=?");
                $affected = $sth->execute(array($newJSON,$_SESSION["email"]));
                echo "succes";
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            } catch (PDOException $exc) {
                echo $exc->getTraceAsString();
            }  
        }
        else{
            echo "The URL you've entered seems not to be valid";
            return;
        }
        //validateFeed
    }
    /**
     * Returns the categories of the user as array
     * @return array all user categories
     */
    public function getUserCategories(){
            return $this->db_selectCategories();
    }
    /**
     * Sets a new user feed category when it does not already exist
     * @param string $categoriyName
     * @return string
     */
    public function setUserCategory($categoriyName){ 
        $result = $this->db_selectCategories();
        
        if (!in_array($categoriyName, $result)) {
            $result[] = $categoriyName;
            $newJSON = json_encode($result);
            try {
                $sth = $this->dbh->prepare("UPDATE users SET categories=? WHERE email=?");
                $affected = $sth->execute(array($newJSON,$_SESSION["email"]));
                return "success";
            } catch (Exception $exc) {
                return $exc->getTraceAsString();
            } catch (PDOException $exc) {
                return $exc->getTraceAsString();
            }
        }
        else {
            return "Category already exists";
        }
    }

    
    /**
     * Selects categories and returns array
     * @return array the selected categorie
     */
    private function db_selectCategories(){
          try {
            $sth = $this->dbh->prepare("SELECT categories FROM users WHERE email = ?");
            $obj = $sth->execute(array($_SESSION["email"]));
            $result = json_decode($sth->fetchAll()[0]["categories"],true);
            
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        } catch (PDOException $exc) {
            echo $exc->getTraceAsString();
        }
        return $result;
    }
    /**
     * Selects user's feeds and returns them in ARRAY
     * @return array all feeds
     */
    private function db_selectUserFeeds(){
        try {
            $sth = $this->dbh->prepare("SELECT privatefeed FROM users WHERE email = ?");
            $sth->execute(array($_SESSION["email"]));
            $result = json_decode($sth->fetchAll()[0]["privatefeed"],true);
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        } catch (PDOException $exc) {
            echo $exc->getMessage();
        }
        return $result;
    }
    /**
     * Verifiess a valid URL
     * @param string $rssFeedURL The URL that needs to be tested
     * @return boolean
     */
    private function validateFeed($rssFeedURL) {
        if (!filter_var($rssFeedURL, FILTER_VALIDATE_URL) === false) {
            return true;
        } else {
            return false;
        }
        $rssValidator = 'http://feedvalidator.org/check.cgi?url=';
        if ($rssValidationResponse = file_get_contents($rssValidator . urlencode($rssFeedURL))) {
            if (stristr($rssValidationResponse, 'This is a valid RSS feed') !== false) {
                return true;
            } else {
                return false; // must be:false
            }
        } else {
            return false; // must be:false
        }
       
    }
    /* =========================================================================
     *                         _._ FEED READER _._
     * =========================================================================
     */
    public function getCategoryFeeds($catid){
        $result = $this->db_selectUserFeeds();
        //print_r($resul);
        return $result;
    }
    public function fetchFeeds($feeds,$start,$end){
        // Iterlating over each key
        $temp = array();
        foreach ($feeds as $key => $value) {
           
            (array) $temp = array_merge((array) $temp, (array) $this->createJSON($value["url"], "test"));
        }
        usort($temp, function($b, $a) {
            return strtotime($a['pubDate']) - strtotime($b['pubDate']);
        });
        if ($start != "na") {
            $temp = array_slice($temp, $start, $end);
            return json_encode($temp);
        } else {
            return json_encode($temp);
        }
    }
    
    private function createJSON($preJ, $feedName) {
        $val = array();
        $chi = array();
        $rss = simplexml_load_file($preJ);
        $t = 0;
        if (isset($rss->item)) {
            $ARRAY = $rss->item;
        } else {
            $ARRAY = $rss->channel->item;
        }
        foreach ($ARRAY as $item)
            if ($t++ < 10) {
                if ($feedName != "") {
                    $dc = $item->children("http://purl.org/dc/elements/1.1/");
                    if (isset($dc->date)) {
                        $val[$t] = array(
                            "link" => $item->link,
                            "title" => (string)$item->title[0],
                            "desc" => $item->description,
                            "pubDate" => date("D, Y-m-d H:i", strtotime((string) $dc->date)),
                            "name" => (string) $rss->channel->title,
                            "tag" => $feedName
                        );
                    } else {
                        $val[$t] = array(
                            "link" => $item->link,
                            "title" => (string)$item->title[0],
                            'desc' =>  (string) $item->description[0],
                            "pubDate" => date("D, d-m-Y H:i", strtotime((string) $item->pubDate)),
                            //"pubDate" => (string)$item->pubDate,
                            "name" => (string) $rss->channel->title,
                            "tag" => $feedName
                        );
                    }
                } else {
                    
                }
            }
            else{
                break;
            }
        return $val;
    }

}
