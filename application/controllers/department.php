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
	* @after _csrfToken
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

		if(RM::post('delete')){
			$project = models\Project::first([
				'id' => RM::post('delete'),
				'department_id' => $id,
				]);

			if($project){
				$project->delete();
			}

		}

		$token = RM::post('token', '');
		if(RM::post('action') == 'create_project' && $this->verifyToken($token)){

			$p = new models\Project([
				"name" => RM::post('name'),
				"team" => RM::post('team'),
				"head" => RM::post('head'),
				"details" => RM::post('details'),
				"status" => [strtolower(RM::post('status'))],
				"due_date" => RM::post('due_date'),
				"department_id" => $id,
				"created_by" => $this->user->id
				]);
			if($p->validate()){
				$p->save();
			}
		}

		switch (RM::post('phase')) {
			case 'all':
				$projects = models\Project::all([
					'department_id' => $id
					],[], 'created', 'desc');
				$view->set('all', 1);
				break;

			case 'completed':
				$projects = models\Project::all([
					'department_id' => $id,
					'status' => 'completed'
					],[], 'created', 'desc');
				$view->set('completed', 1);
				break;
			
			default:
				$projects = models\Project::all([
					'department_id' => $id,
					'status' => ['$ne' => 'completed']
					]);
				$view->set('inprogress', 1);
				break;
		}

		$view->set('projects', $projects);
	}

	/**
	* @before _secure
	*/
	public function project($type = '', $id = ''){
		$view = $this->noview();
		$dept = models\Department::first([
			'id' => $id,
			'company_id' => $this->company->id
			]);
		$project = models\Project::first(['id' => $id, '']);
		if($type == 'delete' && $project){
			$project->delete();
		}else{
			$this->_404();
		}

	}
}