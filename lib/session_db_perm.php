<?php


class session_db_perm extends session_driver_perm {


	public function __construct($wf) {
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
			"obj_type" => WF_INT,
			"obj_id" => WF_INT,
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
	public function user_add($uid, $objtype, $oid=NULL, $data=NULL) {
		/* sanatize */
		if($uid == NULL || $objtype == NULL)
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
			$objtype_id = $this->wf->db->get_last_insert_id(
				'session_perm_type_seq'
			);
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
		if(is_array($conds))
			$where = $conds;
		else
			$where = array($conds => $extra);
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
	
		if(!is_array($where))
			$where = array($where => $extra);
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
	public function user_get($uid=NULL, $obj_type=NULL, $obj_id=NULL) {
		
		/* begin the search */
		$where = array("ptr_type" => SESSION_PERM_USER);
		$cl = "session/db/sdbp";
		if($uid) {
			$where["ptr_id"] = (int)$uid;
			$cl .= "/$uid";
		}
		
		if(is_string($obj_type)) {
			$r = $this->get_type("name", $obj_type);
			$where["obj_type"] = (int)$r[0]["id"];
			$cl .= "/ts$obj_type";
		}
		else if(is_int($obj_type)) {
			$where["obj_type"] = $obj_type;
			$cl .= "/ti$obj_type";
		}
	
		if($obj_id) {
			$where["obj_id"] = $obj_id;
			$cl .= "/ii$obj_id";
		}

		/* restoring cache */
		
		/* executing request */
		$q = new core_db_select("session_perm");
		$q->where($where);
		$this->wf->db->query($q);
		$res = $q->get_result();

		/* managing datas */
		$ret = array();
		foreach($res as $t) {
			$gt = $this->get_type("id", $t["obj_type"]);
			$i = array(
				"obj_type" => (int)$t["obj_type"],
				"obj_id" => (int)$t["obj_id"],
				"name" => $gt[0]["name"],
				"value" => unserialize($t["value"])
			);
			if(!is_array($ret[$i["name"]]))
				$ret[$i["name"]] = array();
			$ret[$i["name"]][] = $i;
		}

		/* store $ret */
		return($ret);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * 
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function register($name) {
		$res = $this->get_type("name", $name);
		if(!is_array($res[0])) {
			$insert = array(
				"create_t" => time(),
				"name" => $name
			);
			$q = new core_db_insert("session_perm_type", $insert);
			$this->wf->db->query($q);
			$pid = $this->wf->db->get_last_insert_id('session_perm_type_seq');
			return($pid);
		}
		
		return($res[0]["id"]);
		
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
		if(is_array($conds))
			$where = $conds;
		else
			$where = array($conds => $extra);
			
		$q = new core_db_select("session_perm_type");
		$q->where($where);
		$this->wf->db->query($q);
		$res = $q->get_result();
		return($res);
	}
	
}