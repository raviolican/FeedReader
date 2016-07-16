<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Controller
 *
 * @author Simon
 */
class Controller {
    //put your code here
    public $dbh = NULL;
    public $model = NULL;
    public $siteSettings; 
    static $lang;
    
    function __construct() {  
         
        // First things first
        $this->openDBConnection();   
        $this->loadModel(); 
        // Load the prefered language
        require_once 'c://xampp/htdocs/FeedReader/locale/'.$_SESSION["language"].'.php';
 
         
        // Generating expirience
        $this->siteSettings = $this->model->getSiteConfiguration();
    }
    private function openDBConnection(){
        
         $this->dbh = new PDO('mysql:dbname=feedreader;host=localhost', "root", "");
         $this->dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
         
    }
    public function loadModel(){
        require 'models/Model.php';
        $this->model = new Model($this->dbh);
    }
    public  function errorPage($code,$errmsg){
       // $this->model->isLoggedInPerformRedirect();
        require_once 'vendor/autoload.php';
        Twig_Autoloader::register();
        $loader = new Twig_Loader_Filesystem( $_SERVER['DOCUMENT_ROOT'].'/FeedReader/views');
        $twig = new Twig_Environment($loader);
        echo $twig->render("404.twig", array_merge($this->siteSettings,
                [
                    "language"  => $_SESSION["language"],                   
                    "email"     => $_SESSION["email"],
                    "language"  => $_SESSION["language"],
                    "code"      => $code,
                    "errmsg"    => $errmsg
                ],   
                Controller::$lang));
    }
}
