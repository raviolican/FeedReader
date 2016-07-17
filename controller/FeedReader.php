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
    /**
     * Creates the user_homepage view
     */
    public function index(){
        if(!isset($_SESSION["email"])){
            $this->errorPage(404,  Controller::$lang["SITE_NOT_FOUND"]);
            exit;
        }
        $my = $this->model->getCategoryFeeds();
        $feeds = $this->model->fetchFeeds($my,1,6);
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
                        "category"      => "all"
                    ],  
                    Controller::$lang
            ));
        $this->dbh = NULL;
    }
    
    /**
     * Creates the view for any category
     * @param integer $category the ctegory 
     */
    public function r($category){
        //  print_r($category);
        $this->model->is_NOT_LoggedInPerformRedirect();
        if(isset($category["category"])){
            if($category["category"] !== "all"){
                $my = $this->model->getCategoryFeeds();
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
                $my = $this->model->getCategoryFeeds();
            }
        }
        else{
            $my = $this->model->getCategoryFeeds();
            foreach($my AS $key => $value){ 
                if($value["category"] !== $category)
                {
                    unset($my[$key]);
                }
                else {
                }
            }   
        }
 
        // Check if there are ANY feeds
        
        if(count($my) > 0){
            if(isset($category["start"])){
                $feeds = $this->model->fetchFeeds($my,$category["start"],$category["end"]);
            }
            else{
                $feeds = $this->model->fetchFeeds($my,0,6);
               // print_r("<pre>");
              //  print_r(json_decode($feeds));
               // print_r("</pre>");
            }
             
        } else {
            Controller::errorPage(404,"Could'nt find any feeds in this category.");
            exit;
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
            $this->dbh = NULL;
        }
        else{
            $this->dbh = NULL;
            echo $feeds;
        }
    }
}
?>