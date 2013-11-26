<?php

class ajaxController extends Controller{
	
	public function getVilleByCpAction(){
		$Cp = $this->app->HTTPRequest->getData('term');		
		$Datas = $this->app->db->select('DISTINCT(ville) as label, code_postal as value')->from(PREFIX . 'contacts')->where_free( 'code_postal LIKE "'. $Cp .'%"' )->get();
		return json_encode($Datas);			
	}
	
	public function getNumSemaineAction(){
		$Date = $this->app->HTTPRequest->getData('date');
		$Tmp = explode('/', $Date);
		return date("W", mktime(13,0,0,$Tmp[1],$Tmp[0],$Tmp[2]));
	}
}