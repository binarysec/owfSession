<?php

class wfr_session_session_permissions extends wf_route_request {

	var $a_session;
	var $a_core_html;
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * constructeur
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function __construct($wf) {
		$this->wf = $wf;
		$this->a_session = $this->wf->session();
// 		$this->a_core_html = $this->wf->core_html();
	}

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Master print function
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */	
	public function show_acl() {
		$pview_name = $this->wf->get_var("pview");
		
		$pview = $this->a_session->get_pview($pview_name);
		if(!$pview) {
			header("Location: /");
			exit(0);
		}
		
		
	}
	
	
}
