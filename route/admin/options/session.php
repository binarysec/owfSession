<?php

class wfr_session_admin_options_session extends wf_route_request {
	
	public function __construct($wf) {
		$this->wf = $wf;
		$this->a_admin_html = $this->wf->admin_html();
		$this->a_core_request = $this->wf->core_request();
		$this->a_core_cipher = $this->wf->core_cipher();
		$this->a_session = $this->wf->session();
		
		$this->lang = $this->wf->core_lang()->get_context(
			"session/options"
		);
		
		$this->error = null;
		$this->uid = null;
		
	}
	
	public function show() {
		$this->uid = $this->wf->get_var("uid");
		if(!$this->uid)
			$this->uid = $this->a_session->session_me["id"];
		$user = array();
		$r = $this->a_admin_html->check_options_policy($this->uid, $user);
		if(!$r)
			exit(0);
	
		$tpl = new core_tpl($this->wf);

		$opt = $this->a_core_request->get_argv(0);
		$action = $this->wf->get_var("action");
		
		switch($opt) {
			case "password":
				if($action == "mod")
					$this->process_password();
					
				$this->a_admin_html->set_title($this->lang->ts('Change password'));
				$tpl_name = 'admin/options/changepassword';
				break;
				
			case "userinformation":
				if($action == "mod")
					$this->process_information();
					
				$this->a_admin_html->set_title($this->lang->ts('Update information'));
				$tpl_name = 'admin/options/userinformation';
				break;
				
			default:
				exit(0);
		}
	
		$perms = $this->a_session->perm->user_get($this->uid);
		
		/* prepare template variable */
		$in = array(
			"uid" => $this->uid,
			"back" => $this->wf->get_var("back"),
			"error" => $this->error,
			"user" => $user,
			"me" => $this->a_session->session_me,
			"perms" => $perms,
			"admin" => $this->a_session->iam_admin()
		);
	
		$tpl->set_vars($in);
		
		$this->a_admin_html->div_set("data-role", "dialog");
		$this->a_admin_html->rendering_options($tpl->fetch($tpl_name));
		exit(0);
	}
	
	private function process_password() {
		$new_password = $this->wf->get_var("new_pass");
		$confirm_password = $this->wf->get_var("confirm_pass");
		
		if(strlen($new_password) <= 5) {
			$this->error = $this->lang->ts("Your password is too short");
			return(false);
		}	
		if($new_password != $confirm_password) {
			$this->error = $this->lang->ts("Password doesn't match");
			return(false);
		}
		$update = array(
			"password" => $this->wf->hash($new_password)
		);
		$this->a_session->user->modify(
			$update,
			$this->uid
		);
		
		$this->wf->redirector($this->a_core_cipher->get_var("back"));
		exit(0);
	}
	
	private function process_information() {
		$update = array();
		
		$update["firstname"] = $this->wf->get_var("firstname");
		$update["name"] = $this->wf->get_var("name");
		$update["email"] = $this->wf->get_var("email");
		$update["perm"] = $this->wf->get_var("perm");
		
		if(strlen($update["firstname"]) <= 2) {
			$this->error = $this->lang->ts("Your first name is too short");
			return(false);
		}
		if(strlen($update["name"]) <= 2) {
			$this->error = $this->lang->ts("Your first name is too short");
			return(false);
		}
		
		if($this->a_session->iam_admin()) {
			if($update["perm"] < 0 && $update["perm"] > 3) {
				$this->error = $this->lang->ts("Invalid account type");
				return(false);
			}
			
			if($update["perm"] == 0)
				$perm = "session:god";
			else if($update["perm"] == 1)
				$perm = "session:admin";
			else if($update["perm"] == 2)
				$perm = "session:simple";
			else if($update["perm"] == 3)
				$perm = "session:ws";
			else
				exit(0);
				
			$perms = $this->a_session->perm->user_get($this->uid);
			
			if(isset($perms["session:god"]))
				$old_obj_type = $perms["session:god"][0]["obj_type"];
			else if(isset($perms["session:admin"]))
				$old_obj_type = $perms["session:admin"][0]["obj_type"];
			else if(isset($perms["session:simple"]))
				$old_obj_type = $perms["session:simple"][0]["obj_type"];
			else if(isset($perms["session:ws"]))
				$old_obj_type = $perms["session:ws"][0]["obj_type"];
				
			$this->a_session->perm->user_remove(array(
				"ptr_id" => $this->uid,
				"obj_type" => $old_obj_type
			));
			$this->a_session->perm->user_add($this->uid, $perm);
			
		}
		if(isset($update["perm"]))
			unset($update["perm"]);
			
		$this->a_session->user->modify(
			$update,
			$this->uid
		);
		
		$this->wf->redirector($this->a_core_cipher->get_var("back"));
		exit(0);
	}
	
}

