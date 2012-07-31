<?php

class wfr_session_session_logon extends wf_route_request {

	var $a_session;
	var $a_core_html;
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * constructeur
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function __construct($wf) {
		$this->wf = $wf;
		$this->a_session = $this->wf->session();
		$this->a_core_html = $this->wf->core_html();
		$this->a_core_cipher = $this->wf->core_cipher();
	}

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * fonction de traitement de l'autentification
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function login() {
		/* prend les inputs */
		$user = $this->wf->get_var("user");
		$pass = $this->wf->get_var("pass");

		$url = $this->a_core_cipher->get_var("back_url");
	
		if(strlen($url) == 0) {
			if(isset($this->wf->ini_arr["session"]["default_url"]))
				$link = $this->wf->linker($this->wf->ini_arr["session"]["default_url"]);
			else	
				$link = $this->wf->linker('/admin');
			$url = "/";
		}
		else
			$link = $url;
			
		if(!isset($user) || !isset($pass)) {
			header("Location: ".$link);
			exit(0);
		}
			
		/* vérification de l'utilisateur */
		$ret = $this->a_session->identify(
			$user,
			$pass
		);
		/* mot de passe ou mail incorrect */
		if($ret == FALSE) {
			$this->wf->display_login(
				"Wrong email or password"
			);
		}
		/* bon login */
		else {
			header("X-Owf-Session: ".$ret["session_id"]);
			header("X-Owf-Session-Var: ".$this->a_session->session_var);
			$this->wf->redirector($link);
			exit(0);
		}
	}

	public function logout() {
		$this->wf->session()->logout();
		if(isset($this->wf->ini_arr["session"]["default_url"]))
			$link = $this->wf->linker($this->wf->ini_arr["session"]["default_url"]);
		else	
			$link = $this->wf->linker('/');
		header("Location: $link");
		exit(0);
	}
	
	public function validate() {
		$code = $this->wf->get_var("c");
		$u = new session_db_user($this->wf);
		$user = $u->get("activated", $code);
		$errors = array();
		
		if(!isset($user[0]))
			$errors[] = "Aucun utilisateur avec ce code de validation";
		
		$tpl = new core_tpl($this->wf);
		$validated = false;
		
		if(count($errors) < 1) {
			$u->modify(array("activated" => "true"), $user[0]["id"]);
			$this->wf->session_mail()->mail_validation($user[0]["id"]);
			$validated = true;
		}
		
		$tpl->set("validated", $validated);
		$this->a_admin_html->set_title("Validation du compte");
		$this->wf->admin_html()->rendering($tpl->fetch("/session/validated"));
	}
}
