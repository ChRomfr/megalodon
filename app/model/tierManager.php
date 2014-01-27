<?php

class tierManager extends BaseModel{
	
	/**
	 * Recupere tout les tiers dans la base et leur type avec le libelle
	 * @return [type] [description]
	 */
	public function getAll(){
		$result = $this->db->select('t.*, tt.libelle as type')
				->from('tiers t')
				->left_join('tiers_type tt', 't.type_id = tt.id')
				->order('t.name, t.type_id')
				->get();

		return $result;
	}

}