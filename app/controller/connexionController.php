<?php
class connexionController extends BaseconnexionController{

		/**
    	 * Affiche et traite le forumaire d identification
    	 * @return [type] [description]
    	 */
    	public function indexAction(){
		
		# Si utilisateur deja connecter on le renvoie a l index
		if( $_SESSION['utilisateur']['id'] != 'Visiteur' ){
			header('location:' . $this->app->Helper->getLink('index') );
			exit;
		}
		
		# Soumission du formulaire
		if( $this->app->Http->postExists('login') ){           
            
            // Chargement du bon model
            if( is_file(APP_PATH . 'model' . DS  . 'utilisateur.php') ):
                $this->load_model('utilisateur');
                $user = new utilisateur( $this->app->HTTPRequest->postData('login') );
            else:
                $this->load_model('utilisateur', 'base_app');
                $user = new Baseutilisateur( $this->app->HTTPRequest->postData('login') );
            endif;	
			
			// Verification information ok
            $Result = $user->checkLogin();
            
            if($Result == 'session_ok'){
            	getACLs();
            	
            	// Enregistrement date dernier connexion dans l utilisateur
            	$this->registry->db->update('user', array('last_connexion' => time()), array('id =' => $_SESSION['utilisateur']['id']));

            	// Enregistrement de la session dans la base
            	$session = array(
            		'session_id' 	=> 	$_SESSION['session_id'], 
            		'user_id' 		=> 	$_SESSION['utilisateur']['id'],
            		'last_update' 	=> 	time(),
            		'url'			=>	$_SERVER['REQUEST_URI'],
            		'ip'			=>	$_SERVER['REMOTE_ADDR']
            	);

            	$this->registry->db->insert('sessions', $session );
            	
            	$index = $this->load_controller('index');

				$this->registry->Helper->pnotify('Bienvenu', 'Vous êtes maintenance connecté !', 'success');

				return $index->indexAction();
            }elseif( $Result !== true){
            	$this->app->smarty->assign('Error_login', $Result);
            	goto print_form;
            }

            # On creer la session
			//$this->app->session->create($user);

			# On met a jour le profil utilisateur avec sa derniere connexion
			//$this->app->db->update(PREFIX . 'user', array('last_connexion' => time()), array('id =' => $_SESSION['utilisateur']['id']));
				
		}
		# Affichage du formulaire
		print_form:
		$this->Helper->getFormValidatorJs();
		return $this->app->smarty->fetch(BASE_APP_PATH . 'view' . DS . 'connexion' . DS . 'index.tpl');
	}

}