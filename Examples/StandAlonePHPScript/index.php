<?php
	// header('Content-Type: text/plain');
	ignore_user_abort(true);
	set_time_limit(0);

	unset( $_POST );
	include_once './ResultsScanner.php'; // Assuming ResultsScanner is in the same directory as this file
	
	$branches = array( 1, 2, 3, 4, 5, 6, 7, 8 );
 	$years = array( 8, 09, 10, 11, 12, 13, 14, 15 );
	$sections = array( 1, 2, 3, 4, 5, 6, 7, 8, 9, 0 );
	$sems = array( 1, 2, 3, 4, 5, 6, 7, 8 );
		
	// $backupFilename = getcwd().'/last';
	// $iRBack = 0;
	// if( file_exists($backupFilename) )
	// {
		// $handle = fopen( $backupFilename, 'r' );
		// $iRBack = fgets( $handle, 10 );
		// fclose($handle);
	// }
	// $iR = '';
	$db = mysqli_connect( DB_HOST, DB_USER, DB_PASS, DB_DBNAME );
	
	function LogToFile($logstr)
	{
		$filename = 'log.txt';
		$handle = fopen($filename, 'a');
		fwrite($handle, $logstr);
		fclose($handle);
	}
	
	if( is_bool($db) )
	{
		LogToFile("Could not connect to Database... \n");
	}
	
	$ThisClassExists = false;
	
	for( $k = 0; $k < sizeof($branches); $k++ )
	{	
		for( $i = 0; $i < sizeof($years); $i++ )
		{
			$ThisClassExists = false;
			for( $j = 0; $j < sizeof($sections); $j++ )
			{
				if( $j != 0 && !$ThisClassExists)
				{
					LogToFile('Skipping sections from 1210'.$branches[$k].(($years[$i]<10)?('0'.$years[$i]):($years[$i])).$sections[$j].('01'));
					continue;
				}
				
				for( $l = 0; $l < sizeof($sems); $l++ )
				{
					if( $l != 0 && !$ThisClassExists)
					{
						LogToFile("Skipping Sem-". $sems[$l]. " for section-" . $sections[$j] . ' from 1210'.$branches[$k].(($years[$i]<10)?('0'.$years[$i]):($years[$i])).$sections[$j].('01'));
						continue;
					}
					
					for( $iStud = 1; $iStud <= 66; $iStud++ )
					{
						$iR = '1210'.$branches[$k].(($years[$i]<10)?('0'.$years[$i]):($years[$i])).$sections[$j].(($iStud<10)?('0'.$iStud):($iStud));
						/* echo intval($iR).'<'.intval($iRBack).'<br/>';
						if( intval($iR) < intval($iRBack) )
							continue; */
						
						$res = getResultFromServer($db, $iR, $sems[$l], 2);
						//echo 'Queried...'.$iR;
						$logstr = '';
						if( $res == 1 )
						{
							$logstr = "Queried...$iR [".$sems[$l]."]...Successful!\r\n";
							if( $ThisClassExists == false )
							{
								$ThisClassExists = true;
							}
						}
						else if( $res == 2 )
						{						
							$logstr = "Queried...$iR [".$sems[$l]."]...ERROR: Network Error!\r\n";
						}
						else if( $res == 3 )
						{
							$logstr = "Queried...$iR [".$sems[$l]."]...Already Exiting in DB... Skipping!\r\n";
							if( $ThisClassExists == false )
							{
								$ThisClassExists = true;
							}
						}
						else if( $res == 0 )
						{
							$logstr = "Queried...$iR [".$sems[$l]."]...ERROR: Invalid Details!\r\n";
						}
						// echo $logstr;
						LogToFile($logstr);
					}
					
				}
			}
		}
	}
	mysqli_close($db);
	
	
	/*function shutdown()
	{
		if( $iR != '' )
		{
			$handle = fopen($backupFilename, 'w');
			fwrite($handle, $iR);
			fclose($handle);
		}
		echo 'Shutting down ....';
	}

	register_shutdown_function('shutdown');	*/