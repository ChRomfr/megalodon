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
				$cc_data = $this->registry->db->get_one('campaign_contacts', array('campaign_id =' => $rdv->source_id, 'contact_id =' => $rdv->tier_id));

				// Ajout d un suivi a la campagne
				$campaign_suivi = array(
					'campaign_id'	=>	$rdv->source_id,
					'contact_id'	=>	$rdv->tier_id,
					'cam_con_id'	=>	$cc_data['id'],
					'suivi'			=>	'Nouveau rendez vous pris',
					'add_by'		=>	$_SESSION['utilisateur']['id'],
					'add_on'		=>	date('Y-m-d H:i:s'),
				);

				$this->registry->db->insert('campaign_contacts_suivi', $campaign_suivi);
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