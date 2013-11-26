<!-- view/contact/add.tpl -->
<ul class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="Accueil">Accueil</a><span class="divider">>></span><li>
	<li><a href="{$smarty.server.HTTP_REFERER}" title="Retour a la liste">Liste</a><span class="divider">>></span></li>
	<li><a href="{$Helper->getLink("entreprise/fiche/{$entreprise.id}")}" title="">{$entreprise.raison_social}</a><span class="divider">>></span></li>
	<li>Nouveau contact</li>
</ul>

<div class="well">
	<form method="post" action="" class="form-horizontal" id="formAddContact">
		<fieldset>
			<legend>Nouveau contact pour <a href="{$Helper->getLink("entreprise/fiche/{$entreprise.id}")}" title="">{$entreprise.raison_social}</a></legend>
			<div class="control-group">
				<label for="nom" class="control-label">Nom :</label>
				<div class="controls">
					<input type="text" name="contact[nom]" id="nom" required />
				</div>
			</div>
			<div class="control-group">
				<label for="prenom" class="control-label">Prenom :</label>
				<div class="controls">
					<input type="text" name="contact[prenom]" id="prenom" required />
				</div>
			</div>
			<div class="control-group">
				<label for="" class="control-label">Email</label>
				<div class="controls">
					<input type="email" name="contact[email]" id="email" />
				</div>
			</div>
			<div class="control-group">
				<label for="telephone" class="control-label">Telephone :</label>
				<div class="controls">
					<input type="text" name="contact[telephone]" id="telephone" />
				</div>
			</div>

			<!-- mobile -->
			<div class="control-group">
				<label for="mobile" class="control-label">Portable :</label>
				<div class="controls">
					<input type="text" name="contact[mobile]" id="mobile" />
				</div>
			</div>

			<!-- fax -->
			<div class="control-group">
				<label for="fax" class="control-label">Fax :</label>
				<div class="controls">
					<input type="text" name="contact[fax]" id="fax" />
				</div>
			</div>
			<!-- Service -->
			<div class="control-group">
				<label class="control-label">Service :</label>
				<div class="controls">
					<select name="contact[service_id]" id="service_id">
						<option value="0"></option>
						{foreach $services as $row}
						<option value="{$row.id}">{$row.libelle}</option>
						{/foreach}
					</select>
				</div>
			</div>

			<!-- Poste -->
			<div class="control-group">
				<label class="control-label">Poste :</label>
				<div class="controls">
					<select name="contact[poste_id]" id="poste_id">
						<option value="0"></option>
						{foreach $postes as $row}
						<option value="{$row.id}">{$row.libelle}</option>
						{/foreach}
					</select>
				</div>
			</div>

			<div class="form-actions">
				<input type="hidden" name="contact[entreprise_id]" value="{$entreprise.id}" />
				<button type="submit" class="btn btn-primary">Enregistrer</button>
			</div>
		</fieldset>
	</form>
</div>

<script type="text/javascript">
<!--
jQuery(document).ready(function(){
	// binds form submission and fields to the validation engine
	$("#formAddContact").validate({
		rules:{
			"contact[nom]":{
				required:true,
			},
			"contact[email]":{
				email:true,
				remote: '{$Helper->getLink("contact/checkemail/'+$('#email').val()+'?nohtml")}',
			}
		},
		messages:{
			"contact[nom]":{
				required:"Veuillez indiquer le nom du contact",
			},
			"contact[email]":{
				email:"Veuillez entrer une adresse email valide",
				remote:"Un contact a deja cette adresse email d utilisee",
			}
		},
		highlight:function(element)
        {
            $(element).parents('.control-group').removeClass('success');
            $(element).parents('.control-group').addClass('error');
        },
        unhighlight: function(element)
        {
            $(element).parents('.control-group').removeClass('error');
            $(element).parents('.control-group').addClass('success');
        }
	});

	$('#telephone').mask("99.99.99.99.99");
	$('#mobile').mask("99.99.99.99.99");
	$('#fax').mask("99.99.99.99.99");
});
//-->
</script>