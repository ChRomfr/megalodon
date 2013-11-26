<?php

class organismeController extends Controller{

	public function indexAction(){
		$organisme = new organisme;

		$this->registry->smarty->assign(array(
			'organismes'	=>	$organisme->get(),
		));

		return $this->registry->smarty->fetch(VIEW_PATH . 'organisme' . DS . 'index.tpl' );
	}

	/**
	 * Affiche et traite le formulaire pour ajouter un organisme dans la base
	 */
	public function addAction(){

		if( $this->registry->Http->post('organisme') ){

			$organisme = new organisme($this->registry->Http->post('organisme'));
			
			if($organisme->isValid() === true){
				$organisme->save();
				$this->registry->cache->remove('global_data');
				$this->registry->smarty->assign('FlashMessage','Organisme ajoute');
				return $this->indexAction();
			}
		}

		printform:
		$this->getFormValidatorJs();
		return $this->registry->smarty->fetch(VIEW_PATH . 'organisme' . DS . 'add.tpl' );
	}

	public function editAction($id){

		if( $this->registry->Http->post('organisme') ){
			$organisme = new organisme($this->registry->Http->post('organisme'));
			if($organisme->isValid() === true){
				$organisme->save();
				$this->registry->cache->remove('global_data');
				$this->registry->smarty->assign('FlashMessage','Organisme modifié');
				return $this->indexAction();
			}
		}

		printform:

		$organisme = new organisme();
		$organisme->get($id);
		$this->getFormValidatorJs();
		$this->registry->smarty->assign('organisme',$organisme);		
		return $this->registry->smarty->fetch(VIEW_PATH . 'organisme' . DS . 'edit.tpl' );
	}

	/**
	 * Traite la suppression d'un organisme dans la base
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function deleteAction($id){

		$organisme = new organisme();
		$organisme->delete($id);

		// Suppression lien contacts organisme
		$this->registry->db->delete('contacts_organisme', null, array('organisme_id =' => $id));

		$this->registry->smarty->assign('FlashMessage','Organisme ajoute');

		return $this->indexAction();
	}

}