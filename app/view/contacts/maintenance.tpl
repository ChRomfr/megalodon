{strip}
{* app/view/contacts/maintenance.tpl *}
<ol class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="Accueil"><i class="fa fa-home"></i>&nbsp;&nbsp;Accueil</a></li>
	<li><a href="{$Helper->getLink("adm")}" title="Administration"><i class="fa fa-dashboard"></i>&nbsp;&nbsp;Administration</a></li>
    <li><a href="{$Helper->getLink("adm/contacts")}" title="Contacts"><i class="fa fa-users"></i>&nbsp;&nbsp;Contacts</a></li>   
    <li class="active">Maintenance</li>
</ol>

<div class="well">
	<h4>Contacts à la corbeille</h4>
	<ul>
		<li>Contact à la corbeille : {$contacts_corbeille}</li>
	</ul>
	{if $contacts_corbeille > 0}
	<div class="text-center">
		<button type="button" class="btn btn-primary" onclick="getCorbeille();">Voir la corbeille</button>
		&nbsp;&nbsp;
		<button type="button" class="btn btn-danger" onclick="cleantrash();"><i class="icon-trash"></i>Vider la corbeille</button>
	</div>
	<div id="maintenace-ctcs-corbeille"></div>
	{/if}
</div>

<div class="well">
	<h4>Verification et optimisation</h4>
	<div class="text-center">
		<button class="btn btn-primary" onclick="startOptimisation();" id="btn-opt">Optimiser</button>&nbsp;
		<button class="btn btn-primary" onclick="getemailerror();" id="btn-checkemail">Verifier les emails</button>&nbsp;
		<button class="btn btn-primary" onclick="deletenoname();" id="btn-deletenoname">Supprimer les contacts sans nom</button>&nbsp;
        <button class="btn btn-primary" onclick="get_contacts_no_email();" id="btn-get_contacts_no_email">Supprimer les contacts pro sans email</button>&nbsp;
        <a href="{$Helper->getLink("adm/contacts_delete_by_email_step1")}" title="" class="btn btn-primary">Suppression par email</a>
	</div>
	<div id="result-opt"></div>
</div>

<div class="well">
	<h4>Geolocalisation</h4>

	<div class="alert alert-warning">
		Cette operation peut prendre plusieurs minutes en fonction du nombre d'entreprise dans la base
	</div>

	<div class="text-center">
		<button class="btn btn-primary" onclick="startgeoloc();">Lancer geolocalisation</button>
		&nbsp;&nbsp;
		<button class="btn btn-primary" onclick="startgeolocemptycoord();">Lancer geolocalisation des entreprises sans coordonnees</button>
	</div>
	<div id="result-geoloc"></div>
</div>

{/strip}
<script type="text/javascript">
function startgeoloc(){
	$('#result-geoloc').html('<i class="icon-spinner icon-spin icon-large"></i>Traitement ...');

	// Requete ajax
	$.get(
        '{$Helper->getLink("contacts/geolocajax")}',{literal}
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
        '{$Helper->getLink("contacts/geolocemptycoordajax")}',{literal}
        {nohtml:'nohtml'},
        function(data){
            $('#result-geoloc').html(data);
        }
    );
    {/literal}
}

function startOptimisation(){
	$('#result-opt').html('<i class="icon-spinner icon-spin icon-large"></i>Traitement ...');
	$('#btn-opt').css('display','none');
	// Requete ajax
	$.get(
        '{$Helper->getLink("contacts/ajaxmaintenanceoptdb")}',{literal}
        {nohtml:'nohtml'},
        function(data){
            if(data == 'go'){
            	optcontact();
            }else{
            	$('#result-opt').html('Erreur');	
            }
        }
    );
    {/literal}
}

function optcontact(){
	$('#result-opt').html('<div class="progress progress-striped active"><div class="bar" style="width: 25%;"></div></div><div>Optimisation de la table <strong>contacts</strong></div>');
	$.get(
        base_url + 'index.php/contacts/ajaxoptcontacts',{literal}
        {},
        function(data){
           $('#result-opt').html( "Optimisation termine" );
        }
    );
    {/literal}
}

function deletenoname(){
    getsavoirinutile('result-opt');
	$.get(
        '{$Helper->getLink("contacts/maintenance_massdelete_noname")}',{literal}
        {nohtml:'nohtml'},{/literal}
        function(data){
            $('#result-opt').html('<div class="text-center">'+ data +'</div>');
        }
    );
}

function get_contacts_no_email(){
    getsavoirinutile('result-opt');
    $.get(
        '{$Helper->getLink("adm/contacts_no_email")}',{literal}
        {nohtml:'nohtml', go_to_trash:'y'},{/literal}
        function(data){
            $('#result-opt').html('<div class="text-center">'+ data +'</div>');
        }
    );
}

function gotocontact(id){
	window.location.href = '{$Helper->getLink("contacts/detail/'+id+'")}';
}
</script>