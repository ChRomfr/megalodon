<?php

class tier extends Record{
	
	const Table = 'tiers';

	public $id;

	public $name;

	public $description;

	public $adresse;

	public $cp;

	public $ville;

	public $pays;

	public $telephone;

	public $fax;

	public $email;

	public $site;

	public $siret;

	public $tva;

	public $internal_code;

}