<?php

class rdvController extends Controller{
	
     
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
			$rdv->save();

			if($rdv->source_type == 'campaign'){
				// Ajout d un suivi a la campagne
			}

			// Ajout notification à l utilisateur qui a eu un rdv de pris
			
			// Envoie d'un email 

			// Ajout d'un log utilisateur
			
			// Ajout d'un log contact

			print_r($rdv);

			exit;
		} 
    }
}