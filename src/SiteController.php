<?php
namespace Lain;

use Lain\Model;
use Lain\View;

class SiteController
{
    public function __construct($model = null)
    {
        $this->model = ($model) ?: $GLOBALS['model'];
        $this->view = new View("../templates");
    }

	private function GenerateToken($length = 20)
    {
        return random_bytes($length);
    }

	public function GenerateLoginCookie($id_user)
	{
        date_default_timezone_set("UTC");
        $cookie_expiration_time = new \DateTime("+60 day");
        $series = '';
        do{
            $series = bin2hex( SiteController::GenerateToken(6) );
        }
        while( $this->model->getSession( $series ) != 2 ); // avoid collisions
        $token  = hash("sha256", SiteController::GenerateToken(32));
        try{
            $session_id = $this->model->newSession($id_user, $series, $token, $cookie_expiration_time);
        }catch (\PDOException $e){
            return false;
        }
        setcookie("login", $series."-".$token, $cookie_expiration_time->getTimestamp(), "/");
        return $this->model->getSession( $series );
    }

	public function UpdateLoginCookie($series, $expired)
	{
        $token = hash("sha256", SiteController::GenerateToken(32));
        $this->model->setSessionToken($series, $token);
        setcookie("login", $series."-".$token, $expired->getTimestamp(), "/");
        return $token;
    }

    public function CookieLogin()
	{
        if(isset($_SESSION['user']))
          return;

        if(!isset($_COOKIE["login"]))
          return;

        $_COOKIE["login"] = preg_replace('/[^a-z0-9-]/', '', strtolower($_COOKIE["login"]));
        list($series, $token) = explode("-", $_COOKIE["login"], 2);
        $series = substr($series, 0, 12);
        $session = $this->model->getSession( $series );
        if(!is_int($session))
        {
            if($session->token === $token)
            {
                date_default_timezone_set("UTC");
                $now = new \DateTime();
                if($now <= $session->expired)
                {
                    $token = $this->UpdateLoginCookie($series, $session->expired);
                    $session->token = $token;
                    $_SESSION['user'] = $session;
                }
                else // login cookie expired and must be deleted
                {
                    $this->logout($series);
                }
            }
            else
            {
                $this->model->delAllSession($session->id);
                $this->model->warnUser($session->id);
            }
        }
        else
        {
            $this->logout($series);
        }
    }

    public function indexActionGet($id = "", $lang = "", $event = "")
    {
        header("Location: /site/login");
    }

    public function loginActionGet($id = "", $lang = "", $event = "")
    {
        $this->view->render('login', null, array(
            'TITLE' => 'Login',
            'data_blocks' => $data,
            'lang' => $lang,
            'langs' => $this->model->langs,
        ));
    }

    public function registerActionGet($id = "", $lang = "", $event = "")
    {
        $this->view->render('register', null, array(
            'TITLE' => 'Register',
            'lang' => $lang,
            'langs' => $this->model->langs,
        ));
    }

    public function registerActionPost($id = "", $lang = "", $event = "")
    {
        $error = false;
        foreach($_POST as $key => $value)
            $_POST[$key] = htmlentities($value);

        if(  empty($_POST["username"])
          or empty($_POST["email"])
          or empty($_POST["password1"])
          or empty($_POST["password2"]))
            $error .= "All fields are required<br>\n";

        if($_POST["password1"] != $_POST["password2"])
            $error .= "Password does not match<br>\n";

        if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL))
            $error .= "Email is not valid<br>\n";

        $remember_me = isset($_POST['remember']);

        if(!$error){
            try{
                $user_id = $this->model->registerUser($_POST["username"], $_POST["email"], $_POST["password1"]);
            }catch (\PDOException $e){
                $err = intval($e->errorInfo[0]);
                switch($err){
                    case 23000:
                        $error = "This username or email already exists in database! Try to <a href='/site/login'>login?</a><br>\n";
                        break;
                    default:
                        $error = "SQLSTATE = ".$err;
                }
                $this->view->render('register', null, array(
                    'TITLE' => 'Register',
                    'lang' => $lang,
                    'error' => $error,
                    'last_post' => $_POST,
                    'langs' => $this->model->langs,
                ));
                exit;
            }

            if(!$remember_me)
                $_SESSION['user'] = $this->model->getUser($_POST["username"], $_POST["password1"]);
            else{
                $series = $this->GenerateLoginCookie($user_id);
                $_SESSION['user'] = $series;
            }
            header("Location: /");
            exit;
        }
    }

    public function loginActionPost($id = "", $lang = "", $event = "")
    {
        if(  empty($_POST["username"])
          or empty($_POST["password"]))
        {
            $this->loginActionGet($id, $lang, $event);
            exit();
        }

        foreach($_POST as $key => $value)
            $_POST[$key] = htmlentities($value);

        $remember_me = isset($_POST['remember']);

        $ret = $this->model->getUser($_POST["username"], $_POST["password"]);
        if(is_int($ret))
        {
          switch($ret){
            case 1:
                $last_username = $_POST["username"];
                $error = "Wrong password!";
                break;
            case 2:
                $last_username = $_POST["username"];
                $error = "Username does not exists.";
                break;
            default:
          }
          $this->view->render('login', null, array(
                'TITLE' => 'Login',
                'last_username' => $last_username,
                'error' => $error,
                'lang' => $lang,
                'langs' => $this->model->langs,
          ));
        }
        else{ // All ok
            $error = false;
            $_SESSION['user'] = $ret;
            if($remember_me){
                $series = $this->GenerateLoginCookie($ret->id);
                $_SESSION['user'] = $series;
            }
            header("Location: /");
        }
    }

    private function logout($series = null)
    {
        if(!is_null($series)){
            try{
                $this->model->delSession($series);
            }catch (\Exception $e){}
        }
        setcookie("login", null, -1, '/');
        unset($_COOKIE["login"]);
        unset($_SESSION["user"]);
    }

    public function logoutActionGet($id = "", $lang = "", $event = "")
    {
        $this->logout($_SESSION["user"]->series);
        header("Location: /site/login");
    }
}
