<?php

class utilisateurController extends BaseutilisateurController{
	
	public function editAction(){
	
		if(  $this->app->HTTPRequest->postExists('user') ){
			$User = new myObject($this->app->HTTPRequest->postData('user') );
			
			if( !isset($User->historique_contact) ){
				$User->historique_contact = 0;
			}else{
				$User->historique_contact = 1;
			}	

			if( !isset($User->index_map_contacts) ){
				$User->index_map_contacts = 0;
			}else{
				$User->index_map_contacts = 1;
			}
			
			$this->app->db->update(PREFIX . 'user', $User, array('id =' => $_SESSION['utilisateur']['id']));

			# Mise a jour des var de sessions
			$User = $this->app->db->get_one(PREFIX . 'user', array('id =' => $_SESSION['utilisateur']['id']));
			$this->app->session->create($User);

			# Redirection utilisateur
			$this->registry->smarty->assign('FlashMessage','Modifications sauvegardées');
			return $this->indexAction();
		}
		
		$user = new utilisateur();
		$user->get($_SESSION['utilisateur']['id']);
		
		$this->registry->smarty->assign('user',$user);
		
		return $this->registry->smarty->fetch(VIEW_PATH . 'utilisateur' . DS . 'edit.shark');
	}

	/**
	 * Affiche les logs sur les contacts liés a l utilisateur
	 * @return [type] [description]
	 */
	public function view_log_contactsAction(){
		$user_id = $_SESSION['utilisateur']['id'];

		$logs = $this->registry->db->select('cl.*, s.raison_social, p.nom, p.prenom')
					->from('contacts_log cl')
					->left_join('contacts c', 'cl.contact_id = c.id')
					->left_join('societe s', 's.contact_id = c.id')
					->left_join('personne p', 'p.contact_id = c.id')
					->where(array('cl.user_id = ' => $user_id))
					->order('cl.date_log DESC')
					->limit(100)
					->get();

		$this->registry->smarty->assign('logs', $logs);

		return $this->registry->smarty->fetch(VIEW_PATH . 'utilisateur' . DS . 'mylogs.meg');

	}
	
	/**
	*	Permet l edition d un utilisateur par son id
	*	@param int $id Identifiant de l utilisateur dans la base
	*	@return mixed code html
	*/
	public function editionAction($id){
		
		$user = new utilisateur();
		$user->get($id);
		
		$this->registry->smarty->assign('user',$user);
		
		return $this->registry->smarty->fetch(VIEW_PATH . 'utilisateur' . DS . 'edition.shark');
	}
	
	public function listeAction(){
		$users = $this->registry->db->get('user');
		$this->registry->smarty->assign('users',$users);
		return $this->registry->smarty->fetch(VIEW_PATH . 'utilisateur' . DS . 'liste.shark');
	}
}
