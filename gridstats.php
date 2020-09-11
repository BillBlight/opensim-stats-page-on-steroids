<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

//grid and website info we will attempt to grab this info automatically as long as your robut info is correct.
$robustURL   = "yourgrid"; //FQDN or IP to your grid/robust server
$robustPORT = "8002"; //port for your robust
$robustIP = "xxx.xxx.xxx.xxx"; //added IP for times when DNS resolution is flakey, this is now used in the fsockopen so is required.
$loginuri = "http://".$robustURL.":".$robustPORT."";

$gridname = "Long Grid Name"; //grid Long Name
$gridsname = "short name"; //grid Short Name
$website = "https://yourwebsite";
$loginscreen = "https://yourloginscreen";
$gridmap = "https://yourgridmap";

$haseconomy = "No";

//your database info
$host = "dbhost";
$user = "dbuser";
$pass = "dbpass";
$dbname = "dbname";


//we will use pagespeedonline to automatically grab images of current web pages
$apiKey = ""; //this is your google api key for pagespeedonline

//we will attempt to get info directly from the grid this will override settings above, which will ensure accuracy.
$gridinfo=file_get_contents($loginuri. '/get_grid_infos');
$gridinfos = simplexml_load_string($gridinfo);


  $gridname  = $gridinfos[0]->gridname;
  $gridsname  = $gridinfos[0]->gridnick;
  $loginscreen = $gridinfos[0]->welcome;
  $website = $gridinfos[0]->about;
  $economy = $gridinfos[0]->economy;
  $loginuri = $gridinfos[0]->login;
  
   //auto testing for economy url 
  $ecocol = "red";
  if (!empty($economy)) { $haseconomy = "Yes";}
  if ($haseconomy === "Yes"){$ecocol = "green";}

// Online / Offline with socket
$socket = @fsockopen($robustIP, $robustPORT, $errno, $errstr, 1);
if (is_resource($socket))
{
$gstatus = "ONLINE";
$color = "green";
}
else {
$gstatus = "OFFLINE";
$color = "red";
}
@fclose($socket);



