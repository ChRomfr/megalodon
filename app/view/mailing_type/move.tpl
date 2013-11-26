<!-- view/categorie/move.tpl -->
<ul class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="Accueil">Accueil</a><span class="divider">>></span></li>
	<li><a href="{$Helper->getLink("categorie")}" title="Categorie">Catégorie</a><span class="divider">>></span></li>
	<li>Deplacement</li>
</ul>

<div class="well">
	<p>Déplace les éléments d'une catégorie vers une autre</p>
	<br/>
	<form method="post" action="" class="form-horizontal" id="form-categorie-move">
		<div class="control-group">
			<label class="control-label">Nouvelle categorie :</label>
			<div class="controls">
				<select name="new_categorie" required>
					<option value=""></option>
					{foreach $global_categories as $row}
						{if $row.id !== $cid}
						<option value="{$row.id}">{$row.libelle}</option>
						{/if}
					{/foreach}
				</select>
			</div>
		</div>
		<div class="form-actions">
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