<?php
	define( 'debug', 0 );

if( debug == 1 )
{
	echo '
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"> 
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title> Results Scanner </title>
	<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
	<link href=\'http://fonts.googleapis.com/css?family=Raleway:400,500,700\' rel=\'stylesheet\' type=\'text/css\'>
	<script src="https://google-code-prettify.googlecode.com/svn/loader/run_prettify.js?lang=css&skin=sons-of-obsidian"></script>
	
</head>';
}
else
{
	// header('Content-Type: text/plain');
}
	include_once './simple_html_dom.php';
	include_once './db.php';
	
	define( 'RETURN_EXTRACTION_FAILED', 0 );
	define( 'RETURN_SUCCESS', 1 );
	define( 'RETURN_NETWORK_ERROR', 2 );
	define( 'RETURN_ID_SKIPPED', 3 );
	
	/* if( isset($_POST) && isset($_POST['rg']) && isset($_POST['sem']) )
	{
		echo getResultFromServer( $_POST['rg'], $_POST['sem'], $_POST['mode'] );
	} */
	
	// getResultFromServer('1210108149', '1', 0);
	
	function getResultFromServer($db, $rg, $sem, $mode)
	{
		$ch = curl_init();
		if( $ch == FALSE )
		{
			return RETURN_NETWORK_ERROR;
			exit;
		}
		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);	
		
		$GrdCrdInputHeaders = array(
			'POST /onlineresults/pages/Newgrdcrdinput1.aspx HTTP/1.1',
			'Host: doeresults.gitam.edu:443',
			'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
			'Accept-Encoding: gzip, deflate',
			'Accept-Language: en-US,en;q=0.8',
			'Content-Type: application/x-www-form-urlencoded',
			'Origin: https://doeresults.gitam.edu',
			'Referer: http://www.gitam.edu/DOE_Administration.aspx?id=1363',
			'Upgrade-Insecure-Requests: 1',
			'User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.101 Safari/537.36'
		);
		
		
		$iR = intval($rg);
		
		// $db = mysqli_connect( DB_HOST, DB_USER, DB_PASS, DB_DBNAME );
		$sql = "SELECT ID FROM resultsfinal WHERE RegdNo='$iR' AND SemNo='$sem'";
		$res = mysqli_query( $db, $sql );
		
		if( mysqli_num_rows($res) > 0 )
		{
			if( $mode == 2 ) // SKIP
			{
				// mysqli_close( $db );
				return RETURN_ID_SKIPPED;exit;
			}
		}
		// mysqli_close( $db );
		
		curl_setopt($ch, CURLOPT_URL,"https://doeresults.gitam.edu/onlineresults/pages/Newgrdcrdinput1.aspx");		
		curl_setopt($ch, CURLOPT_HTTPHEADER, $GrdCrdInputHeaders );
		
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
			'__EVENTTARGET' => '',
			'__EVENTARGUMENT' => '',
			'__VIEWSTATE' => '/wEPDwULLTE3MTAzMDk3NzUPZBYCAgMPZBYCAgcPDxYCHgRUZXh0ZWRkZKKjA/8YeuWfLRpWAZ2J1Qp0eXCJ',
			'__VIEWSTATEGENERATOR' => '65B05190',
			'__EVENTVALIDATION' => '/wEWFQKj/sbfBgLnsLO+DQLIk+gdAsmT6B0CypPoHQLLk+gdAsyT6B0CzZPoHQLOk+gdAt+T6B0C0JPoHQLIk6geAsiTpB4CyJOgHgLIk5weAsiTmB4CyJOUHgKL+46CBgKM54rGBgK7q7GGCALWlM+bAsr6TbZa4e1ProM8biQQXbC9/wS2',
			'cbosem' => ''.$sem,
			'Button1' => 'Get Result',
			'txtreg' => ''.$iR
		)));
		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HEADER, true);
		// curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		if( ($res = curl_exec ($ch)) == FALSE )
		{
			//echo curl_error ( $ch );
			return RETURN_NETWORK_ERROR;exit;
		}
		// echo $res;
		// echo curl_getinfo($ch, CURLINFO_HEADER_OUT);
		// echo '<pre class="prettyprint" style="overflow-x:scroll">'.htmlentities($res).'</pre>';
		
		preg_match('/Set-Cookie: ASP.NET_SessionId=([a-zA-Z0-9]*)/', $res, $matches );
		// print_r($matches);
		$SessionID = $matches[1];
		
		// curl_close($ch);
		// $ch = curl_init();
		curl_setopt($ch, CURLOPT_POST, 0);
		
		$GrdCrdViewerHeaders = array(
			'GET /onlineresults/pages/View_Result_Grid.aspx HTTP/1.1',
			'Host: doeresults.gitam.edu:443',
			'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
			'Accept-Encoding: gzip, deflate, sdch',
			'Accept-Language: en-US,en;q=0.8',
			'Cookie: ASP.NET_SessionId='.$SessionID,
			'Referer: https://doeresults.gitam.edu/onlineresults/pages/Newgrdcrdinput1.aspx',
			'Upgrade-Insecure-Requests: 1',
			'User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.101 Safari/537.36'
		);
		
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $GrdCrdViewerHeaders);
		curl_setopt($ch, CURLOPT_URL,"https://doeresults.gitam.edu/onlineresults/pages/View_Result_Grid.aspx");
		// echo 'test';
		$server_output = curl_exec ($ch);
		// echo 'test';
		
		if( $server_output == FALSE )
		{
			return 2;exit;
		}
		
		if(ExtractDataToDB( $db, $iR, $server_output, $sem ))
		{
			return RETURN_SUCCESS;
		}
		else
		{
			return RETURN_EXTRACTION_FAILED;
		}		
		curl_close ($ch);
	}
		
	function ExtractDataToDB( $db, $RegdNo, $response, $SemNo )
	{
		$html = str_get_html($response);
		
		$SubjectDetails = array();
		$Name = $html->find('span[id=lblname]',-1)->plaintext;
		$Branch = $html->find('span[id=lblbranch]',-1)->plaintext;
		$cgpa = $html->find('span[id=lblcgpa]',-1)->plaintext;
		$gpa = $html->find('span[id=lblgpa]',-1)->plaintext;
		$iSubjects = 0;

		// echo $response;
		if( $SubjectTable == null || !(is_object($SubjectTable)) )
		{
			LogToFile("Suject table is null \n.");
			LogToFIle($response);
			return 0;
		}
		$SubjectTable = $html->find('table[id=GridView1] tbody', 0);
		$Sub = array(
			'SubCode' => '',
			'Sub' => '',
			'SubCredits' => '',
			'SubPoints' => ''
		);
		
		$i = 0;
		
		foreach( $SubjectTable->find('tr') as $tr )
		{
			foreach( $tr->find('td') as $Data )
			{
				if( $i%4 == 0 )
					$Sub['SubCode'] = html_entity_decode($Data->plaintext);
				else if( $i%4 == 1 )
					$Sub['Sub'] = html_entity_decode($Data->plaintext);
				else if( $i%4 == 2 )
					$Sub['SubCredits'] = html_entity_decode($Data->plaintext);
				else
					$Sub['SubPoints'] = html_entity_decode($Data->plaintext);
				
				//0 1 2 3 4 5 6 7 8 9... 
				$iSubjects = floor($i/4);
				$i++;
				// echo $iSubjects;
				
				$SubjectDetails[$iSubjects+1] = $Sub;
			}
		}
		$iSubjects++;
		// print_r( $SubjectDetails );
		// $db = mysqli_connect( DB_HOST, DB_USER, DB_PASS, DB_DBNAME );
		
		$sql = "CREATE TABLE IF NOT EXISTS resultsfinal ( ID INT NOT NULL AUTO_INCREMENT, RegdNo TEXT, SemNo TEXT, Name TEXT, CGPA TEXT, GPA TEXT,".
		"Sub1 TEXT, Sub1Credits TEXT, Sub1Code TEXT, Sub1Points TEXT, Sub2 TEXT, Sub2Credits TEXT, Sub2Code TEXT, Sub2Points TEXT, Sub3 TEXT, Sub3Credits TEXT, Sub3Code TEXT, Sub3Points TEXT,".
		"Sub4 TEXT, Sub4Credits TEXT, Sub4Code TEXT, Sub4Points TEXT, Sub5 TEXT, Sub5Credits TEXT, Sub5Code TEXT, Sub5Points TEXT, Sub6 TEXT, Sub6Credits TEXT, Sub6Code TEXT, Sub6Points TEXT,".
		"Sub7 TEXT, Sub7Credits TEXT, Sub7Code TEXT, Sub7Points TEXT, Sub8 TEXT, Sub8Credits TEXT, Sub8Code TEXT, Sub8Points TEXT, Sub9 TEXT, Sub9Credits TEXT, Sub9Code TEXT, Sub9Points TEXT, PRIMARY KEY (ID) )";
		
		mysqli_query( $db, $sql );
				
		$sql = "SELECT ID FROM resultsfinal WHERE RegdNo='$RegdNo' AND SemNo='$SemNo'";
		$res = mysqli_query( $db, $sql );
		$ID = '';
		if( mysqli_num_rows($res) < 1 )
		{
			$sql = "INSERT INTO resultsfinal ( RegdNo, SemNo, Name, CGPA, GPA ) VALUES ( '$RegdNo', '$SemNo', '$Name', '$cgpa', '$gpa' )";
			mysqli_query($db, $sql);
			$sql = "SELECT ID FROM resultsfinal WHERE RegdNo='$RegdNo' AND SemNo='$SemNo'";
			$res = mysqli_query( $db, $sql );
		}
		
		$IDarray = mysqli_fetch_array($res);
		$ID = $IDarray['ID'];	
		
		for( $iSubjects; $iSubjects > 0; $iSubjects-- )
		{
			$sql = "UPDATE resultsfinal SET Sub$iSubjects='".$SubjectDetails[$iSubjects]['Sub']."', Sub$iSubjects"."Credits='".$SubjectDetails[$iSubjects]['SubCredits']."',". 
			"Sub$iSubjects"."Code='".$SubjectDetails[$iSubjects]['SubCode']."', Sub$iSubjects"."Points='".$SubjectDetails[$iSubjects]['SubPoints']."' WHERE ID='$ID'";
			// echo $sql.'<br/>';
			mysqli_query($db, $sql);
		}
		
		// mysqli_close($db);
		return 1;
		
	}