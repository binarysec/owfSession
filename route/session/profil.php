<?php

class wfr_session__session_profil extends wf_route_request {

	private $a_session;
	private $admin;
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * constructeur
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function __construct($wf) {
		$this->wf 			= $wf;
		$this->a_session 	= $this->wf->session();
		$this->admin 		= $this->wf->admin_html();
		$this->lang			= $this->wf->core_lang()->get_context('session/profil');
	}

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Edit users information
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */	
	public function edit() {
		$uid =$_POST["uid"];
		$user=$this->a_session->get_user();	
		$modif_user=array();
		
		$ret=$this->a_session->get_user("email",$_POST["email_modif"]);
		if(is_array($ret[0])){
			if($ret[0]["id"]!=$uid){
				echo "Email déjà pris";
				exit(0);
			}else
				$modif_user["email"]=$_POST["email_modif"];	
			
		}else
			$modif_user["email"]=$_POST["email_modif"];	
		
		if((isset($_POST["user_name_modif"])) && strlen($_POST["user_name_modif"])>0)
			$modif_user["name"]=$_POST["user_name_modif"];		
		if((isset($_POST["user_firstname_modif"])) && strlen($_POST["user_firstname_modif"])>0)
			$modif_user["firstname"]=$_POST["user_firstname_modif"];	
		if((isset($_POST["phone_modif"])) && strlen($_POST["phone_modif"])>0)
			$modif_user["phone"]=$_POST["phone_modif"];
		if(count($modif_user)>0){
			$this->a_session->user->modify(
				$modif_user,
				$user["id"]);
		}
		$this->wf->core_request()->set_header(
			'Location',
			$this->wf->linker('/admin/myprofile')
		);
		$this->wf->core_request()->send_headers();
		exit(0);
	}
	
	
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Master print function
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */	
	public function show() {
		$user=$this->a_session->get_user();
		$tpl = new core_tpl($this->wf);
		$tpl->set("user",$user);
		$this->admin->set_title(
			$this->lang->ts("Profil de")." ".
			ucfirst($user["name"])." ".ucfirst($user["firstname"])
		);
		$this->admin->rendering(
			$tpl->fetch("session/profil")
		);
		exit(0);	
	}
	
}
