<?php

function strLatLngToAddr( $strLat, $strLng )
{

	$url = "http://maps.google.com/maps/api/geocode/json?latlng=" .$strLng.",".$strLat."&sensor=true&language=ja";
	$strRes = file_get_contents($url);
	$aryGeo = json_decode( $strRes, TRUE );
	if ( !isset( $aryGeo['results'][0] ) )
	return "";

//	$addr_tmp = $aryGeo['results'][0]['address_components'][5]['long_name'].$aryGeo['results'][0]['address_components'][4]['long_name'].$aryGeo['results'][0]['address_components'][3]['long_name'].$aryGeo['results'][0]['address_components'][2]['long_name'].$aryGeo['results'][0]['address_components'][1]['long_name'].$aryGeo['results'][0]['address_components'][0]['long_name'];
//	$addr = mb_convert_encoding($addr_tmp, "SHIFT-JIS", "auto");

	$addr = mb_convert_encoding((string)$aryGeo['results'][0]['formatted_address'], "SHIFT-JIS", "auto");


//http://maps.googleapis.com/maps/api/geocode/json?latlng=43.0603203,141.35831629999998&sensor=false

    return $addr;
}

function strAddrToLatLng( $address )
{

	$url = "http://maps.google.com/maps/api/geocode/json?address='".urlencode( mb_convert_encoding( $address, 'UTF-8' ) )."'&sensor=true";

	$strRes = file_get_contents($url);
	$aryGeo = json_decode( $strRes, TRUE );
	if ( !isset( $aryGeo['results'][0] ) )
	return array("lat" => "error", "lng" => "error");


	$lat = (string)$aryGeo['results'][0]['geometry']['location']['lat'];
	$lng = (string)$aryGeo['results'][0]['geometry']['location']['lng'];

	return array("lat" => $lat, "lng" => $lng);
}

//２点間の緯度経度から距離を求める
//$lat1, $lon1 --- A地点の緯度経度
//$lat2, $lon2 --- B地点の緯度経度
function location_distance($lat1, $lon1, $lat2, $lon2){
	$lat_average = deg2rad( $lat1 + (($lat2 - $lat1) / 2) );//２点の緯度の平均
	$lat_difference = deg2rad( $lat1 - $lat2 );//２点の緯度差
	$lon_difference = deg2rad( $lon1 - $lon2 );//２点の経度差
	$curvature_radius_tmp = 1 - 0.00669438 * pow(sin($lat_average), 2);
	$meridian_curvature_radius = 6335439.327 / sqrt(pow($curvature_radius_tmp, 3));//子午線曲率半径
	$prime_vertical_circle_curvature_radius = 6378137 / sqrt($curvature_radius_tmp);//卯酉線曲率半径
	
	//２点間の距離
	$distance = pow($meridian_curvature_radius * $lat_difference, 2) + pow($prime_vertical_circle_curvature_radius * cos($lat_average) * $lon_difference, 2);
	$distance = sqrt($distance);
	
	$distance_unit = round($distance);
	if($distance_unit < 1000){//1000m以下ならメートル表記
		$distance_unit = $distance_unit."m";
	}else{//1000m以上ならkm表記
		$distance_unit = round($distance_unit / 100);
		$distance_unit = ($distance_unit / 10)."km";
	}
	
	return array("distance" => $distance, "distance_unit" => $distance_unit);
}
