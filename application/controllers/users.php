<?php

/**
 * The Default Example Controller Class
 *
 * @author Shreyansh Goel
 */
use Shared\Controller as Controller;
use Framework\RequestMethods as RequestMethods;

class Users extends Controller {

	/**
	* @before _session
    * @after _csrfToken
	*/
	public function register(){
		$this->setLayout("layouts/empty");

		$token = RequestMethods::post('token', '');
		
		if(!$this->user){
			if(RequestMethods::post('register') && $this->verifyToken($token)){

				$pass = RequestMethods::post("password");
				$cpass = RequestMethods::post("confirm_password");

				$user = new models\User(array(
		            "full_name" => RequestMethods::post("full_name"),
		            "username" => RequestMethods::post("username"),
		            "email" => RequestMethods::post("email"),
		            "password" => sha1($pass),
		            "live" => true
		        ));
				$exist = models\User::all(array(
					'username = ?' => RequestMethods::post("username")
					));

				if (empty($exist)){
				
					if($pass == $cpass){

						if($user->validate()){
							
							$user->save();

							$login = models\User::first(array(
								'username = ?' => RequestMethods::post("username")
								));

							$this->user = $login;

							header("Location: /users/dashboard");

						}else{

							echo "<script>alert('validation not good')</script>";
						}

					}else{
						echo "<script>alert('Passwords do not match')</script>";
					}
				
				}else{
					echo "<script>alert('User exists')</script>";
				}
			}
		}else{

				header("Location: /");
		}

	}


	/**
	* @before _session    * @after _csrfToken
	*/

	public function login(){
		
		$this->setLayout("layouts/empty");

		$token = RequestMethods::post('token', '');

		if(!$this->user){

			if(RequestMethods::post('login') && $this->verifyToken($token)){

				$email = RequestMethods::post("username");
		        $pass = sha1(RequestMethods::post("password"));
		        
		        $login_e = false;
		        
		        if (empty($email)){
		            
		            $login_e = "Empty email";
		        }
		        
		        if (empty($pass)){

		         	$login_e = "Empty password";
		        }
		        
		        if (!$login_e){

		            $user = models\User::first(array(
		                "username = ?" => $email,
		                "live = ?" => true
		            ));

		            try_again:

		            if (!empty($user)){

		            	if($user->password == $pass){
		            	
			                $this->user = $user;
			                header('Location: /users/dashboard');
			            }else{
			            
			                echo "<script>alert('email and password do not match')</script>";
			            }

			        }else{

			        	$user = models\User::first(array(
		                "email = ?" => $email,
		                "live = ?" => true
			            ));

			            if(!empty($user)){

			            	goto try_again;
			            }
		            
		                echo "<script>alert('email does not exist')</script>";
		            } 
		        
		        }else{

		        	echo "<script>alert($login_e)</script>";
		        }
			}
		}else{

				header('Location: /');
		}

		
	}

	/**
	* @before _secure
	*/
    public function dashboard() {

    	$layoutView = $this->getLayoutView();
    	$layoutView->set("seo", Framework\Registry::get("seo"));

    	$view = $this->getActionView();

    	
    }

    /**
	* @before _secure
	*/
    public function taskboard() {

    	$layoutView = $this->getLayoutView();
    	$layoutView->set("seo", Framework\Registry::get("seo"));

    	$view = $this->getActionView();

    	
    }

    /**
	* @before _secure
	*/
    public function projects() {

    	$layoutView = $this->getLayoutView();
    	$layoutView->set("seo", Framework\Registry::get("seo"));

    	$view = $this->getActionView();

    	
    }

    /**
	* @before 
	*/
    public function contact_list() {

    	$layoutView = $this->getLayoutView();
    	$layoutView->set("seo", Framework\Registry::get("seo"));

    	$view = $this->getActionView();

    	
    }

    /**
	* @before _secure
	*/
    public function calendar() {

    	$layoutView = $this->getLayoutView();
    	$layoutView->set("seo", Framework\Registry::get("seo"));

    	$view = $this->getActionView();

    	$layoutView->set('calendar_tab', 1);

    	
    }

    /**
	* @before _secure
	*/
    public function profile() {

    	$layoutView = $this->getLayoutView();
    	$layoutView->set("seo", Framework\Registry::get("seo"));

    	$view = $this->getActionView();

    	$layoutView->set('calendar_tab', 1);

    	
    }


	/**
	* @before _secure
	*/
    public function logout(){
        
        $this->setUser(false);

        header("Location: /");
    }

}
