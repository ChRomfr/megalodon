<?php

class organisme extends Record{

	const Table = 'organismes';

	public $id;

	public $libelle;

	public function isValid(){
		if(empty($this->libelle)){
			return false;
		}

		return true;
	}

	public function delete($id){
		global $db;

		parent::delete($id);

		// Suppression liaison entreprise/organisme
		$db->delete('entreprise_organisme', null, array('organisme_id =' => $id));

		return;
	}

}