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
		$action = $this->wf->get_var("action");
		$id = intval($this->wf->get_var("id"));
		$code = $this->wf->get_var("c");
		$allowed = false;
		$errors = array();
		$firstname = '';
		$lastname = '';
		$email = '';
		$this->user = null;
		
		/* process vars and sanatize */
		if(isset($this->wf->ini_arr['session']['allow_pass_recovering']))
			$allowed = $this->wf->ini_arr['session']['allow_pass_recovering'];
		
		/* if this action is not permitted, throw a 404 */
		if(!$allowed) {
			$this->wf->display_error(404, $this->lang->ts("Password recovery is disabled"));
			exit(0);
		}
		
		/* do action */
		if($action == "search") {
			
			$this->user = $this->search($errors);
			
			if(empty($errors)) {
				// send an email ??
				//$this->session_mail->mail_inscription($uid, $password);
				
				$code = $this->wf->generate_password(16);
				$this->a_session->user->modify(
					array("password_recovery" => $code),
					$this->user['id']
				);
			}
			
			/* step back */
			else
				$action = "";
		}
		elseif($action == "recover") {
			
			/* get user back */
			$user = $this->a_session->user->get("id", $id);
			
			if(isset($user[0])) {
				$this->user = $user[0];
				$is_hacking = !$this->recover($errors);
				
				if($is_hacking) {
					$this->wf->display_error(403, $this->lang->ts("You are trying to change the password of someone else"));
					exit(0);
				}
			}
			else
				$errors[] = $this->lang->ts("Sorry, we lose your identity, please try again");
			
			/* send mail and redirect to login */
			if(empty($errors)) {
				$this->session_mail->mail_password_changed($id, $this->wf->get_var("rec_password"));
				$this->wf->display_msg(
					$this->lang->ts("Password changed !"),
					$this->lang->ts("Your password was changed.<br/>We sent you an email with your new one.")
				);
				//$this->wf->redirector($this->wf->linker("/session/login"));
				exit(0);
			}
			
			/* step back */
			else {
				$action = "search";
			}
		}
		
		/* if user was found then */
		if(!is_null($this->user)) {
			$id = $this->user['id'];
			$firstname = $this->user['firstname'];
			$lastname = $this->user['name'];
			$email = $this->user['email'];
		}
		
		/* admin settings */
		$this->a_admin_html->set_title($this->lang->ts("Password recovery"));
		$this->a_admin_html->set_backlink($this->wf->get_default_url());
		
		/* set tpl vars */
		$this->tpl->set("last_action", $action);
		$this->tpl->set("errors", $errors);
		$this->tpl->set("id", $id);
		$this->tpl->set("firstname", $firstname);
		$this->tpl->set("lastname", $lastname);
		$this->tpl->set("email", $email);
		$this->tpl->set("code", $code);
		
		/* rendering */
		$this->a_admin_html->rendering(
			$this->tpl->fetch('session/password_recovery'),
			false,
			false
		);
		
		exit(0);
	}
	
	private function search(&$err) {
		$u = null;
		$recover = $this->wf->get_var("recovery");
		
		if(strlen($recover) > 0) {
			$user = $this->a_session->user->get("username", $recover);
			if(isset($user[0]))
				$u = $user[0];
			else {
				$user = $this->a_session->user->get("email", $recover);
				if(isset($user[0])) {
					if($user[0]['remote_address'] == ip2long($_SERVER["REMOTE_ADDR"]))
						$u = $user[0];
					else
						$err[] = $this->lang->ts("You can reset your password with an email address only from the last location where you logged in");
				}
				else
					$err[] = $this->lang->ts("User not found");
			}
		}
		else
			$err[] = $this->lang->ts("You did not fill the form");
		
		return $u;
	}
	
	private function recover(&$err) {
		$id = (int) $this->wf->get_var("id");
		$pass = $this->wf->get_var("rec_password");
		$pass_confirm = $this->wf->get_var("rec_password_confirm");
		$code = $this->wf->get_var("c");
		
		if(strlen($pass) > 0 && strlen($pass_confirm) > 0) {
			if(strlen($pass) > 5) {
				if($pass == $pass_confirm) {
					if($id > 0 && !is_null($this->user)) {
						if($code == $this->user['password_recovery']) {
							
							$this->a_session->user->modify(
								array(
									"password" => $pass,
									"password_recovery" => "",
								),
								$id
							);
							
							return true;
						}
						else
							return false;
					}
					else
						$err[] = $this->lang->ts("Something weird happen, we lost your identity");
				}
				else
					$err[] = $this->lang->ts("Passwords does not match");
			}
			else
				$err[] = $this->lang->ts("Your password is too short, use 6 characters at least");
		}
		else
			$err[] = $this->lang->ts("You did not fill the form");
		
		return true;
	}
}
