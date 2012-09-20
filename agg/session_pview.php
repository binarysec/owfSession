<?php

class session_pview_item {
	public function __construct($wf, $name, $struct) {
		$this->wf = $wf;
		$res = $this->wf->session()->perm->get_type("name", $name);
		
		if(!isset($res[0]))
			throw new wf_exception(
				$this->wf,
				WF_EXC_PRIVATE,
				"Cannot make pview, permission $name does not exist"
			);
		
		$this->perm = current($res);
		$this->name = $name;
		$this->default_struct = $this->struct = $struct;
		foreach($this->default_struct as $k => $v)
			$this->default_struct[$k] = "off";
	}
	
	public function get_title($oid = null) {
		return "Editing permission of ".$this->name.
			($oid != null ? " with oid #$oid" : "");
	}
	
	public function get_link($name) {
		return "<a class='btn three' href='".
			$this->wf->linker("/session/permissions").
			"?pview=".$this->name."&amp;oid=$oid'>$name</a>";
	}
	
	// todo : cache query
	public function get_data($oid, $uid = 0) {
		
		/* query */
		$q = new core_db_adv_select();
		$q->alias("p", "session_perm");
		$q->do_comp("p.ptr_type", "=", SESSION_PERM_USER);
		$q->do_comp("p.obj_type", "=", $this->perm["id"]);
		$q->do_comp("p.obj_id", "=", $oid);
		if($uid > 0)
			$q->do_comp("p.ptr_id", "=", $uid);
		
		$this->wf->db->query($q);
		$results = $q->get_result();
		
		foreach($results as $k => $perm)
			$results[$k]["data"] = unserialize($perm["data"]);
		
		return $results;
	}
	
	public function add($uid, $oid) {
		return $this->wf->session()->perm->user_add(
			(int) $uid,
			$this->name,
			(int) $oid,
			$this->default_struct
		);
	}
	
	public function del($uid, $oid) {
		return $this->a_session->perm->user_remove(array(
			"ptr_id" => (int) $uid,
			"obj_type" => $this->perm["id"],
			"obj_id" => (int) $oid
		));
	}
	
	public function set($uid, $oid, array $values = array()) {
		
		/* if values is malformed */
		if(empty($values))
			return false;
		
		foreach($values as $name => $val)
			if(!isset($this->struct[$name]))
				return false;
		
		/* process update */
		$data = current($this->get_data($oid, $uid));
		foreach($data["data"] as $perm => $value)
			if(isset($values[$perm]))
				$data["data"][$perm] = $values[$perm];
		
		/* update database */
		$this->wf->session()->perm->user_mod(
			array("data" => serialize($data["data"])),
			array(
				"ptr_type" => SESSION_PERM_USER,
				"ptr_id" => (int) $uid,
				"obj_type" => $this->perm["id"],
				"obj_id" => (int) $oid
			)
		);
		
		return true;
	}
}

class session_pview extends wf_agg {
	protected $session;
	protected $pviews = array();
	
	public function loader($view_name, $obj_id = null) {
		$this->session = $this->wf->session();
		
		/* get all pviews */
		$pviews = array();
		$ret = array_filter($this->wf->execute_hook("session_permissions_view"));
		foreach($ret as $pview)
			$pviews = array_merge($pviews, $pview);
		
		/* sanatize */
		foreach($pviews as $k => $v)
			$this->pviews[$k] = new session_pview_item($this->wf, $k, $v);
	}
	
	public function get_pview($view = "") {
		return !empty($view) ?
				(isset($this->pviews[$view]) ?
					$this->pviews[$view] : false) :
				(count($this->pviews) > 0 ?
					$this->pviews : false);
	}
}
