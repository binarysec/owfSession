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
				
			case "userpermission":
				if(!$this->a_session->iam_admin()) {
					$this->wf->display_error(403, $this->lang->ts("You are not an administrator"));
					exit(0);
				}
					
				$perms = $this->a_session->perm->user_get($this->uid);
				
				if($action == "mod")
					$this->process_permissions($perms);
					
				/* get session permissions */
				$sp = array();
				$ret = $this->wf->execute_hook("session_permissions");
				foreach($ret as $sp_perms) {
					if(is_array($sp_perms)) {
						foreach($sp_perms as $sp_key => $sp_name) {
							$sp[$sp_key] = $sp_name;
							if(isset($perms[$sp_key]) && $perms[$sp_key])
								$sp[$sp_key] = array(true, $sp_name);
							else
								$sp[$sp_key] = array(false, $sp_name);
						}
					}
				}
				$tpl->set("sp", $sp);

				$this->a_admin_html->set_title($this->lang->ts('User permission'));
				$tpl_name = 'admin/options/userpermission';
				break;
			
			case "userpview":
				if(!$this->a_session->iam_admin()) {
					$this->wf->display_error(403, $this->lang->ts("You are not an administrator"));
					exit(0);
				}
					
				//$perms = $this->a_session->perm->user_get($this->uid);
				
				if($action == "mod")
					$this->process_pview();//$perms);
					
				/* get session permissions */
				$pviews = $this->a_session->get_pview();
				$results = array();
				
				foreach($pviews as $name => $pview) {
					$o = new ${"name"}($this->wf, $name, $this->uid);
					$results[$name] = &$o->resolv;
				}
				
				$tpl->set("results", $results);
				$this->a_admin_html->set_title($this->lang->ts('User pview'));
				$tpl_name = 'admin/options/userpview';
				break;
			
			case "delete":
				if($action == "mod") {
					$this->a_session->user->remove($this->uid);
					$this->wf->redirector($this->wf->linker("/admin/system/session"));
					exit(0);
				}
				
				$this->a_admin_html->set_title($this->lang->ts('Delete user'));
				$tpl_name = 'admin/options/userdelete';
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

		$tpl->merge_vars($in);
		
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
			"password" => $new_password
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
			if($update["perm"] == SESSION_USER_GOD)
				$perm = "session:god";
			else if($update["perm"] == SESSION_USER_ADMIN)
				$perm = "session:admin";
			else if($update["perm"] == SESSION_USER_SIMPLE)
				$perm = "session:simple";
			else if($update["perm"] == SESSION_USER_WS)
				$perm = "session:ws";
			else {
				$this->error = $this->lang->ts("Invalid account type");
				return(false);
			}
				
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
	
	private function process_permissions(&$perms) {
		/* update session permissions */
		$ret = $this->wf->execute_hook("session_permissions");
		foreach($ret as $sp_perms) {
			if(is_array($sp_perms)) {
				foreach($sp_perms as $sp_key => $sp_name) {
					$val = $this->wf->get_var($sp_key);
					if($val == "on") {
						if(isset($perms[$sp_key]))
							$this->a_session->perm->user_remove(array(
								"ptr_id" => $this->uid,
								"obj_type" => $perms[$sp_key][0]["obj_type"]
							));
				
						$this->a_session->perm->user_add(
							$this->uid, 
							$sp_key
						);
					}
					else {
						if(isset($perms[$sp_key]))
							$this->a_session->perm->user_remove(array(
								"ptr_id" => $this->uid,
								"obj_type" => $perms[$sp_key][0]["obj_type"]
							));
					}
				}
			}
		}
		
		$this->wf->redirector($this->a_core_cipher->get_var("back"));
		exit(0);
	}

	private function process_pview() {
	}
	
}
