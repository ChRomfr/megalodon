<?php

class campaignManager extends BaseModel{

	public function get($where = null, $order = 'cp.id DESC'){
		$this->db->select('cp.*, cpt.libelle as type, u.identifiant as assign_to')
			->from('campaign cp')
			->left_join('campaign_type cpt','cp.type_id = cpt.id')
			->left_join('user u','cp.assign = u.id')
			;
		
		if( !is_null($where) ) $this->db->where($where);
		$this->db->order($order);
		return $this->db->get();
	}
	
	public function getById($id){
		$this->db->select('cp.*, cpt.libelle as type, u.identifiant as assign_to')
			->from('campaign cp')
			->left_join('campaign_type cpt','cp.type_id = cpt.id')
			->left_join('user u','cp.assign = u.id')
			->where(array('cp.id =' => $id));

		return $this->db->get_one();
	}

	public function getTargetByCampaignId($id, $filter = 'all'){

		$this->db->select(' DISTINCT(c.id), concat_ws("",s.raison_social, p.nom) as nom, p.prenom, cc.id as cc_id, cc.statut')
			->from('campaign_contacts cc')
			->left_join('contacts c','cc.contact_id = c.id')
			->left_join('personne p','c.id = p.contact_id')
			->left_join('societe s','c.id = s.contact_id')
			->left_join('telephones t','c.id = t.contact_id')
			->where(array('cc.campaign_id =' => $id));

			if( $filter != 'all' ){
				$this->db->where(array('cc.statut =' => $filter));
			}

		return $this->db->get();

	}
}