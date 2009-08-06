<?php


class session_db_perm extends session_driver_perm {


	public function loader($wf) {
		$this->wf = $wf;
		
		$struct = array(
			"id" => WF_PRI,
			"create_t" => WF_INT,
			"name" => WF_VARCHAR
		);
		$this->wf->db->register_zone(
			"session_perm_type", 
			"Session permissions type", 
			$struct
		);

		$struct = array(
			"id" => WF_PRI,
			"create_t" => WF_INT,
			"ptr_type" => WF_INT,
			"ptr_id" => WF_INT,
			"obj_type" => WF_VARCHAR,
			"obj_id" => WF_VARCHAR,
			"data" => WF_DATA
		);
		$this->wf->db->register_zone(
			"session_perm", 
			"Session permissions", 
			$struct
		);


	}
	

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * 
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function user_add($uid, $objtype, $oid, $data) {
		/* sanatize */
		if(!$uid || !$objtype || !$oid)
			return(FALSE);
		
		/* resolv object type */
		$objtype = strtolower($objtype);
		$r = $this->get_type("name", $objtype);
		if(count($r) == 0) {
			/* type doesn't exists, create it */
			$insert = array(
				"create_t" => time(),
				"name" => $objtype,
			);
			$q = new core_db_insert("session_perm_type", $insert);
			$this->wf->db->query($q);
			$objtype_id = $this->wf->db->get_last_insert_id('session_perm_type_seq');
		}
		else
			$objtype_id = &$r[0]["id"];
		
		/*! \todo check if the user exists */
		
		/* look if the input exists */
		$r = $this->get(array(
			"ptr_type" => SESSION_PERM_USER,
			"ptr_id" => (int)$uid,
			"obj_type" => $objtype_id,
			"obj_id" => (int)$oid
		));
		if(count($r) > 0)
			return(TRUE);
		
		/* insert new data */
		$insert = array(
			"create_t" => time(),
			"ptr_type" => SESSION_PERM_USER,
			"ptr_id" => (int)$uid,
			"obj_type" => $objtype_id,
			"obj_id" => (int)$oid,
			"data" => serialize($data)
		);
		$q = new core_db_insert("session_perm", $insert);
		$this->wf->db->query($q);
		$pid = $this->wf->db->get_last_insert_id('session_perm_seq');
		return($pid);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * 
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function user_remove($conds, $extra=NULL) {
		if($extra && is_string($conds))
			$where = array($conds, $extra);
		else
			$where = &$conds;
		$where["ptr_type"] = SESSION_PERM_USER;
		$q = new core_db_delete("session_perm", $where);
		$this->wf->db->query($q);
		return(TRUE);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * 
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function user_mod($update, $where, $extra=NULL) {
	
		if($extra && is_string($conds))
			$where = array($conds, $extra);
		else
			$where = &$conds;
		$where["ptr_type"] = SESSION_PERM_USER;
		$q = new core_db_update("session_perm");
		$q->where($where);
		$q->insert($update);
		$this->wf->db->query($q);
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
			
		$q = new core_db_select("session_perm");
		$q->where($where);
		$this->wf->db->query($q);
		$res = $q->get_result();
		return($res);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * 
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function get_type($conds, $extra=NULL) {
		if($extra && is_string($conds))
			$where = array($conds, $extra);
		else
			$where = &$conds;
			
		$q = new core_db_select("session_perm_type");
		$q->where($where);
		$this->wf->db->query($q);
		$res = $q->get_result();
		return($res);
	}
	
}