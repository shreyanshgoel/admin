<?php

/**
 * The Default Example Controller Class
 *
 * @author Shreyansh Goel
 */
use Shared\Controller as Controller;
use Framework\RequestMethods as RM;
use Framework\Registry as Registry;

class Ajax extends Controller {

    /**
	* @before _secure
	*/
    public function states() {
    	$view = $this->getActionView();
    	$states = models\State::all();

    	$view->set($states);
    }

    /**
    * @before _secure
    */
    public function edit_project() {
        $this->JSONview();
        $view = $this->getActionView();
        
        $d_ids = array_keys(models\Department::all(['company_id' => $this->company->id], ['_id']));
        $project = models\Project::first(['id' => RM::post('project_id'), 'department_id' => ['$in' => $d_ids]]);

        if ($project) {
            $view->set('project',$project);
        }
    }

    /**
    * @before _secure
    */
    public function edit_department() {
        $this->JSONview();
        $view = $this->getActionView();
        
        $dept = models\Department::first(['id' => RM::post('department_id'), 'company_id' => $this->company->id]);

        if ($dept) {
            $view->set('dept',$dept);
        }
    }

    /**
    * @before _secure
    */
    public function calendar_save($id = -1) {
        $start_date = RM::post('date');
        $title = RM::post('title');
        $color = RM::post('color');

        $calendar = new models\Calendar(array(
            'title' => $title,
            'start_date' => $start_date,
            'color' => $color,
            'user_id' => $this->user->id
            ));

        $calendar->save();
    }

    /**
    * @before _secure
    */
    public function calendar_edit($id = -1) {
        $start_date = RM::post('date');
        $title = RM::post('title');
        $color = RM::post('color');

        $calendar = new models\Calendar(array(
            'title' => $title,
            'start_date' => $start_date,
            'color' => $color,
            'user_id' => $this->user->id
            ));

        $calendar->save();
    }

    /**
    * @before _secure
    */
    public function calendar_events() {
        $view = $this->noview();
        $calendar = models\Calendar::all(array(
            'user_id = ?' => $this->user->id
            ));

        $events = array();
        foreach($calendar as $c){
            $e = array();
            $e['id'] = $c->id;
            $e['color'] = $c->color;
            $e['title'] = $c->title;
            $e['start'] = $c->start_date->format('Y-m-d');

            array_push($events, $e);
        }
        echo json_encode($events);
    }

    /**
    * @before _secure
    */
    public function calendar_date_change(){

        $date = RM::post('date');
        $id = RM::post('id');

        $calendar = models\Calendar::first([
            'id' => $id,
            'user_id' => $this->user->id
            ]);

        $calendar->start_date = $date;
        $calendar->save();
    }

    /**
    * @before _secure
    */
    public function check_email() {
        $view = $this->getActionView();

        $check = models\User::all(array(
            'email = ?' => RM::post('email')
            ));
        if(!empty($check)){
            $view->set(array(1));
        }else{
            $view->set(array(0));
        }
    }
}