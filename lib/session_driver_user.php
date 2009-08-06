<?php

abstract class session_driver_user {
	protected $wf;
	
	abstract public function loader($wf);
	
	
	public function generate_session_id() {
		$s1 = $this->wf->get_rand();
		return("E".sha1($s1));
		
	}
	
	
}
