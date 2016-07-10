<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of userController
 *
 * @author Simon
 */
class UserController extends Controller {
    //put your code here
    public function register(){
        require_once 'vendor/autoload.php';
        Twig_Autoloader::register();
        $loader = new Twig_Loader_Filesystem('views');
        $twig = new Twig_Environment($loader);
        echo $twig->render("user_register.twig", $this->siteSettings);
    }
    public function registerNewUser($userdata){ 
        $this->model->registerNewUser($userdata);
    }
}
