
jQuery(document).ready(function(){	
	// Mise en forme des formulaires
	$(".chzn-select").chosen();
	$(".chozen").chosen();
	// Infobulle
	$('.help-text').tooltip();
	// Scrollup
	$.scrollUp({
		animation: 'fade',
		/*activeOverlay: '#00FFFF',*/
		scrollImg: { active: true, type: 'background', src: 'img/top.png' }
	});
	$('#scrollUpTheme').attr('href', 'css/themes/image.css?1.1');
})

// Affichage des message flash
if (typeof flash_message != 'undefined'){
	$(".breadcrumb").after('<div class="alert alert-info"><button type="button" class="close" data-dismiss="alert">&times;</button>'+flash_message+'</div>');
}

// Affichage les notification
if (typeof notify != 'undefined'){
	$(function(){
		$.pnotify({
			title: notify.title,
			text: notify.message,
			type: notify.type,
			opacity: .70
		});
	});
}

// Recupere les parametres URLS
function getParameters() {
  var searchString = window.location.search.substring(1),
      params = searchString.split("&"),
      hash = {};

  if (searchString == "") return {};
  for (var i = 0; i < params.length; i++) {
    var val = params[i].split("=");
    hash[unescape(val[0])] = unescape(val[1]);
  }
  return hash;
}

$(document).ready(function(){	
	$('.autocomplete-user').autocomplete({
		source : base_url + 'index.php/ajax/search_user?nohtml=nohtml',
		minLength: 2,
		dataType: "json",
		selectFirst: true,
		delay: 0,
		select: function(e, ui){
			//window.location.href = base_url + 'index.php/contacts/detail/'+ui.item.value;
			return false;
		}
	});	
})

jQuery(document).ready(function(){	
	$('#search-top-layout').autocomplete({
		source : base_url + 'index.php/contacts/ajax_search_global?nohtml=nohtml',
		minLength: 2,
		dataType: "json",
		selectFirst: true,
		delay: 0,
		select: function(e, ui){
			window.location.href = base_url + 'index.php/contacts/detail/'+ui.item.value;
			return false;
		}
	});	
})

// Tache et notification
if(suser.id != 'Visiteur'){
	var interval = 10000;
	var pagetitle = "";
	var userid = suser.id;
	var count_url = base_url + "index.php/notifications/getcount";
	var list_url = base_url + "index.php/notifications/getlist";
	var tasks_count_url = base_url + 'index.php/ajax/nb_tasks';
	var tasks_list_url = base_url + 'index.php/ajax/tasks_list_navbar';
	$(document).ready(function(){
	    startNotifications();
	    callCron();
	});

	$(document).ready(function() {
		$.get(
	        base_url + 'index.php/ajax/my_tasks',{},
	        function(data){
	            $('#nav-menu-my-task').html(data);
	        }
	    );
	});
}

//-- START Formulaire menu navigation --//
$(document).on('click', '.sh-search-form',function(){
	if($('#global-search-form').css('display') == 'none'){
		$('#global-search-form').css('display','block');
		$('#select-view-form').html('<i class="glyphicon glyphicon-chevron-up"></i>');
	}else{
		$('#global-search-form').css('display','none');
		$('#select-view-form').html('<i class="glyphicon glyphicon-chevron-down"></i>');
	}
});

if (typeof ape_multi_choice != 'undefined'){
	jQuery(document).ready(function(){
		$('#form-input-search-ape').autocomplete({
			source : base_url + 'index.php/ajax/search_ape?nohtml=nohtml',
			minLength: 2,
			dataType: "json",
			selectFirst: true,
			delay: 0,
			select: function(e, ui){
				var selectObj = ui.item;
				$(this).val(selectObj.label)
				$("#form-search-ape").val( ui.item.value );
				return false;
			}
		});	
	});
}

//-- END Formulaire menu navigation --//

//-- START RDV --//
$(document).on('click', '.call-form-rdv',function(){
	$.get(
        base_url + 'index.php/rdv/get_form/',
        {nohtml:'nohtml'},
        function(data){
            $("#modal-global-body").html('<div class="well">'+data+'</div>');
        }        
    );

    $('#modal-global-label').html('Nouveau rendez vous');
    $('#modal-global').modal('show');
});

