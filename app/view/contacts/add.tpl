<!-- app/view/contact/add.tpl -->
{strip}
<ol class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="Accueil">Accueil</a></li>
	<li><a href="{$Helper->getLink("contacts")}" title="Contacts">Contacts</a></li>
	<li class="active">Nouveau</li>
</ol>

<div class="well">
	<form method="post" action="#" id="form-add-contacts" class="form-horizontal" role="form">
		<fieldset>
			<legend>Nouveau contact</legend>
				
				<div class="form-group">
					<label class="control-label col-sm-2 ">Type :</label>
					<div class="col-xs-5">
						<select name="contact[type]" id="contact-type" required onchange="loadgoodtype()" class="form-control">
							<option value=""></option>
							<option value="1" {if isset($smarty.get.societe)}selected="selected"{/if} >Professionel</option>
							<option value="2">Particulier</option>
						</select>
					</div>
				</div>
				
				<div class="form-group">
					<label class="control-label col-sm-2">Type de contact :</label>
					<div class="col-xs-5">
						<select id="contacts-type-contact" name="structure" onchange="loadcomplementform();" required class="form-control">
							<option value=""></option>
							<option value="entreprise">Entreprise</option>
							<option value="personne" {if isset($smarty.get.societe)}selected="selected"{/if}>Personne</option>
						</select>
					</div>
				</div>
				
				<div id="complement-form-add-contacts"></div>
				
				<div class="form-group">
					<label class="control-label col-sm-2">Adresse :</label>
					<div class="col-xs-5">
						<textarea name="contact[adresse1]" id="contact-adresse1" class="form-control">{if isset($ets)}{$ets.adresse1}{/if}</textarea>
					</div>
				</div>
				
				<div class="form-group">
					<label class="control-label col-sm-2">Adresse (suite):</label>
					<div class="col-xs-5">
						<textarea name="contact[adresse2]" id="contact-adresse2" class="form-control">{if isset($ets)}{$ets.adresse2}{/if}</textarea>
					</div>
				</div>
				
				
				<div class="form-group">
					<label class="control-label col-sm-2">Code postal :</label>
					<div class="col-xs-1">
						<input type="text" name="contact[code_postal]" id="contact-codepostal" {if isset($ets)}value="{$ets.code_postal}"{/if} class="form-control"/>
					</div>
				</div>
				
				<div class="form-group">
					<label class="control-label col-sm-2">Ville :</label>
					<div class="col-xs-5">
						<input type="text" name="contact[ville]" id="contact-ville" {if isset($ets)}value="{$ets.ville}"{/if} class="form-control"/>
					</div>
				</div>				
				
				<div class="form-group">
					<label class="control-label col-sm-2">Pays :</label>
					<div class="col-xs-5">
						<input type="text" name="contact[pays]" id="contact-pays" {if isset($ets)}value="{$ets.pays}"{else}value="{$config.default_country}"{/if} class="form-control"/>
					</div>
				</div>
				
				<div class="form-group">
					<label class="control-label col-sm-2">Email :</label>
					<div class="col-xs-5">
						<input type="email" name="contact[email]" id="email" class="form-control"/>
					</div>
				</div>
				
				<div class="form-group">
					<label class="control-label col-sm-2">Telephone : </label>
					<div class="row">
						<div class="col-xs-2">
							<input type="text" name="contact[telephones][1][telephone]" class="form-control"/>
						</div>
						<div class="col-xs-2">
							<select name="contact[telephones][1][type]" class="form-control">
								<option value=""></option>
								<option value="1">Ligne directe</option>
								<option value="2">Standard</option>
								<option value="3">Domicile</option>
								<option value="4">Mobile</option>
								<option value="5">Fax</option>
							</select>
						</div>
					</div>
				</div>
				
				<div class="form-group">
					<label class="control-label col-sm-2">Telephone : </label>
					<div class="row">
						<div class="col-xs-2">
							<input type="text" name="contact[telephones][2][telephone]" class="form-control"/>
						</div>
						<div class="col-xs-2">
							<select name="contact[telephones][2][type]" class="form-control">
								<option value=""></option>
								<option value="1">Ligne directe</option>
								<option value="2">Standard</option>
								<option value="3">Domicile</option>
								<option value="4">Mobile</option>
								<option value="5">Fax</option>
							</select>
						</div>
					</div>
				</div>
				
				<div class="form-group">
					<label class="control-label col-sm-2">Categorie :</label>
					<div class="col-sm-5">
						<select name="categorie[]" class="chozen form-control" multiple>
							<option value=""></option>
							{foreach $global_categories as $row}
							<option value="{$row.id}">{$row.libelle}</option>
						{/foreach}
						</select>
					</div>
				</div>
				
				<div class="form-group">
					<label class="control-label col-sm-2">Client :</label>
					<div class="col-xs-1">
						<select name="contact[client]" id="contact-client" class="form-control">
							<option value="0">Non</option>
							<option value="1">Oui</option>
						</select>
					</div>
				</div>
				
				<div class="form-group">
					<label class="control-label col-sm-2">Code interne :</label>
					<div class="col-xs-3">
						<input type="text" name="contact[code_interne]" id="contact-codeinterne" class="form-control" />
					</div>
				</div>
				
		</fieldset>
		<div class="form-actions text-center">
			<input type="hidden" name="contact[actif]" value="1" />
			<button type="submit" class="btn btn-primary">Enregistrer</button>
			&nbsp;&nbsp;&nbsp;
			<a href="{$Helper->getLink("contacts")}" title="" class="btn btn-danger">Annuler</a>
		</div>
	</form>
	
</div>{* /well *}
{/strip}
<script type="text/javascript">
<!--
function loadcomplementform(societe){
	if( typeof(societe) == 'undefined' ){
        societe = null;
    }
	$("#complement-form-add-contacts").html('<div class="text-center"><i class="glyphicon glyphicon-refresh"></i> Chargement ...</div>');
	$.get(
        '{$Helper->getLink("contacts/ajaxloadaddtypeform")}',{literal}
        {nohtml:'nohtml',type:$('#contacts-type-contact').val(),typesocio:$('#contact-type').val(),societe:societe},{/literal}
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

jQuery(document).ready(function(){
	$('#contact-codepostal').autocomplete({
		source:'{$Helper->getLink("ajax/getVilleByCp?nohtml=nohtml")}',
		minLength:3,
		dataType:"json",
		delay:0,
		select: function(e,ui){			
			$("#contact-ville").val(ui.item.label);	
			$("#contact-codepostal").val(ui.item.value);		
			return false;
		}		
	});
	
	$("#form-add-contacts").validate({
		rules:{
			"contact[code_postal]":{
				required:true,
			},
			"contact[ville]":{
				required:true,
			},
			"contact[email]":{
				email:true,
				remote: '{$Helper->getLink("contacts/checkemail/'+$('#email').val()+'?nohtml")}',
			},
			"contact[pays]":{
				required:true,
			}
		},
		messages:{
			"contact[code_postal]":{
				required:"Veuillez indiquer le code postal",
			},
			"contact[ville]":{
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
            $(element).parents('.form-group').removeClass('success');
            $(element).parents('.form-group').addClass('error');
        },
        unhighlight: function(element)
        {
            $(element).parents('.form-group').removeClass('error');
            $(element).parents('.form-group').addClass('success');
        }
	});
	
});
{if isset($smarty.get.societe)}
loadcomplementform({$smarty.get.societe});
{/if}
//-->
</script>