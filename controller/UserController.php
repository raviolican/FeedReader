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
        /*
        print_r("<pre>");
        print_r($userdata);
        print_r("<pre>");
         * */
        // Re-check password security 6-12 chars only word and with >=1 UPPERCASE
        // && check if eMail is an valid eMail
        echo $userdata["g-recaptcha-response"];
        print_r($this->model->reCaptcha($userdata["g-recaptcha-response"]));
        /*
        if(preg_match("/^(?=.*\d)(?=.*[a-zA-Z])\w{6,12}$/i", $userdata["regInputPWD"])){
            if(filter_var($userdata["regInputEmail"], FILTER_VALIDATE_EMAIL)){
                $this->model->registerNewUser($userdata);
            } else {
                echo "Password not valid";
            }
        } else {
            echo "Password is not secure enought";
        }
         * *Ü/
         */
    }
}
