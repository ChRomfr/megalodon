<!-- START ADM/CONTACTS -->
{strip}
<ol class="breadcrumb">
    <li><a href="{$Helper->getLink("index")}" title="Accueil"><i class="fa fa-home"></i>&nbsp;&nbsp;Accueil</li>
    <li><a href="{$Helper->getLink("adm")}" title="Administration"><i class="fa fa-home"></i>&nbsp;&nbsp;Administration</a></li>
    <li class="active"><i class="fa fa-users"></i>&nbsp;&nbsp;Contacts</li>
</ol>

<div class="well">
	<h4><i class="fa fa-users"></i>&nbsp;&nbsp;Contacts</h4>
	<hr/>
	<ul class="bs-glyphicons">

		<li>
			<a href="{$Helper->getLink("categorie")}" title="">
				<span class="fa fa-sitemap fa-3x"></span>
				<span class="glyphicon-class">Catégories</span>
			</a>
		</li>	

		<li>
			<a href="{$Helper->getLink("importafpi")}" title="">
				<span class="fa fa-upload fa-3x"></span>
				<span class="glyphicon-class">Importer</span>
			</a>
		</li>

		<li>
			<a href="{$Helper->getLink("adm/contacts_maintenance")}" title="Maintenance">
				<span class="fa fa-wrench fa-3x"></span>
				<span class="glyphicon-class">Maintenance</span>
			</a>
		</li>

		<li>
			<a href="{$Helper->getLink("organisme")}" title="">
				<span class="fa fa-puzzle-piece fa-3x"></span>
				<span class="glyphicon-class">Organismes</span>
			</a>
		</li>

		<li>
			<a href="{$Helper->getLink("adm/contacts_postes")}" title="Dictionnaire des potes">
				<span class="fa fa-suitcase fa-3x"></span>
				<span class="glyphicon-class">Postes</span>
			</a>
		</li>

		<li>
			<a href="{$Helper->getLink("adm/contacts_services")}" title="Dictionnaire des services">
				<span class="fa fa-flag fa-3x"></span>
				<span class="glyphicon-class">Services</span>
			</a>
		</li>
	</ul>

</div>