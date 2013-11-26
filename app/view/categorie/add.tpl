<!-- view/categorie/add.tpl -->
{strip}
<ol class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="Accueil">Accueil</a></li>
	<li><a href="{$Helper->getLink("adm")}" title="Administration">Administration</a></li>
	<li><a href="{$Helper->getLink("categorie")}" title="Categorie">Catégorie</a></li>	
	<li>Nouvelle</li>
</ol>
<div class="well">
	<form method="post" id="form-categorie-add" class="form-horizontal">
		<fieldset>
			<legend>Nouvelle</legend>
			<div class="form-group">
				<label class="control-label col-sm-2" for="libelle">Catégorie :</label>
				<div class="col-sm-5">
					<input type="text" name="categorie[libelle]" id="libelle" required class="form-control"/>
				</div>
			</div>
		</fieldset>
		<div class="text-center">
			<hr/>
			<button type="submit" class="btn btn-primary">Enregistrer</button>
		</div>
	</form>
</div><!-- /well -->
{/strip}
<script type="text/javascript">
<!--
jQuery(document).ready(function(){
	// binds form submission and fields to the validation engine
	$("#form-categorie-add").validate({
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