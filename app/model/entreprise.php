<?php

class entreprise extends Record{

	const Table = 'entreprises';

	public $id;
	public $ape_id;
	public $raison_social;
	public $adresse1;
	public $adresse2;
	public $code_postal;
	public $ville;
	public $telephone;
	public $fax;
	public $email;
	public $effectif;
	public $siret;
	public $client;
	public $code_interne;
	public $isValid;

	public $new_id;

	public $migration;

	public function isValid(){
		if(empty($this->raison_social)){
			return false;
		}

		if(empty($this->effectif)){
			$this->effectif = NULL;
		}

		return true;
	}

	/**
	 * Recupere les entreprises par code interne
	 * @param  [type] $code [description]
	 * @return [type]       [description]
	 */
	public function getByCodeInterne($code){
		global $db;
		return $db->get($this->getTable(), array('code_interne =' => $code));
	}

}