<?php

class ca extends Record{
	
	const Table = 'ca';

	/**
	 * Id dans la base
	 * @var [type]
	 */
	public $id;

	/**
	 * Contact avec qui le CA a ete fait
	 * @var [type]
	 */
	public $contact_id;

	/**
	 * Utilisateur à qui est attribué le CA
	 * @var [type]
	 */
	public $user_id;

	/**
	 * Statut du CA 1:prevision 2:realise 3:annuler
	 * @var [type]
	 */
	public $statut;

	/**
	 * Format YYYY-MM-DD
	 * @var [type]
	 */
	public $date_ca;

	/**
	 * Refeence interne bon de commande
	 * @var [type]
	 */
	public $ref_bdc;

	/**
	 * Reference interne facture
	 * @var [type]
	 */
	public $ref_facture;

	/**
	 * Date d ajout dans la base
	 * @var [type]
	 */
	public $date_add;

	public $montant;

	public function get_by_contact_id($contact_id){
		global $db;
		
		$result = $db->select('ca.*')
					->from('ca ca')
					->left_join('user u','ca.user_id = u.id')
					->where(array('contact_id =' => $contact_id))
					->order('date_ca DESC')
					->get();

		return $result;
	}
}