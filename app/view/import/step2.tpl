<!-- START view/import/step2/.tpl -->
{strip}
<ul class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="">Accueil</a><span class="divider">&gt;&gt;</span></li>
	<li><a href="{$Helper->getLink("import")}" title="">Import </a><span class="divider">&gt;&gt;</span></li>
	<li>Etape 2 - Correspondance des champs</li>
</ul>

<div class="well">
	<div id="container-form-correspondance">
		<form method="post" action="{$Helper->getLink("import/step3/{$file}")}" onsubmit="sendformcorrespondance();">
		<div class="row-fluid">
			<div class="span5">
				{* Tableau correspondance entreprise *}
				<table>
					<tbody>
						{foreach $entreprise as $k => $v}
						<tr>
							<td>{$v}</td>
							<td>
								<select name="liaison_entreprise[{$k}]">
									<option></option>
									{foreach $entetes as $k2 => $v2}
									<option value="{$k2}" {if stripos($v,$v2) !== false}selected="selected"{/if}>{$v2}</option>
									{/foreach}
								</select>
						{/foreach}
					</tbody>
				</table>
			</div>{* /span5 *}

			<div class="span5">
				{* Tableau correspondance contact *}
				<table>
					<tbody>
						{foreach $contact as $k => $v}
						<tr>
							<td>{$v}</td>
							<td>
								<select name="liaison_contact[{$k}]">
									<option></option>
									{foreach $entetes as $k2 => $v2}
									<option value="{$k2}" {if stripos($v,$v2) !== false}selected="selected"{/if}>{$v2}</option>
									{/foreach}
								</select>
						{/foreach}
					</tbody>
				</table>
			</div>{* /span5 *}

		</div>{* /row-fluid *}

		<div class="form-actions">
			<button type="submit" class="btn btn-primary">Importer</button>
			&nbsp;&nbsp;
			<a href="{$Helper->getLink("index")}" title="" class="btn btn-danger">Annuler</a>
		</div>
		</form>
	</div>{* /container-form-correspondance *}
	<div id="container-form-submit" style="display:none;">
		<i class="icon-spinner icon-spin icon-large"></i>Traitement ...
	</div>
</div>{* /well *}
{/strip}
<script type="text/javascript">
<!--
function sendformcorrespondance(){
	$('#container-form-correspondance').css('display','none');
	$('#container-form-submit').css('display','block');
}
//-->
</script>
<!-- END view/import/step2.tpl -->