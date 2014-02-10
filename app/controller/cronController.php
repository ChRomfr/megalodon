<?php

class cronController extends Controller{
	
	/**
	 * Envoie un recap des mailings
	 * @return {[type]} [description]
	 */
	public function mailing_recapAction(){
		// Recuperation du jeton
		$token = $this->registry->Http->get('cron_token');

		// On charge le controller
		$cadm = $this->load_controller('adm');

		// Execution de l action
		$result = $cadm->mailings_resumeAction('week_pair');

		// Enregistrement log avec result
		$log = new log(array(
				'log' 		=> 	'Traitement CRON. Result : '. $result,
				'module'	=>	'cron',
				'link_id'	=>	0,
		));

		$log->save();
		
		// on detruit la session admin
		$this->registry->session->destroy();

		// On indique que l action est fini
		return 1;
	}

}