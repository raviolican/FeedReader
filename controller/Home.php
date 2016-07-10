<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Home
 *
 * @author Simon
 */
class Home extends Controller {
    //put your code here 
    public function index($pg){ 
        echo $pg->render('index.twig', $this->siteSettings);
        echo $_SERVER['REQUEST_URI'];
    }
}
