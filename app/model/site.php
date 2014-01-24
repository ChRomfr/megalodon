<?php

class site extends Record{

	const Table = 'sites';
	
	public $id;

	public $libelle;

	public $description;

	public $email;

	public $telephone;

	public $fax;

	public $adresse;

	public $cp;

	public $ville;

	public $pays;

	public $siege;

}