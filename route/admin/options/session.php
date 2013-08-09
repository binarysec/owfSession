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
		
		/* check permissions */
		$ret = $this->wf->execute_hook("waf_website_options");
		foreach($ret as $aopts) {
			if(is_array($aopts)) {
				foreach($aopts as $aopt) {
					if(	end(explode("/", $aopt["route"])) == $opt &&
						!$this->a_session->check_permission($aopt["perm"])
						) {
							$this->wf->display_error(403, $this->lang->ts("You don't have enought permissions"));
							exit(0);
						}
				}
			}
		}
					
		
		switch($opt) {
			case "password":
				if($action == "mod")
					$this->process_password();
					
				$this->a_admin_html->set_title($this->lang->ts('Change password'));
				$tpl_name = 'admin/options/changepassword';
				break;
				
			case "changelang":
				if($action == "mod")
					$this->process_changelang();
					
				$this->a_admin_html->set_title($this->lang->ts('Change language'));
				$tpl_name = 'admin/options/changelang';
				$tpl->set("langs", $this->wf->core_lang()->get_list());
				break;
				
			case "userinformation":
				if($action == "mod")
					$this->process_information();
					
				$this->a_admin_html->set_title($this->lang->ts('Update information'));
				$tpl_name = 'admin/options/userinformation';
				break;
				
			case "userpermission":
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
				
				/* get parameters */
				$errors = array();
				$default_perms = array();
				$pviewvar = $this->wf->get_var("pviewvar");
				$this->oid = $this->wf->get_var($pviewvar != null ? $pviewvar : "oid");
				$this->pview_name = $this->wf->get_var("pview");
				$this->uid = (int) $this->wf->get_var("uid");
				if($this->uid > 0)
					$this->user = current($this->a_session->user->get("id", $this->uid));
				
				/* not enough parameters */
				if($this->oid == null)
					$this->wf->display_error(404, $this->lang->ts("Parameter \"oid\" is missing"), true);
				elseif($this->pview_name == null)
					$this->wf->display_error(404, $this->lang->ts("Parameter \"pview\" is missing"), true);
				
				/* look for permisison */
				$this->pview = $this->a_session->pview->get_pview($this->pview_name);
				
				if(!$this->pview)
					$this->wf->display_error(404, $this->lang->ts("Permission \"$this->pview_name\" not found"), true);
				
				/* process actions */
				if($action == "add") {
					$search = $this->wf->get_var("user");
					$user = $this->a_session->user->get("username", $search);
					if(!count($user))
						$user = $this->a_session->user->get("email", $search);
					
					if(!count($user))
						$errors[] = "User not found";
					
					if(empty($errors))
						$this->pview->add($user[0]["id"], $this->oid);
				}
				elseif($action == "del" && $this->user != null) {
					$this->a_session->perm->user_remove(array(
						"ptr_id" => (int) $this->uid,
						"obj_type" => $this->pview->perm["id"],
						"obj_id" => (int) $this->oid
					));
				}
				elseif($action == "mod" && $this->user != null) {
					$pvname = $this->wf->get_var("pvname");
					$pvvalue = $this->wf->get_var("pvvalue");
					
					$this->pview->set(
						$this->uid,
						$this->oid,
						array($pvname => $pvvalue == "true" ? "on" : "off")
					);
					
					echo json_encode(array(
						"name" => $pvname,
						"value" => $pvvalue,
					));
					
					exit(0);
				}
				
				$data = array();
				$results = $this->pview->get_data($this->oid);
				
				/* adapt struct */
				
				foreach($results as $perms) {
					$user = current($this->a_session->user->get("id", $perms["ptr_id"]));
					
					$perm_arr = $perms["data"];
					
					/* if not defined yet, build and save default perms */
					if($perm_arr == null) {
						$perm_arr = $this->pview->default_struct;
						$this->a_session->perm->user_mod(
							array("data" => serialize($perm_arr)),
							array(
								"ptr_type" => SESSION_PERM_USER,
								"ptr_id" => $user["id"],
								"obj_type" => $this->pview->perm["id"],
								"obj_id" => (int) $this->oid
							)
						);
					}
					
					/* save to an array for the tpl */
					$data[] = array(
						"user" => $user,
						"perm" => $perm_arr,
						"create_time" => date(
							DATE_RFC822,
							isset($perms['create_t']) ? $perms['create_t'] : $perms['t.create_t']
						)
					);
				}
				
				$title = $this->pview->get_title();
				
				/* set tpl vars */
				$tpl->set("results", $data);
				$tpl->set("errors", $errors);
				$tpl->set("title", $title);
				$tpl->set("back", $this->wf->get_var('back'));
				$tpl->set("pview", $this->pview_name);
				$tpl->set("oid", $this->oid);
				
				/* render */
				$this->a_admin_html->set_title($title);
				$this->a_admin_html->set_backlink($this->a_core_cipher->get_var("back"));
				$this->a_admin_html->rendering($tpl->fetch("session/pview"));
				exit(0);
				
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
			"admin" => $this->a_session->iam_admin(),
			"god" => $this->a_session->iam_god()
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
	
	private function process_changelang() {
		$new_lang = $this->wf->get_var("lang");
		
		if(!$this->wf->core_lang()->resolv($new_lang)) {
			$this->error = $this->lang->ts("Incorrect lang code");
			return(false);
		}
		
		$this->a_session->user->modify(array("lang" => $new_lang), $this->uid);
		$this->wf->redirector($this->wf->linker("/"));
	}
	
	private function process_information() {
		$update = array();
		
		$update["firstname"] = $this->wf->get_var("firstname");
		$update["name"] = $this->wf->get_var("name");
		$update["email"] = $this->wf->get_var("email");
		
		if(strlen($update["firstname"]) <= 2) {
			$this->error = $this->lang->ts("Your first name is too short");
			return(false);
		}
		if(strlen($update["name"]) <= 2) {
			$this->error = $this->lang->ts("Your first name is too short");
			return(false);
		}
		
		if($this->a_session->iam_admin()) {
			$update["perm"] = $this->wf->get_var("perm");
			if($update["perm"] == SESSION_USER_GOD) {
				if($this->a_session->iam_god())
					$perm = "session:god";
				else {
					$this->error = $this->lang->ts("You cannot grant yourself more rights");
					return(false);
				}
			}
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
		
		if(array_key_exists('perm', $update))
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
}
