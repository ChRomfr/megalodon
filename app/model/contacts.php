<?php

class contacts extends Record{
	
	const Table = 'contacts';
	
	/**
	*	Identifiant du contact
	*	@var 
	*	@Db: {"name":"id","type":"INT","autoincrement":1,"primary":1,"notnull":1}
	*/
	public $id;
	
	/**
	*	Bool permettant de savoir si le contact est client
	*	@Db: {"name":"client","type":"INT","length":1,"default":"0"}
	*/
	public $client;
	
	/**
	*	Identifiant dans autre logiciel
	*	@Db: {"name":"code_interne","type":"VARCHAR", "length":200, "default":NULL}
	*/
	public $code_interne;
	
	/**
	*	Adresse
	*	@Db: {"name":"adresse1", "type":"TEXT", "default":NULL}
	*/
	public $adresse1;
	
	/**
	*	Adresse suite
	*	@Db: {"name":"adresse2", "type":"TEXT", "default":NULL}
	*/
	public $adresse2;
	
	/**
	*	Latitude
	*	@Db: {"name":"lat", "type":"VARCHAR", "length":"200" ,"default":NULL}
	*/
	public $lat;
	
	/**
	*	Longitude
	*	@Db: {"name":"lng", "type":"VARCHAR", "length":"200" ,"default":NULL}
	*/
	public $lng;
	
	/**
	*	Email
	*	@Db: {"name":"email", "type":"VARCHAR", "length":"200" ,"default":NULL}
	*/
	public $email;
	
	/**
	*	Type 1 = pro 2 = particulier
	*	@Db: {"name":"type", "type":"INT", "length":"1" ,"default":0}
	*/
	public $type;
	
	public $code_postal;
	
	public $ville;
	
	public $pays;
	
	/**
	*	isDelete marque l element en corbeille
	*	@Db: {"name":"type", "type":"INT", "length":"1" ,"default":0}
	*/
	public $isDelete;

	public $ctype;
	
	public function isValid(){
		if(empty($this->client) ){
			$this->client = 0;
		}
	}
	
	public function setDelete($value = 1){
		$this->isDelete = $value;
	}

	
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
		if(empty($this->email)){
			$this->email = NULL;
		}

		if(empty($this->adresse1)){
			$this->email = NULL;
		}

		return parent::save();
	}
}