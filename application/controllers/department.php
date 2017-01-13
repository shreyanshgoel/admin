<?php

/**
 * The Default Example Controller Class
 *
 * @author Shreyansh Goel
 */
use Shared\Controller as Controller;
use Framework\RequestMethods as RequestMethods;

class Department extends Controller {

	/**
	* @before _secure
	*/
	public function info($id = ''){

		$view = $this->getActionView();

		$dept = models\Department::first([
			'id' => $id
			]);

		if(!$dept){
			$this->redirect('/404');
		}

		if(RequestMethods::post('action') == 'create_project'){

			$p = new models\Project([
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
	public function tasks(){

	}


}