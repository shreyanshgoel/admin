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

        if(RequestMethods::get('theme')){

            $this->user->theme_color = RequestMethods::get('theme');
            $this->user->save();

            $this->redirect('/users/dashboard');
        }

    	
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
    public function contact_book() {

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
    public function profile($success = -1){

        $layoutView = $this->getLayoutView();
        $layoutView->set("seo", Framework\Registry::get("seo"));

        $layoutView->set('profile',1);

        $view = $this->getActionView();

        $cp = 1;

        $view->set('update_success', $success);

        if(RequestMethods::post('profile_update')){

            $user = models\User::first(array(
                'id = ?' => $this->user->id
                ));

            // $exist = models\User::first(array(
            //     'id = ?' => ['$ne' => $this->user->id],
            //     'email = ?' => RequestMethods::post('email')
            //     ));

            //if(empty($exist)){

                $user->full_name = RequestMethods::post('full_name');

                // if(RequestMethods::post('change_email')){

                //     $user->tmp_email = null;
                // }

                // if(RequestMethods::post('email') != $user->email){

                //     $user->tmp_email = RequestMethods::post('email');

                //     $string = \Framework\StringMethods::uniqueRandomString(44);
                //     $user->email_confirm_string = $string;

                // }

                $user->state = RequestMethods::post('state');
                $user->address = RequestMethods::post('address');

                if($user->validate()){

                    $user->save();

                    //mail the url to confirm the email

                    $this->redirect('/users/profile/1');


                }else{

                    $view->set('validation', 1);
                }
            // }else{

            //     $view->set('exist', 1);
            // }

        }

        if(RequestMethods::post('change_password')){

            $cp = 2;

            $old = sha1(RequestMethods::post('old'));

            if($this->user->password == $old){

                $pass = RequestMethods::post('new');
                $confirm = RequestMethods::post('confirm');

                if($pass == $confirm){

                    $user = models\User::first(array('id = ?' => $this->user->id));

                    $user->password = sha1($pass);

                    $user->save();

                    $message = "Password Changed<strong>Successfully!</strong>";

                    $view->set('cp_success', 1);

                }else{

                    $message = "New passwords do not match!";
                }

            }else{

                $message = "Wrong Old Password!";
            }

            $view->set('message', $message);


        }

        $state = models\State::all();

        $view->set('cp', $cp)->set('state', $state);
       
        
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
