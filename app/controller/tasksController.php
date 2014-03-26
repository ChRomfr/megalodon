<?php

class tasksController extends Controller{
	
	/**
	 * Marque une tache comme faite
	 * @param [type] $task_id [description]
	 */
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

	/**
	 * Affiche la liste des taches de l utilisateur courant
	 * @return [type] [description]
	 */
	public function mytasksAction(){
		if(!is_null($this->registry->Http->get('all')))
			$tasks = $this->registry->db->get('tasks', array('user_id =' => $_SESSION['utilisateur']['id'], 'is_delete =' => 0), 'date_expire DESC, date_add DESC');
		else
			$tasks = $this->registry->db->get('tasks', array('user_id =' => $_SESSION['utilisateur']['id'], 'process =' => 0, 'is_delete =' => 0), 'date_expire DESC, date_add DESC');

		// Nombre de tache en retard
		$nb_tasks_delay = $this->registry->db->select('COUNT(id) as nb')->from('tasks')->where('date_expire < "'. date('Y-m-d') .'" AND date_expire IS NOT NULL AND is_delete = 0 AND process = 0 AND user_id = '. $_SESSION['utilisateur']['id'])->get_one();

		$this->registry->smarty->assign('tasks', $tasks);
		$this->registry->smarty->assign('task_delay', $nb_tasks_delay['nb']);

		return $this->registry->smarty->fetch(VIEW_PATH . 'tasks' . DS . 'mytasks.shark');
	}

	/**
	 * Marque une tache comme supprimé
	 * @param [type] $task_id [description]
	 */
	public function set_deleteAction($task_id){
		$task = $this->registry->db->get_one('tasks', array('id =' => $task_id, 'user_id =' => $_SESSION['utilisateur']['id']));
		if(empty($task)){ return $this->mytasksAction(); }
		$task['is_delete'] = 1;
		$this->registry->db->update('tasks', $task);
		$this->registry->smarty->assign('FlashMessage', 'Tâche supprimée');
		return $this->mytasksAction();
	}

	public function get_formAction(){
		$controller = $this->registry->Http->get('controller');
		$controller_id = $this->registry->Http->get('controller_id');

		$this->registry->smarty->assign('controller', $controller);
		$this->registry->smarty->assign('controller_id', $controller_id);

		return $this->registry->smarty->fetch(VIEW_PATH.'tasks'.DS.'form.tpl');
	}

	public function addAction(){
		if(!is_null($this->registry->Http->post('task'))){
			$task = new task($this->registry->Http->post('task'));

			$task->user_id = $_SESSION['utilisateur']['id'];
			$task->creat_id = $_SESSION['utilisateur']['id'];
			$task->date_add = date('Y-m-d H:i:s');
			$task->process = 0;
			$task->is_delete = 0;
			$task->priority = 2;
			$task->tache = htmlentities($task->tache);
			
			$task->save();

		}
	}

}