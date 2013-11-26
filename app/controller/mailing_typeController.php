<?php

class mailing_typeController extends Controller{

	public function indexAction(){
		$types = new mailing_type();
		
		$this->registry->smarty->assign('types', $types->get());
		
		return $this->registry->smarty->fetch(VIEW_PATH . 'mailing_type' . DS . 'index.tpl');
	}

}