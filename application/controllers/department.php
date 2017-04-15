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
			if ($project) { 
				$project->delete(); 
			}
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

		$tab = 'projects';

		$token = RM::post('token', '');
		if($this->verifyToken($token)){
			switch (RM::post('action')) {
				case 'create_project':
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
					break;

				case 'edit_project':
					$p = models\Project::first(["id" => RM::post('edit_project_id'), 'department_id' => ['$in' => [$id]]]);
					if($p){
						$p->name = RM::post('name');
						$p->team = RM::post('team');
						$p->head = RM::post('head');
						$p->details = RM::post('details');
						$p->status = strtolower(RM::post('status'));
						$p->due_date = RM::post('due_date');
						$p->created_by = $this->user->id;

						if($p->validate()){
							$p->save();
						}
					}
					$this->redirect(URL);
					break;

				case 'create_folder':
					$exist = models\Folder::first([
						'name' => RM::post('name'),
						'department_id' => $id,
						'user_id' => $this->user->id
						]);
					if(!$exist){
						$f = new models\Folder([
							'name' => RM::post('name'),
							'department_id' => $id,
							'user_id' => $this->user->id
							]);
						if($f->validate()){
							$f->save();
							$tab = 'files';
						}
					}
					break;

				case 'upload':
					$files = $_FILES['file_upload'];
					for ($i=0; $i < count($files['name']); $i++) { 
						$multiple['name']=$files['name'][$i];
						$multiple['size']=$files['size'][$i];
						$multiple['tmp_name']=$files['tmp_name'][$i];

						$temp = $this->_upload('', 'files', ['size' => '6000000'], $multiple);
						$name = explode('.', $multiple['name']);
						if($temp){
							$j = '';
							while (1) {
								$testname = $name[0].$j.'.'.$name[1];
								$exist = models\File::first([
									'name' => $testname,
									'department_id' => $id,
									'user_id' => $this->user->id
									]);
								if(!$exist){
									break;
								}
								$j = (int) $j;
								$j++;
							}

							$new = new models\File([
								'server_name' => $temp,
								'name' =>  $testname,
								'department_id' => $id,
								'user_id' => $this->user->id
								]);
							$new->save();
						}
					}
					$tab = 'files';
					break;
			}
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
		$home_folders = models\Folder::all(['department_id' => $id, 'user_id' => $this->user->id, 'parent_folder_id' => null]);
		$home_files = models\File::all(['department_id' => $id, 'user_id' => $this->user->id, 'folder_id' => null]);
		$view->set([
			'projects' => $projects,
			'c_complete' => $c_complete,
			'c_inprogress' => $c_inprogress,
			'c_all' => $c_all,
			'home_folders' => $home_folders,
			'home_files' => $home_files,
			'tab' => $tab
			]);
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