<ul class="breadcrumb">
	<li class="active">Accueil</li>
</ul>

	<div class="col-md-5 well">
		<h4>Statistiques</h4>
		<ul>
			<li>Contacts : {$stats.nb_ctcs}</li>
			<li>Societe : {$stats.nb_sct}</li>
			<li>Personne : {$stats.nb_per}</li>
			<li>Email : {$stats.nb_email}</li>
		</ul>
	</div><!-- /span5 -->
	<div class="col-md-5 well">
	{if $smarty.session.utilisateur.mailing_adm == 1}
		<div id="calmailingindex"style="width:95%;"></div>
	{/if}
	</div>{* /span5 *}

	{if $smarty.session.utilisateur.index_map_contacts == 1}
	<div class="col-md-10 well">
		<h4>Cartes des contacts</h4>
		<div id="map-city" class="gmap3" style="height:500px;"></div>
	</div>{* /span10 well *}
	{/if}

{if $smarty.session.utilisateur.index_map_contacts == 1}
<script src="http://maps.googleapis.com/maps/api/js?sensor=false" type="text/javascript"></script>
{/if}
<script type="text/javascript">
{if $smarty.session.utilisateur.index_map_contacts == 1}
var globalCluster;
{literal}
$(function(){
	$('#map-city').gmap3({
		map:{
			option:{
				center:[46.578498,2.457275],
              	zoom: 4,
              	mapTypeId: google.maps.MapTypeId.TERRAIN
			}
		},
		marker:{
			values:
			{/literal}{$Markers}{literal}
			,
			cluster:{
				radius:100,
				0:{
					content: '<div class="cluster cluster-1">CLUSTER_COUNT</div>',
					width:53,
					height:52
				},
				20:{
					content: '<div class="cluster cluster-2">CLUSTER_COUNT</div>',
					width:56,
					height:55
				},
				50:{
					content: '<div class="cluster cluster-3">CLUSTER_COUNT</div>',
					width:66,
					height:65
				},
				events: {
	                click: function(cluster) {
	                  var map = $(this).gmap3("get");
	                  map.setCenter(cluster.main.getPosition());
	                  map.setZoom(map.getZoom() + 1);
	                }
              	}
			},

			events:{
              mouseover: function(marker, event, context){
              	//console.log(context.data);
                $(this).gmap3(
                  {clear:"overlay"},
                  {
                  overlay:{
                    latLng: marker.getPosition(),
                    options:{
                      content:  '<div class="infobulle"><div class="bg"></div><div class="text">'+ context.data.rs +'<br/>'+context.data.adresse+'<br/>'+context.data.code_postal+' '+ context.data.ville +'</div></div>',
                      offset: {
                        x:-46,
                        y:-73
                      }
                    }
                  }
                });
              },
              mouseout: function(){
                $(this).gmap3({clear:"overlay"});
              }
            }
		},
	});
});
{/literal}
{/if}

$.getJSON(
	'{$Helper->getLink("mailing/getmailingforcalendar")}', {literal}
	{nohtml:'nohtml'},{/literal}   
	function(data){ 
		
		$(document).ready(function() {
	
			$('#calmailingindex').fullCalendar({
				header: {
					left: '',
					center: '',
					right: ''
				},
				defaultView: 'basicWeek',
				firstDay:1,
				editable: false,
				events:data,		
			});	
		}); 		
	}
);
</script>