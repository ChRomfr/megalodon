<?php

class rdv extends Record{

	const Table = 'rdv';
	
	/**
	 * ID du rdv
	 * @var [type]
	 */
	public $id;

	/**
	 * Date et heure du rdv yyyy-mm-dd hh:yy
	 * @var [type]
	 */
	public $date_rdv;	

	/**
	 * ID utilisateur pour qui est pris le rdv
	 * @var [type]
	 */
	public $user_id;

	public $tier_type;

	public $tier_id;

	public $source_type;

	public $source_id;

	public $description;

	public $add_by;

	public $add_on;

	public $statut;

	public $lieu;

	public $rapport;



}