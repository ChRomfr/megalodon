<div class="well">
	<h3>Import de fichier d'origine</h3>
	<div id="importfichier">
        <button onclick="importfichier();" class="btn">Importer</button>
    </div>
</div>

<script type="text/javascript">
<!--
function importfichier(){
   $("#importfichier").html("Importation en cours ...");
    $.get(
        '{$Helper->getLink("index/ajaximportfichier")}',{literal}
        {nohtml:'nohtml'},
        function(data){
            $("#importfichier").html(data);
        }
    ); 
}
{/literal}
//->
</script>