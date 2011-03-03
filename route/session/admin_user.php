<?php

class wfr_session_session_admin_user extends wf_route_request {

	private $a_session;
	private $a_core_html;
	private $a_admin_html;
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * constructeur
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function __construct($wf) {
		$this->wf = $wf;
		$this->a_session = $this->wf->session();
		$this->a_admin_html = $this->wf->admin_html();
	}


	public function admin_user() {
		$this->a_admin_html->set_title("Administration / Sessions / Utilisateurs");
		$this->a_admin_html->rendering($this->render_list());
	}
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Rail function used to add a user
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function show_add() {
		$username=$this->a_session->user->generate_ref("username","session_user","username");
		$tpl = new core_tpl($this->wf);
		$tpl->set("username", $username);
		echo $tpl->fetch('session/users/show_add');
		exit(0);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Rail function used to add a user
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function add() {
		$ok = true;

		/* no email */
		if(!$_POST['username']) {
// 			$this->a_admin_html->add_error(
// 				'L\'adresse email de l\'utilisateur n\'a pas
// 				&eacute;t&eacute; sp&eacute;cifi&eacute;e.'
// 			);
			$ok = false;
		}else{
			$ret=$this->a_session->user->get("username",$_POST["username"]);
			if(is_array($ret[0]))
				$ok=false;
		}
		/* no email */
		if(!$_POST['email']) {
// 			$this->a_admin_html->add_error(
// 				'L\'adresse email de l\'utilisateur n\'a pas
// 				&eacute;t&eacute; sp&eacute;cifi&eacute;e.'
// 			);
			$ok = false;
		}
		else {
			/* email invalid */
			if(!filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL)) {
// 				$this->a_admin_html->add_error(
// 					'L\'adresse email de l\'utilisateur est malform&eacute;e.'
// 				);
				$ok = false;
			}
		}

		/* no password */
		if(!$_POST['password']) {
// 			$this->a_admin_html->add_error(
// 				'Le mot de passe de l\'utilisateur n\'a pas
// 				&eacute;t&eacute; sp&eacute;cifi&eacute;.'
// 			);
			$ok = false;
		}
		/* no password confirmation */
		if($_POST['password'] && !$_POST['password_confirm']) {
// 			$this->a_admin_html->add_error(
// 				'Le mot de passe de l\'utilisateur n\'a pas
// 				&eacute;t&eacute; confirm&eacute;.'
// 			);
			$ok = false;
		}
		/* passwords mismatch */
		if($_POST['password'] && $_POST['password_confirm']
		&& $_POST['password'] != $_POST['password_confirm']) {
// 			$this->a_admin_html->add_error(
// 				'Les deux mots de passe fournis ne correspondent pas.'
// 			);
			$ok = false;
		}
		
		if($ok) {
			if($_POST['perm'] == 1)
				$perm = "session:admin";
			else if($_POST['perm'] == 2)
				$perm = "session:simple";
			else if($_POST['perm'] == 3)
				$perm = "session:ws";

			$this->a_session->user->add(
				$_POST['username'],
				$_POST['email'],
				$_POST['password'],
				$_POST['name'],
				$_POST['firstname'],
				$perm,
				$_POST['phone']
			);
		}

		$this->wf->core_request()->set_header(
			'Location',
			$this->wf->linker('/admin/system/session/users/list')
		);
		$this->wf->core_request()->send_headers();
		exit(0);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Rail function used to add a user
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function show_edit() {
		$id = (int)$this->wf->get_var("uid");
		$user = $this->a_session->user->get("id", $id);
		$perms = $this->a_session->perm->user_get($id);

		$tpl = new core_tpl($this->wf);
		
		$tpl->set("id", $user[0]["id"]);
		$tpl->set("email", $user[0]["email"]);
		$tpl->set("username", $user[0]["username"]);
		$tpl->set("firstname", $user[0]["firstname"]);
		$tpl->set("name", $user[0]["name"]);
		$tpl->set("phone", $user[0]["phone"]);
		$tpl->set("perms", $perms);

		echo $tpl->fetch('session/users/show_edit');
		exit(0);
	}
	
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Rail function used to edit a user
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function edit() {
		$ok = true;

		/* FATAL: no id */
		if(!$_POST['id']) {
// 			$this->a_admin_html->add_error(
// 				'L\'identifiant de l\'utilisateur &agrave; &eacute;diter
// 				n\'a pas &eacute;t&eacute; sp&eacute;cifi&eacute;.'
// 			);
			exit(0);
		}

		/* get user infos */
		$user = $this->a_session->user->get("id", $_POST['id']);

		/* FATAL: id doesn't exist */
		if(!is_array($user)) {
// 			$this->a_admin_html->add_error(
// 				'L\'utilisateur &agrave; supprimer n\'existe
// 				 pas dans la base de donn&eacute;es.'
// 			);
			exit(0);
		}
		$user = $user[0];
		$update = array();
		
		/* no email */
		if(!$_POST['email']) {
// 			$this->a_admin_html->add_error(
// 				'L\'adresse email de l\'utilisateur n\'a pas
// 				&eacute;t&eacute; sp&eacute;cifi&eacute;e.'
// 			);
			
			$ok = false;
		}
		else {
			/* email invalid */
			if(!filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL)) {
// 				$this->a_admin_html->add_error(
// 					'L\'adresse email de l\'utilisateur est malform&eacute;e.'
// 				);
				$ok = false;
			}
			$update["email"] = $_POST['email'];
		}
		/* passwords mismatch */
		if($_POST['password'] != $_POST['password_confirm']) {
// 			$this->a_admin_html->add_error(
// 				'Les deux mots de passe fournis ne correspondent pas.'
// 			);
			$ok = false;
		}


		if($ok) {
			/* permssion check */
			if($_POST['perm'] == 1)
				$perm = "session:admin";
			else if($_POST['perm'] == 2)
				$perm = "session:simple";
			else if($_POST['perm'] == 3)
				$perm = "session:ws";
			else
				$perm = NULL;
				
			/* update permission */
			if($perm) {
				$perms = $this->a_session->perm->user_get($user["id"]);
				
				if($perms["session:god"])
					$old_obj_type = $perms["session:god"][0]["obj_type"];
				else if($perms["session:admin"])
					$old_obj_type = $perms["session:admin"][0]["obj_type"];
				else if($perms["session:simple"])
					$old_obj_type = $perms["session:simple"][0]["obj_type"];
				else if($perms["session:ws"])
					$old_obj_type = $perms["session:ws"][0]["obj_type"];
					
				$this->a_session->perm->user_remove(array(
					"ptr_id" => $user["id"],
					"obj_type" => $old_obj_type
				));
				$this->a_session->perm->user_add($user["id"], $perm);
			}

			/* update password */
			if(strlen($_POST['password']) > 2)
				$update["password"]  = $_POST['password'];
	
			$update["name"] = $_POST['name'];
			$update["firstname"] = $_POST['firstname'];
			$update["phone"] = $_POST['phone'];
			
			$this->a_session->user->modify(
				$update,
				$user["id"]
			);

			
		}

		$this->wf->core_request()->set_header(
			'Location',
			$this->wf->linker('/admin/system/session/user')
		);
		$this->wf->core_request()->send_headers();
		exit(0);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Rail function used to delete a user
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function delete() {
		$uid = $this->wf->get_var("id");
		$this->a_session->perm->user_remove("ptr_id", (int)$uid);
		$this->a_session->user->remove((int)$uid);

		$this->wf->core_request()->set_header(
			'Location',
			$this->wf->linker('/admin/system/session/user/list')
		);
		$this->wf->core_request()->send_headers();
		exit(0);
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * Rendering user list
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	private function render_list() {
		$dsrc  = new core_datasource_db($this->wf, "session_user");
		$dset  = new core_dataset($this->wf, $dsrc);
		
		$filters = array();
		$cols = array(
			'type_icon' => array(),
			'name' => array(
				'name'      => 'Nom',
				'orderable' => true,
			),
			'email' => array(
				'name'      => 'E-mail',
				'orderable' => true,
			),
			'username' => array(
				'name'      => 'Username',
				'orderable' => true,
			),
			'remote_address' => array(
				'name'      => 'Adresse IP',
				'orderable' => true,
			),
			'login_icon' => array(),
			'session_time_auth' => array(
				'name'      => 'Login',
				'orderable' => true,
			),
			'actions' => array()
			
		);
		
		$dset->set_cols($cols);
		$dset->set_filters($filters);
		
		$dset->set_row_callback(array($this, 'callback_row'));

		/* template utilisateur */
		$tplset = array();
		$dview = new core_dataview($this->wf, $dset);

		
		/* final render */
		$tpl = new core_tpl($this->wf);
		$tpl->set(
			"dataset",
			$dview->render(NULL, $tplset)
		);

		
		return($tpl->fetch("session/users/list"));
	}
	
	public function callback_row($row, $datum) {
		$perm = $this->a_session->perm->user_get($datum["id"]);
		
		/* user online ? */
		if(!$this->a_session->is_online($datum["id"])) {
			$login_icon = '<img src="'.
				$this->wf->linker('/data/session/offline.png').
				'" alt="[On line]" title="On line" />';
			$ip = '-';
		}
		else {
			$login_icon = '<img src="'.
				$this->wf->linker('/data/session/online.png').
				'" alt="[On line]" title="On line" />';
			
			$ip = long2ip($datum["remote_address"])." (".
				$datum["remote_hostname"].
				")";
		}
		
		/* type icon */
		if($perm["session:admin"]) {
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
		else if($perm["session:god"]) {
			$type_icon = '<img src="'.
				$this->wf->linker('/data/session/t_god.png').
				'" alt="[God]" title="God" />';
		}
		
		/* adresse IP */
		if($datum['session_time_auth']) {
			$login_date = date('d/m/Y H:i:s', $datum['session_time_auth']);
		}
		else {
			$login_date = '-';
			
		}
		
		/* actions */
		$actions = '<a class="btn one" href="#" onclick="'.
			"set_form_edit_user('".
			$datum['id'].
			"')\">Edit</a> ".

// 			'<a href="'.
// 			$this->wf->linker("/admin/system/profiles/show/".$datum['id']).
// 			"\">Profile</a>".
// 			
// 			" | ".
			
			'<a class="btn" href="#" onclick="'.
			"set_form_delete_user('".
			$datum['id']."', '".
			$datum['email'].
			"')\">Delete</a>"
		;
		
		return(array(
			"type_icon" => $type_icon,
			'name' => htmlspecialchars($row['name'])." ".htmlspecialchars($datum['firstname']),
			'email' => htmlspecialchars($row['email']),
			'Username'=>"<strong>".$datum['username']."</strong>",
			'ip' => $ip,
			'login_icon' => $login_icon,
			'login' => $login_date,
			'actions' => $actions
		));
	}
	
}
