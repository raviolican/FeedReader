<?php
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
    public function login(){
        require_once 'vendor/autoload.php';
        Twig_Autoloader::register();
        $loader = new Twig_Loader_Filesystem('views');
        $twig = new Twig_Environment($loader);
        echo $twig->render("user_login.twig", $this->siteSettings);
    }
    public function registerNewUser($userdata){
        // Match Password
        if(preg_match("/^(?=.*\d)(?=.*[a-zA-Z])\w{6,12}$/i", $userdata["regInputPWD"]))
        {
            // Check Password  match
            if ($userdata["regInputPWD"] === $userdata["regInputPWD_re"]) 
            {
                // Check  if eMail is valid
                if(filter_var($userdata["regInputEmail"], FILTER_VALIDATE_EMAIL))
                {
                    //Validate Catptcha
                    if($this->model->reCaptcha($userdata["g-recaptcha-response"]) === TRUE)
                    {
                       $this->model->registerNewUser($userdata); 
                    }
                    else {
                        echo "Captcha no valid";
                    }

                } else {
                    echo "E-Mail not valid";
                }
            } else {
                echo "Password don't match";
            }
        } else {
            echo "Password is not secure enought";
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
                echo "OK"; // TODO: COMPUTE A NEW SESSION
            }
            else{
                echo "WRON CREDENTIALS";
            }
        }
    }

}
