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
     
    public function r($category){
        //  print_r($category);
        $this->model->is_NOT_LoggedInPerformRedirect();
         
        
        if(isset($category["category"])){
             
            
            $my = $this->model->getCategoryFeeds($category["category"]);
            foreach($my AS $key => $value){ 
                if($value["category"] !== $category["category"])
                {
                    unset($my[$key]);
                }
                else {
                }
            }   
        }
        else{
            $my = $this->model->getCategoryFeeds($category);
            foreach($my AS $key => $value){ 
                if($value["category"] !== $category)
                {
                    unset($my[$key]);
                }
                else {
                }
            }   
        }
 
        
        if(count($my) <= 0){
            Controller::errorPage(404,"Could'nt find any feeds in this category.");
            exit;
            //$feeds = NULL;
        }
        
        else{
            if(isset($category["start"])){
                $feeds = $this->model->fetchFeeds($my,$category["start"],$category["end"]);
            }
            else{
                $feeds = $this->model->fetchFeeds($my,1,6);
               // print_r("<pre>");
              //  print_r(json_decode($feeds));
               // print_r("</pre>");
            }
             
        }
        if(!isset($category["start"])){
            
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
                    "feeds"         => json_decode($feeds,true),
                    "category"      => $category
                ],  
                Controller::$lang
            ));
        }
        else{
            echo $feeds;
        }
        
         
    }
}
