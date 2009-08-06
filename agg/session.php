<?php

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Web Framework 1                                       *
 * BinarySEC (c) (2000-2008) / www.binarysec.com         *
 * Author: Michael Vergoz <mv@binarysec.com>             *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~         *
 *  Avertissement : ce logiciel est protégé par la       *
 *  loi du copyright et par les traités internationaux.  *
 *  Toute personne ne respectant pas ces dispositions    *
 *  se rendra coupable du délit de contrefaçon et sera   *
 *  passible des sanctions pénales prévues par la loi.   *
 *  Il est notamment strictement interdit de décompiler, *
 *  désassembler ce logiciel ou de procèder à des        *
 *  opération de "reverse engineering".                  *
 *                                                       *
 *  Warning : this software product is protected by      *
 *  copyright law and international copyright treaties   *
 *  as well as other intellectual property laws and      *
 *  treaties. Is is strictly forbidden to reverse        *
 *  engineer, decompile or disassemble this software     *
 *  product.                                             *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

define("SESSION_VALID",        1);
define("SESSION_TIMEOUT",      2);
define("SESSION_USER_UNKNOWN", 3);
define("SESSION_AUTH_FAILED",  4);

class session extends wf_agg {
	private $user;
	private $perm;
	private $me;
	private $core_pref;
	
	private $session_timeout;
	private $session_var;
	
	public function loader($wf) {
		$this->wf = $wf;
		
		/* load user interface */
		$this->user = new session_db_user($wf);
		
		/* registre session preferences group */
		$this->core_pref = $this->core_pref()->register_group(
			"session", 
			"Session"
		);
		
		/* session variable */
		if($this->wf->ini_arr["session"]["variable"])
			$this->session_var = &$this->wf->ini_arr["session"]["variable"];
		else {
			$this->session_var = $this->core_pref->register(
				"variable",
				"Variable context",
				CORE_PREF_VARCHAR,
				"session".rand()
			);
		}

		/* session timeout */
		$this->session_timeout = $this->core_pref->register(
			"timeout",
			"Session timeout",
			CORE_PREF_NUM,
			3600
		);
	}

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Checking session
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function check_session($session_id=NULL) {
		/* try to get existing session */
		$session = $_COOKIE[$this->session_var];
		$res = $this->cache->get("auth_$session");
		if(!$res) {
			$res = $this->user->get("session_id", $session);
			if(count($res) != 0)
				$this->cache->store("auth_$session", $res);
		}

		/* no existing session, open anonymous session */
		if(!$session || count($res) == 0) {
			if($this->wf->ini_arr["session"]["allow_anonymous"]) {
				$this->me = array(
					"id"              => -1,
					"remote_address"  => $_SERVER["REMOTE_ADDR"],
					"remote_hostname" => gethostbyaddr($_SERVER["REMOTE_ADDR"]),
					"session_time"    => time()
				);
				return(SESSION_VALID);
			}
			else {
				return(SESSION_TIMEOUT);
			}
		}

		$this->me = $res[0];

		/* vérfication du timeout */
		if(time() - $this->me["session_time"] > $this->session_timeout) {
			return(SESSION_TIMEOUT);
		}

		/* modification de l'adresse en base + time update */
		$update = array(
			"remote_address"  => $_SERVER["REMOTE_ADDR"],
			"remote_hostname" => gethostbyaddr($_SERVER["REMOTE_ADDR"]),
			"session_time"    => time()
		);
		$res = $this->user->modify($update, (int)$this->me["id"]);
		
		$where = array(
			"id" => (int)$this->me["id"]
		);


		/* utilisation d'un cookie */
		setcookie(
			$this->session_var,
			$session,
			time()+$this->session_timeout,
			"/"
		);
		
		return(SESSION_VALID);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Auth
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function identify($user, $pass) {
	
		/* vérification si l'utilisateur existe */
		$res = $this->user->get(array(
			"email" => $user,
			"password" => md5($pass)
		));

		if(count($res[0]))
			return(FALSE);
		
		$this->me = $res[0];

		/* update les informations dans la bdd */
		$update = array(
			"session_id"        => $this->generate_session_id(),
			"session_time_auth" => time(),
			"session_time"      => time(),
			"remote_address"    => $_SERVER["REMOTE_ADDR"],
			"remote_hostname"   => gethostbyaddr($_SERVER["REMOTE_ADDR"])
		);
		$this->user->modify($update, (int)$this->me["id"]);

// 		/* merge data & update */
// 		$this->me = array_merge($this->me, $update);
		
		/* utilisation d'un cookie */
		setcookie(
			$this->sess_var,
			$update["session_id"],
			time()+$this->sess_timeout,
			"/"
		);

		/* !! attention redirection necessaire */
		return($this->me);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Logout function
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function logout() {
		$update = array(
			"remote_address"    => $_SERVER["REMOTE_ADDR"],
			"remote_hostname"   => gethostbyaddr($_SERVER["REMOTE_ADDR"]),
			"session_id"        => '',
			"session_time"      => NULL
		);
		$this->user->modify($update, (int)$this->me["id"]);
		return(TRUE);
	}

	
}