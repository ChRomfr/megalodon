$(document).ready(function() {	
	$("#form-add-contacts").validate({
		rules:{
			'contact[type]':{
				required:true,
			},
			"contact[zip_code]":{
				required:true,
			},
			"contact[city]":{
				required:true,
			},
			"contact[email]":{
				email:true,
				remote: base_url + 'index.php/contacts/checkemail/'+$('#email').val()+'?nohtml',
			},
			"contact[pays]":{
				required:true,
			}
		},
		messages:{
			"contact[zip_code]":{
				required:"Veuillez indiquer le code postal",
			},
			"contact[city]":{
				required:"Veuillez indiquer la ville",
			},
			"contact[email]":{
				email:"Veuillez saisir une adresse email valide",
				remote:"Cette adresse email est deja utilise",
			},
			"contact[pays]":{
				required:"Veuillez indiquer le pays",
			}
		},
		highlight:function(element)
        {
            $(element).parents('.form-group').removeClass('text-success');
            $(element).parents('.form-group').addClass('text-danger');
        },
        unhighlight: function(element)
        {
            $(element).parents('.form-group').removeClass('text-danger');
            $(element).parents('.form-group').addClass('text-success');
        }
	});
	
});

jQuery(document).ready(function(){
	$('#contact-codepostal').autocomplete({
		source:base_url + 'index.php/ajax/getVilleByCp?nohtml=nohtml',
		minLength:3,
		dataType:"json",
		delay:0,
		select: function(e,ui){			
			$("#contact-city").val(ui.item.label);	
			$("#contact-codepostal").val(ui.item.value);		
			return false;
		}		
	});
});

function loadcomplementform(societe){
	if( typeof(societe) == 'undefined' ){
        societe = null;
    }
	$("#complement-form-add-contacts").html('<div class="text-center"><i class="glyphicon glyphicon-refresh"></i> Chargement ...</div>');
	$.get(
        base_url + 'index.php/contacts/ajaxloadaddtypeform',
        {nohtml:'nohtml',type:$('#contacts-type-contact').val(),typesocio:$('#contact-type').val(),societe:societe},
        function(data){
            $("#complement-form-add-contacts").html(data);
        }
    );
}

function loadgoodtype(){
	if($('#contact-type').val() == '2'){
		$('#contacts-type-contact').val('personne');
		$('#contacts-type-contact').prop('disabled', 'disabled');
		loadcomplementform();
	}else{
		$('#contacts-type-contact').prop('disabled', false);
		$('#contacts-type-contact').val('');
		$("#complement-form-add-contacts").html('');
	}
}