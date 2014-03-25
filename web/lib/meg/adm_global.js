/* Redirige l utilisateur pour supprimer un utilisateur */
function user_delete(uid){
	if(confirm('Etes vous de vouloir supprimer cet utilisateur ?')){
		window.location.href = base_url + 'index.php/adm/users_delete/'+uid;
	}
}

/* Recupere les logs utilisateurs */
function get_logs_by_user(uid){
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

/**
 * Affiche les contacts dans la corbeille
 * @return {[type]} [description]
 */
function getCorbeille(){
    $.get(
        base_url + 'index.php/contacts/maintenancegetcorbeille',{},
        function(data){
            $("#maintenace-ctcs-corbeille").html(data);
        }
    );    
}

/**
 * Supprime les contacts a la corbeille
 * @return {[type]} [description]
 */
function cleantrash(){
    if(confirm('Etes vous sur de vouloir vider la corbeille ?')){
        $.get(
            base_url + 'index.php/contacts/maitenancecleantrash',{},
            function(data){
                $("#maintenace-ctcs-corbeille").html(data + ' contact(s) ont ete supprime de la base');
            }
        );
    }
}

/**
 * Affiche le formulaire permettant de modifier l adresse email
 * depuis la liste affiche dans la maintenance des contacts
 * @param  {[type]} cid [description]
 * @return {[type]}     [description]
 */
function getformeditemail(cid){
    $.get(
        base_url + 'index.php/contacts/ajaxgetformeditemailmaintenance/'+cid,{},
        function(data){
            $("#modal-global-body").html(data);
        }        
    );

    $('#modal-global-label').html('Modification email');
    $('#modal-global').modal('show');
}

/**
 * Affiche la liste des email erronés dans la contact (maintenance)
 * @return {[type]} [description]
 */
function getemailerror(){
    $.get(
        base_url + 'index.php/contacts/ajaxcheckemailcontact',{},        
        function(data){
            $('#result-opt').html('<table id="table-result-email" class="table table-condensed table-striped"><thead><tr><th>#</th><th>Email</th><th></th></thead><tbody></tbody></table>');
            var tpl = '<tr><td>{{id}}</td><td><a href="javascript:gotocontact({{id}});">{{email}}</a></td><td><a href="javascript:getformeditemail({{id}});" title=""><i class="fa fa-edit"></i></a></td></tr>';
            for( var i in data ){      
                $('#table-result-email').append( Mustache.render(tpl, data[i]) );
            }
        },'json'
    );
}

/**
 * Affiche un savoir inutile le temps de faire patient l utilisateur
 * @param  {[type]} elem [description]
 * @return {[type]}      [description]
 */
function getsavoirinutile(elem){
    $.get(
        base_url + 'index.php/ajax/getSavoirInutile',
        {},
        function(data){
            $('#'+elem).html(data);
        }
    );
}