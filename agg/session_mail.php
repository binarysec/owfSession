<?php

class session_mail extends wf_agg {
	//public $a_core_smtp; 
	
	//public $sender = 'Administrateur Binarysec <support@binarysec.com>';
	//private $content;
	//private $current_lang;
	//private $pref_mail;
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 *
	 * loader
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function loader() {
		$this->a_session = $this->wf->session();
		$this->core_lang = $this->wf->core_lang();
		$this->lang = $this->wf->core_lang()->get_context(
			"session/mail"
		);
		
		//$this->lang = $this->core_lang->get_context(
			//"session/mail"
		//);
		//$this->current_lang = $this->lang->lang;
		//$this->a_core_smtp = $this->wf->core_smtp();
		
		//$this->pref_mail = $this->wf->core_pref()->register_group("BSF WAF Mail");
		
		///* Mail headers */
		//$this->content = 'Content-Type: text/plain; charset=iso-8859-15; format=flowed' ."\n";
		//$this->content .= 'Content-Transfer-Encoding: 8bit'."\n";
		//$this->content .= 'MIME-Version: 1.0'."\n";
		//$this->content .= 'From: '.$this->sender."\n";
		//$this->content .= 'X-Priority: 1'."\n";
		//$this->content .= 'X-Mailer: PHP/'.phpversion()."\n";
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * 
	 * inscription
	 * 
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function mail_inscription($uid, $rpass) {
		/* get user */
		$user = $this->a_session->user->get(array("id" => $uid));
		
		/* sanatize */
		if(!isset($user[0]))
			return false;
		
		/* validation link */
		$link = "http://";
		$link .= $this->wf->core_pref()->register_group("core")->get_value("site_name");
		$link .= $this->wf->linker("/session/validate")."?c=".$user[0]["activated"];
		
		/* easywaf linker hack .. */
		if(isset($this->wf->modules["easywaf"]))
			$link = $this->wf->linker("/session/validate")."?c=".$user[0]["activated"];
		
		/* some more tpl vars */
		$more_vars = array("validate" => $link);
		
		/* process */
		return $this->mail($user, $rpass, "session/mail/validate", $more_vars, "Validation de votre compte OWF");
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * 
	 * validation
	 * 
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function mail_validated($uid, $rpass = "") {
		/* get user */
		$user = $this->a_session->user->get(array("id" => $uid));
		
		/* sanatize */
		if(!isset($user[0]))
			return false;
		
		/* some more tpl vars */
		$pref_mail = $this->wf->core_pref()->register_group("BSF WAF Mail");
		
		$more_vars = array(
			"remote_addr" => $_SERVER['REMOTE_ADDR'],
			"date" => ucfirst(date("Y-m-d H:i:s")),
			"date_mail" => ucfirst(date("D, j M Y H:i:s")),
			"contact_mail" => $pref_mail->get_value("contact_mail"),
			"tech_mail" => $pref_mail->get_value("tech_mail"),
		);
		
		/* process */
		return $this->mail($user, $rpass, "session/mail/welcome", $more_vars, "Bienvenue sur Open Web Framework");
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * 
	 * password recovery request
	 * 
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function mail_password_recovery_request($user, $code) {
		/* some more tpl vars */
		$pref_mail = $this->wf->core_pref()->register_group("BSF WAF Mail");
		
		$more_vars = array(
			"remote_addr" => $_SERVER['REMOTE_ADDR'],
			"date" => ucfirst(date("Y-m-d H:i:s")),
			"date_mail" => ucfirst(date("D, j M Y H:i:s")),
			"contact_mail" => $pref_mail->get_value("contact_mail"),
			"tech_mail" => $pref_mail->get_value("tech_mail"),
			"link" => $this->wf->linker("/session/recovery", true)."?c=$code",
		);
		
		/* process */
		return $this->mail($user, "", "session/mail/password_recovery", $more_vars, "Requesting a password change");
	}
		
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * 
	 * password changed
	 * 
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	public function mail_password_recovered($uid, $pass) {
		/* get user */
		$user = $this->a_session->user->get(array("id" => $uid));
		
		/* sanatize */
		if(!isset($user[0]))
			return false;
		
		/* some more tpl vars */
		$pref_mail = $this->wf->core_pref()->register_group("BSF WAF Mail");
		
		$more_vars = array(
			"remote_addr" => $_SERVER['REMOTE_ADDR'],
			"date" => ucfirst(date("Y-m-d H:i:s")),
			"date_mail" => ucfirst(date("D, j M Y H:i:s")),
			"contact_mail" => $pref_mail->get_value("contact_mail"),
			"tech_mail" => $pref_mail->get_value("tech_mail"),
		);
		
		/* process */
		return $this->mail($user, $pass, "session/mail/password_recovered", $more_vars, "Password changed");
	}
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * 
	 * general
	 * 
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	private function mail($user, $rpass, $tplpath, $tplvars = array(), $title = "", $enclosed_files = array()) {
		
		/* lang stuff */
		$lang = isset($user[0]["lang"]) ?
			$user[0]["lang"] :
			$this->core_lang->get_code();
		$this->core_lang->set($lang);
		$lang = $this->core_lang->get();
		
		/* build tpl */
		$tpl = new core_tpl($this->wf);
		$tpl->set("name", htmlentities(ucfirst($user[0]["name"]), ENT_COMPAT, $lang["encoding"]));
		$tpl->set("firstname", htmlentities(ucfirst($user[0]["firstname"]), ENT_COMPAT, $lang["encoding"]));
		$tpl->set("login", $user[0]["username"]);
		$tpl->set("password", $rpass);
		foreach($tplvars as $k => $v)
			$tpl->set($k, $v);
		
		/* create mail */
		$title = empty($title) ? "Open Web Framework Email" : $this->lang->ts($title);
		$mail = $tpl->fetch($tplpath);
		$c_mail = new core_mail(
			$this->wf,
			$this->lang->ts($title),
			$mail,
			$user[0]["email"],
			"OWF <".$this->a_session->session_sender.">"
		);
		
		foreach($enclosed_files as $name => $path)
			$c_mail->attach($path, $name);
		
		/* send mail */
		$c_mail->render();
		$c_mail->send();
		
		return true;
	}
	
	//public function utf8_to_latin9($utf8str) { 
		//$trans = array("€"=>"¤", "� "=>"¦", "š"=>"¨", "Ž"=>"´", "ž"=>"¸", "Œ"=>"¼", "œ"=>"½", "Ÿ"=>"¾");
		//$wrong_utf8str = strtr($utf8str, $trans);
		//$latin9str = utf8_decode($wrong_utf8str);
		//return $latin9str;
	//}
	
	//public function mail_password_link($user_id, $link) {
		//$userc = $this->session->user->get(array("id"=>$user_id));
		//if(!is_array($userc[0]))
			//return FALSE;
			
		//$to = $userc[0]["email"];
		
		//if(isset($userc[0]["lang"])){
			//$current_lang = $this->core_lang->get_code();
			//$this->core_lang->set($userc[0]["lang"]);
		//}
		//$lselect = $this->core_lang->get();

		///* create the change password tpl */
		//$tpl = new core_tpl($this->wf);
		//$tpl->set("from", $this->session->session_sender);
		//$tpl->set("to", $to);
		//$tpl->set("firstname", htmlentities(ucfirst($userc[0]["firstname"]), ENT_COMPAT,$lselect["encoding"]));
		//$tpl->set("link", $link);
		//$tpl->set("date", ucfirst(date("Y-m-d H:i:s")));
		//$tpl->set("date_mail", ucfirst(date("D, j M Y H:i:s")));

		//$tpl->set("contact_mail", $this->pref_mail->get_value("contact_mail"));
		//$tpl->set("tech_mail", $this->pref_mail->get_value("tech_mail"));
		//$mail = $tpl->fetch("session/mail/reset_pwd_link");
		
		//if(isset($current_lang)){
			//$this->core_lang->set($current_lang);
		//}
		
		//$this->a_core_smtp->sendmail(
			//$this->session->session_sender,
			//$to,
			//$mail
		//);
		//return TRUE;
	//}
	
	//public function mail_change_password($user_id, $new_password) {
		//$userc = $this->session->user->get(array("id"=>$user_id));
		//if(!is_array($userc[0]))
			//return FALSE;
			
		//$to = $userc[0]["email"];
		
		//if(isset($userc[0]["lang"])){
			//$current_lang = $this->core_lang->get_code();
			//$this->core_lang->set($userc[0]["lang"]);
		//}
		//$lselect = $this->core_lang->get();

		///* create the change password tpl */
		//$tpl = new core_tpl($this->wf);
		//$tpl->set("from", $this->session->session_sender);
		//$tpl->set("to", $to);
		//$tpl->set("name",  htmlentities(ucfirst($userc[0]["name"]), ENT_COMPAT,$lselect["encoding"]));
		//$tpl->set("firstname", htmlentities(ucfirst($userc[0]["firstname"]), ENT_COMPAT,$lselect["encoding"]));
		//$tpl->set("login", $userc[0]["username"]);
		//$tpl->set("password", $new_password);
		//$tpl->set("remote_addr", $_SERVER['REMOTE_ADDR']);
		//$tpl->set("date", ucfirst(date("Y-m-d H:i:s")));
		//$tpl->set("date_mail", ucfirst(date("D, j M Y H:i:s")));

		//$tpl->set("contact_mail", $this->pref_mail->get_value("contact_mail"));
		//$tpl->set("tech_mail", $this->pref_mail->get_value("tech_mail"));
		//$mail = $tpl->fetch("session/mail/passwd");
		
		//if(isset($current_lang)){
			//$this->core_lang->set($current_lang);
		//}
		
		//$this->a_core_smtp->sendmail(
			//$this->session->session_sender,
			//$to,
			//$mail
		//);
		//return TRUE;
	//}
}
