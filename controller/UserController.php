<?php
/**
 * Description of userController
 *
 * @author Simon
 */
class UserController extends Controller {
    /**
     * Loads registration template
     */
    public function register(){
        $this->model->isLoggedInPerformRedirect();
        require_once 'vendor/autoload.php';
        Twig_Autoloader::register();
        $loader = new Twig_Loader_Filesystem( $_SERVER['DOCUMENT_ROOT'].'/FeedReader/views');
        $twig = new Twig_Environment($loader);

        echo $twig->render("user_register.twig", array_merge(
                $this->siteSettings,
                [
                    "language" => $_SESSION["language"]
                ],  
                Controller::$lang));
    }
    /**
     * Loads login template
     */
    public function login(){
        $this->model->isLoggedInPerformRedirect();
        require_once 'vendor/autoload.php';
        Twig_Autoloader::register();
        $loader = new Twig_Loader_Filesystem( $_SERVER['DOCUMENT_ROOT'].'/FeedReader/views');
        $twig = new Twig_Environment($loader);
        echo $twig->render("user_login.twig", array_merge(
                $this->siteSettings,
                [
                    "language" => $_SESSION["language"]
                ],
                Controller::$lang
             ));

    }
    /*
     * Loads user settings template
     */
    public function settings(){ 
        $this->model->is_NOT_LoggedInPerformRedirect();
        require_once 'vendor/autoload.php';
        Twig_Autoloader::register();
        $loader = new Twig_Loader_Filesystem( $_SERVER['DOCUMENT_ROOT'].'/FeedReader/views');
        $twig = new Twig_Environment($loader); 
        echo $twig->render("user_settings.twig",array_merge(
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
    /**
     * Registers a new user 
     * @param array $userdata propagated with e-mail, password
     * @return type
     */
    public function registerNewUser($userdata){
        // Check if password is NOT valid
        if(!static::passwordValidate($userdata["regInputPWD"],$userdata["regInputPWD_re"])){; 
            return;
        }
        // Check if eMail is not valid
        if(!filter_var($userdata["regInputEmail"], FILTER_VALIDATE_EMAIL))
        {
            echo("The E-Mail adress you've entered seems not be be valid");
            return;
        }
        // Does an reCaptcha check and performs the registration if valid
        if($this->model->reCaptcha($userdata["g-recaptcha-response"]) === TRUE){
            $this->model->registerNewUser($userdata); 
        }
        else {
            echo("The Anti-Bot verification you've entered is not valid.");
        }
    }
    /**
     * AJAX Function used for user login
     * @param array $credentials    user-input
     * @todo Improve security for user inputs!
     */
    public function userLogin($credentials){
        // Empty Strings?
        if($credentials["loginInputEmail"] && $credentials["loginInputPWD"] !== NULL)
        {   // Credentials Right?
            if($this->model->checkLogin($credentials) === TRUE)
            {
                $this->model->defineSessionParams($credentials["loginInputEmail"]);
            }
            else{
                echo _("Username or Password wrong");
            }
        }
    }
    /**
     * Routes to the right correct Model Function
     */
    public function performLougout(){
        // Performing the logOut
        $this->model->performLougout(); 
    }
    /**
     * Checks if the password if valid 
     * @param type $password    password entered by user
     * @param type $password_re password verification
     * @return boolean password valid?
     */
    private static function passwordValidate($password,$password_re){
        if(!preg_match("/^(?=.*\d)(?=.*[a-zA-Z])\w{6,12}$/i", $password)){
            echo _("The password you've entered is not secure enought");
            return FALSE;
        }
        if ($password != $password_re){
            echo _("Your passwords don't match");
            return FALSE;
        }
        return TRUE;
    }
    /**
     * Sets the language key in the session
     * @param string $lang language code eg de_AT
     */
    public function setLanguage($lang){
        print_r($lang);
        Model::setSessionLanguage($lang["selectLanguage"]);
    }
    /**
     * Initiates deleteion of feed
     * @param array $feedName array with the feedname
     * @return type
     */
    public function deleteFeed($feedName){
        if(isset($feedName["key"])){
           $this->model->deleteUserFeed($feedName["key"]);
        }
        else{
            echo "Please select a feed first!";
        }
    }
    public function addCategory($data){
        if(!empty($data["categoryName"])){
            echo $this->model->setUserCategory($data["categoryName"]);
        }
        else{
            echo "There is something missing!";
            return;
        }
         
    }
    public function addUserFeed($feedData){
        if(!empty($feedData["feedName"]) && !empty($feedData["feedUrl"]) && !empty($feedData["category"])){
           $this->model->addUserFeed($feedData);
        }
        else{
            echo "There is something missing";
            return;
        }
         
    }
    
    
    
    
    
}
