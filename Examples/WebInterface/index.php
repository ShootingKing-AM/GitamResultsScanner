<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"> 
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title> Results Scanner </title>
	<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
	<link href='http://fonts.googleapis.com/css?family=Raleway:400,500,700' rel='stylesheet' type='text/css'>
	<script src="https://google-code-prettify.googlecode.com/svn/loader/run_prettify.js?lang=css&skin=sons-of-obsidian"></script>
	
</head>

<style>
	html, body
	{
		font-family: 'Raleway', sans-serif;
	}
	.clocker
	{
		margin-left: 15px;
	}
</style>
<script>
	$(function()
	{
		$('.spoilerdata').hide();
		$('#progressDiv').hide();
		
		$('.spoiler').click(function() {
			$('.'+$(this).attr('for')).toggle(200);
		});
	});
</script>
<body class="container">
	<br/><br/><br/>
	<div class="others">
	<div class="jumbotron">
		<h1 style="color:#5C895C">Results Scanner</h1> 
		<p>Get results directly from GITAM Results Database.</p> 
	</div>
	<form role="form" method="post" action="index.php">
		<div class="form-group">
			<label for="year">Year of Batch Joining :</label>
			<select multiple name="year[]" class="form-control" id="year">
			<?php
				for( $i = 2008; $i < 2016; $i++ )
				{
					if( $i == 2013 )
						echo "<option value=\"".($i%100)."\" selected>$i</option>";
					else
						echo "<option value=\"".($i%100)."\">$i</option>";						
				}
			?>
			</select>			
		</div>
		<div class="form-group">
			<label for="sem">Sem:</label>
			<select multiple name="sem[]" class="form-control" id="sem">
				<?php
					for( $i = 1; $i < 9; $i++ )
					{
						if( $i == 4 )
							echo "<option value=\"$i\" selected>$i</option>";
						else
							echo "<option value=\"$i\">$i</option>";						
					}
				?>
			</select>			
		</div>
		<div class="form-group">
			<label for="section">Section:</label>
			<select multiple name="section[]" class="form-control" id="section">
				<?php
					for( $i = 1; $i < 10; $i++ )
					{
						if( $i == 1 )
							echo "<option value=\"$i\" selected>$i</option>";
						else
							echo "<option value=\"$i\">$i</option>";							
					}
				?>
			</select>			
		</div>
		<div class="form-group">
			<label for="branch">Branch:</label>
			<select multiple name="branch[]" class="form-control" id="branch">
				<option value="01">Biotechonology - 01</option>
				<option value="02">Civil Engineering - 02</option>
				<option value="03">Computer Science & Engineering - 03</option>
				<option value="04">Electronics & Communication Engineering - 04</option>
				<option value="05">Electrical & Electronics Engineering - 05</option>
				<option value="06">Electronics & Instrumentation Engineering - 06</option>
				<option value="07">Information Technology - 07</option>
				<option value="08" selected>Mechanical Engineering - 08</option>
			</select>			
		</div>
		
		<button type="button" class="btn btn-success btn-md" onclick="onClickbactchbutton();" style="margin-top:15px" id="batchbutton">Batch it!</button>
		<button type="submit" onclick="iBIndex = 0;submitted();return false;" name="submitform" class="btn btn-default">Submit</button>
	</form>
	<br/><br/><br/>
	</div>
	<div id="progressDiv" style="position:fixed;width:100%;height:100%;background-color: #FFFFFF;">
		<div style="position: fixed;
		top: 20%;
		right: 0;
		bottom: 0;
		left: 10%;
		width: 80%;">
			
			<div><h2 id="progressHead" style="display:inline">Initializing ....</h2> <span id="duralogs" style="padding-left: 20px;vertical-align: super;"></span></div>
			<div class="progress">
				<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar"
				aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%" id="actProgressBar">
					0%
				</div>
			</div>
			
			<div><h3>Log:</h3></div>
			<div id="progressLog" class="text-info" style="height:45%;max-height:45%;overflow-y:scroll;background-color:#ffffff">
			</div>
			<div style="float:right;">
				<button type="button" class="btn btn-success btn-md" onclick="okbuttonclicked();" style="margin-top:15px" id="okbutton">Ok Thanks!</button>
			</div>
		</div>
	</div>
