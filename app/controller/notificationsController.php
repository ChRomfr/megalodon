<?php

class notificationsController extends Controller{

	public function readAction($nid){
		// Recuperation dans la base
		$data = $this->registry->db->get_one('notifications', array('id =' => $nid));
		
		// Verification utilisateur peut voir la notification
		if(empty($data)){return 'Erreur : la notification recherchée n\'existe pas !';}
		if($data['user_id'] != $_SESSION['utilisateur']['id']){return 'Erreur : requete invalide !';}
		
		// On marque la notification comme lu
		$data['is_read'] = 1;
		$this->registry->db->update('notifications', $data, array('id =' => $nid));
		
		$this->registry->smarty->assign('notification', $data);
		
		return $this->registry->smarty->fetch(VIEW_PATH . 'notifications' . DS . 'read.tpl');
	}
	
	public function getcountAction($user_id){
		$nb = $this->registry->db->count('notifications', array('user_id =' => $user_id, 'is_read =' => 0, 'is_delete =' => 0));

		echo $nb;
		exit;
	}

	public function getlistAction($user_id){
		$notifications = $this->registry->db->get('notifications',array('user_id =' => $user_id, 'is_read =' => 0, 'is_delete =' => 0));
		$this->registry->smarty->assign('notifications', $notifications);
		echo $this->registry->smarty->fetch(VIEW_PATH . 'notifications' . DS . 'notifications_list.shark');
		exit;
	}

	public function setreadAction($notification_id){
		$this->registry->db->update('notifications', array('is_read' => 1), array('id =' => $notification_id, 'user_id =' => $_SESSION['utilisateur']['id']));
	}

	public function markallreadAction($user_id){
		$this->registry->db->update('notifications', array('is_read' => 1), array('user_id =' => $user_id));
	}

	public function viewallAction($user_id = null){
		if( empty($user_id) )
			$user_id = $_SESSION['utilisateur']['id'];

		$notifications = $this->registry->db->get('notifications', array('user_id =' => $user_id, 'is_delete =' => 0), 'date_notification DESC' ,100);

		$this->registry->smarty->assign('notifications', $notifications);

		return $this->registry->smarty->fetch(VIEW_PATH . 'notifications' . DS . 'viewall.shark');
	}

}