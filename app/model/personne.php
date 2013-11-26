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
}