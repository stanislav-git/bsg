<?php
session_start();
if (!isset($_SESSION['user_id'])){
	header('Location: auth.php'); // перезагружаем файл
}
include('connect.php');
include('funct.php');
//
$stmt = $pdo->prepare("SELECT * FROM destination WHERE `who` = ?");
$pos=(int)(trim($_SESSION['user_id']));
if ($pos>100){
	$ship_c=round(($pos/100-floor($pos/100))*100);
} else {
	$ship_c=$pos;
}
$stmt->execute([$pos]);
$dest_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
$num_rows = count($dest_data);

$selec_pos=0;
$cur_pos=0;
$pre_jump=0;
$jump=0;
$fuel=0;
$dest_pos=0;
if ($num_rows==1 && $dest_data[0]['sid']==session_id()){
	$uptime=$pdo->prepare("UPDATE destination SET tim= unix_timestamp(NOW()) WHERE who=?");
    	$uptime->execute([$pos]);
//    	session_write_close();
        $cur_pos=$dest_data[0]['locat'];
        $jump=$dest_data[0]['jumping'];
        $pre_jump=$dest_data[0]['tim_pre'];
        $timer=$dest_data[0]['timer'];
        $dest_pos=$dest_data[0]['map_dest'];
        $fuel=$dest_data[0]['fuel'];
        $ffname=$dest_data[0]['name'];
} else {
//не найдена запись с текущими данными
        $_SESSION=array();
	header('Location: auth.php'); // перезагружаем файл
}
if (isset($_GET['map'])){
	$selec_pos=intval(trim(stripslashes($_GET['map'])));
} 
$maps_cur = $pdo->prepare("SELECT * FROM maps WHERE id_map = ?");
$maps_cur->execute([$cur_pos]);
$m_cur=$maps_cur->fetchAll(PDO::FETCH_ASSOC);
if ($pos<100) {
//Определяем флот и корабли с поломками
	$qfleet = $pdo->prepare("SELECT sum(fuel) as fuel FROM ships WHERE `fleet` = ?");
  	$qfleet->execute([$pos]);
  	$fleet_data = $qfleet->fetch();

  	$qfleet_r = $pdo->prepare("SELECT name FROM ships WHERE `name` = ? and repair= 1");
  	$qfleet_r->execute([$ffname]);
  	$fleet_data_r = $qfleet_r->fetch();
  	$giper=1;
  	if ($fleet_data_r['name']==$ffname) {
    		$giper=0;
//Гипердвиг сломан
  	}
} else {
        if ($selec_pos==0) {$selec_pos=$cur_pos;}
  	$fleet_data['fuel'] = count_fuel($cur_pos,$selec_pos);

	if (strpos($ffname, "айлон") !== false) {
	   $fleet_data['fuel']=1;
	}

// Расход топлива для раптора = 1
  	$giper=1;
  	$rapt_data = $pdo->prepare("SELECT count(scanning.who) as mdet from maps left JOIN anom ON maps.id_map=anom.map 
left JOIN scanning ON anom.id=scanning.id_ano AND scanning.who=? WHERE maps.id_map =? GROUP BY maps.id_map");
  	$rapt_data->execute(array($ship_c,$selec_pos));
  	$det_cur=$rapt_data->fetch();
  	if ($det_cur['mdet']==0) {
    		$rap_jump=1;
  	} else {
    		$rap_jump=0;
  	}
}

$head='<!DOCTYPE Html PUBLIC "-//W3C//DTD Html 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html lang="ru-RU">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
$head=$head."<title>ПАНЕЛЬ НАВИГАЦИИ</title>
<link href='css/bsg.css' rel='stylesheet' type='text/css'>
<script type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js'></script>
</head><body>
<div style='position:relative;min-height: 100%;margin-left:0px;margin-right:0px;background-image:url(\"img/fons/sector_";
$head=$head.$m_cur[0]['name'].".jpg\"); background-size:100% 100%;'><div id='maket'>";
$head=$head."<div id='left'><div id='panel1'><img src='img/fleet/".$dest_data[0]['image'].".png' style='width:100%;height:auto;'></div>";
//echo session_id();

if ($giper==0) {
//неисправен гипердвигатель
    print $head;
    print '<div id="countdown" class="countdown">
		<div class="countdown-text">
        		<span>неисправность гипердвигателя</span>
      		</div>';
      		echo "<div class='countdown-number'>
        			<span class='hour countdown-time'></span>
        			<span class='minutes countdown-time'></span>
        			<span class='seconds countdown-time'></span>
      			</div></div>";
    		echo "<div id='deadline-message' class='deadline-message'>";
		echo "<div class='countdown-text'>
       			<span>неисправность гипердвигателя</span>
		      </div>";
      		echo "<div class='dmessage' style='background-size:100% 100%; background-image:url(\"img/but-red-long-x.png\");'><span>прыжок невозможен</span>
      			</div>";
          echo "</div>";
} else {
   if ($fuel<$fleet_data['fuel']) {
//недостаточно топлива
     print $head;
     print '<div id="countdown" class="countdown">
		<div class="countdown-text">
        		<span>недостаточно тилиума</span>
      		</div>';
      		echo "<div class='countdown-number'>
        			<span class='hour countdown-time'></span>
        			<span class='minutes countdown-time'></span>
        			<span class='seconds countdown-time'></span>
      			</div></div>";
    		echo "<div id='deadline-message' class='deadline-message'>";
		echo "<div class='countdown-text'>
       			<span>недостаточно тилиума</span>
		      </div>";
      		echo "<div class='dmessage' style='background-size:100% 100%; background-image:url(\"img/but-red-long-x.png\");'><span>прыжок невозможен</span>
      			</div>";
          echo "</div>";
   } else {
   //топливо есть
     if ($jump>time()){
     //инициирован прыжок или остывает
        if ($dest_pos==0) {
        //гипердвиг остывает, время не истекло
           print $head;
           print '<div id="countdown" class="countdown">
      			<div class="countdown-text">
        			<span>идет зарядка гипердвигателя</span>
      			</div>
      			<div class="countdown-number">
        			<span class="hour countdown-time"></span>
        			<span class="minutes countdown-time"></span>
        			<span class="seconds countdown-time"></span>
      			</div>
    		</div>
    		<div id="deadline-message" class="deadline-message">';
	    if ($selec_pos<>0 && $selec_pos<>$cur_pos) {
		echo "<div class='countdown-text'>
       			<span>&nbsp;</span>
		      </div>";
      		if ($pos<100) {
      		  	echo "<form id='jump' method='post' action='jump.php'><input type='hidden' name='prep' value='",$pos,"'><input type='hidden' name='pre_jump' value='",$pre_jump,"'><input type='hidden' name='dest' value='",$selec_pos,"'>";
                        echo "<a href=# onClick='document.getElementById(\"jump\").submit();return false;'>
        		<div class='dmessage' style='background-size:100% 100%; background-image:url(\"img/but-green-long-x.png\");'><span>ПРЫЖОК</span>
      			</div></a>";
			echo "</form>";
      		} else {
                  if ($rap_jump==1) {
        		echo "<div style='width:100%;display:inline-table;margin-top:20px;margin-bottom:20px;'>
<div style='width:4%;display:table-cell;'></div><div style='display:table-cell;background-size:100% 100%; padding-top:5px;padding-bottom:5px;background-image:url(\"img/but-green-long.png\");'>
<form id='jump' method='post' action='jump.php'><input type='hidden' name='pre_jump' value='",$pre_jump,"'><input type='hidden' name='prep' value='",$pos,"'>
<input type='hidden' name='dest' value='",$selec_pos,"'><a href=# onClick='document.getElementById(\"jump\").submit();return false;'>
<div style='display:inline-block;'><span>(ПРЫЖОК УДАЧНЫЙ)</span></div></a></form></div><div style='width:4%;display:table-cell;'></div><div style='background-size:100% 100%; padding-top:5px;padding-bottom:5px;background-image:url(\"img/but-grey-long.png\");display:table-cell;'>
<form id='jumpx' method='post' action='jump.php'><input type='hidden' name='fprep' value='",$pos,"'><input type='hidden' name='pre_jump' value='",$pre_jump,"'><input type='hidden' name='dest' value='",$cur_pos,"'>
<a href=# onClick='document.getElementById(\"jumpx\").submit();return false;'><div style='display:inline-block;'><span>(ПРЫЖОК НЕУДАЧНЫЙ)</span></div></a></form></div><div style='width:4%;display:table-cell;'></div> 
      			</div>";
                  } else {
      		  	echo "<form id='jump' method='post' action='jump.php'><input type='hidden' name='prep' value='",$pos,"'><input type='hidden' name='pre_jump' value='",$pre_jump,"'><input type='hidden' name='dest' value='",$selec_pos,"'>";
                        echo "<a href=# onClick='document.getElementById(\"jump\").submit();return false;'>
        		<div class='dmessage' style='background-size:100% 100%; background-image:url(\"img/but-green-long-x.png\");'><span>ПРЫЖОК</span>
      			</div></a>";
			echo "</form>";
                  }
      		}
  	    } else {
		      echo "<div class='countdown-text'>
        		<span>установите координаты</span>
		      </div>";
      		echo "<div class='dmessage' style='  background-size:100% 100%; background-image:url(\"img/but-green-long-x.png\");'>
        		<span>готов</span>
      		      </div>";
  	   }
           echo "</div>";
        } else {
        //идет подготовка к прыжку
           print $head;
           print '<div id="countdown" class="countdown">
      			<div class="countdown-text">
        			<span>расчет координат прыжка</span>
      			</div>';
      		echo "<form id='jump' method='post' action='jump.php'><input type='hidden' name='cancel' value='",$pos,"'>";
      		echo "<a href=# onClick='document.getElementById(\"jump\").submit();return false;'><div class='countdown-number'>
        			<span class='hour countdown-time'></span>
        			<span class='minutes countdown-time'></span>
        			<span class='seconds countdown-time'></span>
      			</div></a></form></div>";
    		echo "<div id='deadline-message' class='deadline-message'>";
		echo "<div class='countdown-text'>
       			<span>&nbsp;</span>
		      </div>";
      		echo "<div class='dmessage' style='background-size:100% 100%; background-image:url(\"img/but-green-long-x.png\");'><span>ПРЫЖОК ЗАВЕРШЕН</span>
      			</div>";
          echo "</div>";
/// прыгает по JS
        }
     } else {
        if ($dest_pos==0) {
        //гипердвиг остыл, готов к прыжку, координаты не заданы
           print $head;
           print '<div id="countdown" class="countdown">
      			<div class="countdown-text">
        			<span>идет зарядка гипердвигателя</span>
      			</div>
      			<div class="countdown-number">
        			<span class="hour countdown-time"></span>
        			<span class="minutes countdown-time"></span>
        			<span class="seconds countdown-time"></span>
      			</div>
    		</div>
    		<div id="deadline-message" class="deadline-message">';
	    if ($selec_pos<>0 && $selec_pos<>$cur_pos) {
		echo "<div class='countdown-text'>
       			<span>&nbsp;</span>
		      </div>";
      		if ($pos<100) {
      		  echo "<form id='jump' method='post' action='jump.php'><input type='hidden' name='prep' value='",$pos,"'><input type='hidden' name='dest' value='",$selec_pos,"'><input type='hidden' name='pre_jump' value='",$pre_jump,"'>";
      		  
      		  echo "<a href=# onClick='document.getElementById(\"jump\").submit();return false;'>
        		<div class='dmessage' style='background-size:100% 100%; background-image:url(\"img/but-green-long-x.png\");'><span>ПРЫЖОК</span>
      			</div></a>";
      		  echo "</form>";
      		} else {
                  if ($rap_jump==1) {
        		echo "<div style='width:100%;display:inline-table;margin-top:20px;margin-bottom:20px;'>
<div style='width:4%;display:table-cell;'></div><div style='display:table-cell;background-size:100% 100%; padding-top:5px;padding-bottom:5px;background-image:url(\"img/but-green-long.png\");'>
<form id='jump' method='post' action='jump.php'><input type='hidden' name='prep' value='",$pos,"'><input type='hidden' name='pre_jump' value='",$pre_jump,"'>
<input type='hidden' name='dest' value='",$selec_pos,"'><a href=# onClick='document.getElementById(\"jump\").submit();return false;'>
<div style='display:inline-block;'><span>(ПРЫЖОК УДАЧНЫЙ)</span></div></a></form></div><div style='width:4%;display:table-cell;'></div><div style='background-size:100% 100%; padding-top:5px;padding-bottom:5px;background-image:url(\"img/but-grey-long.png\");display:table-cell;'>
<form id='jumpx' method='post' action='jump.php'><input type='hidden' name='fprep' value='",$pos,"'><input type='hidden' name='dest' value='",$cur_pos,"'><input type='hidden' name='pre_jump' value='",$pre_jump,"'>
<a href=# onClick='document.getElementById(\"jumpx\").submit();return false;'><div style='display:inline-block;'><span>(ПРЫЖОК НЕУДАЧНЫЙ)</span></div></a></form></div><div style='width:4%;display:table-cell;'></div> 
      			</div>";
                  } else {
      		  	echo "<form id='jump' method='post' action='jump.php'><input type='hidden' name='prep' value='",$pos,"'><input type='hidden' name='dest' value='",$selec_pos,"'><input type='hidden' name='pre_jump' value='",$pre_jump,"'>";
                        echo "<a href=# onClick='document.getElementById(\"jump\").submit();return false;'>
        		<div class='dmessage' style='background-size:100% 100%; background-image:url(\"img/but-green-long-x.png\");'><span>ПРЫЖОК</span>
      			</div></a>";
			echo "</form>";
                  }
      		}
  	    } else {
		      echo "<div class='countdown-text'>
        		<span>установите координаты</span>
		      </div>";
      		echo "<div class='dmessage' style='background-size:100% 100%; background-image:url(\"img/but-green-long-x.png\");'><span>готов</span></div>";
  	   }
           echo "</div>";
        } else {
        //прыжок завершен
  	   header('Location: jump.php?jump'); // перезагружаем файл
        }
     }
   }
}
if ($jump>time()) {
print '<script>
function getTimeRemaining(endtime) {
  var t = Date.parse(endtime) - Date.parse(new Date());
  var seconds = Math.floor((t / 1000) % 60);
  var minutes = Math.floor((t / 1000 / 60) % 60);
  var hour = Math.floor((t / 1000 / 3600) % 60);
  return {
    total: t,
    hour: hour,
    minutes: minutes,
    seconds: seconds
  };
}
function initializeClock(id, endtime) {
  var clock = document.getElementById(id);
  var hourSpan = clock.querySelector(".hour");
  var minutesSpan = clock.querySelector(".minutes");
  var secondsSpan = clock.querySelector(".seconds");
  function updateClock() {
    var t = getTimeRemaining(endtime);
    ';
if ($dest_pos==0) {
print 'if (t.total <= 0) {
      document.getElementById("countdown").className = "hidden";
      document.getElementById("deadline-message").className = "visible";
      clearInterval(timeinterval);
      return true;';
} else {
print 'if (t.total <= 0) {
  document.location.href = "jump.php?jump";
  clearInterval(timeinterval);
  return true;';
}
print '}
    hourSpan.innerHTML = ("0" + t.hour).slice(-2);
    minutesSpan.innerHTML = ("0" + t.minutes).slice(-2);
    secondsSpan.innerHTML = ("0" + t.seconds).slice(-2);
  }
  updateClock();
  var timeinterval = setInterval(updateClock, 1000);
}
var deadline = new Date(';
echo $jump*1000;
echo '); // for endless timer
initializeClock("countdown", deadline);
</script>';
} else {
echo '<script>
      document.getElementById("countdown").className = "hidden";
      document.getElementById("deadline-message").className = "visible";
</script>';
}
?>
    <div id="coord">
<?php 
$maps_data = $pdo->prepare("SELECT maps.id_map as idm, maps.name as mname, count(anom.id) as mano, scanning.who as mdet from maps left JOIN anom ON maps.id_map=anom.map 
left JOIN scanning ON anom.id=scanning.id_ano AND scanning.who=? GROUP BY maps.id_map");
$maps_data->execute([$ship_c]);
$tr=0;
while ($row = $maps_data->fetch()) {
  if ($tr==0){                                       
    echo "<div>";
  }
  echo "<div class='cel'";
  if ($row['idm']==$cur_pos) {
     $cur_name=$row['mname'];
     echo " style='background-color:green;'";
  }
  if ($row['idm']==$dest_pos) {
     echo " style='background-color:yellow;'";
  }
  if ($row['idm']==$selec_pos) {
     echo " style='background-color:blue;'";
  }
  echo "><a href='index.php?map=",$row['idm'],"'";
  if (!isset($row['mdet'])) {echo " style='color:#ff1010;'";}
  echo ">",$row['mname'];
  if (isset($row['mdet'])) {echo "<br>(",$row['mano'],")";}
  echo "</a></div>";
  if ($tr==5) {
    echo "</div>";
    $tr=0;
  } else {
    $tr=$tr+1;
  }
}
?>
<div></div>
    </div>
  </div>
  <div id="bord_c"></div>
  <div id="right">
    <div id='right_top'>
    <div style='padding-top:10px;'>
      <img src='img/<?php echo $cur_name;?>.png' style='width:100%;height:auto;text-align:center;'>
    </div>
    <div id='fuel'>
       <p>ТИЛИУМ</p>
       <p><?php echo $fuel ?></p>
    </div>
<?php
if ($fuel>0) {
echo "<form id='scan1' method='post' action='scan.php'><input type='hidden' name='scan' value='",$ship_c,"'><input type='hidden' name='dest' value='",$cur_pos,"'>";
echo "<a href=# onClick='document.getElementById(\"scan1\").submit();return false;'>";
}
echo "<div id='smessage'><p style='margin-top:0px;margin-bottom:0px'>сканировать сектор</p>
	</div>";
if ($fuel>0) {echo "</a></form>";}
?>
    <div id='power' style='float:right;width:15%;text-align:right;'>
       <a href='auth.php?logout'><img src='img/power_green.png' style='width:100%;height:auto;'></a>    
    </div>
    <div id="radar">
      <img id='im_rad' src='img/radar/<?php echo $dest_data[0]['radimage'];?>.gif' style='width:100%;height:auto;margin-bottom:0px;text-align:center;'>
    </div>
<?php
echo "<div style='position:absolute; width:20px;height:20px;z-index:100;top:88px;left:43%;right:55%;'><img class='mark' id='mark1' style='display:none;' src='img/radar/mayk.gif'></div>";
echo "<div style='position:absolute; width:20px;height:20px;z-index:100;top:95px;left:55%;right:43%;'><img class='mark' id='mark2' style='display:none;' src='img/radar/mayk.gif'></div>";
echo "<div style='position:absolute; width:20px;height:20px;z-index:100;top:97px;left:40%;right:58%;'><img class='mark' id='mark3' style='display:none;' src='img/radar/mayk.gif'></div>";
echo "<div style='position:absolute; width:20px;height:20px;z-index:100;top:120px;left:63%;right:35%;'><img class='mark' id='mark4' style='display:none;' src='img/radar/mayk.gif'></div>";
?>
    </div>
    <div class="scrollbar" id="inform">
    <div id="r2">
<?php
if ($selec_pos<>0){
 $info_pos=$selec_pos;
} else {
 $info_pos=$cur_pos;                          
}
$maps_cur->execute([$info_pos]);
$scan_cur=$maps_cur->fetchAll(PDO::FETCH_ASSOC);
echo "<img src='img/scan/",$scan_cur[0]['name'],"-S.png' style='width:100%;height:auto;'>";
?>
    </div>
<div id="tabss">
<?php
//echo "<input type='hidden' id='current_position' value='",$cur_pos,"'><input type='hidden' id='select_position' value='",$selec_pos,"'>";
//поиск кораблей
//аномалии
$stm = $pdo->prepare("SELECT DISTINCT scanning.id_ano, scanning.level, anom.anomaly, anom.scanned, scanning.report
FROM scanning, anom WHERE scanning.who=:who AND scanning.id_ano=anom.id AND anom.map=:id_map");
$stm->bindValue(':id_map',$info_pos);
$stm->bindValue(':who',$ship_c);
$stm->execute();
$anom_data = $stm->fetchAll(PDO::FETCH_ASSOC);
$num_row=count($anom_data);
if ($num_row>0){
  $jc=$num_row;
  foreach ($anom_data as $dd) {
    echo "<div class='tab'><input type='radio' id='tab-",$jc+100,"'";
    if ($jc==1) {echo " checked";}
    echo " name='tab-group-1'><label for='tab-",$jc+100,"'";
    echo ">Объект ",$jc,"</label><div class='content'>";
    if ($dd['level']==0){
      echo "<p>",$dd['anomaly'],"</p>";
      echo "<h3>Не исследовано</h3>";
    }
    if ($dd['level']==1){
      echo "<p>",$dd['anomaly'],"</p>";
      echo "<hr>";
      echo "<h3>Результаты сканирования:</h3>";
      echo "<p>",$dd['scanned'],"</p>";
    }
    if ($dd['level']==2){
      echo "<p>",$dd['anomaly'],"</p>";
      echo "<hr>";
      echo "<h3>Результаты сканирования:</h3>";
      echo "<p>",$dd['scanned'],"</p>";
      echo "<hr>";
      echo "<h2>Отчет о разведке:</h2>";
      echo "<p>",$dd['report'],"</p>";
    }
    echo "</div></div>";
    $jc=$jc-1;
//    print_r($dd);
  }
  unset($dd);
  echo "</div>";
}
?>
</div>
    </div>
  </div>
</div>
</div>
<script type="text/javascript">
var cur_obj=document.getElementById('tabss').innerHTML;

window.onload = function() {
          document.getElementById('coord').style.display = 'table';
	  var blocks = document.getElementsByClassName( "cel" ); 
	  for( var j = 0; j < blocks.length; j++){ 
	    var hff = blocks[j].clientWidth;
	    blocks[j].style.height = hff +'px'; 
	  }
	  setTimeout(detect_ship,100);
}

function detect_ship() {
        $.ajax({
            type: "POST",
            url: "detect.php",
            data: {current_pos:<?php echo $cur_pos ?>,select_pos:<?php echo $selec_pos ?>,myid:<?php echo $pos ?>},
            success: function(json) {
		var obj=JSON.parse(json);
		var fleets='';               
		var marks=obj.count;
		if (marks>4){marks=4;}
		for(var n=1; n<=marks; n++) {
		    document.getElementsByClassName('mark')[n-1].style.display = 'block';
 		}
		if (obj.current_pos==obj.select_pos || obj.select_pos==0) {
		  for( var m=1; m<=obj.count; m++) {
		    fleets=fleets+'<div class="tabf"><input type="radio" id="tab-'+m+'" name="tab-group-1"><label for="tab-'+m+'">';
		    if (obj.fleets[m].type==2) {
			fleets=fleets+'&nbsp;<img src="img/fleet.png" style="height:20px;width:auto;">&nbsp;';
		    }
		    if (obj.fleets[m].type==1) {
			fleets=fleets+'&nbsp;<img src="img/ship.png" style="height:20px;width:auto;">&nbsp;';
		    }
		    fleets=fleets+'</label><div class="content">Отметка на радаре<hr>';
		    if (obj.fleets[m].type==2) {fleets=fleets+'<b>Флот: </b>';}
		    fleets=fleets+obj.fleets[m].fname;
		    if (obj.fleets[m].type==2) {fleets=fleets+'<hr>Кораблей во флоте: '+obj.fleets[m].cship;}
                    fleets=fleets+'</div></div>';
		  }
		}
		document.getElementById('tabss').innerHTML=fleets+cur_obj;
	   }
       });
       return false;
}
setInterval(detect_ship,20000);
</script>
</body>
</html>