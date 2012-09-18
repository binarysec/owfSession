<?php

if(!defined("OWFCONSOLE"))
	die("This script should be ran using owfconsole.php");

class session_cleanusers extends wf_cli_command {
	function process() {
		$this->wf->msg("Cleaning unactivated users.");
		$q = new core_db_adv_delete("session_user");
		$q->do_comp("activated", "!==", "true");
		$this->wf->db->query($q);
		return true;
	}
}