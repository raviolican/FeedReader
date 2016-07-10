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
    public $siteSettings = array();
    
    function __construct() { 
        $this->openDBConnection();
        $this->loadModel();
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
}
