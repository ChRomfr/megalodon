<?php

class tasksController extends Controller{
	
	public function set_processAction($task_id){
		$date_process = date("Y-m-d H:i:s");
		$uid = $_SESSION['utilisateur']['id'];

		// Mise  a jour de la tache
		$this->registry->db->update('tasks', array('process' => 1, 'date_process' => $date_process), array('id =' => $task_id, 'user_id =' => $uid));

		// Recuperation de la tache
		$task = $this->registry->db->get_one('tasks', array('id =' => $task_id));

		// Mise a jour des taches jumelles
		$this->registry->db->update('tasks',  array('process' => 1, 'date_process' => $date_process, 'process_by' => $uid), array('guid =' => $task['guid'], 'id !=' => $task_id) );

		// Recuperation des utilisateurs pour la notification
		$users = $this->registry->db->get('tasks', array('guid =' => $task['guid'], 'id !=' => $task_id));

		// Envoie des notifications
		if(count($users) > 0){
			$notification = array(
				'sender_id'			=>	0,
				'user_id'			=>	0,
				'is_read'			=>	0,
				'is_delete'			=>	0,
				'date_notification'	=>	date("Y-m-d H:i:s"),
				'message'			=>	'La tache : '. $task['task'] .' a ete faite par '. $_SESSION['utilisateur']['identifiant'],
				'third_type'		=>	'mailings',
				'third_id'			=>	'',
			);
			foreach ($users as $user) {
				$notification['user_id'] = $user['user_id'];
				$this->registry->db->insert('notifications', $notification);
			}
		}

		return 'ok';
	}

	public function mytasksAction(){
		$tasks = $this->registry->db->get('tasks', array('user_id =' => $_SESSION['utilisateur']['id']), 'date_add DESC');

		$this->registry->smarty->assign('tasks', $tasks);

		return $this->registry->smarty->fetch(VIEW_PATH . 'tasks' . DS . 'mytasks.shark');
	}

}