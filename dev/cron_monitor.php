<?php
$user = $_POST['user'];
$pass = $_POST['pass'];

if ($user == 'swm_user' && $pass == 'HappyP@ss') {
//Magento Cron Job Monitor. GNU/GPL
//oliver.higgins@gmail.com
//provided without warranty or support
//
//modifications by chiefair to retrieve Magento connection and database
//info, display time, connection info and all job execution states
//================================================================

/** Get current date, time, UTC and offset **/
$date   = date("Y-m-d");
$time   = date("H:i:s T");
$offset = date("P");
$utc    = gmdate("H:i:s");

/** Mage locations **/
$mageconf = '../app/etc/local.xml';  // Mage local.xml config
$mageapp  = '../app/Mage.php';       // Mage app location

/*******************
 * Let George do it
 * Get connection information from Magento's local.xml file
 *******************/

if (file_exists($mageconf)) {

$xml = simplexml_load_file('../app/etc/local.xml', NULL, LIBXML_NOCDATA);

$db['host'] = $xml->global->resources->default_setup->connection->host;
$db['name'] = $xml->global->resources->default_setup->connection->dbname;
$db['user'] = $xml->global->resources->default_setup->connection->username;
$db['pass'] = $xml->global->resources->default_setup->connection->password;
$db['pref'] = $xml->global->resources->db->table_prefix;

$tblname= $db['pref'] . "cron_schedule";

} 

else {
    exit('Failed to open ../app/etc/local.xml');
}

/* Initialize profile to be run as Magento Admin and log start of export */

require_once $mageapp;
umask(0);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

$storetimestamp = Mage::getModel('core/date')->timestamp(time());

$storetime = date("H:i:s",$storetimestamp);

/************************
 * Start HTML output
 ************************/

echo '<html><head><title>Magento Cron Schedule on ' . $date . ' ' . $time . '</title>
<meta http-equiv="refresh" content="60">
<style type="text/css">html {width: 100%; font-family:  Arial,Helvetica, sans-serif;}
body {line-height:1.0em; font-size: 100%;}
table {border-spacing: 1px; width: 100%;}
th.stattitle {text-align: left; font-size: 100%; font-weight: bold; color: white; background-color: #101010;}
th {text-align: center; font-size: 80%; font-weight: bold; padding: 5px; border-bottom: 1px solid black; border-left: 1px solid black; }
td {text-align: left; font-size: 80%; padding: 4px; border-bottom: 1px solid black; border-left: 1px solid black;}
</style>
</head><body>';

/** Output title, connection info and cron job monitor runtime **/

echo "<h2>Magento Cron Schedule Report</h2><h3>Connection: ".$db['user']."@".$db['host']."&nbsp;&nbsp;&ndash;&nbsp;&nbsp;Database: " . $db['name'] . "&nbsp;&nbsp;&ndash;&nbsp;&nbsp;Table: " . $tblname . "</h3>";
echo "<h4>Runtime: " . $date . "&nbsp;&nbsp;&nbsp;Server: " .$time . "&nbsp;&nbsp;&nbsp;Store: " .$storetime . "&nbsp;&nbsp;&nbsp;Zulu: " . $utc . " UTC</h4>";
echo "<h4>Note: All logged times are UTC, your server timezone offset is " . $offset . " hours from UTC</h4>";

/** Connect to database **/

$conn = mysql_connect($db['host'],$db['user'],$db['pass']);
@mysql_select_db($db['name']) or die(mysql_error());

//================================================================
//Pending jobs

$query='SELECT * FROM ' . $tblname . ' WHERE status ="pending" ORDER BY scheduled_at DESC' ;
$result=mysql_query($query);
$num=mysql_numrows($result);
echo '<table><tbody><tr><th class="stattitle" colspan="5">Jobs Pending: ' . $num . '</th></tr>';
echo "<tr><th>ID</th><th>Job Code</th><th>Status</th><th>Created At</th><th>Scheduled At</th>";
$i=0;
while ($i < $num) {

$schedule_id=mysql_result ($result,$i,"schedule_id");                                      
$job_code=  mysql_result($result,$i,"job_code");
$status=mysql_result ($result,$i,"status");
$created_at=mysql_result ($result,$i,"created_at");
$scheduled_at=mysql_result ($result,$i,"scheduled_at");
$executed_at=mysql_result ($result,$i,"executed_at");
$finished_at=mysql_result ($result,$i,"finished_at");

//output html
echo "<tr>";
echo "<td>".$schedule_id."</td>";
echo '<td>'.$job_code."</td>"; 
echo '<td style="color: red;">'.$status."</td>"; 
echo "<td>".$created_at."</td>"; 
echo "<td>".$scheduled_at."</td>"; 
echo "</tr>";                 
$i++;
}
echo "</tbody></table><hr>";

//================================================================
//Running jobs

$query='SELECT * FROM ' . $tblname . ' WHERE status ="running" ORDER BY executed_at DESC' ;
$result=mysql_query($query);
$num=mysql_numrows($result);
echo '<table><tbody><th class="stattitle" colspan="7">Jobs Running: ' . $num . '</th></tr>';
echo "<tr><th>ID</th><th>Job Code</th><th>Status</th><th>Created At</th><th>Scheduled At</th>";
echo "<th>Executed At</th><th>Finished At</th></tr>";
$i=0;
while ($i < $num) {

$schedule_id=mysql_result ($result,$i,"schedule_id");                                      
$job_code=  mysql_result($result,$i,"job_code");
$status=mysql_result ($result,$i,"status");
$created_at=mysql_result ($result,$i,"created_at");
$scheduled_at=mysql_result ($result,$i,"scheduled_at");
$executed_at=mysql_result ($result,$i,"executed_at");
$finished_at=mysql_result ($result,$i,"finished_at");

//output html
echo "<tr>";
echo "<td>".$schedule_id."</td>";
echo "<td>".$job_code."</td>"; 
echo "<td>".$status."</td>"; 
echo "<td>".$created_at."</td>"; 
echo "<td>".$scheduled_at."</td>"; 
echo "<td>".$executed_at."</td>"; 
echo "<td>".$finished_at."</td>"; 
echo "</tr>";                 
$i++;
}
echo "</tbody>";

//================================================================
//Succsessful jobs

$query='SELECT * FROM ' . $tblname . ' WHERE status ="success" ORDER BY executed_at DESC' ;
$result=mysql_query($query);
$num=mysql_numrows($result);
echo '<tbody><tr><th class="stattitle" colspan="7">Jobs Successful: ' . $num . '</th></tr>';
echo "<tr><th>ID</th><th>Job Code</th><th>Status</th><th>Created At</th><th>Scheduled At</th>";
echo "<th>Executed At</th><th>Finished At</th></tr>";
$i=0;
while ($i < $num) {

$schedule_id=mysql_result ($result,$i,"schedule_id");                                      
$job_code=  mysql_result($result,$i,"job_code");
$status=mysql_result ($result,$i,"status");
$created_at=mysql_result ($result,$i,"created_at");
$scheduled_at=mysql_result ($result,$i,"scheduled_at");
$executed_at=mysql_result ($result,$i,"executed_at");
$finished_at=mysql_result ($result,$i,"finished_at");

//output html
echo "<tr>";
echo "<td>".$schedule_id."</td>";
echo "<td>".$job_code."</td>"; 
echo "<td>".$status."</td>"; 
echo "<td>".$created_at."</td>"; 
echo "<td>".$scheduled_at."</td>"; 
echo "<td>".$executed_at."</td>"; 
echo "<td>".$finished_at."</td>"; 
echo "</tr>";                 
$i++;
}
echo "</tbody>";

//================================================================
//Missed jobs

$query='SELECT * FROM ' . $tblname . ' WHERE status ="missed" ORDER BY executed_at DESC' ;
$result=mysql_query($query);
$num=mysql_numrows($result);
echo '<tbody><tr><th class="stattitle" colspan="7">Jobs Missed: ' . $num . '</th></tr>';
echo "<tr><th>ID</th><th>Job Code</th><th>Status</th><th>Created At</th><th>Scheduled At</th>";
echo "<th>Executed At</th><th>Finished At</th></tr>";
$i=0;
while ($i < $num) {

$schedule_id=mysql_result ($result,$i,"schedule_id");                                      
$job_code=  mysql_result($result,$i,"job_code");
$status=mysql_result ($result,$i,"status");
$created_at=mysql_result ($result,$i,"created_at");
$scheduled_at=mysql_result ($result,$i,"scheduled_at");
$executed_at=mysql_result ($result,$i,"executed_at");
$finished_at=mysql_result ($result,$i,"finished_at");

//output html
echo "<tr>";
echo "<td>".$schedule_id."</td>";
echo "<td>".$job_code."</td>"; 
echo "<td>".$status."</td>"; 
echo "<td>".$created_at."</td>"; 
echo "<td>".$scheduled_at."</td>"; 
echo "<td>".$executed_at."</td>"; 
echo "<td>".$finished_at."</td>"; 
echo "</tr>";                 
$i++;
}
echo "</tbody>";

//================================================================
//Failed jobs

$query='SELECT * FROM ' . $tblname . ' WHERE status ="error" ORDER BY executed_at DESC' ;
$result=mysql_query($query);
$num=mysql_numrows($result);
echo '<tbody><tr><th class="stattitle" colspan="7">Jobs Failed: ' . $num . '</th></tr>';
echo "<tr><th>ID</th><th>Job Code</th><th>Status</th><th>Created At</th><th>Scheduled At</th>";
echo "<th>Executed At</th><th>Finished At</th></tr>";
$i=0;
while ($i < $num) {

$schedule_id=mysql_result ($result,$i,"schedule_id");                                      
$job_code=  mysql_result($result,$i,"job_code");
$status=mysql_result ($result,$i,"status");
$created_at=mysql_result ($result,$i,"created_at");
$scheduled_at=mysql_result ($result,$i,"scheduled_at");
$executed_at=mysql_result ($result,$i,"executed_at");
$finished_at=mysql_result ($result,$i,"finished_at");

//output html
echo "<tr>";
echo "<td>".$schedule_id."</td>";
echo "<td>".$job_code."</td>"; 
echo "<td>".$status."</td>"; 
echo "<td>".$created_at."</td>"; 
echo "<td>".$scheduled_at."</td>"; 
echo "<td>".$executed_at."</td>"; 
echo "<td>".$finished_at."</td>"; 
echo "</tr>";                 
$i++;
}
echo "</tbody></table></body></html>";

//================================================================
// End of report

mysql_close($conn);
} else {
	?>
	<form method="POST" action="/dev/cron_monitor.php">
		User: <input type="text" name="user" /><br/>
		Pass: <input type="password" name="pass" /><br/>
		<input type="submit" name="submit" value="Connect" />
	</form>
	<?php
}
