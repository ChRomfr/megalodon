// Mise en forme formulaire
jQuery(document).ready(function(){	
	$(".chzn-select").chosen();
	$(".chozen").chosen();
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
	$(document).ready(function(){
	    startNotifications();
	});

	$(document).ready(function() {
		$.get(
	        base_url + 'index.php/ajax/my_tasks',
	        {nohtml:'nohtml',},
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

function go_to_contacts_view(cid){
	window.location.href=base_url + 'index.php/contacts/detail/'+ cid;
}
//-- RDV --//
