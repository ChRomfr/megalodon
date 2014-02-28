{strip}
<ol class="breadcrumb">
	<li class="active"><i class="fa fa-home"></i>&nbsp;{$lang.Accueil}</li>
</ol>

<div class="col-md-5 well">
	<h4><i class="fa fa-signal"></i>&nbsp;&nbsp;Statistiques</h4>
	<table class="table table-striped table-condensed">
		<tr>
			<td>Contacts :</td>
			<td>{$stats.nb_ctcs}</td>
		</tr>
		<tr>
			<td>Societes :</td>
			<td>{$stats.nb_sct}</td>
		</tr>
		<tr>
			<td>Personne :</td>
			<td>{$stats.nb_per}</td>
		</tr>
		<tr>
			<td>Email :</td>
			<td>{$stats.nb_email}</td>
		</tr>
	</table>
</div>
<div class="col-md-1"></div>
<div class="col-md-6">
	{if isset($smarty.session.acl.mailing_access) || $smarty.session.utilisateur.isAdmin > 0}
	<div id="widget-mailing" class="well"></div>
	{/if}
</div>
<div class="clearfix"></div>

<div id="end-widget-index"></div>
{if $smarty.session.utilisateur.index_map_contacts == 1}
<div class="col-md-12 well">
	<h4><i class="fa fa-globe"></i>&nbsp;&nbsp;Cartes des contacts</h4>
	<div id="map-city" class="gmap3" style="height:500px;"></div>
</div>
{/if}
{/strip}

{if $smarty.session.utilisateur.index_map_contacts == 1}
<script src="http://maps.googleapis.com/maps/api/js?sensor=false" type="text/javascript"></script>
<script type="text/javascript">var markers = {$Markers}</script>
{/if}
{if isset($current_campaigns)}<script type="text/javascript">var current_campaigns = {$current_campaigns}</script>{/if}
{if isset($meets)}<script type="text/javascript">var meets = {$meets}</script>{/if}