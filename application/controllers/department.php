<?php

/**
 * The Default Example Controller Class
 *
 * @author Shreyansh Goel
 */
use Shared\Controller as Controller;
use Framework\RequestMethods as RM;

class Department extends Controller {

	/**
	* @before _secure
	*/
	public function info($id = ''){
		$view = $this->getActionView();
		$dept = models\Department::first([
			'id' => $id,
			'company_id' => $this->company->id
			]);

		if(!$dept){
			$this->redirect('/404');
		}

		if(RM::post('action') == 'create_project'){

			$p = new models\Project([
				"name" => RM::post('name'),
				"team" => RM::post('team'),
				"head" => RM::post('head'),
				"details" => RM::post('details'),
				"department_id" => $id,
				"created_by" => $this->user->id
				]);
			if($p->validate()){
				$p->save();
			}
		}
		$projects = models\Project::all([
			'department_id' => $id
			]);

		$view->set('projects', $projects);
	}

	/**
	* @before _secure
	*/
	public function project($id = ''){
		$view = $this->getActionView();
	}

	/**
	* @before _secure
	*/
	public function tasks(){

	}
}