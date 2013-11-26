<?php

class mailing_type extends Record{

	const Table = 'mailings_type';

	public 	$id;
	public 	$libelle;

	public function isValid(){
		
		if(empty($this->libelle)){
			return false;
		}

		return true;
	}
}//end class