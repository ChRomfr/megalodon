/**
 * Call controller contacts
 *		action detail
 */

function deleteContact(id){
	if(confirm('Etes vous sur de voiloir supprimer ?')){
		window.location.href = base_url + 'index.php/contacts/delete/'+id;
	}
}

function get_form_add_file(cid){
	$.get(
        base_url + 'index.php/contacts/ajax_form_add_file/'+cid,
        {nohtml:'nohtml'},
        function(data){
            $("#modal-global-body").html('<div class="well">'+data+'</div>');
        }        
    );

    $('#modal-global-label').html('<i class="fa fa-cloud-upload"></i>&nbsp;Ajouter un fichier');
    $('#modal-global').modal('show');
}

function get_form_add_agence(mother_id, contact_id){
	$.get(
        base_url + 'index.php/contacts/ajax_form_add_agence/'+mother_id,
        {nohtml:'nohtml', contact:contact_id},
        function(data){
            $("#modal-global-body").html('<div class="well">'+data+'</div>');
        }        
    );

    $('#modal-global-label').html('Nouvelle agence');
    $('#modal-global').modal('show');
}

function get_form_suivi(cid){
	$.get(
        base_url + 'index.php/contacts/ajax_form_suivi/'+cid,
        {nohtml:'nohtml'},
        function(data){
            $("#modal-fiche-contacts-body").html(data);
        }        
    );

    $('#modal-fiche-contact-label').html('Nouveau suivi');
    $('#modal-fiche-contacts').modal('show');
}

function get_form_send_email(cid){
	$.get(
        base_url + 'index.php/contacts/ajax_form_sendemail/'+cid,
        {nohtml:'nohtml'},
        function(data){
            $("#modal-fiche-contacts-body").html(data);
        }        
    );

    $('#modal-fiche-contact-label').html('Envoie email');
    $('#modal-fiche-contacts').modal('show');
}

function get_form_send_email(eid){
	$.get(
        base_url + 'index.php/contacts/ajax_get_email_detail/'+eid,
        {nohtml:'nohtml'},
        function(data){
            $("#modal-fiche-contacts-body").html('<div class="well">'+data+'</div>');
        }        
    );

    $('#modal-fiche-contact-label').html('Envoie email');
    $('#modal-fiche-contacts').modal('show');
}

function sendUpload(){    
	$("#uploadStatus").html('<div class="alert alert-info"><i class="fa fa-spinner fa-spin fa-lg"></i><br/>Enregistrement en cour ...</div></div>');
	return true;
}

function endUpload(result){
	if( result == 'echec'){
		$("#uploadStatus").html('<div class="alert alert-error">Une erreur est survenu pendant le traitement</div>');
		$("#FormBienEdit").css('display','block');
	}else{
		$(function(){
			$.pnotify({
				title: 'Fichier',
				text: 'Fichier ajouté au contact',
				type: 'success',
				opacity: .8
			});
		});
		ReloadFiles(contact.contact_id);
	    $('#uploadStatus').html('');
	}
}

function GetFormAddPhone(cid){
	$.get(
        base_url + 'index.php/contacts/AjaxAddPhone/'+cid,
        {nohtml:'nohtml'},
        function(data){
            $("#modal-fiche-contacts-body").html(data);
        }        
    );
    $('#modal-fiche-contact-label').html('Nouveau telephone');
    $('#modal-fiche-contacts').modal('show');
}

function ReloadFiles(cid){
	$('#table-contacts-files').find("tr:gt(0)").remove();
	$.get(
        base_url + 'index.php/contacts/get_files/'+cid,
        {nohtml:'nohtml',json: 'json'},
        function(data){
            var tpl = '<tr><td><a href="{{url_download}}" title="" target="_blank">{{name}}</td><td>{{identifiant}}</td><td>{{date_add}}</td></tr>';
        	for( var i in data ){      
            	$('#table-contacts-files').append( Mustache.render(tpl, data[i]) );
        	}
        },'json'
    );
}

// Gmap3
jQuery(document).ready(function(){
	$("#map-contacts").gmap3({
    	marker:{
      	latLng:[contact.lat, contact.lng]
    	},
    	map:{
      		options:{
        		zoom: 14
  			}
		}
  	});

	if(contact.lat == '' || contact.lng == '' || contact.lat == null){
		$.get(
	        base_url + 'index.php/contacts/ajax_geoloc_contact/'+contact.contact_id,
	        {nohtml:'nohtml'},
	        function(data){
	        }        
	    );
    }
});

$(document).ready(function(){
    $('a').tooltip();
});

function deleteSuivi(sid){
	if(confirm('Etes vous sur de vouloir supprimer ce suivi ?')){
		window.location.href = base_url + 'index.php/contacts/suividelete/'+sid;
	}
}

function deleteFile(fid){
	if(confirm('Etes vous sur de vouloir supprimer ce fichier ?')){
		window.location.href = base_url + 'index.php/contacts/file_delete/'+fid;
	}
}

function DeletePhone(pid){
	if(confirm('Etes vous sur de vouloir supprimer ce téléphone ?')){
		window.location.href = base_url + 'index.php/contacts/phone_delete/'+pid;
	}
}

function agenceRemove(aid, mother){
	if(confirm('Etes vous sur de vouloir retirer cette agence ?')){
		window.location.href = base_url + 'index.php/contacts/agence_remove?agence_id='+aid+'&mother='+mother;
	}
}

function gotofiche(cid){
	window.location.href = base_url + 'index.php/contacts/detail/'+cid;
}

// Recuperation mailing du contacts
(function($){
    $.get(
        base_url + 'index.php/contacts/get_mailings_of_contact/'+contact.contact_id,
        {nohtml:'nohtml'},
        function(data){ 
            var tpl = '<tr><td>{{id}}</td><td>{{libelle}}</td><td>{{email}}</td><td>{{& open}}</tr>';
            for( var i in data ){
                $('#table-contacts-mailings').append( Mustache.render(tpl, data[i]) );
            };
        },'json');
})(jQuery);

// Historique du contacts
if(suser.historique_contact == 1){
	(function($){
	    $.get(
	        base_url + 'index.php/contacts/get_logs_of_contact/'+ contact.contact_id,
	        {nohtml:'nohtml'},
	        function(data){ 
	            var tpl = '<tr><td>{{date_log}}</td><td>{{log_user}}</td><td>{{& log}}</td></tr>';
	            for( var i in data ){
	                $('#table-contacts-logs').append( Mustache.render(tpl, data[i]) );
	            };
	        },'json');
	})(jQuery);
}


if(contact.raison_social != ''){
(function($){
    $.get(
        base_url + 'index.php/contacts/get_contacts_societe/'+contact.contact_id,
        {nohtml:'nohtml'},
        function(data){ 
            var tpl = '<tr><td><a href="javascript:gotofiche({{contact_id}})" title="">{{prenom}} {{nom}}</a></td><td>{{email}}</td><td>{{service}}</td><td>{{poste}}</td></tr>';
            for( var i in data ){
                $('#table-contacts-societe').append( Mustache.render(tpl, data[i]) );
            };
        },'json');
	})(jQuery);
}