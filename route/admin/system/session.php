<?php

class wfr_session_admin_system_session extends wf_route_request {
		
	public function __construct($wf) {
		$this->wf = $wf;
		$this->a_admin_html = $this->wf->admin_html();
		$this->a_core_cipher = $this->wf->core_cipher();
		$this->a_session = $this->wf->session();
		
		$this->lang = $this->wf->core_lang()->get_context("admin/session");
	}
	
	
	public function show() {
	
		/* create dataset */
		$dsrc  = new core_datasource_db($this->wf, "session_user");
		$dset  = new core_dataset($this->wf, $dsrc);
		
		$filters = array(
			'lang' => array(
				'type' => WF_CORE_DATASET_SELECT,
				'label' => $this->lang->ts("Langue"),
				
			),
		);
		$cols = array(
			'type_icon' => array(),
			'name' => array(
				'name'      => 'Name',
				'orderable' => true,
				'search' => true
			),
			'firstname' => array(
				'name'      => 'Firstname',
				'orderable' => true,
				'search' => true
			),
			'email' => array(
				'name'      => 'Email',
				'orderable' => true,
				'search' => true
			),
			'username' => array(
				'name'      => 'Username',
				'orderable' => true,
				'search' => true,
				'order-default' => WF_ASC
			),
			'remote_address' => array(
				'name'      => 'Adresse IP',
				'orderable' => true,
			),
			'session_time_auth' => array(
				'name'      => 'Login',
				'orderable' => true,
			),
		);
		
		$dset->set_cols($cols);
		$dset->set_filters($filters);
		$dset->set_row_callback(array($this, 'callback_row'));

		/* template utilisateur */
		$tplset = array();
		$dview = new core_dataview($this->wf, $dset);
		
		
		$tpl = new core_tpl($this->wf);

		/* prepare template variable */
		$in = array(
			"dataset" => $dview->render(NULL, $tplset),
		);
	
		$tpl->set_vars($in);
		
		$this->a_admin_html->set_title($this->lang->ts("User management"));
		
		/* Add back button */
		$this->a_admin_html->set_backlink($this->wf->linker('/admin/system'));
		
		/* rendering using my template */
		$this->a_admin_html->rendering($tpl->fetch('admin/session/index'));
		exit(0);
	}
	
	public function callback_row($row, $datum) {
		$perm = $this->a_session->perm->user_get($datum["id"]);
		
		/* user online ? */
		if(!$this->a_session->is_online($datum["id"])) {
			$login_icon = '<img src="'.
				$this->wf->linker('/data/session/offline.png').
				'" alt="[Off line]" title="Off line" class="ui-li-icon" />';
			$ip = '-';
		}
		else {
			$login_icon = '<img src="'.
				$this->wf->linker('/data/session/online.png').
				'" alt="[On line]" title="On line" class="ui-li-icon" />';
			
			$ip = long2ip($datum["remote_address"])." (".
				$datum["remote_hostname"].
				")";
		}
		
		/* activated icon */
		$activation_required = isset($this->wf->ini_arr['session']['activation_required']) ?
			$this->wf->ini_arr['session']['activation_required'] : false;
		if($activation_required) {
			$activated_icon = $datum["activated"] && $datum["activated"] != "true" ?
				'&nbsp;&nbsp;&nbsp;<img width="16px" src="'.
				$this->wf->linker("/data/session/warning.png").
				'" alt="Not activated" title="Not activated" />' :
				'&nbsp;&nbsp;&nbsp;<img width="16px" src="'.
				$this->wf->linker("/data/session/right.png").
				'" alt="Activated" title="Activated" />';
		}
		else
			$activated_icon = "";
		
		/* type icon */
		if(isset($perm["session:admin"])) {
			$type_icon = '<img src="'.
				$this->wf->linker('/data/session/t_admin.png').
				'" alt="[Administrateur]" title="Administrateur" />';
		}
		else if(isset($perm["session:simple"])) {
			$type_icon = '<img src="'.
				$this->wf->linker('/data/session/t_simple.png').
				'" alt="[Utilisateur simple]" title="Utilisateur simple" />';
		}
		else if(isset($perm["session:ws"])) {
			$type_icon = '<img src="'.
				$this->wf->linker('/data/session/t_webservice.png').
				'" alt="[Web service]" title="Web service" />';
		}
		else if(isset($perm["session:god"])) {
			$type_icon = '<img src="'.
				$this->wf->linker('/data/session/t_god.png').
				'" alt="[God]" title="God" />';
		}
		
		/* adresse IP */
		if($datum['session_time_auth'])
			$login_date = ' - Last login: '.date('d/m/Y H:i:s', $datum['session_time_auth']);
		else 
			$login_date = '';

		$edit = '<span class="edit_user"><a href="" id="'.$datum['id'].'">Edit</a></span>';
		$delete = '<span class="delete_user"><a href="" id="'.$datum['id'].'">Delete</a></span>';
		/* actions */
		$actions = $edit.$delete;
		
		$opt_link = htmlentities($this->a_admin_html->options_link($datum['id'], 'b'));
		$st = htmlspecialchars($datum['firstname']).' '.htmlspecialchars($datum['name']);
		$username = htmlspecialchars($datum['username']);
		$mail = htmlspecialchars($datum['email']);
		
		$r = '<li><a href="'.$opt_link.'">'.$login_icon.
				'<h3>'.$st.'</h3>'.
				'<p><u>'.$username.'</u> - '.$mail.$login_date.$activated_icon.'</p>'.
				'<span class="ui-li-count">'.$type_icon.'</span>'.
			'</a></li>';
		return($r);
	}
	
	
}

