<?php

class utilisateur extends Baseutilisateur{
	const Table = 'user';		    

    public $auth_type;

    public $site_id;
	
	public $contacts_per_page;
	
	public $historique_contact;

    /**
     * Defini si l utilisateur peux exporter les contacts en CSV depuis la liste
     * @var [type]
     */
    public $contacts_export_csv;

    /**
     * Determine si l utilisateur peut gerer les mailings (ajout/edit/suppression/valid/envoye ...)
     * @var bool
     * @Db: {"name":"mailing_adm","type":"INT","length":1,"notnull":1,"default":0}
     */
    public $mailing_adm;

    public $index_map_contacts;

    /**
     * Jeton de securite pour auth auto
     * @var [type]
     */
    public $token;

    /**
     * Jeton de securite pour le sso via link
     * @var [type]
     */
    public $sso_link_token;

    /**
     * Active ou non le sso link pour l utilisateur
     * @var [type]
     */
    public $sso_link;

    /**
     * Active ou non les notifications des contacts lié
     * @var int(1)
     * @Db: {"name":"mailing_adm","type":"INT","length":1,"notnull":1,"default":0}
     */
    public $follow_my_contact;

    /**
     * Determine l'option perso pour l affichage du preview contact
     * @var string
     * @Db: {"name":"contacts_preview","type":"VARCHAR(10)", "default":"none"}
     */
    public $contacts_preview;

    public function checkLogin(){
    	global $registry;
        
    	// Verification auth via LDAP
    	if($registry->config['auth_sso'] == 1){
    		$result = $registry->adldap->user()->authenticate($this->identifiant, $this->password);	
           
    		// On test le resultat
    		if(!$result){
                if($registry->config['auth_php'] == 1){
                    goto auth_php;
                }

    			return false;
    		}

    		if( $this->user_in_localdb() == 0 ){
    			// Creation de l utilisateur dans la base local  
                $user = $this->insert_user_in_localdb();
                $this->hydrate($user);
                $registry->session->create($this);

                return "session_ok";
    		}else{
                $user = $registry->db->get_one('user', array('identifiant =' => $this->identifiant));
                $this->hydrate($user);
                if($this->actif == 0){
                    return "Error_user_not_active";
                }
                $registry->session->create($this);

                return "session_ok";
            }
    	}
        
        // Verification via base PHP
        if($registry->config['auth_php'] == 1){
            auth_php:
            $tmp = $registry->db->get_one('user', array('identifiant =' => $this->identifiant));
            
            if(empty($tmp)){
               return "Error_user_not_found"; 
            }
            
            $this->cryptPassword();
            
            // Verification correspondance mot de passe
            if($this->password != $tmp['password']){
                return "Error_bad_password";
            }
            
            // Creation de la session
            $user = $registry->db->get_one('user', array('identifiant =' => $this->identifiant, 'password =' => $this->password));
            $this->hydrate($user);
            $registry->session->create($this);
            
            return "session_ok";
        }
        
        return "Error_general_empty_method";
    }

    /**
     * Verifie si l utilisateur est present dans la base du logiciel
     * @return [type] [description]
     */
    private function user_in_localdb(){
    	global $registry;

    	return $registry->db->count('user', array('identifiant =' => $this->identifiant));
    }

    /**
     * Injecte l utilisateur dans la base local par rapport au info LDAP
     * @return [type] [description]
     */
    private function insert_user_in_localdb(){
    	global $registry;

    	$user_ldap = $registry->adldap->user()->info($this->identifiant, array("*"));

        return user_add_sso($user_ldap);
    }

}