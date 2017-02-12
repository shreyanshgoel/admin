<?php

/**
 * The Default Example Controller Class
 *
 * @author Shreyansh Goel
 */
use Shared\Controller as Controller;
use Framework\RequestMethods as RM;

class Hey extends Controller {

	public function index(){
		$view = $this->getActionView();

		if(RM::post('action') == 'create_repo'){

			$bare = uniqid();
			$key = Framework\StringMethods::uniqueRandomString(5);

			$repo = new models\Repository([
				'name' => RM::post('name'),
				'bare_name' => $bare,
				'key' => $key,
				'type' => RM::post('type'),
				'user_id' => $this->user->id
				]);
			if($repo->validate()){
				$repo->save();

				shell_exec('mkdir /var/www/admin/public/assets/uploads/bare-repositories/' . $bare . '.git');
				shell_exec('cd /var/www/admin/public/assets/uploads/bare-repositories/' . $bare . '.git; git init --bare; git update-info-server; echo "[http]
			receivepack = true" >> config');
			}
		}

		$all_repos = models\Repository::all([
			'user_id' => $this->user->id
			]);

		$view->set('all_repos', $all_repos);
	}

	public function hey_path($key = '', $repo_name = ''){
		$this->noview();
		$curl = new Curl\Curl;
		$response = $curl->get('http://clutchstart.tk/assets/uploads/bare-repositories/589af446ee700.git/');
		echo $response;
		$curl->close();
		
	}
}