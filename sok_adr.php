<?php
	/*
	*** TOMTKARTA SOM PDF ***
	*** UTEVECKLAT AV MALSOR LIMANI, STADSBYGGNADSKONTORET, JÖNKÖPINGS KOMMUN
	*** @malsor, malsor.limani@jonkoping.se
	*** Licens: CC-BY-NC-SA - https://creativecommons.org/licenses/by-nc-sa/4.0/
	***
	*** FUNKTIONEN NEDAN SÖKER EFTER ADRESSER
	***/
	
	/*** HÄR NEDAN FÖLJER PARAMETRAR SOM MÅSTE FYLLAS I **************************************************************************/
	/*  BYT UT ... MOT MOTSVARANDE UPPGIFTER */
	
	$serverName = "..."; 	//ange dnsnamn eller ipadress till databasserver.
	$pass = "...";			//ange lösenord för användaren gng.
	
	/* ÄNDRA EJ KODEN NEDAN */
	/*****************************************************************************************************************************/
	
	header("Content-Type: application/javascript; charset=utf-8");
	ini_set('mssql.charset', 'ISO-8859-1');
	set_time_limit(10);
		
	$adr = mb_convert_encoding($_GET['adr'], 'ISO-8859-1','UTF-8');

	$connectionInfo = array( "Database"=>"sde_geofir", "UID"=>"gng", "PWD"=>$pass);
	$conn = sqlsrv_connect($serverName, $connectionInfo);
	$sql = "SELECT TOP 30 [Name], PostCity, Shape.STAsText() as geom, PostCode FROM [sde_geofir].[gng].[READDRESS] Where Name like ? ORDER BY [Name]";
	$params = array($adr."%");
	$array= Array();
	if( $conn ) {
		$out = "[";
		$stmt = sqlsrv_query( $conn, $sql, $params );
		while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC)) {
			$namn = mb_convert_encoding($row['Name'], 'UTF-8', 'ISO-8859-1');
			$city = mb_convert_encoding($row['PostCity'], 'UTF-8', 'ISO-8859-1');
			$postcode = $row['PostCode'];
			$shape = $row['geom'];
			array_push($array,$namn.";".$city.";".$shape.";".$postcode);
		}
		$result = array_unique($array);
		echo json_encode($result);
	} else {
		echo '[{"0":"0;Kan inte hämta adresser från databasen."}]';
	}
?>
