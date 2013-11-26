<?php

function squid_log_parser($buffer){

	// http://www.squid-cache.org/Doc/FAQ/FAQ.html#toc6.7
	$inCacheCodes[]='TCP_HIT';
	$inCacheCodes[]='TCP_REFRESH_HIT';
	$inCacheCodes[]='TCP_DENIED';
	$inCacheCodes[]='TCP_REF_FAIL_HIT';
	$inCacheCodes[]='TCP_NEGATIVE_HIT';
	$inCacheCodes[]='TCP_MEM_HIT';
	$inCacheCodes[]='TCP_OFFLINE_HIT';


	$data = array();
	
	$record=preg_split("/\s+/",$buffer);
	
	//$data['date'] = date('Y-m-d',$record[0]+$timezone_diff);
	//$data['time'] = date('H:i:s',$record[0]+$timezone_diff);
	$data['date'] = date('Y-m-d',$record[0]);
	$data['time'] = date('H:i:s',$record[0]);
	$data['ip'] = $record[2];
	$data['resultCode']=$record[3];
	$data['bytes']=$record[4];
	$data['url']=addslashes($record[6]);
	$data['user']=mysql_real_escape_string(substr($record[7],0,50));
	
	// On determine si la requete a utiliser le cache
	$resultCodeArray=explode('/',$data['resultCode']);
	if(in_array($resultCodeArray[0],$inCacheCodes)) {
		$data['inCache']='1';
	} else {
		$data['inCache']='0';
	}
	
	// On retourne les datas pour insertion dans la base
	return $data;
}

function formatBytes($size, $precision = 2)
{
    $base = log($size) / log(1024);
    $suffixes = array('', 'k', 'M', 'G', 'T');   

    return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
}

?>