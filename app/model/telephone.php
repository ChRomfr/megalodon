<?php

class telephone extends Record{
	
	const Table = 'telephones';
	
	public $id;
	
	public $contact_id;
	
	public $telephone;
	
	public $type;
	
	/**
	*	@NoDb: {"nodb":1}
	*/
	protected $error;
	
	/**
	*	Verifie que l'objet est valide
	*	@return bool true si ok, false si erreur
	*/
	public function isValid(){
		$error = 0;
		
		if(empty($this->telephone)){
			$error++;
			$this->error = 'empty number phone';
		}
		
		if(empty($this->type)){
			$error++;
			$this->error = 'empty type phone';
		}
		
		if($error == 0){
			return true;
		}
		
		return false;
	}
	
	/**
	*	Retourne le contenu de la variale $error
	*	@return string contenu de $error
	*/
	public function getError(){
		return $this->error;
	}
	
	public function clearnumber(){
		$this->telephone = utf8_encode(str_replace(array('.','/',' '), '', $this->telephone));
	}
	
}