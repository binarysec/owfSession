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
	private $core_lang;
	private $data_cache = array();
	
	public $session_me;
	public $session_my_perms;
	
	private $session_data;
	
	public $session_timeout;
	public $session_var;
	public $session_sender;
	
	private $v_session_me;
	private $v_session_my_perms;
	
	public function loader($wf) {
		
		$this->a_core_request = $this->wf->core_request();

		/* load permission interface */
		$this->perm = new session_db_perm($wf);
		define("SESSION_USER_GOD",     $this->perm->register("session:god"));
		define("SESSION_USER_ADMIN",   $this->perm->register("session:admin"));
		define("SESSION_USER_SIMPLE",  $this->perm->register("session:simple"));
		define("SESSION_USER_ANON",    $this->perm->register("session:anon"));
		define("SESSION_USER_RANON",   $this->perm->register("session:ranon"));
		define("SESSION_USER_WS",      $this->perm->register("session:ws"));
		foreach($this->wf->execute_hook("session_permissions") as $perm)
			if($perm && is_array($perm))
				foreach($perm as $name => $desc)
					$this->perm->register($name);

		/* load user interface */
		$this->user = new session_db_user($wf);
		
		$this->core_lang = $this->wf->core_lang();
		
		/* register session preferences group */
		$this->core_pref = $this->wf->core_pref()->register_group(
			"session", 
			"Session"
		);
		
		/* session variable */
		if(isset($this->wf->ini_arr["session"]["variable"]))
			$this->session_var = &$this->wf->ini_arr["session"]["variable"];
		else {
			$this->session_var = $this->core_pref->get_value("variable");
		}
		/* session timeout */
		$this->session_timeout = $this->core_pref->get_value("timeout");
		
		$this->session_sender = $this->core_pref->get_value("sender");
		
		/* cookie host */
		$this->cookie_host = $this->core_pref->register(
			"host",
			"Session host for cookie",
			CORE_PREF_VARCHAR,
			""
		);
		
		$this->lang = $this->wf->core_lang()->get_context(
			"session/profil"
		);
		
		$this->pview = $this->wf->session_pview();
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
	

	public function store_virtual($uid) {
		$res = $this->user->get("id", $uid);
		if(count($res) <= 0)
			return(false);
		
		/* store */
		$this->v_session_me = $this->session_me;
		$this->v_session_my_perms = $this->session_my_perms;
		
		/* change */
		$this->session_me = $res[0];
		$this->session_my_perms = $this->perm->user_get($res[0]["id"]);
		
		$this->core_lang->set($res[0]["lang"]);
		
		return(true);
	}
	
	public function restore_virtual() {
		/* store */
		$this->session_me = $this->v_session_me;
		$this->session_my_perms = $this->v_session_my_perms;
	}
	
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Check permissions
	 * if $require_all_perms is on, every permission of the given array is required
	 * otherwise, only one permission of the array is required
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function check_permission($need, $require_all_perms = true) {
		
		/* if user is god or no permissions are required */
		if(isset($this->session_my_perms["session:god"]) || is_null($need))
			return(true);

		/** \todo must check if anon is authorized by the ini file */
		
		/* check permission */
		if(is_array($need)) {
			
			$allowed = $require_all_perms;
			
			foreach($need as $v) {
				
				/* is the current user forbidden on this perm ? */
				$forbidden =
					$v != "session:ranon" && 
					$v != "session:anon" && 
					!isset($this->session_my_perms[$v]) &&
					(!isset($this->session_my_perms[(string)"session:admin"]) ||
					$v == "session:god")
				;
				
				if(	($forbidden && $require_all_perms) ||
					(!$forbidden && !$require_all_perms)
					)
						$allowed = !$require_all_perms;
			}
			
			return($allowed);
		}
		else {
			/* if required permissions are ranon, anon, or if user has permissions, or if user is admin and required permission is not god */
			return
				$need == "session:ranon" ||
				$need == "session:anon" ||
				isset($this->session_my_perms[$need]) ||
					(isset($this->session_my_perms["session:admin"]) &&
					$need != "session:god")
			;
		}
		
		return(true);
	}
	
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Am i admin ?
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function iam_admin() {
		return 
			is_array($this->session_my_perms) && (
			array_key_exists("session:god", $this->session_my_perms) ||
			array_key_exists("session:admin", $this->session_my_perms))
		;
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Am i god ?
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function iam_god() {
		return
			is_array($this->session_my_perms) &&
			array_key_exists("session:god", $this->session_my_perms)
		;
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Am i user manager ?
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function iam_manager() {
		return $this->iam_admin() || array_key_exists("session:manage", $this->session_my_perms);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * User online?
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function is_online($uid = 0) {
		$user = null;
		
		if($uid > 0) {
			$user = $this->user->get("id", (int) $uid);
			$user = isset($user[0]) ? $user[0] : null;
		}
		
		if($user == null)
			$user = $this->get_user();
		
		$online = time() - (int) $user['session_time'];
		
		return $user["id"] > 0 && $online < $this->session_timeout;
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Checking session
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function check_session($session_id=NULL) {
		/* try to get existing session */
		$session = isset($_COOKIE[$this->session_var]) ? $_COOKIE[$this->session_var] : NULL;

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

		/* vérfication du timeout */
		if(time() - $this->session_me["session_time"] > $this->session_timeout) {
			$this->session_me = array(
				"id"              => -1,
				"remote_address"  => ip2long($_SERVER["REMOTE_ADDR"]),
// 				"remote_hostname" => gethostbyaddr($_SERVER["REMOTE_ADDR"]),
				"session_time"    => time()
			);
				
			return(SESSION_TIMEOUT);
		}

		/* getting lang */
		$se = $this->core_lang->get();
		
		/* load user permissions */
		$this->session_my_perms = $this->perm->user_get($res[0]["id"]);
		
		/* modification de l'adresse en base + time update */
		$update = array(
			"remote_address"  => ip2long($_SERVER["REMOTE_ADDR"]),
// 			"remote_hostname" => gethostbyaddr($_SERVER["REMOTE_ADDR"]),
//			"lang"            => $se['code'],
			"session_time"    => time()
		);
		$res = $this->user->modify($update, (int)$this->session_me["id"]);
		
		$where = array(
			"id" => (int)$this->session_me["id"]
		);

		/* utilisation d'un cookie */
		$c = $this->session_var."=$session; expires=".date(DATE_COOKIE, time()+$this->session_timeout)."; path=/";
		$this->a_core_request->set_header(
			"Set-Cookie", 
			$c
		);

		return(SESSION_VALID);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Auth
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function identify($user, $pass, $passCypher=false) {
		$this->wf->no_cache();
		$remote_addr = isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : null;
		
		if($passCypher == false)
			$checkPass = $this->wf->hash($pass);
		else
			$checkPass = $pass;
		
		/* vérification si l'utilisateur existe */
		$res = $this->user->get(array(
			"username" => $user,
			"password" => $checkPass
		));

		if(!isset($res[0]) || !is_array($res[0])) {
			/* log */
			$this->wf->log(
				"Login FAILED from $remote_addr with login ($user), user or password incorrect"
			);
		
			return(FALSE);
		}
		
		if(!is_null($res[0]["activated"]) && $res[0]["activated"] != "true") {
			/* log */
			$this->wf->log(
				"Login ATTEMPT from $remote_addr with login ($user), account not activated yet"
			);
			
			return(FALSE);
		}
	
		/* point to the data */
		$this->session_me = $res[0];

		/* load user permissions */
		$this->session_my_perms = $this->perm->user_get($res[0]["id"]);

		/* checking if we need to update session id */
		if(time()-$res[0]['session_time'] > $this->session_timeout) {
			/* update les informations dans la bdd */
			$update = array(
				"session_id"        => $this->generate_session_id(),
				"session_time"      => time(),
				"remote_address"    => ip2long($remote_addr),
				"remote_hostname"   => gethostbyaddr($_SERVER["REMOTE_ADDR"])
			);
			$this->user->modify($update, (int)$this->session_me["id"]);
			$this->session_me = array_merge($this->session_me, $update);
		}
		
		/* update les informations dans la bdd */
		$update = array(
			"session_time_auth" => time()
		);
		$this->user->modify($update, (int)$this->session_me["id"]);
			
		/* utilisation d'un cookie */
		$this->setcookie(
			$this->session_var,
			$this->session_me["session_id"],
			time()+$this->session_timeout
		);

		/* log */
		$this->wf->log(
			"Login SUCCESS for user ".
			$this->session_me["firstname"]." ".
			$this->session_me["name"].
			' ('.
			$this->session_me["username"].
			') from '.
			$remote_addr.
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
		$this->wf->no_cache();
		$update = array(
			"remote_address"    => ip2long($_SERVER["REMOTE_ADDR"]),
// 			"remote_hostname"   => gethostbyaddr($_SERVER["REMOTE_ADDR"]),
// 			"session_id"        => '',
			"session_time"      => 0
		);
		$this->setcookie($this->session_var, "", time() - 3600);
		$this->user->modify($update, (int)$this->session_me["id"]);
		return true;
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
	
		return(array(
			"info" => $sm,
			"perm" => $this->session_my_perms,
		));
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Return total number of OWF users
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function user_count() {
		$q = new core_db_select("session_user", array("COUNT(*)"), array());
		$this->wf->db->query($q);
		$res = $q->get_result();
		return isset($res[0]["COUNT(*)"]) ? (int) $res[0]["COUNT(*)"] : 0;
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Set the cookie with proper host
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function setcookie($name, $value, $expire = 0, $path = "/") {
		if($this->cookie_host)
			setcookie($name, $value, $expire, $path, $this->cookie_host);
		else
			setcookie($name, $value, $expire, $path);
	}
}
