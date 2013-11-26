<!-- START view/import/index.tpl -->
{strip}
<ul class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="Accueil">Accueil</a><span class="divider">&gt;&gt;</span></li>
	<li>Import</li>
</ul>

<div class="well">

	<form method="post" action="#" enctype="multipart/form-data">
		<fieldset>
			<legend>Importation</legend>
			<p>Pré requis :</p> 
			<ul>
				<li>Fichier au format CSV</li>
				<li>Sepateur <strong>;</strong></li>
			</ul>
			<div class="control-group">
				<label class="control-label">Fichier :</label>
				<div class="controls">
					<input type="file" name="file_import" id="file_import"/>
				</div>
			</div>			
		</fieldset>
		<div class="form-actions">
			<input type="hidden" name="token" value="{$smarty.session.token}" />
			<button type="submit" class="btn btn-primary">Envoyer</button>
			&nbsp;&nbsp;
			<a href="{$Helper->getLink("index")}" title="" class="btn btn-danger">Annuler</a>
		</div>
	</form>

</div>{*/well*}
{/strip}
<!-- END view/import/index.tpl -->