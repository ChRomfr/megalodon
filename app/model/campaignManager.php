<?php

class campaignManager extends BaseModel{

	public function get($where = null, $order = 'cp.id DESC'){
		$this->db->select('DISTINCT(cp.id), cp.*, cpt.libelle as type, group_concat(u.identifiant) as assign_to')
			->from('campaign cp')
			->left_join('campaign_type cpt','cp.type_id = cpt.id')
			->left_join('campaign_assign_to cat','cp.id = cat.campaign_id')
			->left_join('user u','cat.assign_to = u.id');
		
		if(!is_null($where)) $this->db->where($where);
		$this->db->group_by('cat.campaign_id')->order($order);
		return $this->db->get();
	}
	
	public function getById($id){
		$this->db->select('cp.*, cpt.libelle as type, group_concat(u.identifiant) as assign_to, group_concat(u.id) as assign_to_id')
			->from('campaign cp')
			->left_join('campaign_type cpt','cp.type_id = cpt.id')
			->left_join('campaign_assign_to cat','cp.id = cat.campaign_id')
			->left_join('user u','cat.assign_to = u.id')			
			->where(array('cp.id =' => $id))
			->group_by('cat.campaign_id');

		return $this->db->get_one();
	}

	public function getTargetByCampaignId($id, $filter = 'all'){

		$this->db->select(' DISTINCT(c.id), c.nom, c.prenom, cc.id as cc_id, cc.statut, cc.contact_id')
			->from('campaign_contacts cc')
			->left_join('contacts c','cc.contact_id = c.id')
			->left_join('telephones t','c.id = t.contact_id')
			->where(array('cc.campaign_id =' => $id));

			if( $filter != 'all' ){
				$this->db->where(array('cc.statut =' => $filter));
			}

		return $this->db->get();

	}
}