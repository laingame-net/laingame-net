<?php
namespace Lain;

use Lain\Model;
use Lain\View;

class SiteController
{

    public function __construct()
    {
        $this->model = new Model();
        $this->view = new View("../templates");
    }

    public function indexActionGet($id = "", $lang = "", $event = "")
    {
        header("Location: /site/login");
        exit();
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

        if(!$error){
            $ret = $this->model->registerUser($_POST["username"], $_POST["email"], $_POST["password1"]);
            switch($ret){
                case 0: // All ok
                    $_SESSION['user'] = $ret;
                    header("Location: /");
                    exit;
                case 23000:
                    $error = "This username or email already exists in database! Try to <a href='/site/login'>login?</a><br>\n";
                    break;
                default:
                    $error = "SQLSTATE = ".$ret;
            }
        }

        $this->view->render('register', null, array(
            'TITLE' => 'Register',
            'lang' => $lang,
            'error' => $error,
            'last_post' => $_POST,
            'langs' => $this->model->langs,
        ));
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
            unset($_SESSION['password']); // just in case :)
            header("Location: /");
            exit;
        }
    }

    public function logoutActionGet($id = "", $lang = "", $event = "")
    {
        unset($_SESSION["user"]);
        header("Location: /site/login");
    }
}
