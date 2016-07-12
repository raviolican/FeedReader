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
            $result = $sth->fetchAll()[0];
            
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
        if(!checkUserMailExists($userdata[regInputEmail])){
            try {
                $hashedPWD = password_hash($userdata["regInputPWD"], PASSWORD_BCRYPT, ['cost' => 10]);
                $sth = $this->dbh->prepare("INSERT INTO `users`"
                        . "(`id`, `categories`, `email`, `np_feeds`, `password`, `privatefeed`) "
                        . "VALUES('','hi','$userdata[regInputEmail]','NULL','$hashedPWD','NULL')");
                $sth->execute();
                echo "Registration done.";
            } catch (PDOException $ex) {
                echo "Database Error: Couldn't Insert User: " . $ex->getMessage();
            }
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

}
