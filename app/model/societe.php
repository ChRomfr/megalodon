<?php

class societe extends Record{

	const Table = 'societe';
	
	public $id;
	
	public $contact_id;
	
	public $raison_social;
	
	public $siret;
	
	public $effectif;
	
	public $ape_id;
	
	public $mother;
	
	public $parent_id;

	public function update_fields($old_record){

		if(isset($old_record['id'])){
			unset($old_record['id']);
		}

		foreach($old_record as $k => $v){
			if( empty($this->$k) && !empty($v) ){
				$this->$k = $v;
			}
		}

	}

	public function save(){

		if(empty($this->effectif)){
			$this->effectif = NULL;
		}

		if(empty($this->ape_id)){
			$this->ape_id = NULL;
		}
		parent::save();
	}
	
}