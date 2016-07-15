<?php

/**
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FeedReader
 *
 * @author Simon
 */
class FeedReader extends Controller{
    //put your code here
    
    public function r(){
        $this->model->is_NOT_LoggedInPerformRedirect();
        require_once 'vendor/autoload.php';
        Twig_Autoloader::register();
        $loader = new Twig_Loader_Filesystem( $_SERVER['DOCUMENT_ROOT'].'/FeedReader/views');
        $twig = new Twig_Environment($loader); 
        echo $twig->render("feed_category.twig",array_merge(
                $this->siteSettings,
                [   
                    "user_feeds"    => $this->model->getUserFeeds(), 
                    "email"         => $_SESSION["email"],
                    "language"      => $_SESSION["language"],
                    "categories"    => $this->model->getUserCategories(),
                ],  
                Controller::$lang
            ));
    }
}
