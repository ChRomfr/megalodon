{* app/view/contacts/maintenance.tpl *}

<ul class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="Accueil">Accueil</a></li>
	<li class="active">Maintenance</li>
</ul>

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

{* MODAL GENERIQUE MAINTENANCE*}
<div id="modal-maintenance-contacts" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        	<div class="modal-header">
        		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        		<h3 id="modal-maintenance-contact-label"></h3>
        	</div>
        	<div class="modal-body" id="modal-maintenance-contacts-body">
        		
        	</div>
        	<div class="modal-footer">
        		<button class="btn btn-default" data-dismiss="modal" aria-hidden="true">Fermer</button>
        	</div>
        </div>
    </div>
</div>

<script type="text/javascript">
<!--
function getCorbeille(){
	$.get(
        '{$Helper->getLink("contacts/maintenancegetcorbeille")}',{literal}
        {nohtml:'nohtml'},{/literal}
        function(data){
            $("#maintenace-ctcs-corbeille").html(data);
        }
    );    
}

function cleantrash(){
	if(confirm('Etes vous sur de vouloir vider la corbeille ?')){
		$.get(
	        '{$Helper->getLink("contacts/maitenancecleantrash")}',{literal}
	        {nohtml:'nohtml'},{/literal}
	        function(data){
	            $("#maintenace-ctcs-corbeille").html(data + ' contact(s) ont ete supprime de la base');
	        }
	    );
	}
}

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
        '{$Helper->getLink("contacts/ajaxoptcontacts")}',{literal}
        {nohtml:'nohtml'},
        function(data){
            optsocietes();
        }
    );
    {/literal}
}

function optsocietes(){
	$('#result-opt').html('<div class="progress progress-striped active"><div class="bar" style="width: 50%;"></div></div><div>Optimisation de la table <strong>societe</strong></div>');
	$.get(
        '{$Helper->getLink("contacts/ajaxoptsocietes")}',{literal}
        {nohtml:'nohtml'},
        function(data){
            optpersonnes();
        }
    );
    {/literal}
}

function optpersonnes(){
	$('#result-opt').html('<div class="progress progress-striped active"><div class="bar" style="width: 75%;"></div></div><div>Optimisation de la table <strong>personne</strong></div>');
	$.get(
        '{$Helper->getLink("contacts/ajaxoptpersonnes")}',{literal}
        {nohtml:'nohtml'},
        function(data){
            $('#result-opt').html('<div class="progress progress-striped active"><div class="bar" style="width: 100%;"></div></div><div>Finie</div>');
        }
    );
    {/literal}
}

function getemailerror(){
	$.get(
        '{$Helper->getLink("contacts/ajaxcheckemailcontacts")}',{literal}
        {nohtml:'nohtml'},        
        function(data){
        	$('#result-opt').html('<table id="table-result-email" class="table table-condensed table-striped"><thead><tr><th>#</th><th>Email</th><th></th></thead><tbody></tbody></table>');
        	var tpl = '<tr><td>{{id}}</td><td><a href="javascript:gotocontact({{id}});">{{email}}</a></td><td><a href="javascript:getformeditemail({{id}});" title=""><i class="icon icon-edit"></i></a></td></tr>';
        	for( var i in data ){      
            	$('#table-result-email').append( Mustache.render(tpl, data[i]) );
        	}
        },'json'
    );
	{/literal}
}

function deletenoname(){
	$.get(
        '{$Helper->getLink("contacts/maintenance_massdelete_noname")}',{literal}
        {nohtml:'nohtml'},{/literal}
        function(data){
            $('#result-opt').html('<div class="text-center">'+ data +'</div>');
        }
    );
}

function getformeditemail(cid){
	$.get(
        '{$Helper->getLink("contacts/ajaxgetformeditemailmaintenance/'+cid+'")}',{literal}
        {nohtml:'nohtml'},{/literal}
        function(data){
            $("#modal-maintenance-contacts-body").html(data);
        }
        
    );

    $('#modal-maintenance-contact-label').html('Modification email');
    $('#modal-maintenance-contacts').modal('show');
}

function gotocontact(id){
	window.location.href = '{$Helper->getLink("contacts/detail/'+id+'")}';
}
//-->
</script>