<?php

/**
 * The Default Example Controller Class
 *
 * @author Shreyansh Goel
 */
use Shared\Controller as Controller;
use Framework\RequestMethods as RequestMethods;

class Users extends Controller {

	/**
	* @before _secure
	*/
    public function dashboard() {

    	$layoutView = $this->getLayoutView();
    	$layoutView->set("seo", Framework\Registry::get("seo"));

    	$view = $this->getActionView();

    	
    }

    /**
	* @before _secure
	*/
    public function taskboard() {

    	$layoutView = $this->getLayoutView();
    	$layoutView->set("seo", Framework\Registry::get("seo"));

    	$view = $this->getActionView();

    	
    }

    /**
	* @before _secure
	*/
    public function projects() {

    	$layoutView = $this->getLayoutView();
    	$layoutView->set("seo", Framework\Registry::get("seo"));

    	$view = $this->getActionView();

    	
    }

    /**
	* @before 
	*/
    public function contact_list() {

    	$layoutView = $this->getLayoutView();
    	$layoutView->set("seo", Framework\Registry::get("seo"));

    	$view = $this->getActionView();

    	
    }

    /**
	* @before _secure
	*/
    public function calendar() {

    	$layoutView = $this->getLayoutView();
    	$layoutView->set("seo", Framework\Registry::get("seo"));

    	$view = $this->getActionView();

    	$layoutView->set('calendar_tab', 1);

    	
    }

    /**
	* @before _secure
	*/
    public function profile() {

    	$layoutView = $this->getLayoutView();
    	$layoutView->set("seo", Framework\Registry::get("seo"));

    	$view = $this->getActionView();

    	$layoutView->set('calendar_tab', 1);

    	
    }

    /**
    * @before _secure
    */
    public function notes($id = '-1'){

        $view = $this->getActionView();

        $all_notes = models\Note::all(array(
            'user_id = ?' => $this->user->id
            ));

        $view->set('all_notes', $all_notes);

        switch ($id) {
            case 'new':
                if(RequestMethods::post('action') == 'save'){

                    $note = new models\Note(array(
                        'note_id' => uniqid(),
                        'title' => RequestMethods::post('title'),
                        'text' => RequestMethods::post('text'),
                        'user_id' => $this->user->id
                        ));

                    if($note->validate()){

                        $note->save();

                        $this->redirect('/users/notes/' . $note->note_id);

                    }

                }

                $view->set('new', 1);
                break;
            
            case '-1':
                $note = models\Note::first(array(
                    'user_id = ?' => $this->user->id
                    ));

                if($note){

                    $this->redirect('/users/notes/' . $note->note_id);

                }else{

                    $this->redirect('/users/notes/new');
                }
                break;
            
            default:
                $note = models\Note::first(array(
                    'note_id = ?' => $id,
                    'user_id = ?' => $this->user->id
                    ));

                if($note){

                    if(RequestMethods::post('action') == 'save'){

                        $note->title = RequestMethods::post('title');
                        $note->text = RequestMethods::post('text');

                        if($note->validate()){
                            $note->save();
                        }

                    }

                    if(RequestMethods::post('action') == 'delete'){

                        $note->delete();
                        $this->redirect('/users/notes');
                        
                    }

                    $view->set('note', $note);

                }else{

                    $this->redirect('/404');

                }
                break;                
                
        }
        
    }


	/**
	* @before _secure
	*/
    public function logout(){
        
        $this->setUser(false);

        header("Location: /");
    }

}
