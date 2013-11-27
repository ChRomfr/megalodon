<?php

class personne extends Record{

	const Table = 'personne';
	
	public $id;
	
	public $contact_id;
	
	public $societe_id;
	
	public $nom;
	
	public $prenom;
	
	public $poste_id;
	
	public $service_id;

	public function save(){

		if(empty($this->poste_id)){
			$this->poste_id = NULL;
		}

		if(empty($this->service_id)){
			$this->service_id = NULL;
		}

		if(empty($this->societe_id)){
			$this->service_id = NULL;
		}

		return parent::save();
	}
}