<?php

class wfr_session_session_create extends wf_route_request {

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * constructeur
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function __construct($wf) {
		$this->wf = $wf;
		$this->a_session = $this->wf->session();
		$this->a_admin_html = $this->wf->admin_html();
		$this->a_core_cipher = $this->wf->core_cipher();
		
		$this->tpl = new core_tpl($this->wf);
	}

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * fonction de traitement de l'autentification
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function show() {
		if($this->wf->ini_arr['session']['allow_account_creation'] != true)
			exit(0);
			
		$this->allow_pass_register = $this->wf->ini_arr['session']['allow_pass_register'];
		$this->allow_user_register = $this->wf->ini_arr['session']['allow_user_register'];
		
		$this->tpl->set("username", '');
		$this->tpl->set("firstname", '');
		$this->tpl->set("name", '');
		$this->tpl->set("email", '');
		$this->tpl->set("email_confirm", '');
		
		/* addition */
		$action = $this->wf->get_var("action");
		if($action) {
			$errors = $this->process();
			$this->tpl->set("errors", $errors);
		}
		else
			$this->tpl->set("errors", array());
	
		
		$this->tpl->set("allow_pass_register", $this->allow_pass_register);
		$this->tpl->set("allow_user_register", $this->allow_user_register);
		
		/* Add back button */
// 		$this->a_admin_html->set_backlink($this->wf->linker('/admin/system'));
		
		/* rendering using my template */
		$this->a_admin_html->rendering($this->tpl->fetch('session/create'), false, false);
		exit(0);
	}
	
	public function process() {
		$errors = array();

		/* validate username */
		if($this->wf->ini_arr['session']['allow_user_register']) {
			$username = $this->wf->get_var("username");
			if(strlen($username) <= 5)
				$errors[] = "Username is too short";
			/* search in database */
			else {
				$u = $this->a_session->user->get("username", $username);
				if(count($u))
					$errors[] = "Username exists";
			}
		}
		else {
			$username = $this->a_session->user->generate_ref(
				"username", "session_user", "username"
			);
			$this->tpl->set("username", $username);
		}
			
		/* validate general infor */
		$firstname = $this->wf->get_var("firstname");
		if(strlen($firstname) <= 2)
			$errors[] = "Your first name is too short";
		else
			$this->tpl->set("firstname", $firstname);
			
		$name = $this->wf->get_var("name");
		if(strlen($name) <= 2)
			$errors[] = "Your name is too short";
		else
			$this->tpl->set("name", $name);
			
		$email = $this->wf->get_var("email");
		if(strlen($name) <= 2)
			$errors[] = "Your mail address is too short";
			
		$email_confirm = $this->wf->get_var("email_confirm");
		if($email != $email_confirm)
			$errors[] = "Your mail address does not match";
		else {
			$this->tpl->set("email", $email);
			$this->tpl->set("email_confirm", $email);
		}
		
		/* validate the password */
		if($this->wf->ini_arr['session']['allow_pass_register']) {
			$password = $this->wf->get_var("password");
			if(strlen($password) <= 6)
				$errors[] = "Your password is too short";
				
			$email_confirm = $this->wf->get_var("email_confirm");
			if($email != $email_confirm)
				$errors[] = "Your password does not match";
		}
		
		/* addind datas */
		if(count($errors) == 0) {
		
		}

		
// 		var_dump($errors);
// 		exit(0);
		
		
		return($errors);
		
	}
	

}
