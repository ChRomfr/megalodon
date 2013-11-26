<?php

class posteController extends Controller{
	
	public function indexAction(){
		$poste = new poste();
				
		return $this->registry->smarty->assign(array(
			'postes'	=>	$poste->get(),
		));
		
		return $this->registry->smarty->fetch(VIEW_PATH.'poste'.DS.'index.tpl');
	}
	
}