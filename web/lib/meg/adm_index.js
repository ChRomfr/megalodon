jQuery(document).ready(function(){
	$.get(
        base_url + 'adm/ajax_stats',{nohtml:'nohtml'},      
        function(data){
        	$('#adm-stats').html(data);
        }
    );

	$.getJSON(
		base_url + 'adm/ajax_dataforgraph_repart_constacts', 
		{nohtml:'nohtml'},  
		function(data){     
			$.plot($("#contacts-type-repart"), data,{
				series: {
					pie: {
							show: true
					}
				},
				grid: {
					hoverable: true,
					clickable: true
				},      
				legend: {
					show: false
				}
			}); 
			
		}
	);
	
	$.getJSON(
		base_url + 'adm/ajax_dataforgraph_repart_mailing_type',
		{nohtml:'nohtml'},   
		function(data){     
			$.plot($("#mailings-type-repart"), data,{
				series: {
					pie: {
							show: true
					}
				},
				grid: {
					hoverable: true,
					clickable: true
				},      
				legend: {
					show: false
				}
			}); 
			
		}
	);
});