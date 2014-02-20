if(suser.index_map_contacts == 1){
	var globalCluster;
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
				values:markers,
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
}

$.getJSON(
	base_url + 'mailing/getmailingforcalendar',
	{nohtml:'nohtml'},   
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
		$('#calmailingindex-progress').css('display','none');		
	}
);