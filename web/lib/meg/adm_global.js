/* Redirige l utilisateur pour supprimer un utilisateur */
function user_delete(uid){
	if(confirm('Etes vous de vouloir supprimer cet utilisateur ?')){
		window.location.href = base_url + 'index.php/adm/users_delete/'+uid;
	}
}

/* Recupere les logs utilisateurs */
function get_logs(uid){
	$.get(
        base_url + 'index.php/adm/users_view_log_contacts/'+uid,
        {nohtml:'nohtml'},
        function(data){
            $("#modal-global-body").html('<div class="well">'+data+'</div>');
        }        
    );

    $('#modal-global-label').html('LOG UTILISATEUR');
    $('#modal-global').modal('show');
}

/* Affiche le formulaire pour ajouter un groupe utilisateur */
function group_add(){
	$.get(
        base_url + 'index.php/adm/groups_get_form',
        {nohtml:'nohtml'},
        function(data){
            $("#modal-global-body").html('<div class="well">'+data+'</div>');
        }        
    );
    $('#modal-global-label').html('<i class="fa fa-group"></i>&nbsp;&nbsp;Nouveau groupe');
    $('#modal-global').modal('show');
}

/* Affiche le formulaire pour modifier un groupe utilisateur */
function group_edit(tid){
	$.get(
        base_url + 'index.php/adm/groups_get_form/'+tid,
        {nohtml:'nohtml'},
        function(data){
            $("#modal-global-body").html('<div class="well">'+data+'</div>');
        }        
    );
    $('#modal-global-label').html('<i class="fa fa-group"></i>&nbsp;&nbsp;Edition groupe');
    $('#modal-global').modal('show');
}

/* Affiche le message et redirige pour la suppression d un groupe utilisateur */
function group_delete(gid){
	if(confirm('Etes vous sur de vouloir supprimer ce groupe ?')){
		window.location.href=base_url + 'index.php/adm/groups_delete/'+gid;
	}
}

/* Affiche le formulaire pour ajouter un utilisateur dans un groupe */
function form_add_user(gid, gname){
	$.get(
        base_url + 'index.php/adm/groups_form_add_in/'+gid,
        {nohtml:'nohtml'},
        function(data){
            $("#modal-global-body").html('<div class="well">'+data+'</div>');
        }        
    );
    $('#modal-global-label').html('<i class="fa fa-group"></i>&nbsp;&nbsp;'+gname);
    $('#modal-global').modal('show');
}

/* Confirmation pour retirer un utilisateur du groupe */
function remove_group(gid, uid, identifiant){
	if(confirm('Etes vous sur de vouloir retirer l\' utilisateur '+ identifiant +' du groupe ?')){
		window.location.href= base_url + 'index.php/adm/groups_remove_user/'+gid+'?uid='+uid;
	}
}

/*
-	
-----	GESTION DES POSTES POUR LES CONTACTS -----
-
*/

/* Confirmation pour la suppression */
function delete_poste(pid){
	if(confirm('Etes vous sur de vouloir supprime ce poste ?')){
		window.location.href = base_url+'adm/contacts_postes_delete/'+pid;
	}
}

/* Apl le formulaire d ajout */
function get_form_new_poste(){
	$.get(
        base_url + 'index.php/adm/contacts_postes_load_form',
        {nohtml:'nohtml'},
        function(data){
            $("#modal-global-body").html(data);
        }        
    );
    $('#modal-global-label').html('<i class="fa fa-suitcase"></i>&nbsp;&nbsp;Nouveau poste');
    $('#modal-global').modal('show');
}

/* Apl le formulaire d edition */
function get_form_edit_poste(pid){
	$.get(
        base_url + 'index.php/adm/contacts_postes_load_form/'+pid,
        {nohtml:'nohtml'},
        function(data){
            $("#modal-global-body").html(data);
        }        
    );
    $('#modal-global-label').html('<i class="fa fa-suitcase"></i>&nbsp;&nbsp;Modification poste');
    $('#modal-global').modal('show');
}