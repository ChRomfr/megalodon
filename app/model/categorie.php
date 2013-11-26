<?php

class categorie extends Record{

	const Table = 'categorie';

	public 	$id;
	public 	$libelle;

	public function isValid(){
		
		if(empty($this->libelle)){
			return false;
		}

		return true;
	}
}//end class