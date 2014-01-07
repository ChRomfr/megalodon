<?php

class mailingManager extends BaseModel{

	public function count($where = null){
		return $this->db->count('mailings', $where);
	}
	
	public function get($limit = 100, $offset = 0, $where = null){
		return	$this->db->select('m.*, u1.identifiant as demandeur, mt.libelle as type, ma.libelle as action')
				->from('mailings m')
				->left_join('user u1','m.demand_by = u1.id')
				->left_join('mailings_type mt','m.type_id = mt.id')
				->left_join('mailing_actions ma', 'm.action_id = ma.id')
				->where($where)
				->order('m.date_wish DESC')
				->limit($limit)
				->offset($offset)
				->get();
	}
	
	public function getById($id){
	
		return	$this->db->select('m.*, u1.identifiant as validateur, u2.identifiant as demandeur, mt.libelle as type')
					->from('mailings m')
					->left_join('user u1', 'm.demand_by = u1.id')
					->left_join('user u2', 'm.valid_by = u2.id')
					->left_join('mailings_type mt', 'm.type_id = mt.id')
					->where(array('m.id =' => $id))
					->get_one();
	}

	public function getByContactId($contact_id){
		return 	$this->db->select('m.id, m.libelle, cm.email, cm.open')
				->from('mailings m')
				->left_join('contacts_mailing cm','cm.mailing_id = m.id')
				->where(array('cm.contact_id =' => $contact_id))
				->order('m.id DESC')
				->get();
	}


	public function getByAction($aid){
		return	$this->db->select('m.*, u1.identifiant as demandeur, mt.libelle as type')
				->from('mailings m')
				->left_join('user u1','m.demand_by = u1.id')
				->left_join('mailings_type mt','m.type_id = mt.id')
				->where(array('action_id =' => $aid))
				->order('m.date_wish DESC')
				->get();
	}
}