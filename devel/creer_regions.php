<?php
//header("Content-Type: text/json; charset=utf-8");
$polygones = json_decode(file_get_contents('../data/regions.geojson'), TRUE);

foreach($polygones['features'] AS $feature){
	$region = str_replace('/','-', $feature['properties']['REGION']);
	print "<div>$region :";


	$fp = fopen("../regions/$region.html", 'w') or die("Unable to open file!");
	fwrite($fp, json_encode($feature['geometry'], JSON_UNESCAPED_UNICODE));
	fclose($fp);
	print "$fp </div>";
}

