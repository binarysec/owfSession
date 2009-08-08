<?php

class session_pview {
	protected $wf;
	protected $view_name;
	protected $obj_id;
	protected $resolv = array();
	protected $view_info;
	
	protected $session;
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * 
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function __construct($wf, $view_name, $obj_id, $resolv) {
		$this->wf = $wf;
		$this->session = $this->wf->session();
		
		/* get view */
		$p = $this->session->get_pview(
			$view_name
		);
		$this->view_info = array(key($p), $p[key($p)]);
		
		$this->view_name = $view_name;
		$this->obj_id = $obj_id;
		$this->resolv = $resolv;
		
/*		
		var_dump($this->view_info);
		
		exit(0);*/
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * 
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function get_data() {
	
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * 
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function get_link($name) {
		$buf = '<a href="'.
			$this->wf->linker("/session/permissions").
			'?pview='.
			$this->view_name.
			'">'.
			$name.
			'</a>';
			
		return($buf);
	}
	
	
	
}
