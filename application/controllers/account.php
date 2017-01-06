<?php

/**
 * The Default Example Controller Class
 *
 * @author Shreyansh Goel
 */
use Shared\Controller as Controller;
use Framework\RequestMethods as RequestMethods;
use Framework\Registry as Registry;

class Account extends Controller {

	/**
	* @before _session
	*/

	public function register_success(){

		$this->setLayout("layouts/empty");

	}

	/**
	* @before _session
	* @after _csrfToken
	*/

	public function register(){

		$this->setLayout("layouts/empty");
		
		$token = RequestMethods::post('token', '');

		if(RequestMethods::post('register') && $this->verifyToken($token)){
			
			$pass = \Framework\StringMethods::uniqueRandomString(10);
			$user = new models\User(array(
	            "full_name" => RequestMethods::post("full_name"),
	            "mobile" => RequestMethods::post("mobile"),
	            "email" => RequestMethods::post("email"),
	            "password" => sha1($pass),
	            "email_confirm" => true,
	            "live" => true
	        ));
			$exist = models\User::all(array(
				'email = ?' => RequestMethods::post("email")
				));

			if (empty($exist)){

					if($user->validate()){
						
						$user->save();

						//mail te password to the user

						$this->redirect("/account/register_success/$pass");

					}else{

						echo "<script>alert('validation not good')</script>";
					}

			}else{
				echo "<script>alert('User exists')</script>";
			}
		}

	}

	/**
	* @before _session
	* @after _csrfToken
	*/

	public function login(){
		$this->setLayout("layouts/empty");

		$token = RequestMethods::post('token', '');

		if(RequestMethods::post('login')){

			echo "string";

			$email = RequestMethods::post("email");
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
	                "email = ?" => $email,
	                "live = ?" => true
	            ));

	            if (!empty($user)){

	            	if($user->password == $pass){
	            	
		                $this->user = $user;
		                $this->redirect('/users/dashboard');
		            
		            }else{

		        		echo "<script>alert('email and password do not match')</script>";    	
		            }
		            	            
	            }else{
	            
	                echo "<script>alert('email does not exist')</script>";
	            } 
	        
	        }else{

	        	echo "<script>alert($login_e)</script>";
	        }
		}
		
	}
}