<?php

/**
 * The Default Example Controller Class
 *
 * @author Shreyansh Goel
 */
use Shared\Controller as Controller;

class Project extends Controller {

    /**
	* @before _secure
	*/
	public function info($id = ''){
		$view = $this->getActionView();

		$d_ids = array_keys(models\Department::all(['company_id' => $this->company->id], ['_id']));
		$project = models\Project::first(['id' => $id, 'department_id' => ['$in' => $d_ids]]);

		if(!$project){ $this->_404();}

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
