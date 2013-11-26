<?php

class reportController extends Controller{
	
	public function indexAction(){
	
		$param_query = array();
	
		$date = $this->registry->Http->get('date');
		
		if( empty($date) ){
			return $this->Helper->redirect($this->Helper->getLink('index/index'), 3, 'Aucune date de selectionné');
		}
		
		$param_query['date ='] =  $date;
		
		$datas = $this->registry->db->select('ip, SUM(bytes) as cumul, count(id)')->from('access_log')->where($param_query)->group_by('ip')->order('cumul DESC')->get();
		
		// Envoie a smarty
		$this->registry->smarty->assign(array(
			'datas'		=>	$datas,
		));

		// Generation de la page
		return $this->registry->smarty->fetch(VIEW_PATH . 'report' . DS . 'index.tpl');
		
		echo"<pre>";
		print_r($datas);
		echo"</pre>";
		
	}
	
}