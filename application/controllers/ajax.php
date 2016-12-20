<?php

/**
 * The Default Example Controller Class
 *
 * @author Shreyansh Goel
 */
use Shared\Controller as Controller;
use Framework\RequestMethods as RequestMethods;
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
    public function calendar_save($id = -1) {
        
        $start_date = RequestMethods::post('date');

        $title = RequestMethods::post('title');

        $color = RequestMethods::post('color');

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
        
        $start_date = RequestMethods::post('date');

        $title = RequestMethods::post('title');

        $color = RequestMethods::post('color');

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
            $e['start'] = $c->start_date;

            array_push($events, $e);


        }

        echo json_encode($events);


    }

    /**
    * @before _secure
    */
    public function check_email() {

        $view = $this->getActionView();

        $check = models\User::all(array(
            'email = ?' => RequestMethods::post('email')
            ));

        if(!empty($check)){

            $view->set(array(1));

        }else{

            $view->set(array(0));
        }
        


    }
}