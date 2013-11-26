<div class="well">
	<h3>{$site.name}</h3>
	<div id="status"></div>
</div>

<script type="text/javascript">
<!--
$(window).load(function(){
    $("#status").html("Recuperation des logs ...");
	$.get(
        '{$Helper->getLink("live/getfile/{$site.id}")}',{literal}
        {nohtml:'nohtml'},
        function(data){
        	$("#status").html("Construction de la table temporaire");
        	ajaxconstructdb();
        }
    );
});
{/literal}

function ajaxconstructdb(){
	$.get(
        '{$Helper->getLink("live/constructTableTmp")}',{literal}
        {nohtml:'nohtml'},
        function(data){
        	console.log("Construction Db OK");
            $('#status').html('Generation des stats ...<br/>Please wait !');
            ajaxstatindex();
        }
    );
    {/literal}
}

function ajaxstatindex(){    
    $.get(
        '{$Helper->getLink("live/stats")}',{literal}
        {nohtml:'nohtml'},
        function(data){
            console.log("Generation OK");
            $('#status').html(data);
        }
    );
    {/literal}
}

function detailuserbyip(ip){
    $('#status').html('Recuperation des donnees pour '+ ip);
    $.get(
        '{$Helper->getLink("live/detailbyip")}',{literal}
        {nohtml:'nohtml', ip:ip},
        function(data){
            $('#status').html(data);
        }
    );
    {/literal}
}

//-->
</script>

