<?php

if(!defined("OWFCONSOLE"))
	die("This script should be ran using owfconsole.php");

class session_cleanusers extends wf_cli_command {
	function process() {
		
		/* getting validation timeout */
		$force = in_array("f", $this->opts) || array_key_exists("force", $this->opts);
		$timeout = 0;
		
		$pref_grp = $this->wf->core_pref()->register_group("session");
		if($pref_grp)
			$timeout = $pref_grp->get_value("email_validation_timeout");
		
		/* cleaning users */
		$this->wf->msg("Cleaning unactivated users.");
		
		$q = new core_db_adv_delete("session_user");
		$q->do_comp("activated", "!==", "true");
		
		if($timeout > 0 && !$force)
			$q->do_comp("create_time", "<=", time() - $timeout);
		
		$this->wf->db->query($q);
		
		return true;
	}
}