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
        if(preg_match("/^(?=.*\d)(?=.*[a-zA-Z])\w{6,12}$/i", $userdata["regInputPWD"])){
            if ($userdata["regInputPWD"] === $userdata["regInputPWD_re"]) {
                if(filter_var($userdata["regInputEmail"], FILTER_VALIDATE_EMAIL)){
                    if($this->model->reCaptcha($userdata["g-recaptcha-response"]) === TRUE){
                       $this->model->registerNewUser($userdata); 
                    }
                    else{
                        echo "Captcha no valid";
                    }

                } else {
                    echo "E-Mail not valid";
                }
            } else {
                echo "Password don't match"
            }
        } else {
            echo "Password is not secure enought";
        }
    }
}
