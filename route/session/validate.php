<?php

class wfr_session_session_validate extends wf_route_request {
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * constructeur
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function __construct($wf) {
		$this->wf = $wf;
		$this->a_session = $this->wf->session();
		$this->session_mail = $this->wf->session_mail();
		$this->a_admin_html = $this->wf->admin_html();
		
		$this->lang = $this->wf->core_lang()->get_context(
			"session/validate"
		);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * displaying validation page
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function show() {
		$msg = $this->lang->ts("We just sent you an email with a validation link.");
		$msg .= "<br />";
		$msg .= $this->lang->ts("Please take a look at it in order to activate your account.");
		
		$this->wf->display_msg(
			"Account activation",
			$msg,
			"Account activation"
		);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * validate an user account
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function validate() {
		$code = $this->wf->get_var("c");
		$u = new session_db_user($this->wf);
		$user = $u->get("activated", $code);
		$errors = array();
		
		if(!isset($user[0]))
			$errors[] = "Aucun utilisateur avec ce code de validation";
		
		$validated = false;
		
		if(count($errors) < 1) {
			$u->modify(array("activated" => "true"), $user[0]["id"]);
			$this->wf->session_mail()->mail_validated($user[0]["id"]);
			$validated = true;
		}
		
		if($validated)
			$msg = $this->lang->ts("Your account was activated successfully.");
		else
			$msg = $this->lang->ts("There is no account with this code.");
		
		$this->wf->display_msg(
			"Account activation",
			$msg,
			"Account activation"
		);
	}
}
