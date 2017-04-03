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
		$dept = models\Department::first(['id' => $id, 'company_id' => $this->company->id]);

		if(!$dept){ $this->redirect('/404');}
		$view->set('dept', $dept);

		if(RM::post('delete')){
			$project = models\Project::first(['id' => RM::post('delete'), 'department_id' => $id]);

			if($project){ $project->delete(); }
		}

		if(RM::post('mark_urgent')){
			$project = models\Project::first(['id' => RM::post('mark_urgent'), 'department_id' => $id]);

			if($project){
				$project->status = 'urgent';
				$project->save();
			}
			$this->redirect(URL);
		}

		if(RM::post('mark_incomplete')){
			$project = models\Project::first(['id' => RM::post('mark_incomplete'), 'department_id' => $id]);

			if($project){
				$project->status = 'normal';
				$project->save();
			}
			$this->redirect(URL);
		}

		if(RM::post('mark_complete')){
			$project = models\Project::first(['id' => RM::post('mark_complete'), 'department_id' => $id]);

			if($project){
				$project->status = 'completed';
				$project->save();
			}
			$this->redirect(URL);
		}

		$token = RM::post('token', '');
		if(RM::post('action') == 'edit_project' && $this->verifyToken($token)){

			$p = models\Project::first(["id" => RM::post('edit_project_id')]);
			
			$p->name = RM::post('name');
			$p->team = RM::post('team');
			$p->head = RM::post('head');
			$p->details = RM::post('details');
			$p->status = RM::post('status');
			$p->due_date = RM::post('due_date');
			$p->department_id = $id;
			$p->created_by = $this->user->id;

			if($p->validate()){
				$p->save();
			}
			$this->redirect(URL);
		}

		if(RM::post('action') == 'create_project' && $this->verifyToken($token)){

			$p = new models\Project([
				"name" => RM::post('name'),
				"team" => RM::post('team'),
				"head" => RM::post('head'),
				"details" => RM::post('details'),
				"status" => strtolower(RM::post('status')),
				"due_date" => RM::post('due_date'),
				"department_id" => $id,
				"created_by" => $this->user->id
				]);
			if($p->validate()){
				$p->save();
			}

			$this->redirect(URL);
		}

		switch (RM::get('phase')) {
			case 'all':
				$projects = models\Project::all([
					'department_id' => ['$in' => [$id]]
					],[], 'created', 'desc');
				$view->set('all', 1);
				break;

			case 'completed':
				$projects = models\Project::all([
					'department_id' => ['$in' => [$id]],
					'status' => 'completed'
					],[], 'created', 'desc');
				$view->set('completed', 1);
				break;
			
			default:
				$projects = models\Project::all([
					'department_id' => ['$in' => [$id]],
					'status' => ['$ne' => 'completed']
					],[], 'created', 'desc');
				$view->set('inprogress', 1);
				break;
		}

		$c_complete = models\Project::count(['department_id' => ['$in' => [$id]],'status' => 'completed']);
		$c_inprogress = models\Project::count(['department_id' => ['$in' => [$id]],'status' => ['$ne' => 'completed']]);
		$c_all = $c_complete+$c_inprogress;

		$view->set('projects', $projects)->set('c_complete', $c_complete)->set('c_inprogress', $c_inprogress)->set('c_all', $c_all);
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