$(document).on('click', '.save-rdv-rapport',function(){
	$.get(
	    base_url + 'index.php/rdv/ajax_save_rapport/'+rid, {rapport:$('#rdv-rapport').val()},        
	    function(data){
			console.log(data);
			if(data == 'ok'){
				alert('Rapport enregistré');
			}    	
		}
	);
});

function get_rdv_campaign(tid, sid){
	$.get(
        base_url + 'index.php/campaign/take_rdv',
        {nohtml:'nohtml', tier_type:'contacts', tier_id:tid, source_type:'campaign', source_id:sid},
        function(data){
            $("#modal-global-body").html('<div class="well">'+data+'</div>');
        }        
    );

    $('#modal-global-label').html('Nouveau rendez vous');
    $('#modal-global').modal('show');
}

/**
 * Affiche le formulaire de contact depuis le controller contacts
 * @param  {[type]} cid [description]
 * @return {[type]}     [description]
 */
function get_rdv_contacts(cid){
	$.get(
        base_url + 'index.php/contacts/take_rdv/'+cid,
        {tier_type:'contacts', tier_id:cid, source_type:'fiche'},
        function(data){
            $("#modal-global-body").html('<div class="well">'+data+'</div>');
        }        
    );

    $('#modal-global-label').html('Nouveau rendez vous');
    $('#modal-global').modal('show');
}

function go_to_contacts_view(cid){
	window.location.href=base_url + 'index.php/contacts/detail/'+ cid;
}

function go_to_contacts_new_page(cid){
	window.open(base_url + 'index.php/contacts/detail/'+ cid);
}

/**
 * Enregistrement le changement de statut
 * @param  INT rid ID du rdv dans la base
 * @return {[type]}     [description]
 */
function rdv_save_statut(){
	var rdv_statut = $('#statut-rdv').val();
	$.get(
	    base_url + 'index.php/rdv/ajax_save_statut/'+rid, {rdv_statut:rdv_statut},        
	    function(data){
			console.log(data);
			if(data == 'ok'){
				alert('Statut modifie');
			}    	
		}
	);
}

function get_rdv_detail(rid){
	$.get(
	    base_url + 'index.php/rdv/get_detail/'+rid, {nohtml:'nohtml'},        
	    function(data){
			$("#modal-global-body").html(data);    	
		}
	);
	$('#modal-global-label').html('Suivi de cible');
    $('#modal-global').modal('show');
}
//-- END RDV --//

//-- START LOGS --//
function get_logs(module, link_id, user_id){
	var result = null;
	module = module || '';
	link_id = link_id || '';
	user_id = user_id || '';

	// Ajout du tableau HTML dans le modal
	$("#modal-global-body").html('<table class="table table-striped table-condensed" id="table-of-logs"><thead><tr><th>Date</th><th>Log</th><th>Module</th><th>#</th></tr></thead><tbody></tbody></table>');

	$.get(
	    base_url + 'index.php/ajax/get_logs', {module:module, link_id:link_id, user_id:user_id},        
	    function(data){
	    	var tpl = '<tr><td>{{date_log}}</td><td>{{log}}</td><td>{{module}}</td><td>{{link_id}}</td></tr>';
	    	for( var i in data ){      
	        	$('#table-of-logs').append( Mustache.render(tpl, data[i]) );
	    	} 	
		},'json'
	);
	
	$('#modal-global-label').html('Logs');
    $('#modal-global').modal('show');
}

//-- END LOGS --//

//-- START CA --//
/**
 * Affiche le formulaire pour ajouter un CA
 * @param  {[type]} cid [description]
 * @return {[type]}     [description]
 */
function get_form_ca(cid){
	$.get(
	    base_url + 'index.php/ca/get_modal_form/'+cid, {},        
	    function(data){
			$("#modal-global-body").html(data);    	
		}
	);
	$('#modal-global-label').html('Ajout CA');
    $('#modal-global').modal('show')
}

/**
 * Affiche le formulaire permettant l edition du CA
 * @param  {[type]} contact_id [description]
 * @param  {[type]} ca_id      [description]
 * @return {[type]}            [description]
 */
