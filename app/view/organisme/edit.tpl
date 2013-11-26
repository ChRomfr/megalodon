<ul class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="">Accueil</a><span class="divider">>></span></li>
	<li><a href="{$Helper->getLink("organisme")}" title="">Organisme</a><span class="divider">>></span></li>
	<li>Edition</li>
</ul>

<div class="well">
	<form method="post" action="#" id="formEditOrganisme" class="form-horizontal">
		<fieldset>

			<legend>Edition : {$organisme->libelle}</legend>
			<div class="control-group">
				<label class="control-label">Organisme :</label>
				<div class="controls">
					<input type="text" name="organisme[libelle]" id="libelle" value="{$organisme->libelle}"/>
				</div>
			</div>

		</fieldset>

		<div class="form-actions">
			<input type="hidden" name="organisme[id]" value="{$organisme->id}" />
			<button class="btn btn-primary" type="submit">Enregistrer</button>
		</div>

	</form>

</div>

<script type="text/javascript">
<!--
jQuery(document).ready(function(){
	// binds form submission and fields to the validation engine
	$("#formEditOrganisme").validate({
		rules:{
			"organisme[libelle]":{
				required:true,
			},
		},
		messages:{
			"contact[name]":{
				required:"Veuillez indiquer le nom de l'organisme",
			},
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
});
//-->
</script>