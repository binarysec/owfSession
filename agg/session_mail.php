<?php

class session_mail extends wf_agg {
	public $wf = NULL; 
	public $a_core_smtp; 
	public $session;
	public $core_lang; 
	
	public $sender = 'Administrateur Binarysec <support@binarysec.com>';
	private $content;
	private $current_lang;
	private $pref_mail;
	
	public function loader($wf) {
		$this->wf = $wf;
		$this->core_lang = $this->wf->core_lang();
		$this->lang = $this->core_lang->get_context(
			"session/mail"
		);
		$this->current_lang = $this->lang->lang;
		$this->a_core_smtp = $this->wf->core_smtp();
		$this->session = $this->wf->session();
		$this->pref_mail = $this->wf->core_pref()->register_group("BSF WAF Mail");
		
		/* Mail headers */
		$this->content = 'Content-Type: text/plain; charset=iso-8859-15; format=flowed' ."\n";
		$this->content .= 'Content-Transfer-Encoding: 8bit'."\n";
		$this->content .= 'MIME-Version: 1.0'."\n";
		$this->content .= 'From: '.$this->sender."\n";
		$this->content .= 'X-Priority: 1'."\n";
		$this->content .= 'X-Mailer: PHP/'.phpversion()."\n";
	
	}

	public function utf8_to_latin9($utf8str) { 
		$trans = array("€"=>"¤", "� "=>"¦", "š"=>"¨", "Ž"=>"´", "ž"=>"¸", "Œ"=>"¼", "œ"=>"½", "Ÿ"=>"¾");
		$wrong_utf8str = strtr($utf8str, $trans);
		$latin9str = utf8_decode($wrong_utf8str);
		return $latin9str;
	}
	
	public function mail_inscription($user_id,$real_password) {
	
		$userc = $this->session->user->get(array("id"=>$user_id));
		if(!isset($userc[0]))
			return FALSE;

		$to = $userc[0]["email"];
		
		if(isset($userc[0]["lang"])){
			$current_lang = $this->core_lang->get_code();
			$this->core_lang->set($userc[0]["lang"]);
		}
		
		$lselect = $this->core_lang->get();
		
		$link = "http://";
		$link .= $this->wf->core_pref()->register_group("core")->get_value("site_name");
		$link .= $this->wf->linker("/session/validate")."?c=".$userc[0]["activated"];
		
		/* create the change password tpl */
		$tpl = new core_tpl($this->wf);
		$tpl->set("from", $this->session->session_sender);
		$tpl->set("to", $to);
		$tpl->set("name", htmlentities(ucfirst($userc[0]["name"]), ENT_COMPAT,$lselect["encoding"]));
		$tpl->set("firstname", htmlentities(ucfirst($userc[0]["firstname"]), ENT_COMPAT,$lselect["encoding"]));
		$tpl->set("login", $userc[0]["username"]);
		$tpl->set("validate", $link);
		$tpl->set("password", $real_password);

		//$tpl->set("contact_mail", $this->pref_mail->get_value("contact_mail"));
		//$tpl->set("tech_mail", $this->pref_mail->get_value("tech_mail"));
		$mail = $tpl->fetch("session/mail/validate");
		
		if(isset($current_lang)){
			$this->core_lang->set($current_lang);
		}
		
		$c_mail = new core_mail(
			$this->wf,
			"OWF <".$this->session->session_sender.">",
			$to,
			$this->lang->ts("Validation de l'inscription"),
			$mail
		);
		
		$c_mail->render();
		$c_mail->send();
		
		return TRUE;
	}
	
