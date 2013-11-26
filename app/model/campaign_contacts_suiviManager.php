<?php

class campaign_contacts_suiviManager extends BaseModel{
	
	public function getByCCID($ccid){
		$this->db->select('ccs.*, u.identifiant as author')
			->from('campaign_contacts_suivi ccs')
			->left_join('user u','ccs.add_by = u.id')
			->where(array('ccs.cam_con_id =' => $ccid))
			->order('add_on DESC');

		return $this->db->get();
	}

}