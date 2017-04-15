<?php

/**
 * The Default Example Controller Class
 *
 * @author Shreyansh Goel
 */
use Shared\Controller as Controller;
use Framework\RequestMethods as RM;

class Project extends Controller {

    /**
	* @before _secure
	*/
	public function info($id = ''){
		$view = $this->getActionView();

		$d_ids = array_keys(models\Department::all(['company_id' => $this->company->id], ['_id']));
		$project = models\Project::first(['id' => $id, 'department_id' => ['$in' => $d_ids]]);

		if (!$project) {
			$this->_404();
		}

		switch (RM::post('action')) {
			case 'edit_project':

				$project->name = RM::post('name');
				$project->team = RM::post('team');
				$project->head = RM::post('head');
				$project->details = RM::post('details');
				$project->status = strtolower(RM::post('status'));
				$project->due_date = RM::post('due_date');
				$project->created_by = $this->user->id;

				if($project->validate()){
					$project->save();
				}
				$this->redirect(URL);//so that on reload the form does not resubmit
				break;
			
			default:
				# code...
				break;
		}

		$view->set('project', $project);
	}

	/**
	* @before _secure
	*/
	public function tasks($id = ''){
		$view = $this->getActionView();

		$d_ids = array_keys(models\Department::all(['company_id' => $this->company->id], ['_id']));
		$project = models\Project::first(['id' => $id, 'department_id' => ['$in' => $d_ids]]);

		if(!$project){ $this->_404();}

		$view->set('project', $project);
	} 
}
