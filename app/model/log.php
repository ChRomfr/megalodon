<?php

class log extends Record{
	
	const Table = 'logs';

	public $id;

	public $date_log;

	public $log;

	public $module;

	public $link_id;

	public $user_id;

	public $user;

	public function save(){
		if(empty($this->date_log)) $this->date_log = date("Y-m-d H:i:s");
		if(empty($this->user_id)) $this->user_id = $_SESSION['utilisateur']['id'];
		$this->user = $_SESSION['utilisateur']['identifiant'];
		return parent::save();
	}
}