<?php

class categorieController extends Controller{

	/**
	 * Surchage de la fonction pour verification ACL
	 * @param [type] $registry [description]
	 */
	public function __construct($registry){
		parent::__construct($registry);

		if( isAdmin() < 1 ){
			$i = $this->load_controller('index');
			return $i->indexAction();
		}
	}
	
	public function indexAction(){

		$categories = $this->registry->db->get('categorie');

		$this->registry->smarty->assign('categories',$categories);

		return $this->registry->smarty->fetch(VIEW_PATH.'categorie'.DS.'index.tpl');
	}

	/**
	 * Gere l'ajout une nouvelle categorie
	 */
	public function addAction(){

		if( $this->registry->Http->post('categorie') !== NULL ){
			$categorie = new categorie($this->registry->Http->post('categorie'));
			
			if($categorie->isValid() === true){
				$categorie->save();
				$this->registry->smarty->assign('FlashMessage','Catégorie ajoutée');
				$this->registry->cache->remove('global_data');
				return $this->indexAction();
			}
		}

		printform:

		$this->getFormValidatorJs();
		return $this->registry->smarty->fetch(VIEW_PATH.'categorie'.DS.'add.tpl');
	}

	/**
	 * Traite l edition d'une categorie
	 * @param  [type] $cid [description]
	 * @return [type]      [description]
	 */
	public function editAction($cid){

		if( $this->registry->Http->post('categorie') !== NULL ){
			$categorie = new categorie($this->registry->Http->post('categorie'));
			
			if($categorie->isValid() === true){
				$categorie->save();
				$this->registry->smarty->assign('FlashMessage','Catégorie modifiée');
				$this->registry->cache->remove('global_data');
				return $this->indexAction();
			}
		}

		printform:
		$this->getFormValidatorJs();
		$categorie = new categorie;		
		$categorie->get($cid);

		$this->registry->smarty->assign('categorie',$categorie);

		return $this->registry->smarty->fetch(VIEW_PATH.'categorie'.DS.'edit.tpl');
	}

	/**
	 * Supprime une categorie de la base
	 * @param  [type] $cid [description]
	 * @return [type]      [description]
	 */
	public function deleteAction($cid){
		$this->registry->db->delete('categorie', $cid);
		$this->registry->db->delete('entreprise_categorie',$cid);
		$this->registry->smarty->assign('FlashMessage','Catégorie supprimée');
		$this->registry->cache->remove('global_data');
		return $this->indexAction();
		 
	}

	/**
	 * Permet le deplacement des elements d'une categorie vers une autre
	 * @param  [type] $cid [description]
	 * @return [type]      [description]
	 */
	public function moveAction($cid){

		if( $this->registry->Http->post('new_categorie') !== null ){
			$this->registry->db->update('categorie', array('categorie_id' => $this->registry->Http->post('new_categorie')), array('categorie_id =' => $cid));
			return $this->registry->Helper->redirect($this->registry->Helper->getLink('categorie',3,'Elements deplacés dans la nouvelle categorie'));
		}
		$categorie = new categorie();
		$categorie->get($cid);

		$this->getFormValidatorJs();
		$this->registry->smarty->assign(array(
			'cid'		=>	$cid,
			'categorie'	=>	$categorie,
		));

		return $this->registry->smarty->fetch(VIEW_PATH.'categorie'.DS.'move.tpl');
	}

}