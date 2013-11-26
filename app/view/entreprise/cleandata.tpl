<!-- START view/entreprise/cleandata.tpl -->
{strip}
<ul class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="Accueil">Accueil</a><span class="divider">&gt;&gt;</span></li>
	<li>Nettoyage des liens dans la base</li>
</ul>

<div class="well">	
	<p>Verifie et nettoie les liens dans la base de donnees</p>
	<div class="text-center">
		<button class="btn btn-primary" onclick="cleandata();">Nettoyage</button>
		&nbsp;&nbsp;
		<button class="btn btn-primary" onclick="verifieemail();">Verifier les adresses email</button>
	</div>
	<div id="result-cleandata"></div>
</div>{* /well *}
{/strip}
<script type="text/javascript">
<!--
function cleandata(){
	$('#result-cleandata').html('<i class="icon-spinner icon-spin icon-large"></i>&nbsp;&nbsp;Traitement ...');

	// Requete ajax
	$.get(
        '{$Helper->getLink("entreprise/cleandataajax")}',{literal}
        {nohtml:'nohtml'},
        function(data){
            $('#result-cleandata').html(data);
        }
    );
    {/literal}
}
function verifieemail(){
	$('#result-cleandata').html('<i class="icon-spinner icon-spin icon-large"></i>&nbsp;&nbsp;Traitement ...');

	// Requete ajax
	$.get(
        '{$Helper->getLink("entreprise/checkadremailajax")}',{literal}
        {nohtml:'nohtml'},
        function(data){
            $('#result-cleandata').html(data);
        }
    );
    {/literal}
}
//-->
</script>
<!-- END view/entreprise/cleandata.tpl -->