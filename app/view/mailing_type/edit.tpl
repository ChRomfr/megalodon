<!-- view/categorie/edit.tpl -->
<ul class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="Accueil">Accueil</a><span class="divider">>></span></li>
	<li><a href="{$Helper->getLink("categorie")}" title="Categorie">Catégorie</a><span class="divider">>></span></li>
	<li>Edition</li>
</ul>
<div class="well">
	<form method="post" id="form-categorie-edit" class="form-horizontal">
		<fieldset>
			<legend>Edition</legend>
			<div class="control-group">
				<label class="control-label" for="libelle">Catégorie :</label>
				<div class="controls">
					<input type="text" name="categorie[libelle]" id="libelle" required value="{$categorie->libelle}"/>
				</div>
			</div>
		</fieldset>
		<div class="form-actions">
			<input type="hidden" name="categorie[id]" value="{$categorie->id}" />
			<button type="submit" class="btn btn-primary">Enregistrer</button>
		</div>
	</form>
</div><!-- /well -->

<script type="text/javascript">
<!--
jQuery(document).ready(function(){
	// binds form submission and fields to the validation engine
	$("#form-categorie-edit").validate({
		rules:{
			"categorie[libelle]":{
				required:true,
			},
		},
		messages:{
			"categorie[libelle]":{
				required:"Veuillez indiquer le nom de la categorie",
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