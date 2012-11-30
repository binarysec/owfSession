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
				$link = $this->wf->linker('/');
			$url = "/";
		}
		else
			$link = $url;
			
		if(!isset($user) || !isset($pass)) {
			$this->wf->display_login();
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
				"Identification failed"
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
		$link = $this->wf->get_default_url();
		header("Location: $link");
		exit(0);
	}
}
