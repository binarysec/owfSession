<?php

class wfr_session_session_password extends wf_route_request {

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * constructeur
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function __construct($wf) {
		$this->wf = $wf;
		
		$this->a_admin_html = $this->wf->admin_html();
		$this->a_session = $this->wf->session();
		$this->session_mail = $this->wf->session_mail();
		
		$this->tpl = new core_tpl($this->wf);
		
		$this->lang = $this->wf->core_lang()->get_context(
			"session/password"
		);
		
		/*
		$this->core_pref = $this->wf->core_pref()->register_group(
			"session", 
			"Session"
		);
		*/
	}

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Affiche la page de récupération du mot de passe
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function show() {
		
		/* init vars and get parameters */
		$recover = $this->wf->get_var("recovery");
		$code = $this->wf->get_var("c");
		$errors = array();
		$allowed = false;
		$user = null;
		
		/* process vars and sanatize */
		if(isset($this->wf->ini_arr['session']['allow_pass_recovering']))
			$allowed = $this->wf->ini_arr['session']['allow_pass_recovering'];
		
		/* if this action is not permitted, throw a 404 */
		if(!$allowed) {
			$this->wf->display_error(404, $this->lang->ts("Password recovery is disabled"));
			exit(0);
		}
		
		/* do action */
		if($code != null) {
			$user = $this->a_session->user->get("password_recovery", $code);
			
			if(isset($user[0])) {
				$password = $this->a_session->user->generate_password();
				
				$this->a_session->user->modify(
					array(
						"password" => $password,
						"password_recovery" => "",
					),
					$user[0]['id']
				);
				
				$this->session_mail->mail_password_recovered($user[0]["id"], $password);
				
				$this->wf->display_msg(
					$this->lang->ts("Password changed !"),
					$this->lang->ts("Your password was changed.<br/>We sent you an email with your new one.")
				);
				//$this->wf->redirector($this->wf->linker("/session/login"));
				exit(0);
			}
			else
				$errors[] = $this->lang->ts("Something weird occurs, we did not find you. Do you mind giving it another try later ?");
		}
		else {
			if(strlen($recover) > 0) {
				$user = $this->a_session->user->get("username", $recover);
				if(!isset($user[0])) {
					$user = $this->a_session->user->get("email", $recover);
					if(!isset($user[0]))
						$errors[] = $this->lang->ts("User not found");
				}
			}
			
			if(empty($errors) && isset($user[0])) {
				$code = $this->wf->generate_password(16);
				$this->a_session->user->modify(
					array("password_recovery" => $code),
					$user[0]['id']
				);
				$this->session_mail->mail_password_recovery_request($user, $code);
				
				$this->wf->display_msg(
					$this->lang->ts("Password request sent !"),
					$this->lang->ts("We sent you an email with a link inside.<br/>Please go on this page and we will send you another password.")
				);
				$this->wf->redirector($this->wf->linker("/session/login"));
				exit(0);
			}
		}
		
		/* admin settings */
		$this->a_admin_html->set_title($this->lang->ts("Password recovery"));
		$this->a_admin_html->set_backlink($this->wf->get_default_url());
		
		/* set tpl vars */
		$this->tpl->set("errors", $errors);
		
		/* rendering */
		$this->a_admin_html->rendering(
			$this->tpl->fetch('session/password_recovery'),
			false,
			false
		);
		
		exit(0);
	}
}
