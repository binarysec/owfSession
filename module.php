<?php

define("WF_USER_GOD",     "session:god");
define("WF_USER_ADMIN",   "session:admin");
define("WF_USER_SIMPLE",  "session:simple");
define("WF_USER_SERVICE", "session:service");
define("WF_USER_ANON",    "session:anon");
define("WF_USER_RANON",   "session:ranon");

class wfm_session extends wf_module {
	public function __construct($wf) {
		$this->wf = $wf;
	}
	
	public function get_name() { return("session"); }
	public function get_description()  { return("OWF Native Session module"); }
	public function get_banner()  { return("OWF Session/1.2.0"); }
	public function get_version() { return("1.2.0"); }
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
				array("session:anon")
			),
			"/session/logout" => array(
				WF_ROUTE_ACTION,
				"session/logon",
				"logout",
				"Logout",
				WF_ROUTE_HIDE,
				array("session:anon")
			),
			
			/* permission editor */
			"/session/permissions" => array(
				WF_ROUTE_ACTION,
				"session/permissions",
				"show_acl",
				"Logout",
				WF_ROUTE_HIDE,
				array("session:admin")
			),
			"/session/permissions/user" => array(
				WF_ROUTE_ACTION,
				"session/permissions",
				"add_user",
				"Logout",
				WF_ROUTE_HIDE,
				array("session:admin")
			),
			"/session/permissions/delete" => array(
				WF_ROUTE_ACTION,
				"session/permissions",
				"delete_user",
				"Logout",
				WF_ROUTE_HIDE,
				array("session:admin")
			),
			"/session/permissions/matrix" => array(
				WF_ROUTE_ACTION,
				"session/permissions",
				"edit_user",
				"Logout",
				WF_ROUTE_HIDE,
				array("session:admin")
			),
			
			/* user/group/perm */
			"/admin/system/session" => array(
				WF_ROUTE_REDIRECT,
				"/admin/system/session/user",
				$this->ts("Gestion des utilisateurs"),
				WF_ROUTE_SHOW,
				array("session:manage")
			),

			"/admin/system/session/user" => array(
				WF_ROUTE_ACTION,
				"session/admin_user",
				"admin_user",
				$this->ts("Gestion des utilisateurs"),
				WF_ROUTE_HIDE,
				array("session:manage")
			),
			"/admin/session/user/add" => array(
				WF_ROUTE_ACTION,
				"session/admin_user",
				"add",
				"Ajoute un utilisateur",
				WF_ROUTE_HIDE,
				array("session:manage")
			),
			"/admin/session/user/showadd" => array(
				WF_ROUTE_ACTION,
				"session/admin_user",
				"show_add",
				"Ajoute un utilisateur",
				WF_ROUTE_HIDE,
				array("session:manage")
			),
			"/admin/session/user/edit" => array(
				WF_ROUTE_ACTION,
				"session/admin_user",
				"edit",
				"Ajoute un utilisateur",
				WF_ROUTE_HIDE,
				array("session:manage")
			),
			"/admin/session/user/showedit" => array(
				WF_ROUTE_ACTION,
				"session/admin_user",
				"show_edit",
				"Ajoute un utilisateur",
				WF_ROUTE_HIDE,
				array("session:manage")
			),
			"/admin/session/user/delete" => array(
				WF_ROUTE_ACTION,
				"session/admin_user",
				"delete",
				"Ajoute un utilisateur",
				WF_ROUTE_HIDE,
				array("session:manage")
			),
			// My profil
			"/admin/myprofile" => array(
				WF_ROUTE_ACTION,
				"/session/profil",
				"show",
				"Mon profil",
				WF_ROUTE_SHOW,
				array("session:simple")
			),
			"/admin/myprofile/edit" => array(
				WF_ROUTE_ACTION,
				"/session/profil",
				"edit",
				"Mon profil",
				WF_ROUTE_HIDE,
				array("session:simple")
			),
// 			"/session/login" => array(
// 				WF_ROUTE_ACTION,
// 				"session",
// 				"login",
// 				"Login",
// 				WF_ROUTE_HIDE,
// 				array("session:anon")
// 			),
// 			"/session/logout" => array(
// 				WF_ROUTE_ACTION,
// 				"session",
// 				"logout",
// 				"Logout",
// 				WF_ROUTE_HIDE,
// 				array("session:anon")
// 			),
// 			"/img" => array(
// 				WF_ROUTE_ACTION,
// 				"img",
// 				"show_img",
// 				"Img",
// 				WF_ROUTE_HIDE,
// 				array("session:anon")
// 			),
// 			"/css" => array(
// 				WF_ROUTE_ACTION,
// 				"css",
// 				"show_css",
// 				"Css",
// 				WF_ROUTE_HIDE,
// 				array("session:anon")
// 			),
// 			"/js" => array(
// 				WF_ROUTE_ACTION,
// 				"js",
// 				"show_js",
// 				"Js",
// 				WF_ROUTE_HIDE,
// 				array("session:anon")
// 			),
// 			"/admin/system/data" => array(
// 				WF_ROUTE_REDIRECT,
// 				"/data",
// 				"Listing des données",
// 				WF_ROUTE_SHOW,
// 				array("session:god")
// 			),
// 			"/data" => array(
// 				WF_ROUTE_ACTION,
// 				"data",
// 				"show_data",
// 				"Données statiques",
// 				WF_ROUTE_HIDE,
// 				array("session:anon")
// 			),
		));
	}
}
