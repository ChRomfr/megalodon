<?php

class campaign extends Record{

	const Table = 'campaign';
	
	public $id;
	
	public $type_id;
	
	public $title;
	
	public $description;
	
	public $date_start;
	
	public $date_end;
	
	public $objectif;
	
	public $creat_on;
	
	public $creat_by;
	
	public $caller;
	
	public $assign;
	
	public $target;
	
	public $generated;
	
	public $canceled;
	
	public function isValid(){
		$errors = '<ul>';
		$nb_errors = 0;
		
		if(empty($this->title)){
			$nb_errors++;
			$errors .= '<li>Titre invalide</li>';
		}
		
		if(empty($this->description)){
			$nb_errors++;
			$errors .= '<li>Description invalide</li>';
		}
		
		if(empty($this->objectif)){
			$nb_errors++;
			$errors .= '<li>Objectif invalide</li>';
		}
		
		if(empty($this->date_start)){
			$nb_errors++;
			$errors .= '<li>Date de début invalide</li>';
		}
		
		if(empty($this->date_end)){
			$nb_errors++;
			$errors .= '<li>Date de fin invalide</li>';
		}
		
		if(empty($this->assign)){
			$nb_errors++;
			$errors .= '<li>La campagne doit etre assigner à un utilisateur</li>';
		}
		
		if(empty($this->target['ctype'])){
			$nb_error++;
			$errors .= '<li>Selectionner un type de contact</li>';
		}
		
		if($nb_errors == 0){
			// preparation pour sauvegarde
			if( empty($this->id) ){
				$this->creat_by = $_SESSION['utilisateur']['id'];
				$this->creat_on = date("Y-m-d H:i:s");
				$this->date_start = FormatDateToMySql($this->date_start);
				$this->date_end = FormatDateToMySql($this->date_end);
				$this->generated = 0;
				$this->canceled = 0;
				
			}
			
			return true;
		}
		
		return $errors .= '</ul>';
	}
	
}