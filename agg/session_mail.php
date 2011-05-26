<?php

class session_mail extends wf_agg {
	public $wf = NULL; 
	public $a_core_smtp; 

	public $sender = 'Administrateur Binarysec <support@binarysec.com>';
	private $content;
	private $current_lang;
	
	public function loader($wf) {
		$this->wf = $wf;
		
		$this->lang = $this->wf->core_lang()->get_context(
			"session/mail"
		);
		$this->current_lang = $this->lang->lang;
		$this->a_core_smtp = $this->wf->core_smtp();
		$this->session = $this->wf->session();
		
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
		if(!is_array($userc[0])){
			return FALSE;
		}
		/* prend et transforme les données */
		$doc = file_get_contents(
			$this->wf->locate_file("var/session/mails/inscription.".$this->current_lang.".mail")
		);
		$sub = file_get_contents(
			$this->wf->locate_file("var/session/mails/inscription.".$this->current_lang.".subject")
		);
		
		/* construction des patterns */
		$pattern = array(
			"/%NAME%/",
			"/%LOGIN%/",
			"/%PASSWORD%/",
			"/%DATE%/"		
		);
		
		$replace = array(
			ucfirst($userc[0]["name"]),
			$userc[0]["username"],
			$real_password,
			date("Y-m-d H:i:s")
		);
		
		$doc = preg_replace(
			$pattern,
			$replace,
			$doc
		);
		$sub = preg_replace(
			$pattern,
			$replace,
			$sub
		);
				
		$to = $userc[0]["email"];
		
		$this->content .= 'To:'.$to."\n";
		$this->content .= 'Subject:'.$this->utf8_to_latin9($sub)."\n";
		$this->content .= $this->utf8_to_latin9($doc);
		
		$this->a_core_smtp->sendmail(
			$this->sender,
			$to,
			$this->content
		);		
		return TRUE;

	}
	public function mail_change_password($user_id,$new_password) {
		$userc = $this->session->user->get(array("id"=>$user_id));
		if(!is_array($userc[0])){
			return FALSE;
		}
	
		/* prend et transforme les données */
		$doc = file_get_contents(
			$this->wf->locate_file("var/session/mails/change_pwd.".$this->current_lang.".mail")
		);
		$sub = file_get_contents(
			$this->wf->locate_file("var/session/mails/change_pwd.".$this->current_lang.".subject")
		);
		
		/* construction des patterns */
		$pattern = array(
			"/%NAME%/",
			"/%LOGIN%/",
			"/%PASSWORD%/",
			"/%DATE%/"	
		);
		
		$replace = array(
			ucfirst($userc[0]["name"]),
			$userc[0]["username"],
			$new_password,
			date("Y-m-d H:i:s")
		);
		
		$doc = preg_replace(
			$pattern,
			$replace,
			$doc
		);
		$sub = preg_replace(
			$pattern,
			$replace,
			$sub
		);
		
		$to = $userc[0]["email"];
	
		$this->content .= 'To:'.$to."\n";
		$this->content .= 'Subject:'.$this->utf8_to_latin9($sub)."\n";
		$this->content .= $this->utf8_to_latin9($doc);
		
		$this->a_core_smtp->sendmail(
			$this->sender,
			$to,
			$this->content
		);		
		return TRUE;
	}	
}
