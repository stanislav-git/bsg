<?php
session_start(); // стартуем сессию, PHP записывает в нее данные находя их по загруженным кукам если они уже есть
if (isset($_SESSION['user_id'])){
	if ($_SESSION['user_id']==0){
	include_once('modul/connect.php');
	include_once('modul/funct.php');
	echo "<!DOCTYPE Html>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<link href='css/adm.css' rel='stylesheet' type='text/css'>
<script type='text/javascript' src='js/jquery.min.js'></script>
</head>
<body>
<div id='maket'>
<div id='left'>
<a href='logout.php?logout'><img src='img/power.png' style='width:40px;height:auto;'></a>
<a href='admin.php?log'><p>Логины и пароли</p></a>
<a href='admin.php?person'><p>Персонажи</p></a>
<hr>
<a href='admin.php?news'><p>Новости и События</p></a>
<hr>
<a href='admin.php?maps'><p>Карта, аномалии и ресурсы</p></a>
<a href='admin.php?scan'><p>Исследования</p></a>
<hr>
<a href='admin.php?fleet'><p>Флоты</p></a>
<a href='admin.php?ships'><p>Корабли</p></a>
<a href='admin.php?rapt'><p>Рапторы</p></a>
<a href='admin.php?typeship'><p>Типы кораблей</p></a>
<hr>
<a href='admin.php?project'><p>Проекты</p></a>
<hr>
<a href='admin.php?black'><p>Черный рынок</p></a>
<hr>
<a href='admin.php?inform'><p>Инфо панель флота</p></a>
</div>
<div id='cont'>";
//новости и события
if (isset($_GET['news'])){
echo "<div id='myModal' class='modal'><div id='mnews' class='modal-content' style=''>";
echo "</div></div>";
$q_news=$pdo->query("select * from news order by fleet ASC,timnews DESC");
$q_news->execute();
	echo "<h2>Новости и события</h2>";
	echo "<h3><a href=# onclick='btn3(); return false;'>Добавить новость</h3></p>";
	echo "<table style='margin-left:30px;'>";
	$flnews=0;
	echo "<tr><td colspan=3><h3>Общий канал</h3></td></tr>";
while ($news=$q_news->fetch()){
	if ($news['fleet']<>$flnews){
		if (ask_name($news['fleet'])=='' and $news['fleet']<>999) {$nam_fl='Флот потерян';} elseif ($news['fleet']==999){$nam_fl='Клуб';} else {$nam_fl=ask_name($news['fleet']);}
	echo "<tr><td colspan=3><h3>",$nam_fl,"</h3></td></tr>";
	$flnews=$news['fleet'];
	}
	echo "<tr><td><a href=# onclick='btn2(",$news['timnews'],",",$news['fleet'],"); return false;'>",date("d.m.Y H:i:s",$news['timnews']),"</a></td><td><a href=# onclick='btn2(",$news['timnews'],",",$news['fleet'],"); return false;'>",$news['autor'],"</a></td><td><a href=# onclick='btn2(",$news['timnews'],",",$news['fleet'],"); return false;'>",mb_substr($news['news'],0,200),"</a></td></tr>";
}
echo "</table>";
}
//Проекты
if (isset($_GET['project'])){
echo "<div id='myModal' class='modal'><div id='projdet' class='modal-content' style=''>";
echo "</div></div>";
$q_proj=$pdo->query("select id, nazv, flag, fuel, water, comp, id_f,descrip from project order by id_f,flag,type,nazv");
$q_proj->execute();
	echo "<h2>Проекты</h2>";
	echo "<table style='margin-left:30px;'>";
	$fleet=0;
	while ($proj_data=$q_proj->fetch()){
		if ($fleet<>$proj_data['id_f']) {if ($proj_data['id_f']==999){echo "<tr><td colspan=5><hr><h3>Клуб</h3></td></tr>";$fleet=999;} else {echo "<tr><td colspan=5><h3>",ask_name($proj_data['id_f']),"</h3></td></tr>";$fleet=$proj_data['id_f'];}}
		echo "<tr";
		if ($proj_data['flag']==1){ echo " style='background-color:yellow';";}
		echo "><form action='jobs/project.php' method='post'><td><input type='hidden' name='idp' value='",$proj_data['id'],"'>",$proj_data['nazv'],"</td>";
		echo "<td>",mb_strcut($proj_data['descrip'],0,100),"</td><td>",status($proj_data['flag']),"</td>";
		echo "<td><input type='button' name='edit' value='ПРОСМОТР' id='myBtn1' onclick='btn1(",$proj_data['id'],"); return false;'></td><td><input type='submit' name='del' value='УДАЛИТЬ'></td>";
		echo "</form></tr>";		
	}
	echo "</table>";
}
//Типы кораблей
if (isset($_GET['typeship'])){
	$q_type_ship=$pdo->query("SELECT * from typeship order by sizz, purp");
	$q_type_ship->execute();
	$type_ship=$q_type_ship->fetch();

	echo "<h2>Типы кораблей</h2>";
	echo "<div class='typeship'><form action='jobs/typeship.php' method='post'><table class='typeship'><tr><td colspan=2><b>Размер</b></td><td colspan=2><b>Назначение</b></td><td colspan=2><b>Прыжок</b></td></tr>";
	echo "<tr><td colspan=2><select name='sizz'><option value='1'>Огромный</option>
<option value='2'>Большой</option>
<option value='3'>Средний</option></select></td>";
	echo "<td colspan=2><input type='text' name='type' value='' size='15' maxlength='20'></td>";
	echo "<td><img src='img/tilium.gif'></td><td><input type='number' min='1' max='10000' name='jump' value='1' size='4' size='10' maxlength='10'></td><td rowspan=8> <input type='submit' name='add_type' value='Добавить'></tr>";
	echo "<tr><td colspan=6 align='center'><hr><b>Расход ресурса в цикл</b></td></tr>";
	echo "<td><img src='img/tilium.gif'></td><td><input type='number' min='0' max='10000' name='rfuel' value='0' size='10' maxlength='10'></td>";
	echo "<td><img src='img/water.gif'></td><td><input type='number' min='0' max='10000' name='rwater' value='0' size='10' maxlength='10'></td>";
	echo "<td><img src='img/detals.gif'></td><td><input type='number' min='0' max='10000' name='rcomp' value='0' size='10' maxlength='10'></td></tr>";
	echo "<tr><td colspan=6 align='center'><hr><b>Добыча ресурсов</b></td></tr>";
	echo "<td><img src='img/tilium.gif'></td><td><input type='number' min='0' max='10000' name='dfuel' value='0' size='10' maxlength='10'></td>";
	echo "<td><img src='img/water.gif'></td><td><input type='number' min='0' max='10000' name='dwater' value='0' size='10' maxlength='10'></td>";
	echo "<td><img src='img/detals.gif'></td><td><input type='number' min='0' max='10000' name='dcomp' value='0' size='10' maxlength='10'></td></tr>";
	echo "<tr><td colspan=6 align='center'><hr><b>Возврат ресурсов при разборе корабля</b></td></tr>";
	echo "<td><img src='img/tilium.gif'></td><td><input type='number' min='0' max='10000' name='nfuel' value='0' size='10' maxlength='10'></td>";
	echo "<td><img src='img/water.gif'></td><td><input type='number' min='0' max='10000' name='nwater' value='0' size='10' maxlength='10'></td>";
	echo "<td><img src='img/detals.gif'></td><td><input type='number' min='0' max='10000' name='ncomp' value='0' size='10' maxlength='10'></td></tr>";
	echo "</table></form></div>";
	echo "<hr>";
	echo "<div style='float:left;margin-left:50px;'>Выберите размер: <select name='sizz' id='type_siz' onchange='typechange(this);'><option value='0'>не выбран</option><option value='1'>Огромный</option><option value='2'>Большой</option><option value='3'>Средний</option></select>&nbsp;&nbsp; </div>";
	echo "<div id='sel_purp' style='float:left;width:60%;'></div>";
	echo "<div id='type_size' class='typeship' style='margin-top:50px;'> </div>";
	echo "<script>
	function typechange(select){
var selectedOption = select.options[select.selectedIndex];
    $.ajax({
         type: 'POST',
         url: 'modul/typesh.php',
         data: {size:selectedOption.value},
         success: function(html){
             $('#sel_purp').html(html);
	     $('#type_size').html('');
	     $('#type_siz').prop('disabled',true);
         }
    });
    return false;
}
</script>";
}
//Персонажи
if (isset($_GET['person'])){
	$dolj=array(
	0=>'нет',
	1000=>'Президент',
	1001=>'Помощник Президента',
	1002=>'Делегат Кворума',
	1003=>'Специалист',
	1004=>'Законодательная власть',
	2000=>'Командир',
	2001=>'Старший Помощник',
	2002=>'Пилот',
	2003=>'Военный',
	3000=>'Представитель Совета',
	4000=>'Представитель Мятежного Совета'
	);
	$q_fleet=$pdo->query("select who,name,enemy from destination where who<1000 order by name");
	$q_fleet->execute();
	$order=' enemy,name';
	if (isset($_GET['sort'])){
		if ($_GET['sort']=='name'){$order=' enemy, name';}
		if ($_GET['sort']=='fleet'){$order=' id_f, name';}
		if ($_GET['sort']=='dolj'){$order=' dolj, name';}
	}
	$quer='select id,name,dolj,access,enemy,id_f,live from users order by'.$order;
	$q_person=$pdo->query($quer);
	$q_person->execute();
	echo "<h2>Игроки и доступы</h2>";
	echo "<table style='margin-left:50px;width:50%;'><tr><th colspan=5>Новый персонаж</th></tr>";
	echo "<tr><form action='jobs/users.php' method='post'><td><input type='text' name='user' value=''></td>";
	echo "<td><select name='id_f'><option value='0'>Не назначен</option>";
	while ($fleet=$q_fleet->fetch()){
		echo "<option value='",$fleet['who'],"'";
		echo ">",$fleet['name'],"</option>";
	}
	echo "</select></td>";
	echo "<td><select name='access'>";
	foreach ($dolj as $key => $dol){
		echo "<option value='",$key,"'";
		echo ">",$dol,"</option>";
	}
	echo "</select></td>";
	unset($key);
	unset($dol);
	echo "<td><input type='checkbox' value='1' name='sylon'></td><td align='right'><input type='submit' name='addusers' value='Добавить'></td></form></tr></table>";
	echo "<hr><table style='margin-left:50px;width:50%;'><tr><th><a href='admin.php?sort=name&person'>Имя</a></th><th><a href='admin.php?sort=fleet&person'>Флот</a></th><th><a href='admin.php?sort=dolj&person'>Должность</a></th><th colspan=2 align='left'>Сайлон</th></tr>";
	while ($person=$q_person->fetch()) {
		echo "<tr";
		if ($person['live']==0) {echo " style='background-color:red;'";}
		echo "><form action='jobs/users.php' method='post'><td><input type='hidden' name='id' value='",$person['id'],"'><input type='text' name='user' value='",$person['name'],"'></td>";
		echo "<td><select name='id_f'><option value='0'>Не назначен</option>";
		$q_fleet->execute();
		while ($fleet=$q_fleet->fetch()){
			echo "<option value='",$fleet['who'],"'";
			if ($fleet['who']==$person['id_f']) {echo " selected";}
			echo ">",$fleet['name'],"</option>";
		}
		echo "</select></td>";
		echo "<td><select name='access'>";
		foreach ($dolj as $key=>$dol){
			echo "<option value='",$key,"'";
			if ($person['dolj']==$key) {echo " selected";}
			echo ">",$dol,"</option>";
		}
		echo "</select></td>";
		echo "<td><input type='checkbox' value='1' name='sylon'";
		if ($person['enemy']==1){echo " checked";}
		echo "></td><td align='right' nowrap><input type='submit' name='editusers' value='Сохранить'> ";
		if ($person['live']==1) {echo "<input type='submit' name='killuser' value='Убит'>";}
		if ($person['live']==0) {echo "<input type='submit' name='liveuser' value='Жив'>";}
		echo "</td></form></tr>";
	}
	echo "</table>";		
}
//инфо панель
if (isset($_GET['inform'])){
	$qfl=$pdo->query("SELECT who,name from destination where who <1000 order by who");
	$qfl->execute();
	echo "<h2>Открыть инфопанель флота</h2>";
	echo "<form method='get' action='inform.php'><p>Флот: <select name='fleet'>";
	while ($fleet=$qfl->fetch()) {
		echo "<option value='",$fleet['who'],"'>",$fleet['name'],"</option>";
	}
	echo "</select> <input type='submit' value='Вывод на экран'></form>";
}
//исследования
if (isset($_GET['scan'])){
	$qfl=$pdo->query("SELECT who,name from destination where who <1000 order by who");
	$qfl->execute();
	$stm = $pdo->prepare("SELECT DISTINCT destination.`name`, scanning.id_ano, scanning.`level`, anom.map, anom.anomaly, anom.scanned, scanning.report
FROM scanning JOIN destination ON destination.who = scanning.who JOIN anom ON scanning.id_ano=anom.id WHERE scanning.who=? ORDER BY anom.map");
	$qmap=$pdo->prepare("SELECT maps.id_map as idm, maps.name as mname, count(anom.id) as mano, count(scanning.id_ano) as mdet from maps left JOIN anom ON maps.id_map=anom.map 
left JOIN scanning ON anom.id=scanning.id_ano AND scanning.who=? GROUP BY maps.id_map");
	echo "<div id='tabss'>";
	while ($rfl = $qfl->fetch()) {
		$rwho=$rfl['who'];	
//		$stm->execute([$rwho]);
//		$anom_data=$stm->fetchall(PDO::FETCH_ASSOC);
		echo "<div class='tab'><input type='radio' id='tab-",$rfl['who'],"'";
		echo " name='tab-group-1'><label for='tab-",$rfl['who'],"'";
		echo ">",$rfl['name'],"</label><div class='content'>";
		$tr=0;
		echo "<div id='coord'>";
		$qmap->execute([$rwho]);
		while ($map=$qmap->fetch()) {
  			if ($tr==0){                                       
    				echo "<div>";       
  			}
  			echo "<div class='cel'><a href='#'";
  			echo ">",$map['mname'],"<br>(",$map['mano']," | ",$map['mdet'],")</a></div>";
  			if ($tr==5) {
    				echo "</div>";
    				$tr=0;
			} else {
				$tr=$tr+1;
			}
		}
		echo "</div></div></div>";
	}
	echo "</div>";
}

//login
if (isset($_GET['log'])){
  echo "<h2>Логины и пароли</h2>";
  if (isset($_SESSION['err'])){echo "<div id='err'>",$_SESSION['err'],"</div>";unset($_SESSION['err']);}
  echo "<table><tr><th>Логин</th>";
  echo "<th>Пароль</th>";
  echo "<th>Где</th>";
  echo "<th></th>";
  echo "<th>Время<br>последних<br>действий</th>";
  echo "<th>Сессия</th>";
  echo "<th></th></tr>";
  $fleets = $pdo->query("SELECT who, name, locat, tim, sid FROM destination WHERE who<2000 ORDER BY `name`");
  while ($row = $fleets->fetch()) {
    echo "<tr><form method='post' action='jobs/loged.php'><td><input type='hidden' name='id' value='",$row['who'],"'>";
    echo "<input type='text' name='name' size='20' maxlenght='50' value='",$row['name'],"'></td>";
    echo "<td><input type='text' size='9' maxlenght='10' name='pass' placeholder='новый пароль' value='' style='color:#F02020';></td>";
    if ($row['who']>1000) {
    	echo "<td> </td>";
    } else {
    	echo "<td><input type='number' size='2' min='1' max='42' name='loc' value='",$row['locat'],"'></td>";
    }
    echo "<td><input type='checkbox' name='sid' value='",$row['who'],"'";
    if ($row['who']>1000){echo " disabled";}	
    echo "></td>";
    echo "<td>",date("d/m H:i:s",$row['tim']),"</td>";
    echo "<td>",$row['sid'],"</td>";
    echo "</td><td nowrap><input type='submit' name='save' value='Сохранить'></td>";
    echo "</form></tr>";
  }
  echo "</table>";
}


//Корабли
if (isset($_GET['ships'])){
  if ($_GET['ships']==1){
  	echo "<script>alert('Слишком много кораблей, флот невозможно создать, обратитесь к разработчику.');</script>";
  }
echo "<div id='myModal' class='modal'><div id='detail' class='modal-content'>";
echo "<span class='close'>&times;</span><h3>Информация о флоте</h3><p>Состав (список кораблей с листингом)</p>
<p>Население: ХХХХХ чел и 5 сайлонов</p><p>Топливо: НЗ/Резерв/потребление/На прыжок</p>
<p>Вода: НЗ/Резерв/потребление</p><p>Запчасти: НЗ/Резерв/потребление</p>
<h3>Дейстрия с флотом</h3><p>Установить к ангару (логины/пароли для рапторов)</p>";
echo "</div></div>";
	echo "<h2>Корабли</h2>";
	$fl_data = $pdo->query("SELECT destination.name as fname, ships.fleet as fleet, resurs.fuel as fuel, resurs.water as water,
resurs.comp as comp, SUM(typeship.jfuel) as jfuel, SUM(typeship.cargo) as cargo, SUM(typeship.rfuel) as rfuel, SUM(typeship.dfuel) as dfuel, 
SUM(typeship.rwater) as rwater, SUM(typeship.dwater) as dwater, SUM(typeship.rcomp) as rcomp, SUM(typeship.dcomp) as dcomp, 
SUM(ships.human) as human
FROM ships
JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who
LEFT JOIN resurs ON ships.fleet=resurs.id_f
WHERE destination.who<1000
GROUP BY ships.fleet");

	$ship_data=$pdo->prepare("(SELECT ships.id AS ids, ships.name AS name, ships.type AS class, ships.fleet AS fleet, ships.human AS human,
ships.repair AS giper, typeship.jfuel AS jfuel,typeship.sizz AS sizz, typeship.purp AS purp
FROM ships
JOIN typeship ON ships.type=typeship.id
WHERE ships.id= ? LIMIT 1) union
(SELECT ships.id AS ids, ships.name AS name, ships.type AS class, ships.fleet AS fleet, ships.human AS human, ships.repair AS giper,
typeship.jfuel AS jfuel,typeship.sizz AS sizz, typeship.purp AS purp
FROM ships
JOIN typeship ON ships.type=typeship.id
WHERE ships.id<>? and ships.fleet= ? ORDER BY typeship.sizz, typeship.purp,ships.name LIMIT 300)");
	$ship_data->execute(array(0,0,0));
//новый корабль
	echo "<div style='border:1px solid green;margin-left:0px;padding:5px;'><h4>Создать новый корабль</h4>";
	echo "<form method='post' action='jobs/ships.php'><input type='text' name='ship' value='' size='18' placeholder='Название'>";
	echo class_size(array("sizz"=>-1,"purp"=>0));
	echo " <input type='number' min='0' max='10000' step='1' value='0' name='human' placeholder='Население' style='width:6em;'> <input type='submit' name='add_ship' value='ADD'></form></div>";
	$rwq=$ship_data->fetchAll(PDO::FETCH_ASSOC);
	$fl_d=$pdo->query("select who as fleet, name as fname from destination where who<1000 order by who");
	$fl_d->execute();
	$fleets=$fl_d->fetchAll(PDO::FETCH_ASSOC);
//Флот 0
echo "<div style='float:left;width:100%; margin-top:10px;'>";
	foreach ($rwq as $rw0) {
		echo "<form method='post' action='jobs/ships.php'><p style='white-space:nowrap;line-height:30px;";
        	if ($rw0['giper']>0) {echo "background-color:red;";}
		echo "'><input type='hidden' name='ids' value='",$rw0['ids'],"'>";
		echo class_size($rw0);
		echo " <input type='text' name='flname' size='18' value='",$rw0['name'],"'>";
		echo " флот: ";
		echo "<select id='fleet' name='fleet'>";
		echo "<option value='0' selected>0</option>";
		foreach ($fleets as $fl){
        		echo "<option value='",$fl['fleet'],"'";
			echo ">",$fl['fname'],"</option>";
		}
        	echo "</select>";
        	unset($fl);
		echo " население: <input type='number' name='human' min='0' max='10000' step='1' value='",$rw0['human'],"'><wbr> <button type='button' id='myBtn' onclick='btn(",$rw0['ids'],"); return false;'>доп. инфо</button>
 <input type='submit' name='savship' value='Сохранить'> <input type='submit' name='addfleet' value='Создать флот'> <input type='submit' name='delship' value='Удалить'></p></form><hr>";
	}
	unset($rw0);
	echo "<h4>Флот не назначен - корабли не активны, это брошенные корабли.</h4></div>";
//корабли во флотах	
	$qnorms=$pdo->prepare("select n2, n3 from norms where id_f=? LIMIT 1");
	echo "<h2>Корабли во флотах</h2>";
	$fl_data->execute();
	while ($row = $fl_data->fetch()) {
		$qnorms->execute([$row['fleet']]);
		$norms=$qnorms->fetch();
    		echo "<table cellpadding=2><tr><td style='font-size:20px;'><b>",$row['fname'],"</b></td><td></td><td>Запас</td><td>Добыча</td><td>Расход</td><td></td><td></td></tr>
<tr><td></td><td><b>Тилиум:</b></td><td>",$row['fuel'],"</td><td>",$row['dfuel'],"</td><td>",$row['rfuel'],"</td><td>",$row['jfuel'],"</td><td>- на прыжок</td></tr>";
		echo "<tr><td></td><td><b>Вода:</b></td><td>",$row['water'],"</td><td>",$row['dwater'],"</td><td>",$row['rwater'],"</td><td>",round($row['human']*0.12*$norms['n2']/100),"</td><td>- населением</td></tr>
<tr><td></td><td><b>Запчасти:</b></td><td>",$row['comp'],"</td><td>",$row['dcomp'],"</td><td>",$row['rcomp'],"</td><td>",round($row['human']*0.07*$norms['n3']/100),"</td><td>- населением</td></tr></table>
<div style='border:1px solid green; margin-left:30px;'>";
    		$fleet_id=$row['fleet'];
    		$ship_data->execute(array($fleet_id,$fleet_id,$fleet_id));
    		while ($rw2 = $ship_data->fetch()) {
      			if ($fleet_id==$rw2['ids']){
        			echo "<form method='post' action='jobs/ships.php'>";
				echo "<p style='white-space:nowrap;line-height:30px;";
        			if ($rw2['giper']>0) {echo "background-color:red;";}
				echo "'><b><input type='hidden' name='fleet' value='",$fleet_id,"'>
<input type='hidden' name='ids' value='",$rw2['ids'],"'><input type='hidden' name='flname' value='",$rw2['name'],"'>";
				echo "Флагман: ",$rw2['name'];
				echo class_size($rw2);
                                echo "<wbr> население: <input type='number' name='human' min='1' max='10000' value='",$rw2['human'],"'>";
        			echo "<wbr> <button type='button' id='myBtn' onclick='btn(",$rw2['ids'],"); return false;'>доп. инфо</button> <input type='submit' name='savship' value='Сохранить'></b></p></form>";
      			} else {
        			echo "<form method='post' action='jobs/ships.php'><p style='white-space:nowrap;line-height:30px;";
        			if ($rw2['giper']>0) {echo "background-color:red;";}
				echo "'><input type='hidden' name='ids' value='",$rw2['ids'],"'><input type='text' name='flname' size='18' value='",$rw2['name'],"'>";
				echo class_size($rw2);
				echo " флот: ";
				echo "<select id='fleet' name='fleet'>";
				echo "<option value='0'>0</option>";
				foreach ($fleets as $fl){
        				echo "<option value='",$fl['fleet'],"'";
					if ($fl['fleet']==$rw2['fleet']){ echo " selected";}
					echo ">",$fl['fname'],"</option>";
				}
        			echo "</select>";
        			unset($fl);
				
                                echo " <wbr>население: <input type='number' name='human' min='1' max='10000' value='",$rw2['human'],"'><wbr> <button type='button' id='myBtn' onclick='btn(",$rw2['ids'],"); return false;'>доп. инфо</button> <input type='submit' name='savship' value='Сохранить'></p></form>";
      			}
    		}
    		echo "</div>";
    	}
	echo "</div>";
}
//флоты
if (isset($_GET['fleet'])){
  if (isset($_GET['fuel'])){
  echo "<script>alert('При удалении флота потеряно ",stripslashes($_GET['fuel'])," единиц тилиума.');</script>";
  }
  echo '<h2>Fleets</h2><table border=0>';
  echo "<tr><th>Название</th>";
  echo "<th>враг</th>";
  echo "<th>Тилиум</th>";
  echo "<th>Вода</th>";
  echo "<th>Запчасти</th>";
  echo "<th>Где</th>";
  echo "<th>Назначение<br>прыжка</th>";
  echo "<th>Время<br>расчета<br>прыжка</th>";
  echo "<th>Время<br>зарядки<br>гипервиг.</th>";
  echo "<th>Таймер<br>гипердвиг.</th>";
  echo "<th>Картинка радара</th>";
  echo "<th>Картинка фракции</th>";
  echo "<th></th>";
  echo "</div>";
  $fleets = $pdo->query("SELECT destination.who as who, destination.enemy as enemy, destination.name as `name`, destination.locat as locat, destination.map_dest as map_dest, destination.tim_pre as tim_pre, destination.timer as timer,
destination.jumping as jumping, destination.radimage as radimage, destination.image as image, resurs.fuel as fuel, resurs.water as water, resurs.comp as comp
FROM destination join resurs on destination.who=resurs.id_f WHERE destination.who <'1000' ORDER BY `name`");
  while ($row = $fleets->fetch()) {
    echo "<form method='post' action='jobs/fleeted.php'><tr><td><input type='hidden' name='id' value='",$row['who'],"'>";
    echo "<input type='text' name='name' size='10' maxlenght='50' value='",$row['name'],"'></td>";
    echo "<td><input type='checkbox' name='enemy' value='1'";
	if ($row['enemy']==1) {echo " checked";}
	echo "></td>";
    echo "<td><input type='hidden' name='ofuel' value='",$row['fuel'],"'><input type='number' style='width: 5em;' title='тилиум' min='0' max='1000000' name='fuel' value='",$row['fuel'],"'></td>";
    echo "<td><input type='hidden' name='owater' value='",$row['water'],"'><input type='number' style='width: 5em;' title='вода' min='0' max='1000000' name='water' value='",$row['water'],"'></td>";
    echo "<td><input type='hidden' name='ocomp' value='",$row['comp'],"'><input type='number' style='width: 5em;' title='запчасти' min='0' max='1000000' name='comp' value='",$row['comp'],"'></td>";
    echo "<td><input type='number' style='width: 3em;' min='1' max='42' name='loc' value='",$row['locat'],"'></td>";
    echo "<td><input type='number' style='width: 3em;' min='0' max='42' name='dest' value='",$row['map_dest'],"'></td>";
    $maxt=time()+3540;
    echo "<td><input type='number' style='width: 5em;' min='10' max='10000' name='tim_pre' value='",$row['tim_pre'],"'></td>";
    echo "<td><input type='number' style='width: 5em;' min='10' max='10000' name='timer' value='",$row['timer'],"'></td>";
    echo "<td><input type='number' style='width: 6em;' min='0' max='",$maxt,"' name='jumptim' value='";
    if (time()>$row['jumping']) {echo "0";} else {echo $row['jumping'];}
    echo "'></td>";
    echo "<td><select name='radar'>";
    $dir='img/radar/';
    if (is_dir($dir)){
   	  if ($dh=opendir($dir)){
                while (($file = readdir($dh)) !== false) {
			if (filetype($dir . $file)=='file'){
            			echo "<option value='",$file,"'";
            			if ($file==$row['radimage']){ echo " selected";}
				echo ">",$file,"</option>";
			}
        	}
		closedir($dh);
	}
}
    echo "</select></td>";
    echo "<td><select name='imag'>";
    $dir='img/fleet/';
    if (is_dir($dir)){
   	  if ($dh=opendir($dir)){
                while (($file = readdir($dh)) !== false) {
			if (filetype($dir . $file)=='file'){
            			echo "<option value='",$file,"'";
            			if ($file==$row['image']){ echo " selected";}
				echo ">",$file,"</option>";
			}
        	}
		closedir($dh);
	}
}
    echo "</select></td>";
    echo "</td><td nowrap><input type='submit' name='save' value='Сохранить'><input type='submit' name='del' value='Удалить'></td>";
    echo "</tr></form>";
  }
  echo "</table>";
  echo "<h4>ВНИМАНИЕ! При удалении флота, его тилиум исчезнет. Прибавьте тилиум к другому флоту вручную.</h4>";
  echo "<h4>Корабли входившие в удаленный флот необходимо передать в другой флот - на вкладке Корабли</h4>";
  echo "<h4>Результаты разведки и сканирований этим флотом станут недоступны :( Можно полечить прямым доступом к БД.</h4>";

}
//рапторы
if (isset($_GET['rapt'])){
  echo '<h2>Рапторы</h2><table border=0>';
  echo "<tr><th>Название</th>";
	echo "<th>враг</th>";
  echo "<th>Тилиум</th>";
  echo "<th>Где</th>";
  echo "<th>Назначение<br>прыжка</th>";
  echo "<th>Время<br>расчета<br>координат</th>";
  echo "<th>Время<br>зарядки<br>гипервиг.</th>";
  echo "<th>Событие<br>прыжок</th>";
  echo "<th>Картинка радара</th>";
  echo "<th>Картинка фракции</th>";
  echo "<th></th>";
  echo "</div>";
  $fleets = $pdo->query("SELECT * FROM destination WHERE who >'1000' ORDER BY `name`,`who`");
  while ($row = $fleets->fetch()) {
    echo "<form method='post' action='jobs/fleeted.php'><tr><td nowrap>",floor($row['who']/1000)," <input type='hidden' name='id' value='",$row['who'],"'>";
    echo "<input type='text' name='name' size='18' maxlenght='50' value='",$row['name'],"'></td>";
    echo "<td><input type='checkbox' name='enemy' value='1'";
	if ($row['enemy']==1) {echo " checked";}
	echo "></td>";
    echo "<td><input style='width:3em;' type='number' size='3' min='0' max='42' name='fuel' value='",$row['fuel'],"'></td>";
    echo "<td><input style='width:3em;' type='number' size='3' min='0' max='42' name='loc' value='",$row['locat'],"'></td>";
    echo "<td><input style='width:4em;' type='number' size='3' min='0' max='42' name='dest' value='",$row['map_dest'],"'></td>";
    $maxt=time()+3540;
    echo "<td><input type='number' style='width:5em;' size='5' min='5' max='10000' name='tim_pre' value='",$row['tim_pre'],"'></td>";
    echo "<td><input type='number' style='width:5em;' size='5' min='10' max='10000' name='timer' value='",$row['timer'],"'></td>";
    echo "<td><input type='number' style='width:6em;' size='11' min='0' max='",$maxt,"' name='jumptim' value='";
    if (time()>$row['jumping']) {echo "0";} else {echo $row['jumping'];}
    echo "'></td>";
    echo "<td><select name='radar'>";
    $dir='img/radar/';
    if (is_dir($dir)){
   	  if ($dh=opendir($dir)){
                while (($file = readdir($dh)) !== false) {
			if (filetype($dir . $file)=='file'){
            			echo "<option value='",$file,"'";
            			if ($file==$row['radimage']){ echo " selected";}
				echo ">",$file,"</option>";
			}
        	}
		closedir($dh);
	}
}
    echo "</select></td>";
    echo "<td><select name='imag'>";
    $dir='img/fleet/';
    if (is_dir($dir)){
   	  if ($dh=opendir($dir)){
                while (($file = readdir($dh)) !== false) {
			if (filetype($dir . $file)=='file'){
            			echo "<option value='",$file,"'";
            			if ($file==$row['image']){ echo " selected";}
				echo ">",$file,"</option>";
			}
        	}
		closedir($dh);
	}
}
    echo "</select></td>";
    echo "</td><td nowrap><input type='submit' name='save' value='Сохранить'></td>";
    echo "</tr></form>";
  }
  echo "</table>";

}

//карта и аномалии
if (isset($_GET['maps'])){
	echo '<h2>Карта и Аномалии</h2><div id="coord">';
	$maps_data = $pdo->query("SELECT * FROM maps ORDER BY `id_map`");
	$countan=$pdo->prepare("SELECT id FROM anom WHERE map= ?");
	$tr=0;
	while ($row = $maps_data->fetch()) {
  		if ($tr==0){
    			echo "<div>";
  		}
  		echo "<div class='cel'";
  		if ($row['id_map']==stripslashes($_GET['maps'])){
     			echo " style='background-color:green;'";
  		}
  		$tro=$row['id_map'];
  		$countan->execute([$tro]);
  		$numr=$countan->rowCount();
  		echo "><a href='admin.php?maps=",$row['id_map'],"'>",$row['name']," (",$numr,")</a></div>";
  		if ($tr==5) {
    			echo "</div>";
    			$tr=0;
  		} else {
    			$tr=$tr+1;
  		} 
	}
	echo "</div>";
	if ($_GET['maps']<>null){
   		$stm = $pdo->prepare("SELECT * FROM anom WHERE map=:id_map");
   		$stm->bindValue(':id_map',trim($_GET['maps']));
   		$stm->execute();
   		$anom = $stm->fetchAll();
   		echo "<div style='display:table;width:80%;margin-top:10px;'><div style='display:table-row;'>";
   		if (count($anom)<3){
   			echo "<div style='width:30%;display:table-cell;text-align:center;'><form id='add' method='post' action='jobs/edit.php'>
<input type='hidden' name='m0' value='",stripslashes($_GET['maps']),"'><input type='hidden' name='i0' value='0'><textarea rows='4' cols='30' name='t0'>";
     			echo "</textarea><hr>";
     			echo "<textarea rows='4' cols='30' name='s0'>";
     			echo "</textarea><hr>Ресурс: <select name='resurs'><option value='0'>нет</option><option value='1'>тилиум</option><option value='2'>вода</option><option value='3'>металл</option></select><br><br>Качество: <input type='number' name='quality' value='0' min='0' max='2' size='3' step='0.1'><hr><input type='submit' name='add' value='Добавить'>";
     			echo "</form></div>";
   		}
   		foreach ($anom as $id_ano) {
     			echo "<div style='width:30%;display:table-cell;text-align:center;'><form id='add' method='post' action='jobs/edit.php'>
<input type='hidden' name='id_ano' value='",$id_ano['id'],"'><input type='hidden' name='m0' value='",stripslashes($_GET['maps']),"'>
<textarea rows='4' cols='30' name='text'>";
     			echo $id_ano['anomaly'],"</textarea><hr>";
     			echo "<textarea rows='4' cols='30' name='scan'>";
     			echo $id_ano['scanned'],"</textarea><hr>Ресурс: <select name='resurs'";
     			switch ($id_ano['resurs']){
			case 1:echo " style='background-color:orange;'";break;	
			case 2:echo " style='background-color:#1E90ff;'";break;	
			case 3:echo " style='background-color:#d3d3d3;'";break;	
			}
			echo "><option value='0'";
			if ($id_ano['resurs']==0) {echo " selected";}
			echo ">нет</option><option value='1'";
			if ($id_ano['resurs']==1) {echo " selected";}
			echo ">тилиум</option><option value='2'";
			if ($id_ano['resurs']==2) {echo " selected";}
			echo ">вода</option><option value='3'";
			if ($id_ano['resurs']==3) {echo " selected";}
			echo ">металл</option></select><br><br>Качество: <input type='number' name='quality' min='0' max='2' size='3' step='0.05' value='",$id_ano['quality'],"'><hr><input type='submit' name='save' value='Сохранить'>
<input type='submit' name='del' value='Удалить'>";
     			echo "</form></div>";
   		}
   		echo "</div></div><h4>При создании новой аномалии - она не будет видна, пока не будет сканирована</h4>";
	}
}
}
} else {
  header('Location: testsess.php');
}
?>
</div>
</div>
<script>
var modal = document.getElementById('myModal');
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
function btn2(news,fleet) {
    modal.style.display = 'block';
    $.ajax({
         type: "POST",
         url: "modul/news.php",
         data: {tim:news,fleet:fleet},
         success: function(html){
             $("#mnews").html(html);
         }
    });
    return false;
}
function btn3() {
    modal.style.display = 'block';
    $.ajax({
         type: "POST",
         url: "modul/news.php",
         data: {nw:1,tim:0,fleet:0},
         success: function(html){
             $("#mnews").html(html);
         }
    });
    return false;
}

function btn1(proj) {
    modal.style.display = 'block';
    $.ajax({
         type: "POST",
         url: "modul/viewproj.php",
         data: {ship:proj},
         success: function(html){
             $("#projdet").html(html);
         }
    });
    return false;
}

function btn(fleet) {
    modal.style.display = 'block';
    $.ajax({
         type: "POST",
         url: "modul/ship_detail.php",
         data: {ship:fleet},
         success: function(html){
             $("#detail").html(html);
         }
    });
    return false;
}
</script>
</body>
</html>