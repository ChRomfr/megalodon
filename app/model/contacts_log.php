<?php

class clog extends Record{

	const Table = 'contacts_log';
	
	public $id;
	
	public $date_log;
	
	public $contact_id;
	
	public $user_id;
	
	public $log;
	
	public function __construct(array $data = array()){
		parent::__construct($data);
		return $this;
	}
	
}