<?php

class session_db_user extends session_driver_user {
	private $data = array();
	
	private $session;
	private $core_cache;
	
	private $gcache;
	private $ucache = array();
	
	public function __construct($wf) {
		$this->wf = $wf;
		
		$struct = array(
			"id" => WF_PRI,
			"username" => WF_VARCHAR,
			"password" => WF_VARCHAR,
			"name" => WF_VARCHAR,
			"firstname"=>WF_VARCHAR,
			"email" => WF_VARCHAR,
			"phone"=>WF_VARCHAR,
			"create_time" => WF_INT,
			"session_id" => WF_VARCHAR,
			"session_time_auth" => WF_INT,
			"session_time" => WF_INT,
			"session_data" => WF_DATA,
			"remote_address" => WF_INT,
			"remote_hostname" => WF_VARCHAR,
			"forwarded_remote_address" => WF_INT,
			"forwarded_remote_hostname" => WF_VARCHAR
		);
		$this->wf->db->register_zone(
			"session_user", 
			"Core session table", 
			$struct
		);
		
		$this->session = $this->wf->session();

		$this->core_cache = $this->wf->core_cacher();
		$this->gcache = $this->core_cache->create_group("session_db_user_gcache");

		$r = $this->get();
		if(!isset($r[0]) || !is_array($r[0])) {
			$pass = $this->generate_password();
			$this->add(
				"OWF",
				"wf@binarysec.com", 
				$pass,
				"Open Web Framework", 
				"user",
				"session:god",
				"0262458307"
			);
			$this->wf->log(
				"Adding Default user - Login : OWF - Password : $pass"
			);
		}
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * 
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function add(	
			$username,
			$email,
			$password, 
			$name, 
			$firstname,
			$type,
			$phone=NULL
		) {
		/* sanatization */
		if(!$email || !$password || !$username)
			return(FALSE);

		/* vérification si l'utilisateur existe */
		$r = $this->get("email", $email);
		if(isset($r[0]) && is_array($r[0]))
			return(FALSE);
		
		$r = $this->get("username", $username);
		if(isset($r[0]) && is_array($r[0]))
			return(FALSE);

		/* input */
		$insert = array(
			"email" => $email,
			"name" => $name,
			"username" => $username,
			"firstname" => $firstname,
			"password" => $this->wf->hash($password),
			"create_time" => time()
		);
		if($phone)
			$insert["phone"] = $phone;

		/* sinon on ajoute l'utilisateur */
		$q = new core_db_insert("session_user", $insert);
		$this->wf->db->query($q);
		$uid = $this->wf->db->get_last_insert_id('session_user_id_seq');

		/* reprend les informations */
		$user = $this->get("username", $username);
		
		/* add initials permissions */
		$this->session->perm->user_add(
			$user[0]["id"],
			$type
		);
		
		/* retourne l'identifiant de l'utisateur créé */
		return($uid);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * 
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function remove($uid) {
		$user = $this->get("id", (int)$uid);
		if(count($user) == 0)
			return(FALSE);
			
		$q = new core_db_delete(
			"session_user", 
			array("id" => (int)$uid)
		);
		$this->wf->db->query($q);

		return(TRUE);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * 
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function modify($data, $uid) {
		if(!$data)
			return(TRUE);

		if(isset($data["password"]))
			$data["password"] = $this->wf->hash($data["password"]);
	
		$q = new core_db_update("session_user");
		$where = array("id" => (int)$uid);
		$q->where($where);
		$q->insert($data);
		$this->wf->db->query($q);
		return(TRUE);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * 
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function get($conds = NULL, $extra = NULL) {
		
		if(is_array($conds))
			$where = $conds;
		else
			if(isset($conds))
				$where = array($conds => $extra);
	
		/* create cache line */
		$cl = "session_get";
		if(is_array($where)){
			foreach($where as $k => $v)
				$cl .= "_$k:$v";
		}
		
// 		echo $cl;
// 		/* select cache */
// 		if($where["id"]) {
// 			/* check if the user exists */
// 			$r = $this->gcache->get("session_db_user_id".$where["id"]);
// 			if($r) {
// 				$this->ucache[$where["id"]] = $this->core_cache->create_group(
// 					"session_db_user_gc".
// 					$where["id"]
// 				);
// 				$c = &$this->ucache[$where["id"]];
// 			}
// 			else
// 				$c = &$this->gcache;
// 				
// 			$isthere_id = true;
// 			
// 		}
// 		else {
// 			$c = &$this->gcache;
// 			$isthere_id = false;
// 		}

		/* get cache */
		if(($cache = $this->gcache->get($cl))) {
			return($cache);
		}
		
		/* try query */
		$q = new core_db_select("session_user");
		if(is_array($where))
			$q->where($where);
		$this->wf->db->query($q);
		$res = $q->get_result();

		/* store cache */
		if(count($res) > 0)
			$this->gcache->store($cl, $res);
			
		return($res);
	}
	
	
	public function generate_ref($name,$table_name,$key_field=NULL) {
		if(!$key_field)$key_field="ref";
		
		$id_start = 3;
		$rand = "";
		while(1) {
			/* nombre aléatoire */
			$r = $this->wf->get_rand(10);
			for($a=0; $a<strlen($r); $a++) 
				$rand .= ord($r[$a]);
			
			while(1) {
				$p1 = strtoupper($name[rand(0, strlen($name)-1)]);
				$p2 = strtoupper($name[rand(0, strlen($name)-1)]);
				
				$key = $p1.$p2.
					substr($rand, 0, $id_start);
				
				if(preg_match("/^([A-Z0-9]+)$/", $key) == TRUE)
					break;
			}
			
			/* check si la ref existe */
			$q = new core_db_select($table_name);
			$where = array(
				$key_field => $key
			);
	
			$q->where($where);
			$this->wf->db->query($q);
			$res = $q->get_result();
			
			/* si on a pas de result alors c'est bon */
			if(count($res) == 0)
				break;
			
			/* augmente la taille */
			$id_start++;
		}
		return($key);
	}
	
	public function generate_password() {
		$size = rand(8, 11);
		$return = NULL;
		for($a = 0; $a <= $size; $a++) {
			$bet = rand(0x21, 0x7d);
			if(
				($bet >= 0x30 && $bet <= 0x39) ||
				($bet >= 0x41 && $bet <= 0x5a) ||
				($bet >= 0x61 && $bet <= 0x7a)
				)
				$return .= chr($bet);
			else 
				$a--;
		}
		return($return);
	}

}
