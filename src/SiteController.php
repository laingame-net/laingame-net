<?php
namespace Lain;

use Lain\View;

class SiteController
{
    public function __construct($model = null)
    {
        $this->model = (isset($model)) ? $model : $GLOBALS['model'];
        $this->view = new View("../templates");
    }

    private function GenerateToken($length = 20)
    {
        return random_bytes($length);
    }

    public function GenerateLoginCookie($id_user)
    {
        require '../config.php';
        date_default_timezone_set("UTC");
        $expired = new \DateTime("+60 day");
        $series = '';
        do {
            $series = bin2hex(SiteController::GenerateToken(6));
        } while ($this->model->getSession($series) != 2); // avoid collisions
        $token = hash("sha256", SiteController::GenerateToken(32));
        try {
            $session_id = $this->model->newSession($id_user, $series, $token, $expired);
        } catch (\PDOException $e) {
            return false;
        }
        setcookie("login", $series . "-" . $token, [
            'expires' => $expired->getTimestamp(),
            'path' => '/',
            'domain' => '',
            'secure' => $https,
            'httponly' => $httponly,
            'samesite' => $samesite,
        ]);
        return $this->model->getSession($series);
    }

    public function UpdateLoginCookie($series, $expired)
    {
        require '../config.php';
        $token = hash("sha256", SiteController::GenerateToken(32));
        $this->model->setSessionToken($series, $token);
        setcookie("login", $series . "-" . $token, [
            'expires' => $expired->getTimestamp(),
            'path' => '/',
            'domain' => '',
            'secure' => $https,
            'httponly' => $httponly,
            'samesite' => $samesite,
        ]);
        return $token;
    }

    public function CookieLogin()
    {
        if (isset($_SESSION['user'])) {
            return;
        }

        if (!isset($_COOKIE["login"])) {
            return;
        }

        $_COOKIE["login"] = preg_replace('/[^a-z0-9-]/', '', strtolower($_COOKIE["login"]));
        list($series, $token) = explode("-", $_COOKIE["login"], 2);
        $series = substr($series, 0, 12);
        $session = $this->model->getSession($series);
        if (!is_int($session)) {
            if ($session->token === $token) {
                date_default_timezone_set("UTC");
                $now = new \DateTime();
                if ($now <= $session->expired) {
                    $token = $this->UpdateLoginCookie($series, $session->expired);
                    $session->token = $token;
                    $_SESSION['user'] = $session;
                } else // login cookie expired and must be deleted
                {
                    $this->logout($series);
                }
            } else {
                $this->model->delAllSession($session->id);
                $this->model->warnUser($session->id);
            }
        } else {
            $this->logout($series);
        }
    }

    public function viewActionGet($id = "", $lang = "", $event = "")
    {
	    $data = $this->model->getBlocksTable(0);
        $this->view->render('index', 'index', array(
          'TITLE' => 'Level',
          'data_blocks' => $data,
          'lang' => $lang
        ));
    }

    public function indexActionGet($id = "", $lang = "", $event = "")
    {
        header("Location: /site/login");
    }

    public function loginActionGet($id = "", $lang = "", $event = "")
    {
        $this->view->render('login', null, array(
            'TITLE' => 'Login',
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

        if (empty($_POST["username"])
            or empty($_POST["email"])
            or empty($_POST["password1"])
            or empty($_POST["password2"])) {
            $error .= "All fields are required<br>\n";
        }

        if ($_POST["password1"] != $_POST["password2"]) {
            $error .= "Passwords does not match<br>\n";
        }

        if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
            $error .= "Email is not valid<br>\n";
        }

        if(!preg_match('/^[\p{L}][\p{L}_\d-]{3,32}$/Du', $_POST["username"])){
            $error .= "Username is not valid.<br>\n";
            $error .= "It must be 4-32 character long and contain only letters numbers and underscores and start with letter.\n";
        }

        $remember_me = isset($_POST['remember']);
        date_default_timezone_set("UTC");
        $now = new \DateTime();

        if (!$error) {
            try {
                $user_id = $this->model->registerUser($_POST["username"], $_POST["email"], $_POST["password1"], $now);
            } catch (\PDOException $e) {
                $err = intval($e->errorInfo[0]);
                switch ($err)
                {
                    case 23000:
                        $error .= "This username or email already exists in database! Try to <a href='/site/login'>login?</a><br>\n";
                        break;
                    default:
                        $error .= "SQLSTATE = " . $err."<br>\n";;
                }
            }
        }

        if ($error) {
            $this->view->render('register', null, array(
                'TITLE' => 'Register',
                'lang' => $lang,
                'error' => $error,
                'last_post' => $_POST,
                'langs' => $this->model->langs,
            ));
            return;
        } else {
            if (!$remember_me) {
                $_SESSION['user'] = new User(
                    ['id' => $user_id,
                    'name' => $_POST["username"],
                    'email' => $_POST["email"],
                    'registered_at' => $now,
                    'can_edit' => 1,
                    'warn_cookie_stolen' => 0]);
            } else {
                $_SESSION['user'] = $this->GenerateLoginCookie($user_id);
            }
            header("Location: /");
        }
    }

    public function loginActionPost($id = "", $lang = "", $event = "")
    {
        if (empty($_POST["username"])
            or empty($_POST["password"])) {
            $this->loginActionGet($id, $lang, $event);
            exit();
        }

        foreach ($_POST as $key => $value) {
            $_POST[$key] = htmlentities($value);
        }

        $remember_me = isset($_POST['remember']);

        try {
            $user_from_db = $this->model->getUser($_POST["username"]);
        } catch (\PDOException $e) {
            return;
        }

        if (!is_a($user_from_db, 'Lain\User') or !password_verify($_POST["password"], $user_from_db->password)) {
            $error = "The username or password you have entered is invalid.";
            unset($user_from_db->password);
        }

        if (isset($error)) {
            $this->view->render('login', null, array(
                'TITLE' => 'Login',
                'last_username' => $_POST["username"],
                'error' => $error,
                'lang' => $lang,
                'langs' => $this->model->langs,
            ));
        } else { // All ok

            $error = false;
            if ($remember_me) {
                $_SESSION['user'] = $this->GenerateLoginCookie($user_from_db->id);
            } else {
                $_SESSION['user'] = $user_from_db;
            }
            header("Location: /");
        }
    }

    private function logout($series = null)
    {
        if (!is_null($series)) {
            try {
                $this->model->delSession($series);
            } catch (\Exception $e) {}
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
