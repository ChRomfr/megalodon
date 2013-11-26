<!-- view/categorie/move.tpl -->
<ol class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="Accueil">Accueil</a></li>
	<li><a href="{$Helper->getLink("adm")}" title="Administration">Administration</a></li>
	<li><a href="{$Helper->getLink("categorie")}" title="Categorie">Catégorie</a></li>	
	<li>Deplacement</li>
</ol>

<div class="well">
	<div class="bs-callout bs-callout-info">
		<p>Déplace les éléments d'une catégorie vers une autre</p>
	</div>
	<br/>
	<form method="post" action="" class="form-horizontal" id="form-categorie-move">

		<div class="form-group">
			<label class="control-label col-sm-2">Catégorie de départ :</label>
			<div class="col-sm-5">
				<input type="text" disabled value="{$categorie->libelle}" class="form-control" />
			</div>
		</div>

		<div class="form-group">
			<label class="control-label col-sm-2">Déplacer vers :</label>
			<div class="col-sm-5">
				<select name="new_categorie" required class="form-control">
					<option value=""></option>
					{foreach $global_categories as $row}
						{if $row.id !== $cid}
						<option value="{$row.id}">{$row.libelle}</option>
						{/if}
					{/foreach}
				</select>
			</div>
		</div>
		<div class="text-center">
			<hr/>
			<button type="submit" class="btn btn-primary">Déplacer</button>
		</div>
	</form>
</div>

<script type="text/javascript">
<!--
jQuery(document).ready(function(){
	// binds form submission and fields to the validation engine
	$("#form-categorie-move").validate({
		rules:{
			"new_categorie":{
				required:true,
			},
		},
		messages:{
			"new_categorie":{
				required:"Veuillez indiquer le nom de la nouvelle categorie",
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