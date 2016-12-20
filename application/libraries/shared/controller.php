<?php

/**
 * Subclass the Controller class within our application.
 *
 * @author Faizan Ayubi
 */

namespace Shared {

    use Framework\Events as Events;
    use Framework\Router as Router;
    use Framework\Registry as Registry;
    use Framework\RequestMethods as RequestMethods;
    use Aws\S3\S3Client;

    class Controller extends \Framework\Controller {

        /**
         * @readwrite
         */
        protected $_user;
        
        public function seo($params = array()) {
            $seo = Registry::get("seo");
            foreach ($params as $key => $value) {
                $property = "set" . ucfirst($key);
                $seo->$property($value);
            }
            $this->layoutView->set("seo", $seo);
        }

        /**
         * @protected
         */
        public function _secure() {
            $user = $this->getUser();
            if (!$user) {
                $this->redirect("/");
            }
        }

        /**
         * @protected
         */
        public function _session() {
            $user = $this->getUser();
            if ($user) {
                $this->redirect('/users/dashboard');
            }
        }

        public function redirect($url) {
            $this->noview();
            header("Location: {$url}");
            exit();
        }

        public function _404($msg = "Invalid Request") {
            $this->noview();
            throw new \Framework\Router\Exception\Controller($msg);
        }

        public function setUser($user) {
            $session = Registry::get("session");
            if ($user) {
                $session->set("user", $user->id);
            } else {
                $session->erase("user");
            }
            $this->_user = $user;
            return $this;
        }

        protected function log($message = "") {
            $logfile = APP_PATH . "/logs/" . date("Y-m-d") . ".txt";
            $new = file_exists($logfile) ? false : true;
            if ($handle = fopen($logfile, 'a')) {
                $timestamp = strftime("%Y-%m-%d %H:%M:%S", time());
                $content = "[{$timestamp}]{$message}\n";
                fwrite($handle, $content);
                fclose($handle);
                if ($new) {
                    chmod($logfile, 0777);
                }
            }
        }
        
        public function logout() {
            $this->setUser(false);
            session_destroy();
            self::redirect("/");
        }
        
        public function noview() {
            $this->willRenderLayoutView = false;
            $this->willRenderActionView = false;
        }

        public function JSONview() {
            $this->willRenderLayoutView = false;
            $this->defaultExtension = "json";
        }
        
        /**
         * The method checks whether a file has been uploaded. If it has, the method attempts to move the file to a permanent location.
         * @param string $name
         * @param string $type files or images
         */
        protected function _upload($name, $type = "images", $opts = []) {
            if (isset($_FILES[$name])) {

                $file = $_FILES[$name];
                $path = APP_PATH . "/public/assets/uploads/{$type}/";
                $extension = pathinfo($file["name"], PATHINFO_EXTENSION);
                $size = $file["size"];

                if (isset($opts['extension'])) {
                    $ex = $opts['extension'];

                    if (!preg_match("/^".$ex."$/", $extension)) {
                        echo "extension doesnt match";
                        return false;
                    }
                }

                 if (isset($opts['size'])) {
                    $s = $opts['size'];

                    if ($size > $s) {
                        echo "size is big";
                        return false;
                    }
                }

                if (isset($opts['name'])) {
                    $filename = $opts['name'] . ".{$extension}";
                } else {
                    $filename = uniqid() . ".{$extension}";
                }

                if(move_uploaded_file($file["tmp_name"], $path . $filename)){

                    return $extension;
                }
            }
            echo "something went wrong";
            return FALSE;
        }

        public function __construct($options = array()) {
            parent::__construct($options);

            Services\Db::connect();

            // schedule: load user from session           
            Events::add("framework.router.beforehooks.before", function($name, $parameters) {
                $session = Registry::get("session");
                $controller = Registry::get("controller");
                $user = $session->get("user");
                if ($user) {
                    $controller->user = \models\User::first(array("id = ?" => $user));
                }
            });

            // schedule: save user to session
            Events::add("framework.router.afterhooks.after", function($name, $parameters) {
                $session = Registry::get("session");
                $controller = Registry::get("controller");
                if ($controller->user) {
                    $session->set("user", $controller->user->id);
                }

                // Set Flash Message to the Action View
                $flashMessage =  $session->get('$flashMessage', null);
                if ($flashMessage) {
                    $session->erase('$flashMessage');
                    $controller->actionView->set('message', $flashMessage);
                }
            });
        }

        public function __destruct() {
            $view = $this->layoutView;
            if ($view && !$view->get('seo')) {
                $view->set('seo', \Framework\Registry::get("seo"));
            }
            parent::__destruct();
        }

        /**
         * Checks whether the user is set and then assign it to both the layout and action views.
         */
        public function render() {
            /* if the user and view(s) are defined, 
             * assign the user session to the view(s)
             */
            if ($this->user) {
                if ($this->actionView) {
                    $key = "user";
                    if ($this->actionView->get($key, false)) {
                        $key = "__user";
                    }
                    $this->actionView->set($key, $this->user);
                }
                if ($this->layoutView) {
                    $key = "user";
                    if ($this->layoutView->get($key, false)) {
                        $key = "__user";
                    }
                    $this->layoutView->set($key, $this->user);
                }
            }
           
            parent::render();
        }

        /**
         * @protected
         */

        public function _csrfToken() {
            $session = Registry::get("session");
        
            $csrf_token = \Framework\StringMethods::uniqueRandomString(44);
            $session->set('Auth\Request:$token', $csrf_token);

            if ($this->actionView) {
                $this->actionView->set('__token', $csrf_token);
            }
        }

        public function verifyToken($token = null) {
            $session = Registry::get("session");
            $csrf = $session->get('Auth\Request:$token');

            if ($csrf && $csrf === $token) {
                return true;
            }
            return false;
        }


    }

}