</body>
<script>
		var toQuery = 0;
		var qSuccess = 0;
		var qInvDetFailed = 0;
		var qNetFailed = 0;
		var Queried = 0;
		var qSkipped = 0;
		var iBIndex = 0; // Current Batch Index
		
		var ErroredRegdNos = [], NetErrorredRegdNos = [];
		var Batches = [];
		var iBatchIndex = 0;
		
		function Batch(years, sections, branchs, sems)
		{
			this.years = years;
			this.sections = sections;
			this.branchs = branchs;
			this.sems = sems;
			
			this.ErroredRegdNos = [];
			this.NetErrorredRegdNos = [];
			
			this.toQuery = 0;
			this.qInvDetFailed = 0;
			this.qNetFailed = 0;
			this.Queried = 0;
			this.qSkipped = 0;
			this.qSuccess = 0;
			
			this.timetaken = 0;
			this.startTime = 0;
			this.endtime = 0;
		}
		
		function onClickbactchbutton()
		{
			var years = []; 
			$('#year :selected').each(function(i, selected){ 
				years[i] = $(selected).val(); 
			});
			var sections = []; 
			$('#section :selected').each(function(i, selected){ 
				sections[i] = $(selected).val(); 
			});
			var branchs = []; 
			$('#branch :selected').each(function(i, selected){ 
				branchs[i] = $(selected).val(); 
			});
			var sems = []; 
			$('#sem :selected').each(function(i, selected){ 
				sems[i] = $(selected).val(); 
			});
			
			$('#progressLog').append('Batching['+iBatchIndex+'] for Years: '+years+' Sections: '+sections+' Branchs: '+branchs+' Sems: '+sems+' <br/>');
			
			var b1 = new Batch( years, sections, branchs, sems );
			// alert(iBatchIndex);
			Batches[iBatchIndex] = b1;
			// alert(b1);
			// Batches.push(b1);
			iBatchIndex++;
		}
		
		function onNetErrorRetryClick(iBatchIndex)
		{
			var NetArrayIndex = Batches[iBatchIndex].qNetFailed;
			
			toQuery = 0;
			qSuccess = 0;
			qInvDetFailed = 0;
			Queried = 0;
			qNetFailed = 0;
			qSkipped = 0;
			
			toQuery = Batches[iBatchIndex].NetErrorredRegdNos.length;
			
			$("#actProgressBar").attr('aria-valuemin', '0');
			$("#actProgressBar").attr('aria-valuemax', toQuery);
			$("#actProgressBar").attr('aria-valuenow', '0');
			$('.others').css('opacity','0.1');
			$('.others').off();
			$("#progressDiv").show(100);
			$('#okbutton').hide();
			
			$('#progressLog').append('Starting Retry hoping success for NetErrors for Batch['+iBatchIndex+']... <br/>');
			
			var i = 0;
			
			for( i = 1; i < (NetArrayIndex+1); i++ )
			{
				var iRegdNo = Batches[iBatchIndex].NetErrorredRegdNos[i];
				var iR = substr(iRegdNo, 0, 10);
				var semn = substr(iRegdfNo, 11, 1);
				
				var str='Getting data for '+ iR +' for sem '+semn;
				
				$.ajax({
					type: "POST",
					url : "ResultsScanner.php",    // Assuming ResultsScanner.php is in the same directory as this file.
					data: "rg=" + iR + "&sem=" + sem + "&mode=2",
					ajdetails: {iReg: iR, sem: semn, BIndex: iBatchIndex},
					success: function(data)
					{
						data.trim();
						// alert(data);
						$('#progressHead').html( 'Working ... ' + '(<font class="text-success">'+Queried+'</font>/<font class="text-info">'+toQuery+'</font>)' );
						// alert(data);
						if(data == '1')
						{				
							// alert(sems);
							$('#progressLog').append('Getting data for '+ this.ajdetails.iReg +' for sem '+this.ajdetails.sem + '... <font class="text-success">SUCCESS! </font><br/>');
							updateProgressBar(1, this.ajdetails.iReg+':'+this.ajdetails.sem, this.ajdetails.BIndex);
						}
						else if( data == '2' )
						{
							$('#progressLog').append('Getting data for '+ this.ajdetails.iReg +' for sem '+this.ajdetails.sem + '... <font class="text-danger">Network ERROR! </font><br/>');
							updateProgressBar(2, this.ajdetails.iReg+':'+this.ajdetails.sem, this.ajdetails.BIndex);										
						}
						else if( data == '3' )
						{
							$('#progressLog').append('Getting data for '+ this.ajdetails.iReg +' for sem '+this.ajdetails.sem + '... <font class="text-info">Already Exiting in DB... SKIPPING!</font><br/>');
							updateProgressBar(3, this.ajdetails.iReg+':'+this.ajdetails.sem, this.ajdetails.BIndex);											
						}	
						else
						{
							// alert(sems);
							$('#progressLog').append('Getting data for '+ this.ajdetails.iReg +' for sem '+this.ajdetails.sem + '... <font class="text-danger">Invalid Details FAILED! </font><br/>');
							updateProgressBar(0, this.ajdetails.iReg+':'+this.ajdetails.sem, this.ajdetails.BIndex);
						}     
					}  
				});
			}
		}
		
		function okbuttonclicked()
		{
			toQuery = 0;
			qSuccess = 0;
			qInvDetFailed = 0;
			Queried = 0;
			qNetFailed = 0;
			qSkipped = 0;
			
			$('.others').on();
			$('#progressDiv').hide();
			$('.others').css('opacity', '1');
		}
		
		var notChanging = true;
		
		var startTime = 0;
		var etr = 0;
		var endtime = 0;
		var Timer;
		
		function updateProgressBar(code, RegdNo, iBatchIndex)
		{
			// if( !Batches[iBatchIndex] )
				// return;
				
			while( notChanging != true ){ }
			notChanging = false;
			
			Queried++;
			// alert('Queried++ '+Queried+' for '+ RegdNo );
			Batches[iBatchIndex].Queried++;
			
			if( code == 1 )
			{
				qSuccess++;
				Batches[iBatchIndex].qSuccess++;
			}
			else if( code == 2 )
			{
				qNetFailed++;
				Batches[iBatchIndex].qNetFailed++;
				Batches[iBatchIndex].ErroredRegdNos[Batches[iBatchIndex].qNetFailed] = RegdNo;
			}
			else if( code == 3 )
			{
				qSkipped++;
				Batches[iBatchIndex].qSkipped++;
			}
			else
			{
				qInvDetFailed++;
				Batches[iBatchIndex].qInvDetFailed++;
				Batches[iBatchIndex].NetErrorredRegdNos[Batches[iBatchIndex].qInvDetFailed] = RegdNo;
			}
			
			$("#actProgressBar").attr('aria-valuenow', ($("#actProgressBar").attr('aria-valuenow')+1));
			// alert(((toQuery-Queried)/toQuery));
			var percent = (((1-((toQuery-Queried)/toQuery))*100).toFixed(2));
			$("#actProgressBar").css('width', percent+'%');
			$("#actProgressBar").text( percent+'%' );
		
			percent = (((1-((Batches[iBatchIndex].toQuery-Batches[iBatchIndex].Queried)/toQuery))*100).toFixed(2));
			if( percent == 100 )
			{
				// alert('test');
				endtime = new Date();
				var timetaken = new Date(endtime.getTime()-startTime.getTime());
				
				Batches[iBatchIndex].timetaken = timetaken;
				Batches[iBatchIndex].startTime = startTime;
				Batches[iBatchIndex].endtime = endtime;
				
				$('#progressLog').append('Completed. <br/>');
				$('#progressLog').append('----------------------------------------------------------------------------------------<br/>');
				$('#progressLog').append('Report: <br/>');
				$('#progressLog').append('Queried: <font class="text-success">'+(qSuccess+qNetFailed+qInvDetFailed)+'/'+toQuery+'</font><br/>');
				$('#progressLog').append('Failed due to Network Error: <font class="text-danger">'+qNetFailed+'</font>    <button type="button" class="btn btn-success btn-sm" onclick="onNetErrorRetryClick('+iBatchIndex+');" style="/* margin-top:15px */" id="retrynbutton">Retry</button><br/>');
				$('#progressLog').append('Failder due to Invalid Details: <font class="text-danger">'+qInvDetFailed+'</font><br/>');
				$('#progressLog').append('Skipped : <font class="text-info">'+qSkipped+'</font><br/>');
				$('#progressLog').append('----------------------------------------------------------------------------------------<br/>');
				
				submitted();				
			}				
			
			var $t = $('#progressLog');
			$t.scrollTop($('#progressLog')[0].scrollHeight);			
			notChanging = true;
		}
		
		function submitted()
		{
			if( iBatchIndex <= iBIndex )
			{
				
				$('#okbutton').show();
				$('#progressHead').html( 'Done! <font class="text-success">'+qSuccess+'</font>/<font class="text-info">'+toQuery+'</font>' );
				
				clearInterval(Timer);
				return;
			}
			// alert('iBIndex: '+iBIndex+' lenb: '+Batches.length);
			
			var years = Batches[iBIndex].years;
			var sections = Batches[iBIndex].sections;
			var branchs = Batches[iBIndex].branchs;
			var sems = Batches[iBIndex].sems;
			
			if( iBIndex == 0 )
			{
				toQuery = 0;
				qSuccess = 0;
				qInvDetFailed = 0;
				Queried = 0;
				qNetFailed = 0;
				qSkipped = 0;
				
				for( var p = 0; p < iBatchIndex; p++ )
				{
					toQuery += Batches[p].years.length*Batches[p].sections.length*Batches[p].branchs.length*Batches[p].sems.length*66;
				}
				
				
				$('#progressHead').html('Initializing...');
				$("#actProgressBar").attr('aria-valuemin', '0');
				$("#actProgressBar").attr('aria-valuemax', toQuery);
				$("#actProgressBar").attr('aria-valuenow', '0');
				$('#actProgressBar').css('width', '0%');				
				$('#progressLog').append('Starting... <br/>');				
			}
			
			Batches[iBIndex].toQuery = Batches[iBIndex].years.length*Batches[iBIndex].sections.length*Batches[iBIndex].branchs.length*Batches[iBIndex].sems.length*66;
			
			$('.others').css('opacity','0.1');
			$('.others').off();
			$("#progressDiv").show(100);
			$('#okbutton').hide();
			
			startTime = new Date();
			
			Timer = setInterval(function()
			{
				var currDate = new Date();
				// console.log( currDate.getTime() - startTime.getTime() );
				// var millisecs = Math.abs(currDate.getTime() - startTime.getTime());///Queried 
				
				// var seconds = millisecs/1000;
				// millisecs = millisecs%1000;
				
				// console.log(seconds);
				var diffDate = new Date(currDate.getTime() - startTime.getTime());
				// console.log( diffDate );
				var PerQueryMS = Math.ceil((diffDate.getTime())/Queried);				
				var etaMS = PerQueryMS*(toQuery-Queried);
				
				var PerQuerySecs = Math.floor(PerQueryMS/1000);
				var PerQueryMs = PerQueryMS%1000;
				
				var diffTs = diffDate.getTime();
				var diffHours = Math.floor(diffTs/(60*60*1000));
				diffTs -= diffHours*(60*60*1000);
				
				var diffMins = Math.floor(diffTs/(60*1000));
				diffTs -= diffMins*(60*1000);
				
				var diffSecs = Math.floor(diffTs/1000);
				
				var etaHours, etaMins, etaSecs;
				etaHours = Math.floor(etaMS/(60*60*1000));
				etaMS -= etaHours*(60*60*1000);
				
				etaMins = Math.floor(etaMS/(60*1000));
				etaMS -= etaMins*(60*1000);
				
				etaSecs = Math.floor(etaMS/1000);
				
				$('#duralogs').html('<span class="label clocker label-success">'+(PerQuerySecs+'.'+PerQueryMs)+'s/query</span>'+
						'<span class="label clocker label-info">'+(diffHours+':'+diffMins+':'+diffSecs)+'</span>' + 
						'<span class="label clocker label-warning">'+(etaHours+':'+etaMins+':'+etaSecs)+'</span>');
			}, 1000);
			
			var i = 0, j = 0, k = 0, l = 0, iStud = 0;
			
			for( i = 0; i < years.length; i++ )
			{
				for( j = 0; j < sections.length; j++ )
				{
					for( k = 0; k < branchs.length; k++ )
					{
						for( l = 0; l < sems.length; l++ )
						{
							for( iStud = 1; iStud < 67; iStud++ )
							{
								// alert( "bracke : " + years[i] );
								var iR = '121'+branchs[k]+((years[i]<10)?('0'+years[i]):(years[i]))+sections[j]+((iStud<10)?('0'+iStud):(iStud));
								// alert(iR);
								// alert(iR);
								// break;
								// $('#progressLog').append();
								// var str='Getting data for '+ iR +' for sem '+sems[l];
								
								$.ajax({
									type: "POST",
									url : "getfuncs.php",    
									data: "rg=" + iR + "&sem=" + sems[l] + "&mode=2",
									ajdetails: {iReg: iR, sem: sems[l], BIndex: iBIndex, iStartTime: startTime},
									success: function(data)
									{
										data.trim();
										// alert(data);
										$('#progressHead').html( 'Working ... ' + '(<font class="text-success">'+Queried+'</font>/<font class="text-info">'+toQuery+'</font>)');
										// alert(data);
										if(data == '1')
										{				
											// alert(sems);
											updateProgressBar(1, this.ajdetails.iReg+':'+this.ajdetails.sem, this.ajdetails.BIndex);
											$('#progressLog').append('- '+ this.ajdetails.iReg +' ['+this.ajdetails.sem + ']... <font class="text-success">SUCCESS! </font><br/>');
										}
										else if( data == '2' )
										{
											updateProgressBar(2, this.ajdetails.iReg+':'+this.ajdetails.sem, this.ajdetails.BIndex);										
											$('#progressLog').append('- '+ this.ajdetails.iReg +' ['+this.ajdetails.sem + ']... <font class="text-danger">Network ERROR! </font><br/>');
										}
										else if( data == '3' )
										{
											updateProgressBar(3, this.ajdetails.iReg+':'+this.ajdetails.sem, this.ajdetails.BIndex);											
											$('#progressLog').append('- '+ this.ajdetails.iReg +' ['+this.ajdetails.sem + ']... <font class="text-info">Already Exiting in DB... SKIPPING!</font><br/>');
										}	
										else
										{
											// alert(sems);
											updateProgressBar(0, this.ajdetails.iReg+':'+this.ajdetails.sem, this.ajdetails.BIndex);
											$('#progressLog').append('- '+ this.ajdetails.iReg +' ['+this.ajdetails.sem + ']... <font class="text-danger">Invalid Details FAILED! </font><br/>');
										}     
									}  
								});
							}
						}
					}
				}
			}		
			iBIndex++;
			// alert( 'leaving submitted');
		}
</script>
</html>