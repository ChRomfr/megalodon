<!-- view/importafpi/step2/.tpl -->
<ul class="breadcrumb">
	<li><a href="{$Helper->getLink("index")}" title="">Accueil</a><span class="divider">>></span></li>
	<li><a href="{$Helper->getLink("importafpi")}" title="">Import - AFPI</a><span class="divider">&gt;&gt;</span></li>
	<li>Etape 2 - Correspondance des champs</li>
</ul>

<div class="well">
	<div id="form-container" style="display:block">
		<form method="post" action="{$Helper->getLink("importafpi/step3/{$file}")}" onsubmit="submitformstep2();">
		<div class="container">
			<div class="row">

				<div class="col-md-5">
					<!-- Tableau entreprise -->
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
				</div><!-- /span5 -->

				<div class="col-md-5">
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
				</div><!-- /span5 -->

			</div><!-- /row-fluid -->
		</div>{* /container-fluid *}

		<hr/>
		
		<div>
			<p><input type="checkbox" name="use_rs_double" id="use_rs_double" /> Verification des doublons par la raison social</p>
			<p><input type="checkbox" name="email_valid" id="email_valid" checked="checked" /> Importer seulement les contacts avec des emails</p>
			<p><input type="checkbox" name="logs_verbose" id="logs_verbose" checked="checked" /> Résultat import détaillé</p>
			<p><input type="checkbox" name="simulation" id="simulation" /> Mode simulation, traite le fichier sans enregistrer dans la base</p>
		</div>

		<div class="form-actions">
			<button type="submit" class="btn btn-primary">Importer</button>
			&nbsp;&nbsp;
			<a href="{$Helper->getLink("importafpi")}" title="" class="btn btn-danger">Annuler</a>
		</div>

		</form>
	</div>
	<div id="form-processing" style="display:none;">
		<div class="progress progress-striped active" style="width:500px;">
	    	<div class="bar" style="width: 0%;"></div>
	    </div>
		Traitement en cours ...
	</div>
</div><!-- /well -->
<script type="text/javascript">
function submitformstep2(){
	$('#form-container').css('display','none');
	$('#form-processing').css('display','block');
	return true;
}

jQuery(function($){

   var progress = setInterval(function() {
	    var $bar = $('.bar');
	    
	    if ($bar.width()==400) {
	        //clearInterval(progress);
	        $bar.width(0);
	        //$('.progress').removeClass('active');
	    } else {
	        $bar.width($bar.width()+40);
	    }
	    //$bar.text($bar.width()/4 + "%");
	}, 800);

});
</script>