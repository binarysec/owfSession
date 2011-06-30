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
	public $user;
	public $perm;
	
	private $core_pref;
	private $data_cache = array();
	
	public $session_me;
	public $session_my_perms;
	private $session_data;
	public $session_timeout;
	public $session_var;
	
	
	public function loader($wf) {
		$this->wf = $wf;
		
		/* load permission interface */
		$this->perm = new session_db_perm($wf);
		define("SESSION_USER_GOD",     $this->perm->register("session:god"));
		define("SESSION_USER_ADMIN",   $this->perm->register("session:admin"));
		define("SESSION_USER_SIMPLE",  $this->perm->register("session:simple"));
		define("SESSION_USER_ANON",    $this->perm->register("session:anon"));
		define("SESSION_USER_RANON",   $this->perm->register("session:ranon"));
		define("SESSION_USER_WS",      $this->perm->register("session:ws"));

		/* load user interface */
		$this->user = new session_db_user($wf);
		
		
		/* registre session preferences group */
		$this->core_pref = $this->wf->core_pref()->register_group(
			"session", 
			"Session"
		);
		
		/* session variable */
		if(isset($this->wf->ini_arr["session"]["variable"]))
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
		$this->lang = $this->wf->core_lang()->get_context(
			"session/profil"
		);
	}

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Get current user
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function get_user() {
		return($this->session_me);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Get current permissions
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function get_perms() {
		return($this->session_my_perms);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Check permissions
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function check_permission($need) {
		
		if(isset($this->session_my_perms["session:god"]))
			return(true);

		/* check permission */
		if(is_array($need)) {
			foreach($need as $k => $v) {
				if(
					$v != "session:anon" && 
					!$this->session_my_perms[$v]
					) {
					if($v == "session:god")
						return(false);
					else if(isset($this->session_my_perms["session:admin"]))
						return(true);
					return(false);
				}
			}
		}
		else {
			if(
				$need != "session:anon" && 
				!$this->session_my_perms[$need]
				) {
					
				if($need == "session:god")
					return(false);
				else if(isset($this->session_my_perms["session:admin"]))
					return(true);
				else if(!$need)
					return(true);
				return(false);
			}
		}
		
		return(true);
	}
	
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Am i admin ?
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function iam_admin() {
		if(isset($this->session_my_perms["session:god"]))
			return(true);
		if(isset($this->session_my_perms["session:admin"]))
			return(true);
		return(false);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Am i god ?
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function iam_god() {
		if(isset($this->session_my_perms["session:god"]))
			return(true);
		return(false);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * User online?
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function is_online($uid=NULL) {
		if($uid) {
			$res = $this->user->get("id", $uid);
			$res = $res[0];
		}
		else
			$res = $this->get_user();
		$online = time() - $res['session_time'];
		if($online > $this->session_timeout) 
			return(FALSE);
		return(TRUE);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Checking session
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function check_session($session_id=NULL) {
		/* try to get existing session */
		$session = $_COOKIE[$this->session_var];

// 		$res = $this->cache->get("auth_$session");
// 		if(!$res) {
			$res = $this->user->get("session_id", $session);
// 			if(count($res) != 0)
// 				$this->cache->store("auth_$session", $res);
// 		}

		/* no existing session, open anonymous session */
		if(count($res) == 0) {
			if($this->wf->ini_arr["session"]["allow_anonymous"]) {
				$this->session_me = array(
					"id"              => -1,
					"remote_address"  => ip2long($_SERVER["REMOTE_ADDR"]),
// 					"remote_hostname" => gethostbyaddr($_SERVER["REMOTE_ADDR"]),
					"session_time"    => time()
				);
				return(SESSION_VALID);
			}
			else {
				return(SESSION_TIMEOUT);
			}
		}

		/* point to the data */
		$this->session_me = $res[0];

		/* load user permissions */
		$this->session_my_perms = $this->perm->user_get($res[0]["id"]);
 
		/* vérfication du timeout */
		if(time() - $this->session_me["session_time"] > $this->session_timeout) {
			return(SESSION_TIMEOUT);
		}

		/* modification de l'adresse en base + time update */
		$update = array(
			"remote_address"  => ip2long($_SERVER["REMOTE_ADDR"]),
// 			"remote_hostname" => gethostbyaddr($_SERVER["REMOTE_ADDR"]),
			"session_time"    => time()
		);
		$res = $this->user->modify($update, (int)$this->session_me["id"]);
		
		$where = array(
			"id" => (int)$this->session_me["id"]
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
			"username" => $user,
			"password" => $this->wf->hash($pass)
		));
	

		if(!is_array($res[0])) {
			/* log */
			$this->wf->log(
				"Login ATTEMPT from ".
				$_SERVER["REMOTE_ADDR"].
				' ('.
// 				gethostbyaddr($_SERVER["REMOTE_ADDR"]).
				')'
			);
		
			return(FALSE);
		}
	
		/* point to the data */
		$this->session_me = $res[0];

		/* load user permissions */
		$this->session_my_perms = $this->perm->user_get($res[0]["id"]);

		/* update les informations dans la bdd */
		$update = array(
			"session_id"        => $this->generate_session_id(),
			"session_time_auth" => time(),
			"session_time"      => time(),
			"remote_address"    => ip2long($_SERVER["REMOTE_ADDR"]),
// 			"remote_hostname"   => gethostbyaddr($_SERVER["REMOTE_ADDR"])
		);
		$this->user->modify($update, (int)$this->session_me["id"]);
		$this->session_me = array_merge($this->session_me, $update);
		
// 		/* merge data & update */
// 		$this->session_me = array_merge($this->session_me, $update);
		
		/* utilisation d'un cookie */
		setcookie(
			$this->session_var,
			$update["session_id"],
			time()+$this->session_timeout,
			"/"
		);

		/* log */
		$this->wf->log(
			"Login SUCCESS for user ".
			$this->session_me["name"].
			' ('.
			$this->session_me["email"].
			') from '.
			$_SERVER["REMOTE_ADDR"].
			' ('.
			$this->session_me["remote_hostname"].
			')'
		);
		
		/* !! attention redirection necessaire */
		return($this->session_me);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Logout function
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function logout() {
		$update = array(
			"remote_address"    => ip2long($_SERVER["REMOTE_ADDR"]),
// 			"remote_hostname"   => gethostbyaddr($_SERVER["REMOTE_ADDR"]),
			"session_id"        => '',
			"session_time"      => NULL
		);
		setcookie(
			$this->session_var,
			$session,
			time(),
			"/"
		);
		$this->user->modify($update, (int)$this->session_me["id"]);
		return(TRUE);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Generate a session id
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	private function generate_session_id() {
		$s1 = $this->wf->get_rand();
		$s2 = $this->wf->get_rand();
		return("E".$this->wf->hash($s1).$this->wf->hash($s2));
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Get a registered permissions view
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function get_pview($view) {
		/** \todo caching */
		$r = $this->wf->execute_hook("session_permissions_view");
		foreach($r as $h) {
			if($h[$view])
				return($h);
		}
		return(NULL);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Search user from database  
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function search_user_db($query, $search, $comp="~=") {
	
		/* check permissions, only admin can looks at user db */
		if(!$this->iam_admin()) 
			return(false);
			
		$query->alias("session_user", "session_user");
		
		$query->do_comp("session_user.firstname", $comp, $search);
		$query->do_or();
		$query->do_comp("session_user.name", $comp, $search);
		$query->do_or();
		$query->do_comp("session_user.username", $comp, $search);
		$query->do_or();
		$query->do_comp("session_user.email", $comp, $search);
		
		return(true);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Create link for user session table
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function search_user_link($data) {
		/* put information */
		$ret = 
			$this->lang->ts("Compte")." : <strong>".$data["username"]."</strong><br/>".
			$this->lang->ts("Nom")." : ".$data["firstname"]." ".$data["name"]."<br/>".
			$this->lang->ts("Email")." : ".$data["email"]
		;
		return($ret);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Backend function to retrieve information about the current user
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function json_info() {
		$sm = $this->session_me;
	
		unset($sm["password"]);
		unset($sm["session_id"]);
	
		$online = time() - $sm['session_time'];
		if($online > $this->session_timeout) 
			return(false);
		
		return(array(
			"info" => $sm,
			"perm" => $this->session_my_perms,
		));
	}

}
