<?php
	include_once './simple_html_dom.php';

	if( isset($_POST) && isset($_POST['rg']) && isset($_POST['sem']) )
	{
		echo getResultFromServer( $_POST['rg'], $_POST['sem'], $_POST['mode'] );
	}
		
	function getResultFromServer($rg, $sem, $mode)
	{
		$ch = curl_init();
		if( $ch == FALSE )
		{
			return 2;
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
			'Referer: https://doeresults.gitam.edu/onlineresults/pages/Newgrdcrdinput1.aspx',
			'Upgrade-Insecure-Requests: 1',
			'User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.101 Safari/537.36'
		);
		
		
		$iR = intval($rg);
		
		$db = mysqli_connect( DB_HOST, DB_USER, DB_PASS, DB_DBNAME );
		$sql = "SELECT ID FROM resultsfinal WHERE RegdNo='$iR' AND SemNo='$sem'";
		$res = mysqli_query( $db, $sql );
		
		if( mysqli_num_rows($res) > 0 )
		{
			if( $mode == 2 ) // SKIP
			{
				mysqli_close( $db );
				return 3;exit;
			}
		}
		mysqli_close( $db );
		
		curl_setopt($ch, CURLOPT_URL,"https://doeresults.gitam.edu/onlineresults/pages/Newgrdcrdinput1.aspx");		
		curl_setopt($ch, CURLOPT_HTTPHEADER, $GrdCrdInputHeaders );
		
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
			'__EVENTTARGET' => '',
			'__EVENTARGUMENT' => '',
			'__VIEWSTATE' => '/wEPDwUKMTA3MjA4MTY2NmRkNen76Dh1xyCJ+d0+MNI18gjoQsk=',
			'__VIEWSTATEGENERATOR' => '65B05190',
			'__EVENTVALIDATION' => '/wEWFALN0aLYBwLnsLO+DQLIk+gdAsmT6B0CypPoHQLLk+gdAsyT6B0CzZPoHQLOk+gdAt+T6B0C0JPoHQLIk6geAsiTpB4CyJOgHgLIk5weAsiTmB4CyJOUHgKL+46CBgKM54rGBgK7q7GGCPKQGg7JY0iXzZSn7rFncm/zXnWh',
			'cbosem' => ''.$sem,
			'Button1' => 'Get Result',
			'txtreg' => ''.$iR
		)));
		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HEADER, true);
		if( ($res = curl_exec ($ch)) == FALSE )
		{
			//echo curl_error ( $ch );
			return 2;exit;
		}
		
		preg_match('/Set-Cookie: ASP.NET_SessionId=([a-zA-Z0-9]*)/', $res, $matches );
		$SessionID = $matches[1];
		
		$GrdCrdViewerHeaders = array(
			'GET /onlineresults/pages/NewReportviewer1.aspx HTTP/1.1',
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
		curl_setopt($ch, CURLOPT_HTTPHEADER, $GrdCrdViewerHeaders);
		curl_setopt($ch, CURLOPT_URL,"https://doeresults.gitam.edu/onlineresults/pages/NewReportviewer1.aspx");
		$server_output = curl_exec ($ch);
		if( $server_output == FALSE )
		{
			return 2;exit;
		}
		
		if(ExtractDataToDB( $iR, $server_output, $sem ))
		{
			return 1;
		}
		else
		{
			return 0;
		}		
		curl_close ($ch);
	}
		
	function ExtractDataToDB( $Regdn, $response, $SemNo )
	{
		$needle = strpos( $response, '<div class="crystalstyle"' );
		if( !$needle )
		{
			return 0;
		}
		
		$resp = substr( $response , $needle );		
		$html = str_get_html($response);		
		$ret = $html->find('.crystalstyle');
		
		//<div class="ad0de45e2c-15d7-4cb9-939c-d2f389804f58-0" style="top:143px;left:80px;width:80px;height:15px;">
		$className = array();
		
		if( !preg_match( "/<div class=\"[0-9a-zA-Z-]*\" style=\"top:143px;left:80px;width:80px;height:15px;\">/s", $resp, $className ) )
		{
			return 0;
		}
		
		$classNameExact = $className[0];
		$classNameExact = substr($classNameExact, 12, 40);
		
		$className = array();
		if( !preg_match( "/.[a-zA-z0-9-]* {font-size:9pt;color:#000000;font-family:Times New Roman;/s", $response, $className ))
		{
			return 0;
		}
		$TextclassNameExact = $className[0];
		$TextclassNameExact = substr($TextclassNameExact, 1, 40);
		$TextStripedClassName = substr($TextclassNameExact, 0, 39);
		
		$elements = $html->find('.'.$classNameExact);
		
		$AllValues = array();
		$AllValues['0'] = array();
		$AllValues['1'] = array();
		$AllValues['2'] = array();
		$AllValues['3'] = array();
		$AllValues['4'] = array();
		$AllValues['5'] = array();
		
		$AllValues['c0'] = 0;
		$AllValues['c1'] = 0;
		$AllValues['c2'] = 0;
		$AllValues['c3'] = 0;
		$AllValues['c4'] = 0;
		$AllValues['c5'] = 0;
				
		foreach( $elements as $elem )
		{			
			$elemAttrs = $elem->getAllAttributes();
			
			while( !isset($elemAttrs['class'])
			||(($elemAttrs['class'] != $TextStripedClassName.'0') 
			&& ($elemAttrs['class'] != $TextStripedClassName.'1')
			&& ($elemAttrs['class'] != $TextStripedClassName.'2')
			&& ($elemAttrs['class'] != $TextStripedClassName.'3')
			&& ($elemAttrs['class'] != $TextStripedClassName.'4')
			&& ($elemAttrs['class'] != $TextStripedClassName.'5')))
			{
				$elem = $elem->childNodes(0);
				$elemAttrs = $elem->getAllAttributes();
			}
			$index = $elemAttrs['class']{39};
			$AllValues['c'.$index]++;
			$AllValues[''.$index][''.$AllValues['c'.$index]] = trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', html_entity_decode(preg_replace('/&nbsp;/', ' ', $elem->innertext))));
		}
		
		$db = mysqli_connect( DB_HOST, DB_USER, DB_PASS, DB_DBNAME );
		
		$sql = "CREATE TABLE IF NOT EXISTS resultsfinal ( ID INT NOT NULL AUTO_INCREMENT, RegdNo TEXT, SemNo TEXT, Name TEXT, CGPA TEXT, GPA TEXT,".
		"Sub1 TEXT, Sub1Credits TEXT, Sub1Code TEXT, Sub1Points TEXT, Sub2 TEXT, Sub2Credits TEXT, Sub2Code TEXT, Sub2Points TEXT, Sub3 TEXT, Sub3Credits TEXT, Sub3Code TEXT, Sub3Points TEXT,".
		"Sub4 TEXT, Sub4Credits TEXT, Sub4Code TEXT, Sub4Points TEXT, Sub5 TEXT, Sub5Credits TEXT, Sub5Code TEXT, Sub5Points TEXT, Sub6 TEXT, Sub6Credits TEXT, Sub6Code TEXT, Sub6Points TEXT,".
		"Sub7 TEXT, Sub7Credits TEXT, Sub7Code TEXT, Sub7Points TEXT, Sub8 TEXT, Sub8Credits TEXT, Sub8Code TEXT, Sub8Points TEXT, Sub9 TEXT, Sub9Credits TEXT, Sub9Code TEXT, Sub9Points TEXT, PRIMARY KEY (ID) )";
		
		mysqli_query( $db, $sql );
		
		$SubjectDetails = array();
		$Name = $AllValues['0'][4];
		$Branch = $AllValues['0'][5];
		$RegdNo = $AllValues['0'][6];
		$iSubjects = 0;
		
		for( $i = 11; $i < (sizeof($AllValues['0'])-4); $i += 4 )
		{
			$Sub = array(
				'SubCode' => $AllValues['0'][$i],
				'Sub' => $AllValues['0'][$i+1],
				'SubCredits' => $AllValues['0'][$i+2],
				'SubPoints' => $AllValues['0'][$i+3]
			);
			$iSubjects++;
			$SubjectDetails[$iSubjects] = $Sub;
		}
		
		$sql = "SELECT ID FROM resultsfinal WHERE RegdNo='$RegdNo' AND SemNo='$SemNo'";
		$res = mysqli_query( $db, $sql );
		$ID = '';
		if( mysqli_num_rows($res) < 1 )
		{
			$sql = "INSERT INTO resultsfinal ( RegdNo, SemNo, Name, CGPA, GPA ) VALUES ( '$RegdNo', '$SemNo', '$Name', '".$AllValues['5'][1]."', '".$AllValues['5'][2]."' )";
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
			mysqli_query($db, $sql);
		}
		
		mysqli_close($db);
		return 1;
	}