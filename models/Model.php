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
     * @since 1.0
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
     * Registers new user when credentials match
     * @param array $userdata [regInputEmail], [regInputPWD] and [regInputPWD_re]
     * @throws Exception
     */
    public function registerNewUser($userdata){
        // Re-check password security 6-12 chars only word and with >=1 UPPERCASE
        if(preg_match("/^(?=.*\d)(?=.*[a-zA-Z])\w{6,12}$/i", $userdata["regInputPWD"])){
            if(filter_var($userdata["regInputEmail"], FILTER_VALIDATE_EMAIL)){
                 try{
                    $sth = $this->dbh->prepare("SELECT * FROM `users` WHERE `email` = '$userdata[regInputEmail]'");
                    $sth->execute();
                    $result = $sth->rowCount();
                    if($result === 1){ 
                        throw new Exception("User allready registered.");
                    }
                    else{
                        if($userdata["regInputPWD"] === $userdata["regInputPWD_re"]){
                            try {
                                $hashedPWD = password_hash($userdata["regInputPWD"], PASSWORD_BCRYPT, ['cost' => 10]);
                                $sth = $this->dbh->prepare("INSERT INTO `users`(`id`, `categories`, `email`, `np_feeds`, `password`, `privatefeed`) VALUES"
                                        . "('','hi','$userdata[regInputEmail]','NULL','$hashedPWD','NULL')");
                                $sth->execute();
                                echo "ok";
                            } catch (PDOException $ex) {
                                echo "Database Error: Couldn't Insert User: ".$ex->getMessage();
                            }
                        }
                        else{
                            throw new Exception("Passwords don't match.");
                        }
                    }
                 } catch (Exception $ex) {
                     echo "Database Error: Couldn't insert user: ".$ex->getMessage();
                 } catch (PDOException $ex){
                     echo "Database Error: Couldn't insert user: ".$ex->getMessage();
                 }
            }
            else{
                echo "Email not ok";
            }
        }
        else{
            echo "General Error: Password is not secure enought.";
        }
    }
}
