<?php


class wfm_session extends wf_module {
	public function __construct($wf) {
		$this->wf = $wf;
	}
	
	public function get_name() { return("session"); }
	public function get_description()  { return("OWF Native Session module"); }
	public function get_banner()  { return("OWF Session/1.3.0"); }
	public function get_version() { return("1.3.0"); }
	public function get_authors() { return("Michael VERGOZ"); }
	public function get_depends() { return(NULL); }
	
	public function session_permissions() {
		return(array(
			"session:manage" => $this->ts("Allow to manage users")
		));
	}
	
	public function get_actions() {
		return(array(
			"/session/login" => array(
				WF_ROUTE_ACTION,
				"session/logon",
				"login",
				"Login",
				WF_ROUTE_HIDE,
				array("session:ranon")
			),
			"/session/logout" => array(
				WF_ROUTE_ACTION,
				"session/logon",
				"logout",
				"Logout",
				WF_ROUTE_HIDE,
				array("session:ranon")
			),
			"/session/validate" => array(
				WF_ROUTE_ACTION,
				"session/logon",
				"validate",
				"",
				WF_ROUTE_HIDE,
				array("session:ranon")
			),
			
			"/session/create" => array(
				WF_ROUTE_ACTION,
				"session/create",
				"show",
				"Account creation",
				WF_ROUTE_HIDE,
				array("session:ranon")
			),
			

			/* Admin session integration */
			"/admin/options/session" => array(
				WF_ROUTE_ACTION,
				"admin/options/session",
				"show",
				$this->ts("Session options"),
				WF_ROUTE_HIDE,
				array("session:simple")
			),
			
			"/admin/system/session" => array(
				WF_ROUTE_ACTION,
				"admin/system/session",
				"show",
				$this->ts("Gestion des utilisateurs"),
				WF_ROUTE_SHOW,
				array("session:manage")
			),
		));
	}
	
	public function search_module() {
		$return = array();
		
		$info = array(
			"name" => $this->ts("session_user"),
			"agg" => "session",
			"met_db" => "search_user_db",
			"met_link" => "search_user_link",
		);
		$return[] = $info;
		
		return($return);
	}

	public function admin_options() {
		$return = array();
		
		$info = array(
			"text" => $this->ts("Change password"),
			"route" => "/admin/options/session/password",
			"perm" => array("session:simple"),
			"type" => "dialog",
		);
		$return[] = $info;
		
		$info = array(
			"text" => $this->ts("Update personnals informations"),
			"route" => "/admin/options/session/userinformation",
			"perm" => array("session:simple"),
			"type" => "dialog",
			"icon" => "info"
		);
		$return[] = $info;
		
		$info = array(
			"text" => $this->ts("User capatibility"),
			"route" => "/admin/options/session/userpermission",
			"perm" => array("session:manage"),
			"type" => "dialog",
			"icon" => "star"
		);
		$return[] = $info;
		
		$info = array(
			"text" => $this->ts("Delete user"),
			"route" => "/admin/options/session/delete",
			"perm" => array("session:simple"),
			"type" => "dialog",
			"icon" => "delete"
		);
		$return[] = $info;
		
		return($return);
	}
	
	public function json_module() {
		$return = array();
		
		$info = array(
			"agg" => "session",
			"method" => "json_info",
			"perm" => array("session:ranon")
		);
		$return[] = $info;
		
		return($return);
	}
	
	public function owf_post_init() {
		
		/* register session preferences group */
		$pref_grp = $this->wf->core_pref()->register_group(
			"session", 
			"Session"
		);
		
		/* register session preference vars */
		
		/* var context */
		$pref_grp->register(
			"variable",
			"Variable context",
			CORE_PREF_VARCHAR,
			"session".rand()
		);
		
		/* session timeout */
		$pref_grp->register(
			"timeout",
			"Session timeout",
			CORE_PREF_NUM,
			3600
		);
		
		/* mail sender information */
		$pref_grp->register(
			"sender",
			"Session information mail from",
			CORE_PREF_VARCHAR,
			"contact@owf.re"
		);
		
		/* email validation timeout */
		$pref_grp->register(
			"email_validation_timeout",
			"Email validation timeout",
			CORE_PREF_NUM,
			604800 // a week
		);
	}
}
