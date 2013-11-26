<!-- START view/entreprise/geoloc.tpl -->
{strip}
<ul class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="">Accueil</a><span class="divider">&gt;&gt;</span></li>
	<li>Géolocalisation</li>
</ul>

<div class="well">	
	<h3>Géolocalisation</h3>
	<div id="result-geoloc">
		<div class="alert alert-warning">
		Cette operation peut prendre plusieurs minutes en fonction du nombre d'entreprise dans la base
		</div>

		<div class="text-center">
			<button class="btn btn-primary" onclick="startgeoloc();">Lancer geolocalisation</button>
			&nbsp;&nbsp;
			<button class="btn btn-primary" onclick="startgeolocemptycoord();">Lancer geolocalisation des entreprises sans coordonnees</button>
		</div>
</div>{* /well *}
{/strip}
<script type="text/javascript">
<!--
function startgeoloc(){
	$('#result-geoloc').html('<i class="icon-spinner icon-spin icon-large"></i>Traitement ...');

	// Requete ajax
	$.get(
        '{$Helper->getLink("entreprise/geolocajax")}',{literal}
        {nohtml:'nohtml'},
        function(data){
            $('#result-geoloc').html(data);
        }
    );
    {/literal}
}

function startgeolocemptycoord(){
	$('#result-geoloc').html('<i class="icon-spinner icon-spin icon-large"></i>Traitement ...');

	// Requete ajax
	$.get(
        '{$Helper->getLink("entreprise/geolocemptycoordajax")}',{literal}
        {nohtml:'nohtml'},
        function(data){
            $('#result-geoloc').html(data);
        }
    );
    {/literal}
}
//-->
</script>
<!-- END view/entreprise/geoloc.tpl -->