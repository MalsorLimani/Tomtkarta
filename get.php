<?php
	/*
	*** TOMTKARTA SOM PDF ***
	*** UTEVECKLAT AV MALSOR LIMANI, STADSBYGGNADSKONTORET, JÖNKÖPINGS KOMMUN
	*** @malsor, malsor.limani@jonkoping.se
	*** Licens: CC-BY-NC-SA - https://creativecommons.org/licenses/by-nc-sa/4.0/
	***
	*** FUNKTIONEN NEDAN SÖKER EFTER FASTIGHETSYTOR OCH SKAPAR PDF
	***/
	
	/*** HÄR NEDAN FÖLJER PARAMETRAR SOM MÅSTE FYLLAS I **************************************************************************/
	
	/*  BYT UT ... MOT MOTSVARANDE UPPGIFTER */
	
	$serverName = "..."; 	//ange servernamn eller ipadress till databasserver.
	$db = "...";			//ange databasnamn till databas som innehåller fastighetsytor.
	$tabell = "...";		//ange tabellnamn som innehåller fastighetsytor.
	$anv = "gng";			//ange användarnamn till databasen (gng fungerar bra).
	$pass = "...";			//ange lösenord för användaren ovan.
	$kolumn = "...";		//ange kolumnnamn som innehåller fullständing fastighetsbeteckning.
	
	$agserver = "https://.../arcgis/rest/"; 						//ange url till arcgis-server (OBS! MÅSTE VARA ÅTKOMLIG EXTERNT!!).
	$svcUrl = "https://.../arcgis/rest/services/.../MapServer";		//ange url till arcgis-tjänst för tomtkarta (OBS! MÅSTE VARA ÅTKOMLIG EXTERNT!!).
	$srs = "...";													//ange referenssystem i EPSG-kod (t ex 3008 för Sweref99 1330).
	
	
	/* OBS!!! ÄNDRA EJ KODEN NEDAN */
	/*****************************************************************************************************************************/
	
	ini_set('mssql.charset', 'ISO-8859-1');
	ini_set("error_reporting", E_ALL);
	
	$gata = mb_convert_encoding($_POST['gata'], 'ISO-8859-1', 'UTF-8');
	$city = mb_convert_encoding($_POST['city'], 'ISO-8859-1', 'UTF-8');
	
	if (empty($gata)){
		http_response_code(621);
		die('{"results":[{"value":{"url":""}}],"messages":[{"error":"Ingen adress har angivits."}]}');
		
	} else {
		
		$scale=0;
		$geometri =0;
		$xmin =0;
		$xmax =0;
		$ymin =0;
		$ymax =0;
		
		$fastbet ="";
		
		$connectionInfo = array( "Database"=>$db, "UID"=>$anv, "PWD"=>$pass);
		$conn = sqlsrv_connect($serverName, $connectionInfo);
		$sql =
			"SELECT 
				geometry::EnvelopeAggregate(shape).STPointN(1).STX AS MinX,
				geometry::EnvelopeAggregate(shape).STPointN(1).STY AS MinY,
				geometry::EnvelopeAggregate(shape).STPointN(3).STX AS MaxX,
				geometry::EnvelopeAggregate(shape).STPointN(3).STY AS MaxY,
				fastighet
			FROM [".$db."].[".$anv."].[".$tabell."]	
			WHERE (
				".$kolumn." =
					(SELECT TOP 1 RealEstateName
						FROM sde_geofir.gng.READDRESS
						WHERE Name LIKE '".$gata."%' AND PostCity LIKE '".$city."')
					)
			group by ".$kolumn;
				
		if( $conn ) {
			$stmt = sqlsrv_query( $conn, $sql );
			while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC)) {
				
				$fastbet = mb_convert_encoding($row['fastighet'], 'UTF-8', 'ISO-8859-1');
				
				$xmin = round($row['MinX']);
				$xmax = round($row['MaxX']);
				$ymin = round($row['MinY']);
				$ymax = round($row['MaxY']);
				
			}
			
			$xx = ($xmax-$xmin);
			$yy = ($ymax-$ymin);
			
			// OBS! Dessa värden ändras om man ändrar kartutsnittet på Utskriftsmallarna.
			// Höjd 235m (94m) på A4 i skala 1:1000 (1:400).
			// Bredd 160m (64m) på A4 i skala 1:1000 (1:400).
			
			if ($xx>$yy){
				//landscape
				if ($xx<=94 && $yy<=64) {
					$scale = "400";
					$namn['skala'] = $scale;
				}else if ($xx<=235 && $yy<=160){
					$scale = "1000";
					$namn['skala'] = $scale;
				}else {
					http_response_code(620);
					die('{"results":[{"value":{"url":""}}],"messages":[{"error":"Fel: Fastigheten '.$fastbet.' är för stor för att få plats på en A4.", "fastbet":"'.$fastbet.'"}]}');
				}
				
			} else {
				//portrait
				if ($yy<=94 && $xx<=64) {
					$scale = "400";
					$namn['skala'] = $scale;
				}else if ($yy<=235 && $$xx<=160){
					$scale = "1000";
					$namn['skala'] = $scale;
				}else {
					http_response_code(620);
					die('{"results":[{"value":{"url":""}}],"messages":[{"error":"Fel: Fastigheten '.$fastbet.' är för stor för att få plats på en A4.", "fastbet":"'.$fastbet.'"}]}');
				}
			}
			
		} else {
			http_response_code(622);
			die('{"results":[{"value":{"url":""}}],"messages":[{"error":"Ingen koppling mot databasen."}]}');
		}
		
		$printData = function($skala, $xmin, $xmax, $ymin, $ymax, $bet){
			
			$json = json_decode(file_get_contents($svcUrl."?f=json"), true);
			$lager ="";
			for ($x=0; $x<count($json['layers']); $x++){
				$lager .= $json['layers'][$x]['id'].",";
			}
			$lager = substr_replace($lager, "", strrpos($lager, ","), strlen(","));
			
			if (($xmax-$xmin)>($ymax-$ymin)){
				$layout = 'tomtkarta_a4_l';
			} else {
				$layout = 'tomtkarta_a4_p';
			}
			
			$data='{"mapOptions":{"showAttribution":false,"extent":{"xmin":'.$xmin.',"ymin":'.$ymin.',"xmax":'.$xmax.',"ymax":'.$ymax.',"spatialReference":{"wkid":"'.$GLOBALS['srs'].'","latestWkid":"'.$GLOBALS['srs'].'","vcsWkid":105702,"latestVcsWkid":5613}},"spatialReference":{"wkid":"'.$GLOBALS['srs'].'","latestWkid":"'.$GLOBALS['srs'].'"},"scale":'.$skala.'},"operationalLayers":[{"id":"Tomtkarta färg","title":"Tomtkarta färg","opacity":1,"minScale":0,"maxScale":0,"url":"'.$GLOBALS['svcUrl'].'","visibleLayers":['.$lager.']},{"id":"map_graphics","opacity":1,"minScale":0,"maxScale":0,"featureCollection":{"layers":[]}}],"exportOptions":{"dpi":150},"layoutOptions":{"titleText":"'.$bet.'"}}';

		return "services/Utilities/PrintingTools/GPServer/Export%20Web%20Map%20Task/execute?f=json&Web_Map_as_JSON=".urlencode($data)."&Format=PDF&Layout_Template=".$layout;
		};
		
		header("Content-Type: application/json");
		$ur = $agserver.$printData($scale, $xmin, $xmax, $ymin, $ymax, $fastbet);
		$pd = file_get_contents($ur, false, $context);
		$pd= str_replace('"messages":[]','"messages":{"fastbet":"'.$fastbet.'"}',$pd);
		print_r( $pd);
	}
?>