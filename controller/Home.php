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
        if(isset($_SESSION["email"])){
            header("Location: http://localhost/FeedReader/feeds/");
            echo $pg->render('user_homepage.twig', array_merge($this->siteSettings,
                    [
                        'email'=>$_SESSION["email"],
                        "language" => $_SESSION["language"],
                        "categories"    => $this->model->getUserCategories(),
                    ],  
                    Controller::$lang)); 
        }
        else{
            echo $pg->render('index.twig', array_merge($this->siteSettings,["language" => $_SESSION["language"]],  Controller::$lang));
        }
    }
}
?>