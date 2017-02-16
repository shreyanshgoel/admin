<?php

/**
 * The Default Example Controller Class
 *
 * @author Shreyansh Goel
 */
use Shared\Controller as Controller;
use Framework\RequestMethods as RM;
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
		$token = RM::post('token', '');

		if(RM::post('register') && $this->verifyToken($token)){
			$pass = \Framework\StringMethods::uniqueRandomString(10);
			$user = new models\User([
	            "full_name" => RM::post("full_name"),
	            "mobile" => RM::post("mobile"),
	            "email" => RM::post("email"),
	            "password" => sha1($pass),
	            "email_confirm" => true,
	            "live" => true
	        ]);

	        $exist = models\User::all(['email = ?' => RM::post("email")]);

			if (empty($exist)){

					if($user->validate() && !empty(RM::post('company'))){
						$user->save();
						
						$git_folder_name = uniqid();
						$company = new models\Company([
				        	"name" => RM::post('company'),
				        	"founder_id" => $user->id,
				        	"git_folder_name" => $git_folder_name,
				            "live" => true
				        ]);
						$company->save();
						shell_exec('mkdir /var/www/admin/public/assets/uploads/bare-repositories/' . $git_folder_name);
						shell_exec('mkdir /var/www/admin/public/assets/uploads/clone-repositories/' . $git_folder_name);

						$user->company_ids = [$company->id];
						$user->designations = [$company->id => ["founder"]];
						$user->permissions = [$company->id => 1];

						$user->save();

						//mail te password to the user

						$this->redirect("/account/register_success/$pass");

					}else echo "<script>alert('validation not good')</script>";

			}else{
				$added = models\User::all([
					'email' => RM::post("email"),
					'live' => false
					]);

				if($added){

					$added->name = RM::post('full_name');
					$added->mobile = RM::post('mobile');
					$added->password = sha1($pass);

					$added->live = true;

					$this->redirect("/account/register_success/$pass");
					$added->save();
				}else{
					echo "<script>alert('User exists')</script>";
				}
			} 
		}
	}

	/**
	* @before _session
	* @after _csrfToken
	*/
	public function login(){
		$this->setLayout("layouts/empty");
		$token = RM::post('token', '');

		if(RM::post('login') && $this->verifyToken($token)){

			$email = RM::post("email");
	        $pass = sha1(RM::post("password"));
	        
	        $login_e = false;
	        if (empty($email)) $login_e = "Empty email";  
	        if (empty($pass)) $login_e = "Empty password";
	        
	        if (!$login_e){
	            $user = models\User::first([
	                "email = ?" => $email,
	                "live = ?" => true
	            ]);
	            if (!empty($user)){
	            	if($user->password == $pass){
		                $this->user = $user;
		                $this->redirect('/users/dashboard');
		            }else echo "<script>alert('email and password do not match')</script>";    	
	            }else echo "<script>alert('email does not exist')</script>";
	        }else echo "<script>alert($login_e)</script>";
		}
	}
}