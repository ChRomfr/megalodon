<ul class="breadcrumb"></ul>

<div class="well">
	<form method="post" action="#" id="formAddOrganisme" class="form-horizontal">
		<fieldset>

			<legend>Nouvel organisme</legend>
			<div class="control-group">
				<label class="control-label">Organisme :</label>
				<div class="controls">
					<input type="text" name="organisme[libelle]" id="libelle" />
				</div>
			</div>

		</fieldset>

		<div class="form-actions">
			<button class="btn btn-primary" type="submit">Enregistrer</button>
		</div>

	</form>

</div>

<script type="text/javascript">
<!--
jQuery(document).ready(function(){
	// binds form submission and fields to the validation engine
	$("#formAddOrganisme").validate({
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