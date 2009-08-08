<?php

class session_db_user extends session_driver_user {
	private $data = array();
	private $session;
	
	public function __construct($wf) {
		$this->wf = $wf;
		
		$struct = array(
			"id" => WF_PRI,
			"email" => WF_VARCHAR,
			"password" => WF_VARCHAR,
			"name" => WF_VARCHAR,
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
		
		$this->add(
			"wf@binarysec.com", 
			"lala", 
			"Open Web Framework", 
			"session:admin"
		);
		
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * 
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function add($email, $password, $name, $type) {

		/* sanatization */
		if(!$email || !$password)
			return(FALSE);

		/* vérification si l'utilisateur existe */
		$r = $this->get("email", $email);
		if(is_array($r[0]))
			return(FALSE);


		/* input */
		$insert = array(
			"email" => $email,
			"name" => $name,
			"password" => $this->wf->hash($password),
			"create_time" => time()
		);

		/* sinon on ajoute l'utilisateur */
		$q = new core_db_insert("session_user", $insert);
		$this->wf->db->query($q);
		$uid = $this->wf->db->get_last_insert_id('session_user_id_seq');

		/* reprend les informations */
		$user = $this->get("email", $email);
		
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

		if($data["password"])
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
	public function get($conds, $extra=NULL) {
		if(is_array($conds))
			$where = $conds;
		else
			$where = array($conds => $extra);
	
		$q = new core_db_select("session_user");
		$q->where($where);
		$this->wf->db->query($q);
		$res = $q->get_result();
		return($res);
	}


}
