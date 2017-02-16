<?php

/**
 * The Default Example Controller Class
 *
 * @author Shreyansh Goel
 */
use Shared\Controller as Controller;
use Framework\RequestMethods as RM;

class Users extends Controller {

	/**
	* @before _secure
	*/
    public function dashboard() {
    	$lview = $this->getLayoutView();
    	$lview->set("seo", Framework\Registry::get("seo"));
        $lview->set('dashboard', 1);

        $view = $this->getActionView();

        if(RM::get('theme')){
            $this->user->theme_color = RM::get('theme');
            $this->user->save();
            $this->redirect('/users/dashboard');
        }
    }

    /**
	* @before _secure
	*/
    public function taskboard() {
    	$lview = $this->getLayoutView();
    	$lview->set("seo", Framework\Registry::get("seo"));
    	$lview->set('taskboard', 1);
    }

    /**
	* @before _secure
	*/
    public function projects() {
    	$lview = $this->getLayoutView();
    	$lview->set("seo", Framework\Registry::get("seo"));
        $lview->set('projects', 1);
    }

    /**
	* @before _secure
	*/
    public function members() {
    	$lview = $this->getLayoutView();
    	$lview->set("seo", Framework\Registry::get("seo"));
        $lview->set('members', 1);

    	$view = $this->getActionView();

        if(RM::post('action') == 'add_contact'){
            $exist = models\User::first(['email' => RM::post('email')]);
            if(!$exist){
                $user = new models\User([
                    "email" => RM::post('email'),
                    "designations" => [
                        $this->company->id => [
                            RM::post('designation')
                            ]],
                    "permissions" => [
                        $this->company->id => RM::post('permission')
                        ],
                    "company_ids" => [$this->company->id],
                    "live" => false
                    ]);

                $user->save();

                //@todo Send mail to the new user and let him fill the details
                //@todo A cronjob that deletes users which do not register after all
            }else{

                $ids = $exist->company_ids;
                array_push($ids, $this->company->id);
                $exist->company_ids = $ids;

                $d = $exist->designations;
                $d[$this->company->id] = [RM::post('designation')];
                $exist->designations = $d;

                $p = $exist->permissions;
                $p[$this->company->id] = RM::post('permission');
                $exist->permissions = $p;

                $exist->save();
            }
        }
    }

    /**
	* @before _secure
	*/
    public function calendar() {
    	$lview = $this->getLayoutView();
    	$lview->set("seo", Framework\Registry::get("seo"));

    	$lview->set('calendar_tab', 1);
    }

    /**
	* @before _secure
	*/
    public function profile($success = -1){
        $lview = $this->getLayoutView();
        $lview->set("seo", Framework\Registry::get("seo"));
        $lview->set('profile',1);

        $view = $this->getActionView();

        $cp = 1;

        $view->set('update_success', $success);

        if(RM::post('profile_update')){

            $user = models\User::first(array(
                'id = ?' => $this->user->id
                ));

            // $exist = models\User::first(array(
            //     'id = ?' => ['$ne' => $this->user->id],
            //     'email = ?' => RM::post('email')
            //     ));

            //if(empty($exist)){

                $user->full_name = RM::post('full_name');

                // if(RM::post('change_email')){

                //     $user->tmp_email = null;
                // }

                // if(RM::post('email') != $user->email){

                //     $user->tmp_email = RM::post('email');

                //     $string = \Framework\StringMethods::uniqueRandomString(44);
                //     $user->email_confirm_string = $string;

                // }
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

        if(RM::post('change_password')){
            $cp = 2;
            $old = sha1(RM::post('old'));

            if($this->user->password == $old){
                $pass = RM::post('new');
                $confirm = RM::post('confirm');

                if($pass == $confirm){
                    $user = models\User::first(array('id = ?' => $this->user->id));
                    $user->password = sha1($pass);
                    $user->save();

                    $message = "Password Changed <strong>Successfully!</strong>";
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
        $lview = $this->getLayoutView();
        $lview->set("seo", Framework\Registry::get("seo"));
        $lview->set('notes',1);

        $view = $this->getActionView();

        switch ($id) {
            case 'new':
                if(RM::post('action') == 'save'){
                    $note = new models\Note(array(
                        'note_id' => uniqid(),
                        'title' => RM::post('title'),
                        'text' => RM::post('text'),
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
                    if(RM::post('action') == 'save'){
                        $note->title = RM::post('title');
                        $note->text = RM::post('text');

                        if($note->validate()){
                            $note->save();
                        }
                    }

                    if(RM::post('action') == 'delete'){
                        $note->delete();
                        $this->redirect('/users/notes');
                    }
                    $view->set('note', $note);
                }else{
                    $this->redirect('/404');
                }
                break;
        }

        $all_notes = models\Note::all(array(
            'user_id = ?' => $this->user->id
            ));
        $view->set('all_notes', $all_notes);
    }

	/**
	* @before _secure
	*/
    public function logout(){
        $this->setUser(false);
        $this->redirect('/');
    }
}
