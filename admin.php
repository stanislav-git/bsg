<?php
session_start(); // стартуем сессию, PHP записывает в нее данные находя их по загруженным кукам если они уже есть
if (isset($_SESSION['user_id'])){
if ($_SESSION['user_id']==0){
include_once('modul/connect.php');

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
<a href='admin.php?maps'><p>Карта и аномалии и ресурсы</p></a>
<a href='admin.php?fleet'><p>Флоты</p></a>
<a href='admin.php?rapt'><p>Рапторы</p></a>
<a href='admin.php?ships'><p>Корабли</p></a>
<a href='admin.php?scan'><p>Исследования</p></a>
<a href='admin.php?inform'><p>Инфо панель флота</p></a>
</div>
<div id='cont'>";
//echo $_SESSION['user_id'];
//исследования
if (isset($_GET['inform'])){
	$qfl=$pdo->query("SELECT who,name from destination where who <100 order by who");
	$qfl->execute();
	echo "<h2>Открыть инфопанель флота</h2>";
	echo "<form method='post' action='inform.php'><p>Флот: <select name='fleet'>";
	while ($fleet=$qfl->fetch()) {
		echo "<option value='",$fleet['who'],"'>",$fleet['name'],"</option>";
	}
	echo "</select> <input type='submit' value='Вывод на экран'></form>";
}
if (isset($_GET['scan'])){
	$qfl=$pdo->query("SELECT who,name from destination where who <100 order by who");
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
  echo '<h1>Логины и пароли</h1><table border=1>';
  echo "<tr><th>Логин</th>";
  echo "<th>Пароль</th>";
  echo "<th>Где</th>";
  echo "<th></th>";
  echo "<th>Время<br>последних<br>действий</th>";
  echo "<th>Сессия</th>";
  echo "<th></th>";
  echo "</div>";
  $fleets = $pdo->query("SELECT who, name, locat, tim, sid FROM destination WHERE who<200 ORDER BY `name`");
  while ($row = $fleets->fetch()) {
    echo "<form method='post' action='jobs/loged.php'><tr><td><input type='hidden' name='id' value='",$row['who'],"'>";
    echo "<input type='text' name='name' size='20' maxlenght='50' value='",$row['name'],"'></td>";
    echo "<td><input type='text' size='9' maxlenght='10' name='pass' placeholder='новый пароль' value='' style='color:#F02020';></td>";
    if ($row['who']>100) {
    	echo "<td> </td>";
    } else {
    	echo "<td><input type='number' size='2' min='1' max='42' name='loc' value='",$row['locat'],"'></td>";
    }
    echo "<td><input type='checkbox' name='sid' value='",$row['who'],"'";
    if ($row['who']>100){echo " disabled";}	
    echo "></td>";
    echo "<td>",date("d F H:i:s",$row['tim']),"</td>";
    echo "<td>",$row['sid'],"</td>";
    echo "</td><td nowrap><input type='submit' name='save' value='Сохранить'></td>";
    echo "</tr></form>";
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
	echo "<h1>Корабли</h1>";
	$fl_data = $pdo->query("SELECT destination.name as fname, ships.fleet as fleet, destination.fuel as fuel, destination.water as water,
destination.comp as comp, SUM(typeship.jfuel) as jfuel, SUM(typeship.cargo) as cargo, SUM(typeship.rfuel) as rfuel, SUM(typeship.dfuel) as dfuel, 
SUM(typeship.rwater) as rwater, SUM(typeship.dwater) as dwater, SUM(typeship.rcomp) as rcomp, SUM(typeship.dcomp) as dcomp, 
SUM(ships.human) as human
FROM ships
JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who
WHERE destination.who<100
GROUP BY ships.fleet");
	$qclass=$pdo->query("SELECT * from typeship order by id");
	$qclass->execute();
	$class=$qclass->fetchAll(PDO::FETCH_ASSOC);

	$qnorms=$pdo->query("select id, quality from norms");
	$qnorms->execute();
	$norms=$qnorms->fetchAll(PDO::FETCH_ASSOC);

	$ship_data=$pdo->prepare("SELECT ships.id as ids, ships.name as name, ships.type as class, ships.fleet as fleet, ships.human as human, ships.repair as giper, typeship.jfuel as jfuel FROM ships join typeship on ships.type=typeship.id WHERE ships.fleet= ?");
	$ship_data->execute([0]);
//новый корабль
	echo "<div style='border:1px solid green;margin-left:0px;padding:5px;'><h4>Создать новый корабль</h4>";
	echo "<form method='post' action='jobs/ships.php'><input type='text' name='ship' value='' placeholder='Название'>";
	echo " <select id='class' name='class'>";
	foreach ($class as $cl){
        	echo "<option value='",$cl['id'],"'";
		echo ">",$cl['type'],"</option>";
	}
        echo "</select>";
        unset($cl);
	echo " <input type='number' min='0' max='10000' value='0' name='human' placeholder='Население'> <input type='submit' name='add_ship' value='ADD'></form></div>";
	$rwq=$ship_data->fetchAll(PDO::FETCH_ASSOC);
	$fl_d=$pdo->query("select who as fleet, name as fname from destination where who<100 order by who");
	$fl_d->execute();
	$fleets=$fl_d->fetchAll(PDO::FETCH_ASSOC);
//Флот 0
echo "<div style='float:left;width:100%; margin-top:10px;'>";
	foreach ($rwq as $rw0) {
		echo "<form method='post' action='jobs/ships.php'><input type='hidden' name='ids' value='",$rw0['ids'],"'>
<input type='text' name='flname' value='",$rw0['name'],"'>";
		echo "<select id='class' name='class'>";
		foreach ($class as $cl){
        		echo "<option value='",$cl['id'],"'";
        		if ($rw0['class']==$cl['id']) {echo " selected";}
			echo ">",$cl['type'],"</option>";
		}
        	echo "</select>";
        	unset($cl);
		echo " флот: ";
		echo "<select id='fleet' name='fleet'>";
		echo "<option value='0' selected>0</option>";
		foreach ($fleets as $fl){
        		echo "<option value='",$fl['fleet'],"'";
			echo ">",$fl['fname'],"</option>";
		}
        	echo "</select>";
        	unset($fl);
		echo " население: <input type='number' name='human' min='0' max='10000' value='",$rw0['human'],"'> <input type='checkbox' name='giper' value='",$rw0['giper'],"'";
		if ($rw0['giper']==0) {echo " checked";}
		echo "> <button type='button' id='myBtn' onclick='btn(",$rw0['ids'],"); return false;'>доп. инфо</button>
 <input type='submit' name='savship' value='Сохранить'> <input type='submit' name='addfleet' value='Создать флот'> <input type='submit' name='delship' value='Удалить'></form><hr>";
	}
	unset($rw0);
	echo "<h4>Флот не назначен - корабли не активны, это брошенные корабли.</h4></div>";
//корабли во флотах	
	echo "<h2>Корабли во флотах</h2>";
	$fl_data->execute();
	while ($row = $fl_data->fetch()) {
    		echo "<table cellpadding=2><tr><td style='font-size:20px;'><b>",$row['fname'],"</b></td><td></td><td>Запас</td><td>Добыча</td><td>Расход</td><td></td><td></td></tr>
<tr><td></td><td><b>Тилиум:</b></td><td>",$row['fuel'],"</td><td>",$row['dfuel'],"</td><td>",$row['rfuel'],"</td><td>",$row['jfuel'],"</td><td>- на прыжок</td></tr>";
		echo "<tr><td></td><td><b>Вода:</b></td><td>",$row['water'],"</td><td>",$row['dwater'],"</td><td>",$row['rwater'],"</td><td>",$row['human']*$norms[1]['quality'],"</td><td>- населением</td></tr>
<tr><td></td><td><b>Запчасти:</b></td><td>",$row['comp'],"</td><td>",$row['dcomp'],"</td><td>",$row['rcomp'],"</td><td>",$row['human']*$norms[1]['quality'],"</td><td>- населением</td></tr></table>
<div style='border:1px solid green; margin-left:30px;'>";
    		$fleet_id=$row['fleet'];
    		$ship_data->execute([$fleet_id]);
    		while ($rw2 = $ship_data->fetch()) {
      			if ($fleet_id==$rw2['ids']){
        			echo "<form method='post' action='jobs/ships.php'><p><b><input type='hidden' name='fleet' value='",$fleet_id,"'>
<input type='hidden' name='ids' value='",$rw2['ids'],"'>
<input type='hidden' name='flname' value='",$rw2['name'],"'>
Флагман: ",$rw2['name'];
				echo " <select id='class' name='class'>";
				foreach ($class as $cl){
        				echo "<option value='",$cl['id'],"'";
        				if ($rw2['class']==$cl['id']) {echo " selected";}
					echo ">",$cl['type'],"</option>";
				}		
        			echo "</select>";
        			unset($cl);
                                echo " население: <input type='number' name='human' min='1' max='10000' value='",$rw2['human'],"'> гипердвигатель: 
<input type='checkbox' name='giper' value='",$rw2['giper'],"'";
        			if ($rw2['giper']==0) {echo " checked";}
        			echo "> <button type='button' id='myBtn' onclick='btn(",$rw2['ids'],"); return false;'>доп. инфо</button> <input type='submit' name='savship' value='Сохранить'></b></p></form>";
      			} else {
        			echo "<form method='post' action='jobs/ships.php'><p><input type='hidden' name='ids' value='",$rw2['ids'],"'><input type='text' name='flname' value='",$rw2['name'],"'>";
				echo " <select id='class' name='class'>";
				foreach ($class as $cl){
        				echo "<option value='",$cl['id'],"'";
        				if ($rw2['class']==$cl['id']) {echo " selected";}
					echo ">",$cl['type'],"</option>";
				}		
        			echo "</select>";
        			unset($cl);
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
				
                                echo " население: <input type='number' name='human' min='1' max='10000' value='",$rw2['human'],"'><input type='hidden' name='giper' value='0'> <button type='button' id='myBtn' onclick='btn(",$rw2['ids'],"); return false;'>доп. инфо</button> <input type='submit' name='savship' value='Сохранить'></p></form>";
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
  echo '<h1>Fleets</h1><table border=1>';
  echo "<tr><th>Название</th>";
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
  $fleets = $pdo->query("SELECT * FROM destination WHERE who <'100' ORDER BY `name`");
  while ($row = $fleets->fetch()) {
    echo "<form method='post' action='jobs/fleeted.php'><tr><td><input type='hidden' name='id' value='",$row['who'],"'>";
    echo "<input type='text' name='name' size='10' maxlenght='50' value='",$row['name'],"'></td>";
    echo "<td><input type='number' style='width: 5em;' title='тилиум' min='0' max='1000000' name='fuel' value='",$row['fuel'],"'></td>";
    echo "<td><input type='number' style='width: 5em;' title='вода' min='0' max='1000000' name='water' value='",$row['water'],"'></td>";
    echo "<td><input type='number' style='width: 5em;' title='запчасти' min='0' max='1000000' name='comp' value='",$row['comp'],"'></td>";
    echo "<td><input type='number' style='width: 3em;' min='1' max='42' name='loc' value='",$row['locat'],"'></td>";
    echo "<td><input type='number' style='width: 3em;' min='0' max='42' name='dest' value='",$row['map_dest'],"'></td>";
    $maxt=time()+3540;
    echo "<td><input type='number' style='width: 5em;' min='10' max='10000' name='tim_pre' value='",$row['tim_pre'],"'></td>";
    echo "<td><input type='number' style='width: 5em;' min='10' max='10000' name='timer' value='",$row['timer'],"'></td>";
    echo "<td><input type='number' style='width: 8em;' min='0' max='",$maxt,"' name='jumptim' value='";
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
  echo '<h1>Рапторы</h1><table border=1>';
  echo "<tr><th>Название</th>";
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
  $fleets = $pdo->query("SELECT * FROM destination WHERE who >'100' ORDER BY `name`,`who`");
  while ($row = $fleets->fetch()) {
    echo "<form method='post' action='jobs/fleeted.php'><tr><td nowrap>",floor($row['who']/100)," <input type='hidden' name='id' value='",$row['who'],"'>";
    echo "<input type='text' name='name' size='18' maxlenght='50' value='",$row['name'],"'></td>";
    echo "<td><input type='number' size='3' min='0' max='42' name='fuel' value='",$row['fuel'],"'></td>";
    echo "<td><input type='number' size='3' min='0' max='42' name='loc' value='",$row['locat'],"'></td>";
    echo "<td><input type='number' size='3' min='0' max='42' name='dest' value='",$row['map_dest'],"'></td>";
    $maxt=time()+3540;
    echo "<td><input type='number' size='5' min='5' max='10000' name='tim_pre' value='",$row['tim_pre'],"'></td>";
    echo "<td><input type='number' size='5' min='10' max='10000' name='timer' value='",$row['timer'],"'></td>";
    echo "<td><input type='number' size='11' min='0' max='",$maxt,"' name='jumptim' value='";
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
	echo '<h1>Карта и Аномалии</h1><div id="coord">';
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
     			echo $id_ano['scanned'],"</textarea><hr>Ресурс: <select name='resurs'><option value='0'";
			if ($id_ano['resurs']==0) {echo " selected";}
			echo ">нет</option><option value='1'";
			if ($id_ano['resurs']==1) {echo " selected";}
			echo ">тилиум</option><option value='2'";
			if ($id_ano['resurs']==2) {echo " selected";}
			echo ">вода</option><option value='3'";
			if ($id_ano['resurs']==3) {echo " selected";}
			echo ">металл</option></select><br><br>Качество: <input type='number' name='quality' min='0' max='2' size='3' step='0.1' value='",$id_ano['quality'],"'><hr><input type='submit' name='save' value='Сохранить'>
<input type='submit' name='del' value='Удалить'>";
     			echo "</form></div>";
   		}
   		echo "</div></div><h4>При создании новой аномалии - она не будет видна, пока не будет сканирована</h4>";
	}
}
}
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