	public function mail_validation($user_id, $real_password = "") {
		$userc = $this->session->user->get(array("id"=>$user_id));
		if(!isset($userc[0]))
			return FALSE;

		$to = $userc[0]["email"];
		
		if(isset($userc[0]["lang"])){
			$current_lang = $this->core_lang->get_code();
			$this->core_lang->set($userc[0]["lang"]);
		}
		
		$lselect = $this->core_lang->get();
		
		/* create the change password tpl */
		$tpl = new core_tpl($this->wf);
		$tpl->set("from", $this->session->session_sender);
		$tpl->set("to", $to);
		$tpl->set("name", htmlentities(ucfirst($userc[0]["name"]), ENT_COMPAT,$lselect["encoding"]));
		$tpl->set("firstname", htmlentities(ucfirst($userc[0]["firstname"]), ENT_COMPAT,$lselect["encoding"]));
		$tpl->set("login", $userc[0]["username"]);
		$tpl->set("password", $real_password);
		$tpl->set("remote_addr", $_SERVER['REMOTE_ADDR']);
		$tpl->set("date", ucfirst(date("Y-m-d H:i:s")));
		$tpl->set("date_mail", ucfirst(date("D, j M Y H:i:s")));

		$tpl->set("contact_mail", $this->pref_mail->get_value("contact_mail"));
		$tpl->set("tech_mail", $this->pref_mail->get_value("tech_mail"));
		$mail = $tpl->fetch("session/mail/welcome");
		
		if(isset($current_lang)){
			$this->core_lang->set($current_lang);
		}
		
		$this->a_core_smtp->sendmail(
			$this->session->session_sender,
			$to,
			$mail
		);
		
		return TRUE;
	}
	
	public function mail_password_link($user_id, $link) {
		$userc = $this->session->user->get(array("id"=>$user_id));
		if(!is_array($userc[0]))
			return FALSE;
			
		$to = $userc[0]["email"];
		
		if(isset($userc[0]["lang"])){
			$current_lang = $this->core_lang->get_code();
			$this->core_lang->set($userc[0]["lang"]);
		}
		$lselect = $this->core_lang->get();

		/* create the change password tpl */
		$tpl = new core_tpl($this->wf);
		$tpl->set("from", $this->session->session_sender);
		$tpl->set("to", $to);
		$tpl->set("firstname", htmlentities(ucfirst($userc[0]["firstname"]), ENT_COMPAT,$lselect["encoding"]));
		$tpl->set("link", $link);
		$tpl->set("date", ucfirst(date("Y-m-d H:i:s")));
		$tpl->set("date_mail", ucfirst(date("D, j M Y H:i:s")));

		$tpl->set("contact_mail", $this->pref_mail->get_value("contact_mail"));
		$tpl->set("tech_mail", $this->pref_mail->get_value("tech_mail"));
		$mail = $tpl->fetch("session/mail/reset_pwd_link");
		
		if(isset($current_lang)){
			$this->core_lang->set($current_lang);
		}
		
		$this->a_core_smtp->sendmail(
			$this->session->session_sender,
			$to,
			$mail
		);
		return TRUE;
	}
	
	public function mail_change_password($user_id, $new_password) {
		$userc = $this->session->user->get(array("id"=>$user_id));
		if(!is_array($userc[0]))
			return FALSE;
			
		$to = $userc[0]["email"];
		
		if(isset($userc[0]["lang"])){
			$current_lang = $this->core_lang->get_code();
			$this->core_lang->set($userc[0]["lang"]);
		}
		$lselect = $this->core_lang->get();

		/* create the change password tpl */
		$tpl = new core_tpl($this->wf);
		$tpl->set("from", $this->session->session_sender);
		$tpl->set("to", $to);
		$tpl->set("name",  htmlentities(ucfirst($userc[0]["name"]), ENT_COMPAT,$lselect["encoding"]));
		$tpl->set("firstname", htmlentities(ucfirst($userc[0]["firstname"]), ENT_COMPAT,$lselect["encoding"]));
		$tpl->set("login", $userc[0]["username"]);
		$tpl->set("password", $new_password);
		$tpl->set("remote_addr", $_SERVER['REMOTE_ADDR']);
		$tpl->set("date", ucfirst(date("Y-m-d H:i:s")));
		$tpl->set("date_mail", ucfirst(date("D, j M Y H:i:s")));

		$tpl->set("contact_mail", $this->pref_mail->get_value("contact_mail"));
		$tpl->set("tech_mail", $this->pref_mail->get_value("tech_mail"));
		$mail = $tpl->fetch("session/mail/passwd");
		
		if(isset($current_lang)){
			$this->core_lang->set($current_lang);
		}
		
		$this->a_core_smtp->sendmail(
			$this->session->session_sender,
			$to,
			$mail
		);
		return TRUE;
	}	
}
