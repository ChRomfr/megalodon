<?php

class mailing extends Record{
	
	/**
	 * @version : {"version":2013072501}
	 */
	const Table = 'mailings';
	
	/**
	 * Cle primaire
	 * @var int
	 * @Db: {"name":"id","type":"INT","autoincrement":1,"primary":1,"notnull":1}
	 */
	public $id;

	/**
	 * Identifiant de l utilisateur qui a effectue la demande
	 * @var int
	 * @Db : {"name":"demande_by","type":"INT","notnull":1}
	 */
	public $demand_by;

	/**
	 * Date de la demande
	 * @var datetime
	 * @Db : {"name":"demand_on","type":"DATETIME","notnull":1}
	 */
	public $demand_on;

	/**
	 * Utilisateur qui a valide le mailing
	 * @var int
	 * @Db : {"name":"valid_by","type":"INT","notnull":1}
	 */
	public $valid_by;

	/**
	 * Date de validation du mailing
	 * @var datetime
	 * @Db : {"name":"valid_on","type":"DATETIME"}
	 */
	public $valid_on;

	/**
	 * Bool qui determine si le mailing est envoye
	 * @var int
	 * @Db: {"name":"send","type":"INT","default":0,"notnull":1}
	 */
	public $send;

	/**
	 * Contient les criteres de recherche serialise
	 * @var text
	 * @Db: {"name":"cible","type":"TEXT"}
	 */
	public $cible;

	/**
	 * nom du mailing
	 * @var char
	 * @Db: {"name":"libelle","type":"VARCHAR","length":200,"notnull":1}
	 */
	public $libelle;

	/**
	 * Description detaille du mailing
	 * @var text
	 * @Db: {"name":"description","type":"TEXT"}
	 */
	public $description;

	/**
	 * Date a laquelle on souhaite voir partir le mailing
	 * @var date
	 * @Db: {"name":"date_wish","type":"DATE","notnull":1}
	 */
	public $date_wish;

	/**
	 * Determine si le mailing est valide
	 * @var int
	 * @Db : {"name":"valid","type":"INT","length":1,"default":0,"notnull":1}
	 */
	public $valid;

	/**
	 * Date a laquel le mailing a ete envoye
	 * @var datetime
	 * @Db: {"name":"date_send","type":"DATE"}
	 */
	public $date_send;
	
	/**
	 * Raison du refus
	 * @var text
	 * @Db: {"name":"refus","type":"TEXT"}
	 */
	public $refus;
	
	/**
	 * 
	 * @var char
	 * @Db: {"name":"caller","type":"VARCHAR","length":150}
	 */
	public $caller;
	
	/**
	*	@NoDb: {"nodb":1}
	*/
	public $validateur;
	
	/**
	*	@NoDb: {"nodb":1}
	*/
	public $demandeur;

}