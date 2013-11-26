<?php

class entrepriseManager extends BaseModel{
	
	public function getByEmptyCoords(){
		return 	$this->db
					->select('e.*')
					->from('entreprises e')
					->where_free('lat IS NULL OR lat = ""')
					->get();
	}

}