<?php

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Web Framework 1                                       *
 * BinarySEC (c) (2000-2008) / www.binarysec.com         *
 * Author: Olivier Pascal <op@binarysec.com>             *
 * Vergoz Michael <mv@binarysec.com>					 *
 * Buchou Joris <jb@binarysec.com>						 *
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

/**
 * \page session Session
 * \class session
 * \brief Classe permettant de gerer les permissions et les comptes
 * utilisateurs
 * 
 * \author Buchou Joris
 * \author Padie Martial
 * \author Vergoz Michael
 * \date  2010
 */ 


define("SESSION_VALID",        1);
define("SESSION_TIMEOUT",      2);
define("SESSION_USER_UNKNOWN", 3);
define("SESSION_AUTH_FAILED",  4);


class session extends wf_agg {
	public $user; //!< instance de la librairie session_db_user
	public $perm; //!< instance de la librairie session_db_perm
	
	private $core_pref;
	private $data_cache = array();
	
	public $session_me; //!< utilisateur courant
	public $session_my_perms; //!< permissions de l'utilisateur courant
	private $session_data;
	public $session_timeout; //!< durée de la session
	public $session_var; //!< variables de session
	
	
	
	/*!
	 * Fonction permettant d'executer la methode appellé dans les librairies
	 * si elle n'existe pas dans l'aggregateur
	 * \param $name le nom de la librairie dans laquelle la methode se trouve
	 * \param $args tableau contenant :
	 * \li [0]=> le nom de la fonction a appeller
	 * \li [1]=> le tableau des arguments
	 * \return la valeur de retour de la fonction appellé ou FALSE si 
	 * un erreur survient.
	 */
	public function __call($name, $args) {
		if($name=="session_db_user"){
			if(!method_exists($this->user,$args[0]))
				return FALSE;	
			$r=call_user_func_array(
					array($this->user,$args[0]),
					$args[1]);
			return $r;
		}
		else if($name=="session_db_perm"){
			if(!method_exists($this->perm,$args[0]))
				return FALSE;
			$r=call_user_func_array(
					array($this->perm,$args[0]),
					$args[1]);
			return $r;
		}
		return FALSE;
	}
	
	/*!
	 * Fonction de chargement de l'aggregateur.
	 * Cette fonction utilise les librairies :
	 * \li session_db_user : gestion des utilisateurs
	 * Attention : si vous utilisez le module bsf_waf_saas cette librairie
	 * est surchargé
	 * \li session_db_perm : gestion des permissions
	 * \param wf instance du framework courant
	 */
	public function loader($wf) {
		$this->wf = $wf;

		/* load permission interface */
		$this->perm = new session_db_perm($wf);
		define("SESSION_USER_GOD",     $this->perm->register("session:god"));
		define("SESSION_USER_ADMIN",   $this->perm->register("session:admin"));
		define("SESSION_USER_MANAGER",  $this->perm->register("session:manager"));
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

	/*!
	 * Cette fonction renvoie l'utilisateur courant
	 * \return array comprenant les informations de l'utilisateur courant
	 */ 
	public function get_user() {
		return($this->session_me);
	}
	
	/*!
	 * Cette fonction renvoie les permissions de l'utilisateur courant
	 * \return array comprenant les permissions de l'utilisateur courant
	 */ 
	public function get_perms() {
		return($this->session_my_perms);
	}
	

	/*!
	 * Cette fonction permet de verifier si l'utilisateur possede les
	 * permissions passées en parametre
	 * \param need la permission necessaire
	 * \return TRUE si l'utilisateur possede la permission
	 * et FALSE sinon
	 */ 
	public function check_permission($need) {
		/* bypass */
		if(
			$this->session_my_perms["session:god"] ||
			$this->session_my_perms["session:admin"]
			) {
			return(TRUE);
		}
		
		if($this->session_my_perms["session:manager"]){
			if(is_array($need)) {
				foreach($need as $k => $v) {
					if($v == "session:simple")
						return(TRUE);
				}
			}
		}
		/* check permission */
		$ret = array();
	
		if(is_array($need)) {
			foreach($need as $k => $v) {
				if($v != "session:anon" && !$this->session_my_perms[$v])
					return(FALSE);
			}
		}
		
		return(TRUE);
	}
	

	/*!
	 * Cette fonction permet de savoir si l'utilisateur est en ligne
	 * \param uid l'identifiant de l'utilisateur
	 * \return TRUE si l'utilisateur est en ligne et FALSE sinon
	 */ 
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
	

	/*!
	 * Cette fonction permet de verifier la validité de la session
	 * \param session_id l'identifiant de la session
	 * \return Renvoie SESSION_VALID si la session est valide ou
	 * SESSION_TIMEOUT si la session est expirée
	 */ 
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
	

	
	/*!
	 * Cette fonction permet de s'authentifier à la plateforme et
	 * de verfier la validité du mot de passe. Elle permet aussi de 
	 * lancer la session et d'inscrire les informations de connection 
	 * en base de donnée.
	 * \param user la reference de l'utilisateur
	 * \param pass le mot de passe brut
	 * \return TRUE si le couple login mot de passe est correcte et FALSE sinon.
	 */ 
	public function identify($user, $pass) {
	
		/* vérification si l'utilisateur existe */
		$res = $this->user->get(array(
			"client" => $user,
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
			$this->session_me["client"].
			') from '.
			$_SERVER["REMOTE_ADDR"].
			' ('.
			$this->session_me["remote_hostname"].
			')'
		);
		
		/* !! attention redirection necessaire */
		return($this->session_me);
	}
	

	/*!
	 * Cette fonction permet de se deconnecter de la plateforme et
	 * de mettre à jour les informations de connection en base de donnée.
	 * \return TRUE
	 */ 
	public function logout() {
		$update = array(
			"remote_address"    => ip2long($_SERVER["REMOTE_ADDR"]),
// 			"remote_hostname"   => gethostbyaddr($_SERVER["REMOTE_ADDR"]),
			"session_id"        => '',
			"session_time"      => NULL
		);
		$this->user->modify($update, (int)$this->session_me["id"]);
		return(TRUE);
	}
	

	/*!
	 * Cette fonction permet de generer un identifiant de session aleatoire. 
	 * \return un identifiant de session
	 */ 
	private function generate_session_id() {
		$s1 = $this->wf->get_rand();
		$s2 = $this->wf->get_rand();
		return("E".$this->wf->hash($s1).$this->wf->hash($s2));
	}
	

	/*!
	 * Cette fonction permet de recuperer un pview, c'est à dire la liste
	 * des permissions de la vue passé en parametre.
	 * \param view le nom de la vue 
	 * \return un tableau des permissions de la vue ou NULL si rien n'a
	 * été trouvé.
	 */ 
	public function get_pview($view) {
		/** \todo caching */
		$r = $this->wf->execute_hook("session_permissions_view",array(NULL));
		
		foreach($r as $h) {
			if($h[$view])
				return($h);
		}
		return(NULL);
	}
	
	
	
	

}
