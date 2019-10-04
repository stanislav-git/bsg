<?php
session_start(); // стартуем сессию, PHP записывает в нее данные находя их по загруженным кукам если они уже есть
include('connect.php');
?>
<!DOCTYPE Html PUBLIC "-//W3C//DTD Html 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="css/adm.css" rel="stylesheet" type="text/css">
</head>
<body>
<div id='maket'>
<div id='left'>
<a href='auth.php?logout'><img src='img/power.png' style='width:40px;height:auto;'></a>
<a href='admin.php?log'><p>Логины и пароли</p></a>
<a href='admin.php?maps'><p>Карта и аномалии</p></a>
<a href='admin.php?fleet'><p>Флоты</p></a>
<a href='admin.php?rapt'><p>Рапторы</p></a>
<a href='admin.php?ships'><p>Корабли</p></a>
<a href='admin.php?scan'><p>Исследования</p></a>




</div>
<div id='cont'>
<?php
$class_data=array("Крейсер","Огромный","Большой","Средний");

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
  $fleets = $pdo->query("SELECT who, name, locat, tim, sid FROM destination ORDER BY `name`");
  while ($row = $fleets->fetch()) {
    echo "<form method='post' action='loged.php'><tr><td><input type='hidden' name='id' value='",$row['who'],"'>";
    echo "<input type='text' name='name' size='20' maxlenght='50' value='",$row['name'],"'></td>";
    echo "<td><input type='text' size='9' maxlenght='10' name='pass' placeholder='новый пароль' value='' style='color:#F02020';></td>";
    if ($row['who']>100) {
    	echo "<td><input type='number' size='2' min='0' max='30' name='loc' value='",$row['locat'],"'></td>";
    } else {
    	echo "<td><input type='number' size='2' min='1' max='30' name='loc' value='",$row['locat'],"'></td>";
    }
    echo "<td><input type='checkbox' name='sid' value='",$row['who'],"'></td>";
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
	echo "<h1>Корабли</h1>";
	$fl_data = $pdo->query("SELECT destination.who as f_id,destination.fuel,destination.name,sum(ships.fuel) as power FROM destination,ships WHERE destination.who <'100' and destination.who = ships.fleet GROUP BY destination.who");
	$ship_data=$pdo->prepare("SELECT * FROM ships WHERE fleet= ?");
	$ship_data->execute([0]);
	echo "<div style='border:1px solid green;margin-left:0px;'><h4>Создать новый корабль</h4>";
	echo "<form method='post' action='ships.php'><p><input type='text' name='ship' value=''>";
	echo "<select id='class' name='class'>";
	foreach ($class_data as $class){
        	echo "<option value='",$class,"'";
		echo ">",$class,"</option>";
	}
        echo "</select>";
        unset($class);
	echo "<input type='number' name='power' min='1' max='30' value='1'><input type='submit' name='add_ship' value='ADD'></p></form></div>";

	$rwq=$ship_data->fetchAll(PDO::FETCH_ASSOC);
	echo "<form method='post' action='fleeted.php'><p><input type='submit' name='add_fleet' value='Создать флот'></p><div style='float:left;width:5%;'>";
	foreach ($rwq as  $rw1) {
      		echo "<p style='height:23px;margin-top:5px;margin-bottom:5px;'><input type='radio' name='fla' value='",$rw1['id'],"'></p><hr>";
	}
	unset($rw1);
	echo "</div></form><div style='float:left;width:90%;'>";
	$qfleets=$pdo->query("SELECT who FROM destination WHERE who <100 ORDER BY 'who'");
	$fleets=$qfleets->fetchAll(PDO::FETCH_ASSOC);
	foreach ($rwq as $rw0) {
		echo "<form method='post' action='ships.php'><p style='height:23px;margin-top:5px;margin-bottom:5px;'><input type='hidden' name='ids' value='",$rw0['id'],"'>
<input type='text' name='flname' value='",$rw0['name'],"'>";
		echo "<select id='class' name='class'>";
		foreach ($class_data as $class){
        		echo "<option value='",$class,"'";
        		if ($rw0['type']==$class) {echo " selected";}
			echo ">",$class,"</option>";
		}
        	echo "</select>";
        	unset($class);
		echo " флот: ";
		echo "<select id='fleet' name='fleet'>";
		echo "<option value='0' selected>0</option>";
		foreach ($fleets as $fl){
        		echo "<option value='",$fl['who'],"'";
			echo ">",$fl['who'],"</option>";
		}
        	echo "</select>";
        	unset($fl);
		echo " power: <input type='number' name='power' min='1' max='300' value='",$rw0['fuel'],"'><input type='submit' name='savship' value='Сохранить'></p></form><hr>";
	}
	unset($rw0);
	echo "</div>";
	echo "<div style='clear:both;'><h4>Флот не назначен - корабли не активны</h4></div>";
//корабли во флотах	
	echo "<h2>Корабли во флотах</h2>";
	while ($row = $fl_data->fetch()) {
    		echo "<p><b>",$row['name'],"</b> (запас тилиума:",$row['fuel']," расход тилиума:",$row['power'],")</p>
<div style='border:1px solid green; margin-left:30px;'>";
    		$fleet_id=$row['f_id'];
    		$ship_data->execute([$fleet_id]);
    		while ($rw2 = $ship_data->fetch()) {
    		        //var_dump($rw2);
			//var_dump($fleet_id);
			//var_dump($rw2['id']);
      			if ($fleet_id==$rw2['id']){
        			echo "<form method='post' action='ships.php'><p><b><input type='hidden' name='fleet' value='",$fleet_id,"'><input type='hidden' name='ids' value='",$rw2['id'],"'><input type='hidden' name='flname' value='",$rw2['name'],"'>
Флагман: ",$rw2['name']," тип:";
				echo "<select id='class' name='class'>";
				foreach ($class_data as $class){
        				echo "<option value='",$class,"'";
        				if ($rw2['type']==$class) {echo " selected";}
					echo ">",$class,"</option>";
				}		
        			echo "</select>";
        			unset($class);
                                echo " мощность: <input type='number' name='power' min='1' max='300' value='",$rw2['fuel'],"'> гипердвигатель: 
<input type='checkbox' name='giper' value='",$rw2['repair'],"'";
        			if ($rw2['repair']==0) {echo " checked>";}
        			echo "<input type='submit' name='savship' value='Сохранить'></b></p></form>";
      			} else {
        			echo "<form method='post' action='ships.php' name='editship'><p><input type='hidden' name='ids' value='",$rw2['id'],"'><input type='text' name='flname' value='",$rw2['name'],"'>";
				echo "<select id='class' name='class'>";
				foreach ($class_data as $class){
        				echo "<option value='",$class,"'";
        				if ($rw2['type']==$class) {echo " selected";}
					echo ">",$class,"</option>";
				}		
        			echo "</select>";
        			unset($class);
				echo " флот: ";
				echo "<select id='fleet' name='fleet'>";
				echo "<option value='0'>0</option>";
				foreach ($fleets as $fl){
        				echo "<option value='",$fl['who'],"'";
					if ($fl['who']==$rw2['fleet']){ echo " selected";}
					echo ">",$fl['who'],"</option>";
				}
        			echo "</select>";
        			unset($fl);
				
                                echo " мощность: <input type='number' name='power' min='1' max='300' value='",$rw2['fuel'],"'><input type='hidden' name='giper' value='0'><input type='submit' name='savship' value='Сохранить'></p></form>";
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
    echo "<form method='post' action='fleeted.php'><tr><td><input type='hidden' name='id' value='",$row['who'],"'>";
    echo "<input type='text' name='name' size='10' maxlenght='50' value='",$row['name'],"'></td>";
    echo "<td><input type='number' size='3' min='0' max='300000' name='fuel' value='",$row['fuel'],"'></td>";
    echo "<td><input type='number' size='3' min='1' max='30' name='loc' value='",$row['locat'],"'></td>";
    echo "<td><input type='number' size='3' min='0' max='30' name='dest' value='",$row['map_dest'],"'></td>";
    $maxt=time()+3540;
    echo "<td><input type='number' size='5' min='10' max='10000' name='tim_pre' value='",$row['tim_pre'],"'></td>";
    echo "<td><input type='number' size='5' min='10' max='10000' name='timer' value='",$row['timer'],"'></td>";
    echo "<td><input type='number' size='11' min='0' max='",$maxt,"' name='jumptim' value='";
    if (time()>$row['jumping']) {echo "0";} else {echo $row['jumping'];}
    echo "'></td>";
    echo "<td><input type='text' size='8' maxlenght='50' name='radar' value='",$row['radimage'],"'></td>";
    echo "<td><input type='text' size='8' maxlenght='50' name='imag' value='",$row['image'],"'></td>";
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
    echo "<form method='post' action='fleeted.php'><tr><td nowrap>",floor($row['who']/100)," <input type='hidden' name='id' value='",$row['who'],"'>";
    echo "<input type='text' name='name' size='18' maxlenght='50' value='",$row['name'],"'></td>";
    echo "<td><input type='number' size='3' min='0' max='30' name='fuel' value='",$row['fuel'],"'></td>";
    echo "<td><input type='number' size='3' min='0' max='30' name='loc' value='",$row['locat'],"'></td>";
    echo "<td><input type='number' size='3' min='0' max='30' name='dest' value='",$row['map_dest'],"'></td>";
    $maxt=time()+3540;
    echo "<td><input type='number' size='5' min='5' max='10000' name='tim_pre' value='",$row['tim_pre'],"'></td>";
    echo "<td><input type='number' size='5' min='10' max='10000' name='timer' value='",$row['timer'],"'></td>";
    echo "<td><input type='number' size='11' min='0' max='",$maxt,"' name='jumptim' value='";
    if (time()>$row['jumping']) {echo "0";} else {echo $row['jumping'];}
    echo "'></td>";
    echo "<td><input type='text' size='8' maxlenght='50' name='radar' value='",$row['radimage'],"'></td>";
    echo "<td><input type='text' size='8' maxlenght='50' name='imag' value='",$row['image'],"'></td>";
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
   			echo "<div style='width:30%;display:table-cell;text-align:center;'><form id='add' method='post' action='edit.php'>
<input type='hidden' name='m0' value='",stripslashes($_GET['maps']),"'><input type='hidden' name='i0' value='0'><textarea rows='4' cols='30' name='t0'>";
     			echo "</textarea><hr>";
     			echo "<textarea rows='4' cols='30' name='s0'>";
     			echo "</textarea><hr><input type='submit' name='add' value='Добавить'>";
     			echo "</form></div>";
   		}
   		foreach ($anom as $id_ano) {
     			echo "<div style='width:30%;display:table-cell;text-align:center;'><form id='add' method='post' action='edit.php'>
<input type='hidden' name='id_ano' value='",$id_ano['id'],"'><input type='hidden' name='m0' value='",stripslashes($_GET['maps']),"'>
<textarea rows='4' cols='30' name='text'>";
     			echo $id_ano['anomaly'],"</textarea><hr>";
     			echo "<textarea rows='4' cols='30' name='scan'>";
     			echo $id_ano['scanned'],"</textarea><hr><input type='submit' name='save' value='Сохранить'>
<input type='submit' name='del' value='Удалить'>";
     			echo "</form></div>";
   		}
   		echo "</div></div><h4>При создании новой аномалии - она не будет видна, пока не будет сканирована</h4>";
	}
}
?>
</div>
</div>
</body>
</html>
