<?php

class Application
{
    /** @var null The controller */
    private $url_controller = null;
    /** @var null The method (of the above controller), often also named "action" */
    private $url_action = null;

    /** @var array URL parameters */
    private $url_params = array();

    /**
     * "Start" the application:
     * Analyze the URL elements and calls the according controller/method or the fallback
     */
    public function __construct()
    {
        require_once 'vendor/autoload.php';
        Twig_Autoloader::register();
        $loader = new Twig_Loader_Filesystem('views');
        $twig = new Twig_Environment($loader,array('debug' => true));

        // create array with URL parts in $url
        $this->splitUrl();

        // check for controller: no controller given ? then load start-page
        if (!$this->url_controller) {
            require APP . 'controller/Home.php';
            
            $page = new Home();
            $page->index($twig);

        }
        elseif (file_exists(APP . 'controller/' . $this->url_controller . '.php')) {
            // here we did check for controller: does such a controller exist ?
 
            // if so, then load this file and create this controller
            // example: if controller would be "car", then this line would translate into: $this->car = new car();
            require APP . 'controller/' . $this->url_controller . '.php';
            $this->url_controller = new $this->url_controller();

            // check for method: does such a method exist in the controller ?
            if (method_exists($this->url_controller, $this->url_action)) {

                if (!empty($this->url_params)) {
                    // Call the method and pass TWIG OBJECT  
                    if(get_class($this->url_controller) == "UserController"){
                        switch ($this->url_action) {
                            case "registerNewUser":
                                call_user_func_array(array($this->url_controller, $this->url_action), array($this->url_params));
                                break;
                            case "userLogin":
                                call_user_func_array(array($this->url_controller, $this->url_action), array($this->url_params));
                                break;                           
                            case "setLanguage":
                                call_user_func_array(array($this->url_controller, $this->url_action), array($this->url_params));
                                break;                           
                            case "deleteFeed":
                                call_user_func_array(array($this->url_controller, $this->url_action), array($this->url_params));
                                break;                           
                            case "addFeed":
                                call_user_func_array(array($this->url_controller, $this->url_action), array($this->url_params));
                                break;
                            case "addUserFeed":
                                call_user_func_array(array($this->url_controller, $this->url_action), array($this->url_params));
                                break;                           
                            case "addCategory":
                                call_user_func_array(array($this->url_controller, $this->url_action), array($this->url_params));
                                break;  
                            default:
                                $contr = new Controller();
                                $contr->err404();
                                break;
                        }
                    }
                    if(get_class($this->url_controller) === "FeedReader"){
                        switch ($this->url_action) {
                            case "r":
                                call_user_func_array(array($this->url_controller, $this->url_action), array($this->url_params));
                                break;
                            default:
                                $contr = new Controller();
                                $contr->err404();
                                break;
                        }
                    }
                    //call_user_func_array(array($this->url_controller, $this->url_params), $twig);
                    
                } else {
                    // If no parameters are given, just call the method without parameters, like $this->home->method();
                    $this->url_controller->{$this->url_action}();
                }

            } else {
                if (strlen($this->url_action) == 0) {
                    // no action defined: call the default index() method of a selected controller
                    $this->url_controller->index();
                }
                else {
                    // Handling not found error
                    $contr = new Controller();
                    $contr->errorPage(404,Controller::$lang["SITE_NOT_FOUND"] );
                    exit;
                    
                }
            }
        } else {
            // Handling not found error
            $contr = new Controller();
            $contr->errorPage(404,Controller::$lang["SITE_NOT_FOUND"] );
            exit;
        }
    }

    /**
     * Get and split the URL
     */
    private function splitUrl()
    {
        
        if (isset($_GET['url'])) {
            // split URL
            $url = trim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
           
            
            $this->url_controller = isset($url[0]) ? $url[0] : null;
            if(isset($url[0])){
                 
                if($url[0] === "users"){
                    $this->url_controller = "UserController";
                }
                elseif($url[0] === "feeds"){
                    $this->url_controller = "FeedReader"; 
                }
            } else {
                $this->url_controller = NULL;
            }
            $this->url_action = isset($url[1]) ? $url[1] : null;
            
            // Remove controller and action from the split URL
            unset($url[0], $url[1]);
            
            // Rebase array keys and store the URL params
            array_shift($_GET); 
            
            $this->url_params = !empty($_GET) ? $_GET : $url[2]; 
            
            
            //$this->url_params = $_GET;
            #echo 'Controller: ' . $this->url_controller . '<br>';
            # echo 'Action: ' . $this->url_action . '<br>';
            #echo 'Parameters: ' . print_r($this->url_params, true) . '<br>';
        }
    }
}
