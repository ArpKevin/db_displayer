<!DOCTYPE html>
<html lang="hu">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<meta http-equiv="content-language" content="hu">
<title>DATABASE viewer</title>
<style id="inlineCSS">
 th,td {padding:5px;border: 1px solid gray}
 th {background-color: silver}
 select {padding:10px}
 a {color:green;text-decoration:none}
 q {color:blue}
 td.num {color:blue;text-align:right}
 div {box-sizing:border-box}
 p.jegyzet {font-size:80%;color:gray}
</style>
<script>
</script>
<body>
<?php

//ALAP OBJEKTUM by Z-HORV
class Viewer_obj
{
 public $tabla;
 public $muvelet;
 public $szuro;
 public $DB;
 function __construct($adatbazis)
 {
  $this->DB = mysqli_connect("localhost","root",null,$adatbazis); //FEJLESZTENI kapcsolati hiba!	   
  $this->tabla=(isset($_GET['t']) ? preg_replace("/[^0-9a-z\_]/",'',strtolower($_GET['t'])) : '');	
  $this->muvelet=(isset($_GET['m']) ? preg_replace("/[^0-9a-z\_]/",'',strtolower($_GET['m'])) : '');  
  $this->szuro='';
  $Q=mysqli_query($this->DB,"SHOW TABLES");    
  print "<h1>DATABASE viewer</h1>
  <div style='float:left;overflow-x:auto;width:24.99%;padding:5px;background-color:silver'>
  <h2><q>$adatbazis</q></h2>
	<ul>
	 <li>TÁBLÁK</li>
	 <ul>"; //0 = 'Tables_in_'.$adatbazis
	 while ($tabla = mysqli_fetch_array($Q)) print "<li><a href='?t=".$tabla[0]."&m=select'>".$tabla[0]."</a></li>";
  if ($this->tabla<>'')	  
  print "	 
	 </ul>
	 <li>MŰVELETEK (".$this->tabla.")</li>
	 <ul>
	  <li><a href='?t=".$this->tabla."&m=describe'>Szerkezet</a></li>
	  <li><a href='?t=".$this->tabla."&m=content'>Tartalom</a></li>
	  <li><a href='?t=".$this->tabla."&m=indexes'>Indexek</a></li>
	  <li><a href='?t=".$this->tabla."&m=search'>Keresés</a></li>
	 </ul>
	</ul>
	<p class='jegyzet'>Z-HORV 2024-10-17</p>
  </div>";
 }
 public function cimsor_kiir($alcim)
 {
  print "<h2><q>".$this->tabla." </q> tábla / $alcim</h2>";	
 }  
 public function valaszt()
 {	
  $this->cimsor_kiir("kiválasztva");
 }
 public function szerkezet()
 {	   
  $this->cimsor_kiir("szerkezet");
  $Q=mysqli_query($this->DB,"DESCRIBE ".$this->tabla);
  print "<table>";
  print "<tr><th>Field</th><th>Type</th></tr>";
  while ($sor = mysqli_fetch_array($Q))
	print "<tr><td>".$sor['Field']."</td><td>".$sor['Type']."</td></tr>";  
  print "</table>";
  print "<p class='jegyzet'>Total: ".mysqli_num_rows($Q)." mező</p>";
 }
 public function tartalom()
 {
  $this->cimsor_kiir("tartalom");  
  $Q=mysqli_query($this->DB,"SELECT * FROM ".$this->tabla.$this->szuro); //ha csak mezőinfo kell: " LIMIT 1"
  print "<div style='overflow-x:auto'><table style='width:99%'><tr>";  
  while ($mezo = mysqli_fetch_field($Q)) print "<th>".$mezo->name."</th>";    
  while ($sor = mysqli_fetch_array($Q,MYSQLI_NUM)) 
  {	  
   print "<tr>";   
   foreach($sor as $adat) print "<td class='".(is_numeric($adat)?"num":null)."'>$adat</td>";
   print "</tr>";
  } 
  print "<tr></table></div>";
  print "<p class='jegyzet'>Total: ".mysqli_num_rows($Q)." rekord</p>";
 }
 public function indexek()
 {
  $this->cimsor_kiir("indexek");
  $Q=mysqli_query($this->DB,"SHOW INDEX FROM ".$this->tabla);
  print "<table>";
  print "<tr><th>Key_name</th><th>Column_name</th><th>Non_unique</th></tr>";
  while ($sor = mysqli_fetch_array($Q))
	print "<tr><td ".($sor['Key_name']=="PRIMARY"?" style='background-color:yellow'":null).">".$sor['Key_name']."</td><td>".$sor['Column_name']."</td><td>".($sor['Non_unique']?"<b style='color:red'>true</b>":"<b style='color:green'>false</b>")."</td></tr>";  
  print "</table>";
  print "<p class='jegyzet'>Total: ".mysqli_num_rows($Q)." indexfájl</p>";
 } 
 public function kereses()
 {
  $this->cimsor_kiir("keresés");
  if (isset($_POST['o']))
  {   
   $o=preg_replace("/[^0-9a-z\_]/",'',strtolower($_POST['o']));
   $k=mysqli_real_escape_string($this->DB,$_POST['k']);
   $this->szuro=" WHERE $o LIKE '$k'";
   print "<p style='background-color:yellow'>...".$this->szuro."</p>";	  
   $this->tartalom();
  }
  else
  {
   $Q=mysqli_query($this->DB,"SELECT * FROM ".$this->tabla." LIMIT 1");
   print "<form method='post'>
   Oszlop:<br><select name='o'>";
   while ($mezo = mysqli_fetch_field($Q)) print "<option value='".$mezo->name."'>".$mezo->name."</option>";   
   print "</select><br>Keresendő:<br>
   <input name='k' type='text' value='%'>(enter)
   </form>";
  } 
 } 
}
//
$Viewer = New Viewer_obj("zhorv_rendeles");

print "<div style='float:left;width:74.99%;padding:10px;border:10px solid silver'>";
if ($Viewer->tabla<>'' && $Viewer->muvelet<>'')
switch ($Viewer->muvelet)
{
 case "select": case "valaszt":
   $Viewer->valaszt();
 break;
 case "describe": case "leiras":
   $Viewer->szerkezet();
 break; 
 case "content":  case "tartalom":
   $Viewer->tartalom();
 break; 
 case "indexes":  case "indexek":
   $Viewer->indexek();
 break;  
 case "search":  case "kereses":
   $Viewer->kereses();
 break;  
 default : print "Ismeretlen művelet!";
}

print "</div>";
?>
</body>
</html>