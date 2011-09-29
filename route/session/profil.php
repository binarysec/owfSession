<?php

class wfr_session__session_profil extends wf_route_request {

	private $a_session;
	private $admin;
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * constructeur
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function __construct($wf) {
		$this->wf = $wf;
		$this->a_session = $this->wf->session();
		$this->admin = $this->wf->admin_html();
		$this->lang = $this->wf->core_lang()->get_context('session/profil');
	}

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Edit users information
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */	
	public function edit() {
		if(!is_numeric($_POST["uid"])) { 
			echo $this->lang->ts("Erreur lors de la lecture des données");
			exit(0);
		}
		$uid = $_POST["uid"];
		$user = $this->a_session->get_user();	
		$modif_user = array();
		
		$ret = $this->a_session->get_user("email",$_POST["email_modif"]);
		if(isset($ret[0])) {
			if($ret[0]["id"] != $uid) {
				echo $this->lang->ts("Email déjà pris");
				exit(0);
			}else 
				$modif_user["email"] = $_POST["email_modif"];
				
			
		}else
			$modif_user["email"] = $_POST["email_modif"];	
		
		/* Check name */
		if((isset($_POST["user_name_modif"])) && strlen($_POST["user_name_modif"])>0) {
			$modif_user["name"] = $_POST["user_name_modif"];		
		}
		
		/* Check firstname */
		if((isset($_POST["user_firstname_modif"])) && strlen($_POST["user_firstname_modif"])>0) {
			$modif_user["firstname"] = $_POST["user_firstname_modif"];	
		}
		
		/* Check phone */
		if((isset($_POST["phone_modif"])) && strlen($_POST["phone_modif"])>0) {
			$modif_user["phone"] = $_POST["phone_modif"];
		}
		
		if(count($modif_user)>0){
			$this->a_session->user->modify(
				$modif_user,
				$user["id"]);
		}

		$this->wf->core_request()->set_header(
			'Location',
			$this->wf->linker('/admin/session/myprofile')
		);
		$this->wf->core_request()->send_headers();
		exit(0);
	}

	
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Master print function
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */	
	public function show() {
		$user_t = $this->a_session->get_user();
		$user = $this->a_session->user->get(array("id" => $user_t["id"]));
		$user = $user[0];
		$tpl = new core_tpl($this->wf);
		
		$lselect = $this->wf->core_lang()->get(); 
		$user["name"] = ucfirst(htmlentities($user["name"],ENT_COMPAT,$lselect["encoding"]));
		$user["firstname"] = ucfirst(htmlentities($user["firstname"],ENT_COMPAT,$lselect["encoding"]));
		$user["phone"] = htmlentities($user["phone"],ENT_COMPAT,$lselect["encoding"]); 
		$user["email"] = htmlentities($user["email"],ENT_COMPAT,$lselect["encoding"]);

		$tpl->set("user",$user);
		$this->admin->set_title(
			$this->lang->ts("Profil de")." ".
			$user["name"]." ".$user["firstname"]
		);
		$this->admin->rendering(
			$tpl->fetch("session/profil")
		);
		exit(0);	
	}
	
}
