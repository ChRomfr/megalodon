<?php

class filesController extends Controller{
	
	public function get_by_contact_idAction($cid){
		$result = $this->registry->db->select('cf.*, u.identifiant')
					->from('contacts_files cf')
					->left_join('user u','cf.user_id = u.id')
					->where(array('cf.contact_id =' => $cid))
					->order('cf.name')
					->get();

		if($_SESSION['utilisateur']['isAdmin'] > 0){
			$i=0;
			foreach($result as $row){
				$result[$i]['delete_file'] = '<a href="javascript:deleteFile('.$row['id'].');" title="Supprimer ce fichier"><i class="fa fa-trash-o"></i></a>';
				$i++;
			}

		}

		return json_encode($result);
	}

}