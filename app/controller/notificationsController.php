<?php

class notificationsController extends Controller{
	
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