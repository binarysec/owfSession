<?php

class wfr_session_session_create extends wf_route_request {

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * constructeur
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function __construct($wf) {
		$this->wf = $wf;
		$this->a_session = $this->wf->session();
		$this->session_mail = $this->wf->session_mail();
		$this->a_admin_html = $this->wf->admin_html();
		$this->a_core_cipher = $this->wf->core_cipher();
		
		$this->core_pref = $this->wf->core_pref()->register_group(
			"session", 
			"Session"
		);
		
		$this->lang = $this->wf->core_lang()->get_context(
			"session/create"
		);
		
		$this->tpl = new core_tpl($this->wf);
	}

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * fonction de traitement de l'autentification
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function show() {
		
		$this->registering = is_null($this->a_session->get_perms());
		
		/* check if public account creation is allowed */
		if($this->registering) {
			if(!$this->core_pref->get_value("allow_account_creation")) {
				$this->wf->display_error(403, "Forbidden");
				exit(0);
			}
		}
		else if(!$this->a_session->iam_manager()) {
			$this->wf->display_error(403, "Forbidden");
			exit(0);
		}
		
		$errors;
		
		/* get preferences */
		$this->allow_pass_register = $this->core_pref->get_value('allow_pass_register');
		$this->allow_user_register = $this->core_pref->get_value('allow_user_register');
		$this->auto_activate = !$this->registering || !$this->core_pref->get_value('activation_required');
		$auto_val = $this->wf->get_var("auto_validate");
		
		if($auto_val == "on" && !$this->registering)
			$this->auto_activate = true;
		
		/* set tpl vars */
		$this->tpl->set("username", '');
		$this->tpl->set("firstname", '');
		$this->tpl->set("name", '');
		$this->tpl->set("email", '');
		$this->tpl->set("email_confirm", '');
		
		/* addition */
		$action = $this->wf->get_var("action");
		if($action) {
			
			/* process action */
			$errors = $this->process();
			
			/* if no error occurs, user was added */
			if(count($errors) < 1) {
				
				/* redirect to the proper page */
				if(! $this->registering)
					$this->wf->redirector($this->wf->linker("/admin/system/session"));
				elseif(! $this->auto_activate)
					$this->wf->redirector($this->wf->linker("/session/valshow"));
				else
					$this->wf->display_login("Account created, please login");
				
				exit(0);
			}
		}
		else
			$errors = array();
			
		$this->tpl->set("registering", $this->registering);
		$this->tpl->set("errors", $errors);
		$this->tpl->set("allow_pass_register", $this->allow_pass_register);
		$this->tpl->set("allow_user_register", $this->allow_user_register);
		
		/* Add back button */
		$this->a_admin_html->set_title($this->lang->ts(
			($this->registering ? "Registration" : "Adding user")
		));
		$this->a_admin_html->set_backlink($this->wf->linker('/admin/system/session/'));
		
		/* rendering using my template */
		$this->a_admin_html->rendering(
			$this->tpl->fetch('session/create'),
			!$this->registering,
			!$this->registering
		);
		
		exit(0);
	}
	
	public function process() {
		$errors = array();
		
		/* get parameters */
		$username = $this->wf->get_var("username");
		$firstname = $this->wf->get_var("firstname");
		$name = $this->wf->get_var("name");
		$email = $this->wf->get_var("email");
		$email_confirm = $this->wf->get_var("email_confirm");
		$password = $this->wf->get_var("pass");
		$password_confirm = $this->wf->get_var("pass_confirm");
		$phone = null; // not provided yet
		
		/* validate username */
		if($this->core_pref->get_value('allow_user_register')) {
			
			/* check parameter */
			if(strlen($username) <= 5)
				$errors[] = "Username is too short";
			
			/* search in database */
			else {
				$u = $this->a_session->user->get("username", $username);
				if(count($u))
					$errors[] = "Username exists";
				else
					$this->tpl->set("username", $username);
			}
		}
		else {
			$username = $this->a_session->user->generate_ref(
				"username", "session_user", "username"
			);
			$this->tpl->set("username", $username);
		}
		
		/* check parameters */
		if(strlen($firstname) <= 2)
			$errors[] = "Your first name is too short";
		else
			$this->tpl->set("firstname", $firstname);
		
		if(strlen($name) <= 2)
			$errors[] = "Your name is too short";
		else
			$this->tpl->set("name", $name);
		
		if(strlen($name) <= 2)
			$errors[] = "Your mail address is too short";
		else
			$this->tpl->set("email", $email);
		
		if($email != $email_confirm)
			$errors[] = "Your mail address does not match";
		else {
			$this->tpl->set("email", $email);
			$this->tpl->set("email_confirm", $email);
		}
		
		/* validate the password */
		if($this->core_pref->get_value('allow_pass_register')) {
			if(strlen($password) <= 6)
				$errors[] = "Your password is too short";
			
			if($password != $password_confirm)
				$errors[] = "Your password does not match";
		}
		else
			$password = $this->a_session->user->generate_password();
		
		/* if no error occurs, add data */
		if(count($errors) == 0) {
			$uid = $this->a_session->user->add(
				$username,
				$email,
				$password,
				$name,
				$firstname,
				"session:simple",
				$phone,
				$this->auto_activate
			);
			
			if(!$uid) {
				$errors[] = "Email exists";
			}
			else {
				if($this->auto_activate)
					$this->session_mail->mail_validated($uid, $password);
				else
					$this->session_mail->mail_inscription($uid, $password);
			}
		}
		
		return($errors);
	}
}
