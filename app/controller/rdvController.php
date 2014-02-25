<?php

class rdvController extends Controller{
	
    public function myAction(){
    	$uid = $_SESSION['utilisateur']['id'];
    	$rdv = new rdv();

    	$this->registry->smarty->assign('meet',$rdv->getByUserId($uid));

    	return $this->registry->smarty->fetch(VIEW_PATH.'rdv'.DS.'my.meg');
    }

    public function get_detailAction($rid){
    	$rdv = new rdv();
    	$rdv->get($rid);

    	if($rdv->tier_type == 'contacts'){
    		$this->load_manager('contacts');
    		$tier = $this->manager->contacts->getById($rdv->tier_id);
    	}

    	$this->registry->smarty->assign(array(
    		'rdv'	=>	$rdv,
    		'tier'	=>	$tier,
    	));

    	return $this->registry->smarty->fetch(VIEW_PATH.'rdv'.DS.'get_detail.meg');
    }

    public function ajax_get_infos_tierAction($rid){
    	$rdv = new rdv();

    	$rdv->get($rid);

    	if($rdv->tier_type == 'contacts'){
    		$this->load_manager('contacts');
    		$result = $this->manager->contacts->getById($rdv->tier_id);
    		return json_encode($result);
    	}
    }

	public function get_formAction(){

	   if( isset($_GET['tier_type']) && $_GET['tier_type'] == 'contacts'){
	   		$row = new contacts();
	   		$row->get($_GET['tier_id']);
	   		$this->registry->smarty->assign('tier', $row);
	   }

		return $this->registry->smarty->fetch(VIEW_PATH.'rdv'.DS.'form.meg');
	}
    
    
    public function addAction(){
       	if(!is_null($this->registry->Http->post('rdv'))){

			$rdv = new rdv($this->registry->Http->post('rdv'));
			$rdv->add_by = $_SESSION['utilisateur']['id'];
			$rdv->add_on = date('Y-m-d H:i:s');
			$rdv->statut = 0;
			$rid = $rdv->save();

			print_r($rdv);

			exit;
		} 
    }
}