$mysqli = new mysqli($host,$user,$pass,$dbname);
$presenceuseraccount = 0;
$preshguser = 0;
$monthago = time() - 2592000;
$lastmonth = time() - 2419200;
if ($pres = $mysqli->query("SELECT * FROM GridUser")) {
	while ($presrow = $pres->fetch_array()) {
		if ($luser = $mysqli->query("
    SELECT UserID, Login
 	WHERE UserID LIKE '%http%'
	AND Login < ".lastmonth."")) 
	
	{
			++$presenceuseraccount;
		}else{
			++$preshguser;
		}
	}
}



$pastmonth = 0;
if ($tpres = $mysqli->query("SELECT * FROM GridUser WHERE Logout < '".$monthago."'")) {
	$pastmonth = $tpres->num_rows;
}
$totalaccounts = 0;
if ($useraccounts = $mysqli->query("SELECT * FROM UserAccounts")) {
	$totalaccounts = $useraccounts->num_rows;
}
$rsize = 0;
$totalregions = 0;
$totalvarregions = 0;
$totalsingleregions = 0;
$totalsize = 0;
if($regiondb = $mysqli->query("SELECT * FROM regions WHERE regionName NOT LIKE '%http%';")) {
	while ($regions = $regiondb->fetch_array()) {
		++$totalregions;
		if ($regions['sizeX'] == 256) {
			++$totalsingleregions;
		}else{
			++$totalvarregions;
		}
		$rsize = $regions['sizeX'] * $regions['sizeY'];
		$totalsize += $rsize;
	}
}
$arr = ['Grid_Name' => '<b>'.$gridname.'</b>',
    'GridStatus' => '<b><font color="'.$color.'">'.$gstatus.'</b></font>',
	'InWorld' => number_format($presenceuseraccount),
	'HG_Visitors_Last_30_Days' => number_format($preshguser),
	'Local_Users_Logins_Last_30_Days' => number_format($pastmonth),
	'TotalAccounts' => number_format($totalaccounts),
	'Regions' => number_format($totalregions),
	'Var_Regions' => number_format($totalvarregions),
	'Single_Regions' => number_format($totalsingleregions),
	'Total_LandSize' => number_format($totalsize),
    'Has_Economy' => '<b><font color="'.$ecocol.'">'.$haseconomy.'</b></font>',
	'Login_URL' => $loginuri,
	'Website' => '<i><a href='.$website.'>'.$website.'</a></i>',
	'Login_Screen' => '<i><a href='.$loginscreen.'>'.$loginscreen.'</a></i>',
    'Grid_Map' => '<i><a href='.$gridmap.'>'.$gridmap.'</a></i>'];
    
if ($_GET['format'] == "json") {
	header('Content-type: application/json');
	echo json_encode($arr);
}else if ($_GET['format'] == "xml") {
	function array2xml($array, $wrap='Stats', $upper=true) {
	    $xml = '';
	    if ($wrap != null) {
	        $xml .= "<$wrap>\n";
	    }
	    foreach ($array as $key=>$value) {
	        if ($upper == true) {
	            $key = strtoupper($key);
	        }
	        $xml .= "<$key>" . htmlspecialchars(trim($value)) . "</$key>";
	    }
	    if ($wrap != null) {
	        $xml .= "\n</$wrap>\n";
	    }
	    return $xml;
	}
	header('Content-type: text/xml');
	print array2xml($arr);
}else{
	foreach($arr as $k => $v) {
		echo '<B>'.$k.': </B>'.$v.'<br>';
	}

	}
$mysqli->close();
?>
<?php
 $reqUrl1=$website;
 $reqUrl2=$loginscreen;
 $reqUrl3=$gridmap;


 $result1 = file_get_contents('https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url='.$reqUrl1.'&key='.$apiKey.'&screenshot=true');
 $result2 = file_get_contents('https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url='.$reqUrl2.'&key='.$apiKey.'&screenshot=true');
 $result3 = file_get_contents('https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url='.$reqUrl3.'&key='.$apiKey.'&screenshot=true');
 
 
 $data1 = json_decode($result1, true);
 $img1 = str_replace('_','/',$data1['lighthouseResult']['audits']['final-screenshot']['details']['data']);
 //$decoded1 = base64_decode($img1);
  echo "<br/>";
 //echo '<img src="'.$img1.'">';
 
 $data2 = json_decode($result2, true);
 $img2 = str_replace('_','/',$data2['lighthouseResult']['audits']['final-screenshot']['details']['data']);
 //$decoded2 = base64_decode($img2);
  echo "<br/>";
 //echo '<img src="'.$img2.'">';

 $data3 = json_decode($result3, true);
 $img3 = str_replace('_','/',$data3['lighthouseResult']['audits']['final-screenshot']['details']['data']);
 //$decoded3 = base64_decode($img3);
  echo "<br/>";
 //echo '<img src="'.$img3.'">';
?>

<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $gridsname ." Status" ;?></title>
        <style type="text/css">
            body {
            background: rgb(3,0,244);
            background: linear-gradient(0deg, rgba(3,0,244,1) 0%, rgba(10,250,0,1) 33%, rgba(249,249,249,1) 66%);
            }
        </style>
    </head>
    <body>



<table style="background:transparent;" border="0" cellspacing="0" cellpadding="0">
<thead>
<tr>
<th class="tablestyle" style="background-color: #ccffcc; text-align: center;" colspan="3"><span style="color: #0000ff; background-color: #ccffcc;"><strong>Site Images</strong></span></th>
</tr>
</thead>
<tbody>
<tr>
<td style="text-align: center; background:transparent;"><a href="<?php echo $website ;?>">Website</a></td>
<td style="text-align: center; background:transparent;"><a href="<?php echo $loginscreen ;?>">Login Screen</a></td>
<td style="text-align: center; background:transparent;"><a href="<?php echo $gridmap ;?>">Grid Map</a></td>
</tr>
<tr>
    <td style="background:transparent;"><a href="<?php echo $website ;?>"><?php echo '<img src="'.$img1.'">'; ?></a></td>
    <td style="background:transparent;"><a href="<?php echo $loginscreen ;?>"><?php echo '<img src="'.$img2.'">'; ?></a></td>
    <td style="background:transparent;"><a href="<?php echo $gridmap ;?>"><?php echo '<img src="'.$img3.'">';?></a></td>
</tr>
</tbody>
</table>
  </body>
</html>
