<?php

/**
 * The Default Example Controller Class
 *
 * @author Shreyansh Goel
 */
use Shared\Controller as Controller;
use Framework\RequestMethods as RM;

class Git extends Controller {

	public function index(){
		$view = $this->getActionView();

		if(RM::post('action') == 'create_repo'){

			$name = RM::post('name');
			$repo = new models\Repository([
				'name' => $name,
				'type' => RM::post('type'),
				'user_id' => $this->user->id,
				'company_id' => $this->company->id
				]);
			if($repo->validate()){
				$repo->save();

				shell_exec('mkdir ' . APP_PATH . '/public/assets/uploads/bare-repositories/' . $this->company->git_folder_name . '/' . $name . '.git');
				shell_exec('cd ' . APP_PATH . '/public/assets/uploads/bare-repositories/' . $this->company->git_folder_name . '/' . $name . '.git; git init --bare; git update-info-server; echo "[http]
			receivepack = true" >> config');
			}else{
				echo "nopes";
			}
		}

		$all_repos = models\Repository::all([
			'user_id' => $this->user->id
			]);

		$view->set('all_repos', $all_repos);
	}
}