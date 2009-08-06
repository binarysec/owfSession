<?php

class session_db_user extends session_driver_user {
	private $data = array();

	public function loader($wf) {
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
			"remote_address" => WF_VARCHAR,
			"remote_hostname" => WF_VARCHAR,
			"forwarded_remote_address" => WF_VARCHAR,
			"forwarded_remote_hostname" => WF_VARCHAR,
			"data" => WF_DATA
		);
		$this->wf->db->register_zone(
			"session_db_user", 
			"Core session table", 
			$struct
		);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * 
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function add($data) {
		/* sanatization */
		if(!$data["email"] || !$data["password"])
			return(FALSE);

		/* vérification si l'utilisateur existe */
		if($this->get("email", $data["email"]))
			return(FALSE);

		if(count($data["permissions"]) <= 0)
			$data["permissions"] = array(WF_USER_SIMPLE);
		
		/* input */
		$insert = array(
			"email" => $data["email"],
			"name" => $data["name"],
			"password" => md5($data["password"]),
			"create_time" => time(),
			"data" => serialize($data["data"])
		);

		/* sinon on ajoute l'utilisateur */
		$q = new core_db_insert("session_db_user", $insert);
		$this->wf->db->query($q);
		$uid = $this->wf->db->get_last_insert_id('session_db_user_id_seq');

		/* reprend les informations */
		$user = $this->get("email", $data["email"]);

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
			"session_db_user", 
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
		if(!$insert)
			return(TRUE);

		$q = new core_db_update("session_db_user");
		$where = array("id" => $uid);
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
		if($extra && is_string($conds))
			$where = array($conds, $extra);
		else
			$where = &$conds;
			
		$q = new core_db_select("session_db_user");
		$q->where($where);
		$this->wf->db->query($q);
		$res = $q->get_result();
		return($res);
	}

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Add user specific information
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function set_data($data, $uid) {
		$this->data = array_merge($this->data, $data);

		/* update les informations dans la bdd */
		$q = new core_db_update("session_db_user");
		$where = array(
			"id" => (int)$uid
		);
		$update = array(
			"session_data" => serialize($this->data)
		);

		$q->where($where);
		$q->insert($update);
		$this->wf->db->query($q);
	}
	
	public function unset_data($list) {
		foreach($list as $v)
			unset($this->data[$v]);
	}
	
	public function get_data($key) {
		return($this->data[$key]);
	}
}
