<!-- view/import/step3.tpl -->
{strip}
<div class="well">
	<h5>Statistique d'import</h5>
	<ul class="unstyled">
		<li>Nombre de lignes dans le fichier : <strong>{$nb_lines}</strong></li>
		<li>Lignes sans email : {$nb_lines_noemail}</li>
		<li>Email deja present : {$nb_email_present}</li>
		<li>Telephne deja present : {$nb_tel_present}</li>
		<li>Fax deja present :  <strong>{$nb_fax_present}</strong></li>
		<li>SIRET deja present : <strong>{$nb_siret_present}</strong></li>
		<li>Contacts présent plusieurs fois : <strong>{$nb_errors_contacts_double}</strong></li>
		<li>Nouveau contact : <strong>{$nb_new_contacts}</li>
		<li>Mise a jour de contact : <strong>{$nb_update_contacts}</li>
	</ul>
	<hr/>
	<h5>Log des imports</h5>
	<table class="table table-condensed">
		{foreach $logs as $k => $v}
		<tr>
			<td>{$v}</td>
		</tr>
		{/foreach}
	</table>
</div>
{/strip}