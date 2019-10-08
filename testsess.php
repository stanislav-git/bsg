<?php
include('connect.php');
$head="<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<title>Авторизация</title>
</head><body style='background-image:url(\"img/fons/hub_large.jpg\"); background-size: 100% 100%;'>
        <div id='localtime' style='position:absolute;left:65px;top:60px;color:white;'>Локальное время: </div>
        <div id='servertime' style='position:absolute;left:60px;top:100px;color:white;'>Время на сервере: ".date('H:i:s',time())."</div>
	<div style='position:absolute;left:175px;top:130px;'><form action='testsess.php' id='logi' method='post'>
		<div style='width:100%;height:30%;color:white;font-family:\"Crystal\",\"Arial\";font-size:1.2em;text-align:center;'>";
if (isset($_POST['login'])){
	$login=$_POST['login'];
	$passw=md5(trim($_POST['passw']));
//проверяем юзера в бд
	$quser=$pdo->prepare("SELECT who,pass,locat,sid,tim FROM destination WHERE name=?");
	$quser->execute([$login]);
	$ask_user = $quser->fetchAll(PDO::FETCH_ASSOC);
	$num_rows = count($ask_user);
	if ($num_rows==1) {
//комфлота найден
		if ($ask_user[0]['pass']<>$passw) {
//неверный пароль
			header('Location: testsess.php?err=2'); // перезагружаем файл
			exit;
		}
//проверяем sid|tim|cookie
	        session_start();
		$_SESSION['user_id']=$ask_user[0]['who'];
		$sess=session_id();
		$time = 86400;
		setcookie('login', $login, time()+$time, "/");
		setcookie('sess',$sess, time()+$ttlcookie, "/");
        	if ($ask_user[0]['tim']+$ttlsession<time() or $ask_user[0]['sid']==$sess) {
			session_write_close();
	//зашли
			$time = 86400;
			setcookie('login', $login, time()+$time, "/");
			setcookie('sess',$sess, time()+$ttlcookie, "/");
			$who=$ask_user[0]['who'];
                       	$updlo = $pdo->prepare("UPDATE destination set sid= ?, tim= ? where who= ?");
                       	$updlo->execute(array($sess,time(),$who));
	// ушли на главную
			header('Location: index.php'); 
			exit;
		} else {
			$_SESSION=array();
			session_destroy();
	//недавно был другой чувак
			header('Location: testsess.php?err=4'); // перезагружаем файл
			exit;
		}
	} elseif ($num_rows>1) {
//пилоты раптора
  		$rap_id=0;
  		$rap_n=0;
  		foreach ($ask_user as $dd) {
    			if ($dd['locat']==0 or $dd['tim']+$ttlsession<time()) {
        			//есть ли свободный раптор
				$rap_id = $dd['who'];
				$rap_sid = $dd['sid'];
				$rap_tim = $dd['tim'];
          		}
          	}
		if ($ask_user[0]['pass'] <> $passw){
//неверный пароль
			header('Location: testsess.php?err=2'); // перезагружаем файл
			exit;
		}
		if ($rap_id==0) {
//нет свободных рапторов
			header('Location: testsess.php?err=3'); // перезагружаем файл
			exit;
		}
//проверка sid|tim|cookie
	        session_start();
		$_SESSION['user_id']=$rap_id;
		$sess=session_id();
		session_write_close();
//топливо и позиция
		$mfleet=$pdo->prepare("SELECT locat, fuel FROM destination WHERE who= ?");
		$mship=round(($rap_id/100-floor($rap_id/100))*100);
		$mfleet->execute([$mship]);
		$motherfleet=$mfleet->fetch();
		$motfl=$motherfleet['locat'];
       		$fuel=$motherfleet['fuel']-10;
		if ($motherfleet['fuel']>10) {
       			$upd = $pdo->prepare("UPDATE destination set locat = ?, fuel = 10, sid= ?, tim= ? where who= ?");
       			$upd->execute(array($motfl,$sess,time(),$rap_id));
       			$count = $upd->rowCount();
       			if ($count==1) {
       				$mupd = $pdo->prepare("UPDATE destination set fuel = ? where who= ?");
       				$mupd->execute(array($fuel,$mship));
       				$counm = $mupd->rowCount();
    				if ($counm <>1) {
      					echo $mship," ",$rap_id;
      					print_r($motherfleet);
      					die('не обновлен mothership $mship');
    				}
  			} else {
    				die('не обновлен раптор $mship');
  			}
		} else {
	//нет топлива
			$_SESSION=array();
			session_destroy();
			header('Location: testsess.php?err=5'); // перезагружаем файл
			exit;
		}
        	if ($rap_tim+$ttlsession<time() or $rap_sid==$sess) {
	//зашли
			$time = 86400;
			setcookie('login', $login, time()+$time, "/");
			setcookie('sess',$sess, time()+$ttlcookie, "/");
                       	$updlo = $pdo->prepare("UPDATE destination set sid= ?, tim= ? where who= ?");
                       	$updlo->execute(array($sess,time(),$rap_id));
	// ушли на главную
			header('Location: index.php'); 
			exit;
		} else {
			$_SESSION=array();
			session_destroy();
	//недавно был другой чувак
			header('Location: testsess.php?err=4'); // перезагружаем файл
			exit;
		}
	} else {
		if ($login=='admin'){
// Может админ?


//
			header('Location: admin.php');
			exit;
		}	
//Левый чувак	
		header('Location: testsess.php?err=1'); // перезагружаем файл
		exit;
	}
}
if (!isset($_SESSION['user_id']) or !isset($_POST)) {
	$foot="<br>&nbsp;</div><table>
			<tr>
				<td style='color:white;'>Логин:</td>
				<td><input type='text' name='login' /></td>
			</tr>
			<tr>
				<td style='color:white;'>Пароль:</td>
				<td><input type='hidden' name='times' value='' id='times'><input type='password' name='passw' /></td>
			</tr>
			<tr>
				<td></td>
				<td><input type='button' value='Авторизоваться' onclick='timez();'/></td>
			</tr>
		</table>
	</div></form>
<script type='text/javascript'>
Data = new Date();
Hour = Data.getHours();
Minutes = Data.getMinutes();
Seconds = Data.getSeconds();
document.getElementById('localtime').innerHTML='<p>Текущее время: '+Hour+':'+Minutes+':'+Seconds+' TZ:'+Data.getTimezoneOffset()/60+'</p>';
function timez(){
  document.getElementById('times').value=Date.parse(Data)/1000;
  document.getElementById(\"logi\").submit();
}
</script></body></html>";
	print $head;
	if (isset($_GET['err'])){
//		if ($_GET['err']==0){echo "Нажали выход";}
		if ($_GET['err']==1){echo "Неправильный логин";}
		if ($_GET['err']==2){echo "Неправильный пароль";}
		if ($_GET['err']==3){echo "Нет свободных машин";}
		if ($_GET['err']==4){echo "Капитан уже на мостике";}
		if ($_GET['err']==5){echo "Недостаточно тилиума";}
		if ($_GET['err']==6){echo "Кука или сессия не валидна";}
		if ($_GET['err']==7){echo "7";}
		if ($_GET['err']==8){echo "8";}
	}
	print $foot;
}
?>