function get_form_edit_ca(contact_id, ca_id){
	$.get(
	    base_url + 'index.php/ca/get_modal_form/'+contact_id, {ca_id:ca_id},        
	    function(data){
			$("#modal-global-body").html(data);    	
		}
	);
	$('#modal-global-label').html('Edition CA');
    $('#modal-global').modal('show')
}

/**
* Affiche la modal permettant l ajout d un telephoneà une fiche
*/
function GetFormAddPhone(cid){
	$.get(
        base_url + 'index.php/contacts/AjaxAddPhone/'+cid,
        {nohtml:'nohtml'},
        function(data){
            $("#modal-global-body").html(data);
        }        
    );
    $('#modal-global-label').html('Nouveau telephone');
    $('#modal-global').modal('show');
}

/**
 * Permet d affiche le resume via une modal
 * @param  {[type]} cid [description]
 * @return {[type]}     [description]
 */
function contacts_preview_modal(cid){
	$.get(
        base_url + 'index.php/contacts/preview_modal/'+cid,{},
        function(data){
            $("#modal-global-body").html(data);
        }        
    );
    $('#modal-global-label').html('');
    $('#modal-global').modal('show');
}

/**
 * Permet de marque une tache comme realise via le checkbox
 * @param  {[type]} tid [description]
 * @return {[type]}     [description]
 */
function task_process(tid){
	$.get(
        base_url + 'index.php/tasks/set_process/'+tid,{},        
        function(data){
        	$('#task-id-'+tid+'').css('text-decoration','line-through');
        	$(function(){
	    	    $.pnotify({
				    title: 'Tache terminée',
				    text: 'Tache marqué comme terminée.',
				    type: 'success',
				    hide: true,
				});
			});
        }
    );
}

function callCron(){
	 // Save the pagetitle
    pagetitle = $(document).attr('title');

    $('#task-icon').bind('click', function() {
        getTaskList();
    });
    
    // Initial poll
    startCron(); 

    // Set poll timer
    setInterval(function(){ 
        startCron(); 
    }, interval);
}

function startCron(){
	countTasks();
}

/**
 * Retourne le nombre de tache a faire
 * @return {[type]} [description]
 */
function countTasks() {
    $.ajax({
        url: tasks_count_url,
        cache: false
    }).done(function(result) {
        setTasksCounter(result);
    });
    return false;
}

/**
 * Affiche le nombre de tache
 * @param {[type]} number [description]
 */
function setTasksCounter(number) {
    var counter = $('#task-counter');
    var icon = $('#task-icon i');
    var title = pagetitle;
    if(parseInt(number) == 0) {
        $(counter).hide();
        $(icon).removeClass('icon-white');
    } else {
        $(counter).show();
        $(icon).addClass('icon-white');
        title = '(' + number + ') ' + pagetitle;
        $('.task-counter').html(number);
    }
    $(counter).html(number);
    //$(document).attr('title', title);
    return false;
}

function setTaskItems(content) {
   // $('.notification-icon .notification-item').remove();
    $('.notification-item').remove();
   // $('.notification-icon .notification-empty').remove();
    $('#nav-tasks-list').remove();
    var spinner = $('#task-spinner');
    $(spinner).before(content);
    $(spinner).hide();

    return false;
}

function getTaskList() {
    $('.notification-icon .notification-item').hide();
    $('.notification-icon .notification-empty').hide();
    var spinner = $('#task-spinner');
    $(spinner).show();
            
    $.ajax({
        url: tasks_list_url,
        cache: false
    }).done(function(result) {
        setTaskItems(result);
    });
    return false;
}


function task_get_form(controller,cid){
	$.get(
        base_url + 'index.php/tasks/get_form',{controller:controller, controller_id:cid},
        function(data){
            $("#modal-global-body").html(data);
        }        
    );
    $('#modal-global-label').html('Nouvelle tâche');
    $('#modal-global').modal('show');
}

/**
 * Demande la confirmation pour supprimer une tache
 * @param  {[type]} tid [description]
 * @return {[type]}     [description]
 */
function task_delete(tid){
	if(confirm('Etes vous sur de vouloir supprimer cette tâche ?')){
		window.location.href = base_url+'index.php/tasks/set_delete/'+tid;
	}
}