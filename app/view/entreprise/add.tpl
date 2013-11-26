{strip}
<ul class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="">Accueil</a><span class="divider">>></span></li>
	<li><a href="{$Helper->getLink("entreprise")}" title="">Entreprises</a><span class="divider">>></span></li>
	<li>Nouvelle</li>
</ul>
<div class="well">
	<form method="post" action="" id="formAddEntreprise" class="form-horizontal">
		<fieldset>
			<legend>Nouvelle entreprise</legend>
			<!-- raison social -->
			<div class="control-group">
				<label class="control-label">Raison social :</label>
				<div class="controls">
					<input type="text" name="ets[raison_social]" id="raison_social" required />
				</div>	
			</div>
			<!-- siret -->
			<div class="control-group">
				<label class="control-label">Siret :</label>
				<div class="controls">
					<input type="text" name="ets[siret]" id="siret" />
				</div>	
			</div>
			<!-- adresse 1 -->
			<div class="control-group">
				<label class="control-label">Adresse :</label>
				<div class="controls">
					<textarea name="ets[adresse1]" id="adresse1"></textarea>
				</div>
			</div>
			<!-- adresse 2 -->
			<div class="control-group">
				<label class="control-label">Adresse (suite):</label>
				<div class="controls">
					<textarea name="ets[adresse2]" id="adresse2"></textarea>
				</div>
			</div>
			<!-- codepostal -->
			<div class="control-group">
				<label class="control-label">Code postal :</label>
				<div class="controls">
					<input type="text" name="ets[code_postal]" id="code_postal" />
				</div>
			</div>
			<!-- ville -->
			<div class="control-group">
				<label class="control-label">Ville :</label>
				<div class="controls">
					<input type="text" name="ets[ville]" id="ville" />
				</div>
			</div>
			<!-- Telephone -->
			<div class="control-group">
				<label class="control-label">Telephone :</label>
				<div class="controls">
					<input type="text" name="ets[telephone]" id="telephone" />
				</div>
			</div>
			<!-- Fax -->
			<div class="control-group">
				<label class="control-label">Fax :</label>
				<div class="controls">
					<input type="text" name="ets[fax]" id="fax" />
				</div>
			</div>
			<!-- Email -->
			<div class="control-group">
				<label class="control-label">Email :</label>
				<div class="controls">
					<input type="email" name="ets[email]" id="email" />
				</div>
			</div>
			<!-- effectif -->
			<div class="control-group">
				<label class="control-label">Effectif :</label>
				<div class="controls">
					<input type="text" name="ets[effectif]" id="effectif" class="input-mini" />
				</div>
			</div>
			<!-- categorie -->
			<div class="control-group">
				<label class="control-label">Categorie :</label>
				<select name="categorie[]" class="chzn-select" multiple>
					<option value="0"></option>
					{foreach $global_categories as $row}
					<option value="{$row.id}">{$row.libelle}</option>
					{/foreach}
				</select>
			</div>
			<!-- ape -->
			<div class="control-group">
				<label class="control-label">APE :</label>
				<select name="ets[ape_id]" class="chzn-select">
					<option value="0"></option>
					{foreach $apes as $row}
					<option value="{$row.id}">{$row.code}</option>
					{/foreach}
				</select>
			</div>
		</fieldset>
		<div class="form-actions">
			<button class="btn btn-primary" type="submit">Enregistrer</button>
		</div>
	</form>
</div><!--/well -->
{/strip}
<script type="text/javascript">
	$(".chzn-select").chosen();

jQuery(document).ready(function(){
	// binds form submission and fields to the validation engine
	$("#formAddEntreprise").validate({
		rules:{
			"ets[raison_social]":{
				required:true,
			},
			"ets[siret]":{
				required:true,
				remote: '{$Helper->getLink("entreprise/checksiret/'+$('#siret').val()+'?nohtml")}',
			},
			"ets[email]":{
				email:true,
				remote: '{$Helper->getLink("entreprise/checkemail/'+$('#email').val()+'?nohtml")}',
			}
		},
		messages:{
			"ets[raison_social]":{
				required:"Veuillez indiquer la raison social",
			},
			"ets[siret]":{
				remote:"Ce numero de siret est deja enreigstrer",
			},
			"ets[email]":{
				email:"Veuillez entrer une adresse email valide",
				remote:"Une entreprise a deja cette adresse email d utilisee",
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
	$('#fax').mask("99.99.99.99.99");
});
</script>