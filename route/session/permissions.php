<?php

class wfr_session_session_permissions extends wf_route_request {

	private $a_session;
	private $admin;
	
	private $pview_name;
	private $pview_perm_name;
	private $pview_perm;
	private $oid;
	private $object;
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * constructeur
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function __construct($wf) {
		$this->wf = $wf;
		$this->a_session = $this->wf->session();
		$this->admin = $this->wf->admin_html();
// 		$this->a_core_html = $this->wf->core_html();
	}

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Add a permission
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */	
	public function add_user() {
		$this->pview_name = $this->wf->get_var("pview");
		$this->oid = $this->wf->get_var("oid");
		
		$pview = $this->a_session->get_pview($this->pview_name);
		if(!$pview)
			exit(0);

		/* get key */
		$this->pview_perm_name = $pview[$this->pview_name];

		/* lookup user */
		$u = $this->wf->get_var("user");
		$user = $this->a_session->user->get("email", $u);
		$user = $user[0];

		$this->a_session->perm->user_add(
			$user["id"],
			$this->pview_perm_name,
			$this->oid
		);
		
		$this->wf->core_request()->set_header(
			'Location',
			$this->wf->linker('/session/permissions').
			"?pview=".$this->pview_name.
			"&oid=".$this->oid
		);
		$this->wf->core_request()->send_headers();
		exit(0);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Delete a permission
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */	
	public function delete_user() {
		$this->pview_name = $this->wf->get_var("pview");
		$this->oid = $this->wf->get_var("oid");
		$uid = $this->wf->get_var("uid");
		
		$pview = $this->a_session->get_pview($this->pview_name);
		if(!$pview)
			exit(0);

		/* get key */
		$this->pview_perm_name = $pview[$this->pview_name];
		
		/* get permission information */
		$this->pview_perm = $this->a_session->perm->get_type(
			"name", 
			$this->pview_perm_name
		);
		$this->pview_perm = $this->pview_perm[0];
		if(!$this->pview_perm)
			exit(0);
		
		$this->a_session->perm->user_remove(array(
			"ptr_id" => (int)$uid,
			"obj_type" => $this->pview_perm["id"],
			"obj_id" => (int)$this->oid
		));

		$this->wf->core_request()->set_header(
			'Location',
			$this->wf->linker('/session/permissions').
			"?pview=".$this->pview_name.
			"&oid=".$this->oid
		);
		$this->wf->core_request()->send_headers();
		exit(0);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Edit permissions of users
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */	
	public function edit_user() {
		$this->pview_name = $this->wf->get_var("pview");
		$this->oid = $this->wf->get_var("oid");
		$perm = $this->wf->get_var("perm");
		
		$pview = $this->a_session->get_pview($this->pview_name);
		if(!$pview)
			exit(0);

		/* get key */
		$this->pview_perm_name = $pview[$this->pview_name];
		
		/* get permission information */
		$this->pview_perm = $this->a_session->perm->get_type(
			"name", 
			$this->pview_perm_name
		);
		$this->pview_perm = $this->pview_perm[0];
		if(!$this->pview_perm)
			exit(0);
			
		/* load obj */
		$on = &$this->pview_name;
		$this->object = new ${on}($this->wf, $this->pview_name, $this->oid);

		/* read matrix */
		foreach($perm as $pk => $pv) {

			$where = array(
				"ptr_id" => (int)$pk,
				"obj_type" => $this->pview_perm["id"],
				"obj_id" => (int)$this->oid
			);
			
			/* prepare update */
			$data_up = array();
			foreach($this->object->resolv as $k => $v) {
				if($pv[$k] == "on")
					$data_up[$k] = "on";
				else
					$data_up[$k] = "off";
			}
			$update = array(
				"data" => serialize($data_up)
			);
			
			$this->a_session->perm->user_mod($update, $where);
		}
	
		$this->wf->core_request()->set_header(
			'Location',
			$this->wf->linker('/session/permissions').
			"?pview=".$this->pview_name.
			"&oid=".$this->oid
		);
		$this->wf->core_request()->send_headers();
		exit(0);
	}
	
	
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Master print function
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */	
	public function show_acl() {
		$this->pview_name = $this->wf->get_var("pview");
		$this->oid = $this->wf->get_var("oid");
		
		$pview = $this->a_session->get_pview($this->pview_name);
		if(!$pview)
			exit(0);

		/* get key */
		$this->pview_perm_name = $pview[$this->pview_name];
		
		/* get permission information */
		$this->pview_perm = $this->a_session->perm->get_type(
			"name", 
			$this->pview_perm_name
		);
		$this->pview_perm = $this->pview_perm[0];
		if(!$this->pview_perm)
			exit(0);
		
		/* load obj */
		$on = &$this->pview_name;
		$this->object = new ${on}($this->wf, $this->pview_name, $this->oid);
		
		$title = $this->object->get_title();
		$tpl = new core_tpl($this->wf);
		
		$tpl->set(
			"dataset",
			$this->generate_dataset()
		);
		$tpl->set(
			"title",
			$title
		);
		$tpl->set(
			"pview",
			$this->pview_name
		);
		$tpl->set(
			"oid",
			$this->oid
		);
		
		$this->admin->set_title($title);
		$this->admin->rendering(
			$tpl->fetch("session/permissions/list_acl")
		);
		exit(0);
		
		
	}
	
	private function generate_dataset() {
		$dsrc  = new core_datasource_db($this->wf, "session_perm");
		
		/* precondition */
		$pcs = array(
			array("t.ptr_type", "=", SESSION_PERM_USER),
			array("t.obj_type", "=", $this->pview_perm["id"])
		);
		if($this->oid) {
			$pcs[] = array("t.obj_id", "=", $this->oid);
		}
		$dsrc->add_preconds($pcs);
		
		$dset  = new core_dataset($this->wf, $dsrc);
		
		$filters = array();
		$cols = array(
			'icons' => array(),
			'mail' => array(
				'name' => 'Compte',
			),
			'nom' => array(
				'name' => 'Nom',
// 				'orderable' => true,
			),
			'create_t' => array(
				'name' => 'Date de création',
// 				'orderable' => true,
			)
		);
		
		/* add object permission */
		foreach($this->object->resolv as $k => $v) {
			$cols[$k] = array(
				"name" => '<div class="session_pview_th">'.
					$v.
					'</div>'
			);
		}
		$cols['actions'] = array();
		
		/* set colones / filters */
		$dset->set_cols($cols);
		$dset->set_filters($filters);
		
		$dset->set_row_callback(array($this, 'callback_row'));

		/* template utilisateur */
		$tplset = array(
// 			'scripts' => $this->render_dialogs()
		);
		$dview = new core_dataview($this->wf, $dset);
		$dview_render = $dview->render(NULL, $tplset);
		return($dview_render);
	}
	
	public function callback_row($row, $datum) {
		$user = $this->a_session->user->get("id", $datum["ptr_id"]);
		$user = $user[0];
		$perm = $this->a_session->perm->user_get($datum["ptr_id"]);
		
		
		/* date de création */
		$createtime = $datum['create_time'] ? 
			$datum['create_time'] : 
			$datum['t.create_time'];
			
		$create_time = date(DATE_RFC822, $createtime);
		
		/* type icon */
		if($perm["session:god"] || $perm["session:admin"]) {
			$type_icon = '<img src="'.
				$this->wf->linker('/data/session/t_admin.png').
				'" alt="[Administrateur]" title="Administrateur" />';
		
		}
		else if($perm["session:simple"]) {
			$type_icon = '<img src="'.
				$this->wf->linker('/data/session/t_simple.png').
				'" alt="[Utilisateur simple]" title="Utilisateur simple" />';
		}
		else if($perm["session:ws"]) {
			$type_icon = '<img src="'.
				$this->wf->linker('/data/session/t_webservice.png').
				'" alt="[Web service]" title="Web service" />';
		}
		
		/* build action */
		$delete_url = $this->wf->linker('/session/permissions/delete').
			"?pview=".$this->pview_name.
			"&uid=".$datum["ptr_id"];
		if($this->oid)
			$delete_url .= "&oid=".$this->oid;
			
		$actions = '<a href="'.
			$delete_url.
			'">'.
			'<img src="'.
			$this->wf->linker('/data/session/delete.png').
			'" alt="[Supprimer]" title="Supprimer" />'.
			'</a>';
	
		$ret = array(
			'icons' => $type_icon,
			'mail' => $user["email"],
			'nom' => $user["name"],
			"create_t" => $create_time
		);
		
		/* show permission */
		$p = @unserialize($datum["data"]);
		foreach($this->object->resolv as $k => $v) {
			$pname = "perm[".$datum["ptr_id"]."]";
			$name = $pname."[$k]";
			
			$ret[$k] = '<input '.
				'type="hidden" '.
				'name="'.$pname.'[__session__]"/>';

			if($p[$k] == "on") {
				$ret[$k] .= '<input '.
					'type="checkbox" '.
					'name="'.$name.'" '.
					'checked/>';
				
			}
			else {
				$ret[$k] .= '<input '.
					'type="checkbox" '.
					'name="'.$name.'"/>';
			}
			

		}
// 		var_dump($ret);
		$ret["actions"] = $actions;
// 		exit(0);
		return($ret);
	}
	
}
