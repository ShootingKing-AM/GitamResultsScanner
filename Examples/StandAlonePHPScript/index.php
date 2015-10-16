<?php
	header('Content-Type: text/plain');
	ignore_user_abort(true);
	set_time_limit(0);

	unset( $_POST );
	include_once './ResultsScanner.php'; // Assuming ResultsScanner is in the same directory as this file
	
	$years = array( 8, 09, 10, 11, 12, 13, 14, 15 );
	$sections = array( 1, 2, 3, 4, 5, 6, 7, 8, 9, 0 );
	$sems = array( 1, 2, 3, 4, 5, 6, 7, 8 );
	
	// var $iR; //= '12101'+((years[i]<10)?('0'+years[i]):(years[i]))+sections[j]+((iStud<10)?('0'+iStud):(iStud));
	// int $res = 0;
	
	$filename = 'log.txt';	
	
	for( $i = 0; $i < sizeof($years); $i++ )
	{
		for( $j = 0; $j < sizeof($sections); $j++ )
		{
			for( $l = 0; $l < sizeof($sems); $l++ )
			{
				for( $iStud = 1; $iStud <= 66; $iStud++ )
				{					
					$iR = '12101'.(($years[$i]<10)?('0'.$years[$i]):($years[$i])).$sections[$j].(($iStud<10)?('0'.$iStud):($iStud));
					$res = getResultFromServer($iR, $sems[$l], 2);
					//echo 'Queried...'.$iR;
					$logstr = '';
					if( $res == 1 )
					{
						$logstr = "Queried...$iR [".$sems[$l]."]...Successful!\r\n";
					}
					else if( $res == 2 )
					{						
						$logstr = "Queried...$iR [".$sems[$l]."]...ERROR: Network Error!\r\n";
					}
					else if( $res == 3 )
					{
						$logstr = "Queried...$iR [".$sems[$l]."]...Already Exiting in DB... Skipping!\r\n";
					}
					else if( $res == 0 )
					{
						$logstr = "Queried...$iR [".$sems[$l]."]...ERROR: Invalid Details!\r\n";
					}
					// echo $logstr;
					$handle = fopen($filename, 'a');
					fwrite($handle, $logstr);
					fclose($handle);
					// exit;
				}
			}
		}
	}				