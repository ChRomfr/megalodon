<?php

class contact extends Record{

	const Table = 'contact';

	public $id;
	public $poste_id;
	public $service_id;
	public $entreprise_id;
	public $nom;
	public $prenom;
	public $telephone;
	public $fax;
	public $email;
	public $mobile;
	public $date_add;
	public $date_edit;
	public $code_interne;

	public $new_id;

	public $migration;

	public function isValid(){

		if(empty($this->nom)){
			return false;
		}

		if(empty($this->entreprise_id)){
			return false;
		}

		return true;
	}

}