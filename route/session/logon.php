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
	}

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * fonction de traitement de l'autentification
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function login() {
		/* prend les inputs */
		$user = $this->wf->get_var("user");
		$pass = $this->wf->get_var("pass");

		$url = base64_decode($this->wf->get_var("back_url"));
		
		if(!$url)
			$url = $this->wf->linker("/");
		
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
			if(strlen($url) <= 1) {
				if(isset($this->wf->ini_arr["session"]["default_url"]))
					$link = $this->wf->linker($this->wf->ini_arr["session"]["default_url"]);
				else	
					$link = $this->wf->linker('/');
				
				header("X-Owf-Session: ".$ret["session_id"]);
				header("X-Owf-Session-Var: ".$this->a_session->session_var);
				header("Location: ".$link);
				exit(0);
			}
			header("X-Owf-Session: ".$ret["session_id"]);
			header("X-Owf-Session-Var: ".$this->a_session->session_var);
			header("Location: ".$url);
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
		
		if(count($errors) < 1) {
			$u->modify(array("activated" => "true"), $user[0]["id"]);
			$this->wf->session_mail()->mail_validation($user[0]["id"]);
		}
		
		$this->wf->core_request()->set_header(
			'Location',
			$this->wf->linker('/')
		);
		$this->wf->core_request()->send_headers();
		exit(0);
	}
}
