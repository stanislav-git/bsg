<?php
include_once('modul/connect.php');
$fleet=1;
if (isset($_POST['fleet'])) {$fleet=$_POST['fleet'];}
if (isset($_GET['fleet'])) {$fleet=$_GET['fleet'];}
if (isset($fleet)) {
	$head='<!DOCTYPE Html>
<html lang="ru-RU">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>ИНФОРМАЦИОННАЯ ПАНЕЛЬ</title>
<link href="css/info.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="js/jquery.min.js"></script>
</head><body><div style="position:relative;min-height: 100%;margin-left:0px;margin-right:0px;background-image:url(\'img/fons/inform.jpg\'); background-size:100% 100%;">
<div id="maket"><div id="myModal" class="modal"><div class="modal-content"></div></div>';
        $qfl=$pdo->prepare("SELECT destination.name as fname, ships.fleet as fleet, destination.fuel as fuel, destination.water as water, 
destination.comp as comp, SUM(typeship.jfuel) as jfuel, SUM(typeship.cargo) as cargo, SUM(typeship.rfuel) as rfuel, SUM(typeship.dfuel) as dfuel, 
SUM(typeship.rwater) as rwater, SUM(typeship.dwater) as dwater, SUM(typeship.rcomp) as rcomp, SUM(typeship.dcomp) as dcomp, SUM(ships.human) as human
FROM ships
JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who
WHERE destination.who=?
GROUP BY ships.fleet");
	$qtype=$pdo->prepare("SELECT ships.fleet AS fleet, ships.`type` as stype, count(typeship.id) AS ncount
FROM ships
JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who
WHERE destination.who=?
GROUP BY ships.type");
        $qtype->execute([$fleet]);
        $ships_type=$qtype->fetchAll(PDO::FETCH_ASSOC);
	$ships_huge=0;
	$ships_big=0;
	$ships_small=0;
        foreach ($ships_type as $sd) {
		if ($sd['stype']<3) {$ships_huge=$ships_huge+$sd['ncount'];}
		if ($sd['stype']>2 and $sd['stype']<7) {$ships_big=$ships_big+$sd['ncount'];}
		if ($sd['stype']>6) {$ships_small=$ships_small+$sd['ncount'];}
	}
	$ships_all=$ships_huge+$ships_big+$ships_small;
	unset($sd);
//        $ships_big=count($ships_type['']);
	$qfl->execute([$fleet]);
	$fl_data=$qfl->fetch();
	echo $head;
        mb_internal_encoding('UTF-8');
	echo "<div id='head'>ФЛОТ: ",mb_strtoupper($fl_data['fname']),"</div>";
	echo "<div id='news'><div class='titl'><img src='img/event.png'></div><div id='cnews'>";
	$qnews=$pdo->prepare("SELECT * from news where fleet=0 or fleet=? order by idn LIMIT 10");
	$qnews->execute([$fleet]);
	while ($news = $qnews->fetch()) {
		echo "<p>",$news['news'],"</p>";
		echo "<span>",$news['autor'],"</span>";

	}
	echo "</div></div>";
	echo "<div id='logo'><img src='img/pechat.png'><p style='margin-top:10px;'>",$fl_data['human'],"</p></div>";
	echo "<div id='resurs'><div class='titl'><img src='img/resurs.png'></div><div id='cresurs'>
<table style='width:100%;height:100%;border-spacing: 0px;padding-top:4px;'><tr><td></td><td style='width:22%;'>ПРОИЗВОДСТВО</td><td style='width:22%;'>ПОТРЕБЛЕНИЕ</td><td style='width:22%;'>РЕЗЕРВ</td><td style='width:22%;'>НЗ</td></tr>
<tr><td><img src='img/tilium.gif'></td><td></td><td>",$fl_data['jfuel'],"/",$fl_data['rfuel'],"/</td><td></td><td>",$fl_data['fuel'],"</td></tr>
<tr><td><img src='img/water.gif'></td><td></td><td>",$fl_data['rwater'],"/</td><td></td><td>",$fl_data['water'],"</td></tr>
<tr><td><img src='img/detals.gif'></td><td></td><td>",$fl_data['rcomp'],"/</td><td></td><td>",$fl_data['comp'],"</td></tr>
<tr><td colspan=5><div class='butt'>ОТЧЕТ ПО ОБЕСПЕЧЕНИЮ</div></td></tr>
</table>";
	echo "</div></div>";                              
	echo "<div id='stat'><div class='titl'><img src='img/stat.png'></div>
<div id='statl'><div class='informer'>
<table style='width:100%; height:100%;border-spacing: 0px;padding:0px;'><tr><td colspan=3 style='height:30px;'><div class='butt'>НАСТРОЕНИЯ</div></td></tr><tr>
<td style='width:33%;vertical-align:bottom;'><img src='img/green.gif' style='width:20px;height:50%;'> <img src='img/red.gif' style='width:20px;height:50%;'></td>
<td style='width:33%;vertical-align:bottom;'><img src='img/green.gif' style='width:20px;height:90%;'> <img src='img/red.gif' style='width:20px;height:10%;'></td>
<td style='width:33%;vertical-align:bottom;'><img src='img/green.gif' style='width:20px;height:70%;'> <img src='img/red.gif' style='width:20px;height:30%;'></td></tr>
<tr style='height:34px;'><td><span class='green'>вера</span><br><span class='red'>страх</span></td><td><span class='green'>надежда</span><br><span class='red'>отчаянье</span></td><td><span class='green'>лояльность</span><br><span class='red'>анархия</span></td></tr></table>                            
</div></div>
<div id='statr'><div class='informer'>
<table style='width:100%; height:100%;border-spacing: 0px;padding:0px;'><tr><td colspan=3 style='height:30px;'><div class='butt'>СОСТАВ ФЛОТА</div></td></tr><tr>
<td>ОГРОМНЫЕ<br>",$ships_huge,"</td><td>БОЛЬШИЕ<br>",$ships_big,"</td><td>СРЕДНИЕ<br>",$ships_small,"</td></tr>
<tr><td colspan='3'>ИТОГО: ",$ships_all,"</td></tr></table>
</div></div></div>";
	echo "<div id='qvorum'><div class='titl'><img src='img/qvorum.png'></div><div class='titl2' nowrap><img src='img/project.png'><img src='img/init.png'><img src='img/supp.png'></div>
<div id='cqvorum'>
<div>
<div><div>пайкон</div><div>каприка</div><div>выборы в государственные органы власти</div></div>
<div><div>сагитарион</div><div>гименон</div><div>о признании сайлонов несовершеннолетними</div></div>
<div><div>либран</div><div>офис президента</div><div>о реорганизации системы государственной власти</div></div>
<div><div>пайкон</div><div>каприка</div><div>выборы в государственные органы власти</div></div>
<div><div>сагитарион</div><div>гименон</div><div>о признании сайлонов несовершеннолетними</div></div>
<div><div>либран</div><div>офис президента</div><div>о реорганизации системы государственной власти</div></div>
</div>
</div></div>";
	echo "<div id='project'><div class='titl'><img src='img/projects.png'></div><div class='titl2'><img src='img/project.png'><img src='img/headl.png'><img src='img/progr.png'></div>
<div id='cproject'>
<div>
<div><div>ХХХ</div><div>Анри-Рене-Альбер-Ги де Мопассан</div><div style='font-family:\"crystalregular\";font-size:20px;'>00:00:00</div></div>
<div><div>сагитарион</div><div>гименон</div><div style='font-family:\"crystalregular\";font-size:20px;'>00:00:00</div></div>
<div><div>либран</div><div>офис президента</div><div style='font-family:\"crystalregular\";font-size:20px;'>00:00:00</div></div>
<div><div>пайкон</div><div>каприка</div><div style='font-family:\"crystalregular\";font-size:20px;'>00:00:00</div></div>
<div><div>сагитарион</div><div>гименон</div><div style='font-family:\"crystalregular\";font-size:20px;'>00:00:00</div></div>
<div><div>либран</div><div>офис президента</div><div style='font-family:\"crystalregular\";font-size:20px;'>00:00:00</div></div>
</div>
</div></div>";

echo "</div></div></body></html>";
}
?>