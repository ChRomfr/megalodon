<!-- START view/import/step3.tpl -->
{strip}
<ul class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="">Accueil</a><span class="divider">&gt;&gt;</span></li>
	<li><a href="{$Helper->getLink("import")}" title="">Import </a><span class="divider">&gt;&gt;</span></li>
	<li>Etape 3 -Import termine</li>
</ul>

<div class="well">
	<h3>Resultat import</h3>
	<table class="table table-condensed">
		{foreach $logs as $k => $v}
		<tr>
			<td>{$v|nl2br}</td>
		</tr>
		{/foreach}
	</table>
</div>
{/strip}
<!-- END view/import/step3.tpl -->