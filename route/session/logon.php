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

}
