<?php

class mailingManager extends BaseModel{
	
	public function get($limit = 100, $offset = 0, $where = null){
		return	$this->db->select('m.*, u1.identifiant as demandeur')
				->from('mailings m')
				->left_join('user u1','m.demand_by = u1.id')
				->where($where)
				->order('m.date_wish DESC')
				->limit($limit)
				->offset($offset)
				->get();
	}
	
	public function getById($id){
	
		return	$this->db->select('m.*, u1.identifiant as validateur, u2.identifiant as demandeur')
					->from('mailings m')
					->left_join('user u1', 'm.demand_by = u1.id')
					->left_join('user u2', 'm.valid_by = u2.id')
					->where(array('m.id =' => $id))
					->get_one();
	}
}