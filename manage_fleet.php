<?php
session_start();
include_once('modul/connect.php');
if (isset($_COOKIE['user'])){
	$q_fleet=$pdo->prepare("select id_f,dolj from users where id=? LIMIT 1");
	$q_fleet->execute([$_COOKIE['user']]);
	$data_fleet=$q_fleet->fetch();
}
if (isset($_SESSION['user_id']) or isset($data_fleet['id_f'])){
session_write_close();
$head='<!DOCTYPE html>
<html lang="ru-RU">
<head>
<meta charset="utf-8">
<title>УПРАВЛЕНИЕ ФЛОТОМ</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script type="text/javascript" src="js/jquery.min.js"></script>
<link rel="stylesheet" href="css/base1.css">
<link rel="stylesheet" href="css/m_fleet.css">
</head>
<body>
<div id="myModal" class="modal"><div class="modal-content" id="info"></div></div>
<header>ФЛОТ: </header>
<div class="container">
<div class="main-content">
<div class="content-wrap">';
echo $head;
echo "<main>";
echo "<figure id='logo'><img src='img/info/col/pechat.png'>";
echo "<figcaption>40998</figcaption>";
echo "</figure>";
echo "<nav>";
echo "<P class='but' style='display:inline-block;width:100%;padding:0px;'>ОТЧЕТ О НАСЕЛЕНИИ</P>";
echo "<P class='but' style='display:inline-block;width:100%;padding:0px;'>НОВОСТИ</P>";
echo "<P class='but red' style='display:inline-block;width:100%;padding:0px;'>ВЫХОД</P>";
echo "</nav>";
echo "<div id='resurs'><div class='titl'><img src='img/sector.png'></div><p id='sector'>RJ-407</p>";
echo "<table><tr><td style='min-width:33%;'><img src='img/tilium.gif'></td><td style='min-width:33%;'><img src='img/water.gif'></td><td style='min-width:33%;'><img src='img/detals.gif'></td></tr>";
echo "<tr><td><small>100%</small></td><td><small>100%</small></td><td><small>нет</small></td></tr></table></div>";
echo "</main>";
echo "<div id='left'>";
echo "<div id='norms'><div class='titl'><img src='img/norm.png'></div>";
echo "<table><tr><th></th><th>Норма<br>потребления</th><th>Норма<br>производства</th></tr>";
echo "<tr><td><img src='img/tilium.gif'></td><td><span class='sm but red'>-</span> 1.06 <span class='but sm'>+</span></td><td rowspan='3'><span class='but red sm'>-</span> 1.00 <span class='but sm'>+</span></td></tr>";
echo "<tr><td><img src='img/water.gif'></td><td><span class='sm but red'>-</span> 1.05 <span class='but sm'>+</span></td></tr>";
echo "<tr><td><img src='img/detals.gif'></td><td><span class='sm but red'>-</span> 1.00 <span class='but sm'>+</span></td></tr>";
echo "<tr><td colspan=3><span class='but' style='display:inline-block;width:35%;padding:0px;margin:20px 0px;'>статистика</span></td></tr>";
echo "</table></div>";
echo "<div id='proiz'><div class='titl'><img src='img/dobycha.png'></div>";
echo "<table><tr><th></th><th>Корабли</th><th>Мощность</th></tr>";
echo "<tr><td style='text-align:right;'><img src='img/tilium.gif'></td><td><span class='green but'>5</span></td><td>800</td></tr>";
echo "<tr><td style='text-align:right;'><img src='img/water.gif'></td><td><span class='green but'>5</span></td><td>800</td></tr>";
echo "<tr><td style='text-align:right;'><img src='img/detals.gif'></td><td><span class='green but'>5</span></td><td>800</td></tr>";
echo "<tr><td style='text-align:right;'>Простаивает</td><td><span class='green but'>5</span></td><td>800</td></tr>";
echo "<tr><td style='text-align:right;'>Сломан</td><td><span class='green but red off'>5</span></td><td>800</td></tr>";
echo "<tr><td style='text-align:right;'>Забастовка</td><td><span class='green but red off'>5</span></td><td>800</td></tr></table>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "<aside>";
echo "<div id='balans'><div class='titl'><img src='img/dobycha.png'></div>";
echo "<table><tr><th></th><th>Резерв</th><th>Производство</th><th>Потребление</th><th>Баланс</th></tr>";
echo "<tr><td><img src='img/tilium.gif'></td><td>300000</td><td>4560</td><td>10000</td><td style='color:#00ff00;'>800</td></tr>";
echo "<tr><td><img src='img/water.gif'></td><td>300000</td><td>3450</td><td>10000</td><td style='color:#00ff00;'>900</td></tr>";
echo "<tr><td><img src='img/detals.gif'></td><td>300000</td><td>5678</td><td>10000</td><td style='color:#ff0000;'>-800</td></tr>";
echo "<tr><td colspan='5'><span class='but' style='display:inline-block;width:30%;padding:0px;margin:20px 0px;'>статистика</span></td></tr>";
echo "</table></div>";
echo "<div id='transfer'>";
echo "<div class='titl'><img src='img/obmen.png'></div>";
echo "<table><tr><th></th><th>Передать<wbr> в другой<wbr> флот</th><th>Выдать<wbr> частному<wbr> лицу</th><th>Принять <wbr>пожертвование</th></tr>";
echo "<tr><td><img src='img/tilium.gif'></td><td>10000</td><td>800</td><td>800</td></tr>";
echo "<tr><td><img src='img/water.gif'></td><td>10000</td><td>900</td><td>800</td></tr>";
echo "<tr><td><img src='img/detals.gif'></td><td>10000</td><td>-800</td><td>800</td></tr></table>";
echo "</div>";
echo "</aside>";
echo "</div></div>";
echo "</body></html>";
}
?>
