<?php

class contacts_suivi extends Record{
	
	const Table = 'contacts_suivi';

	public $id;
	public $cid;
	public $uid;
	public $date_suivi;
	public $suivi;
	public $source;
	public $source_id;

}