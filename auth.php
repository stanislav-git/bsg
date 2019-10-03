<?php
session_start(); // стартуем сессию, PHP записывает в нее данные находя их по загруженным кукам если они уже есть
include('connect.php');
$upr=$pdo->prepare("UPDATE destination SET sid = ? , tim = ? WHERE who = ?");
$head="<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<title>Авторизация</title>
</head><body style='background-image:url(\"img/fons/hub_large.jpg\"); background-size: 100% 100%;'>
	<div style='position:absolute;left:175px;top:130px;'><form action='auth.php' method='post'>
		<div style='width:100%;height:30%;color:white;font-family:\"Crystal\",\"Arial\";font-size:1.2em;text-align:center;'>";
$foot="<br>&nbsp;</div><table>
			<tr>
				<td style='color:white;'>Логин:</td>
				<td><input type='text' name='login' /></td>
			</tr>
			<tr>
				<td style='color:white;'>Пароль:</td>
				<td><input type='password' name='password' /></td>
			</tr>
			<tr>
				<td></td>
				<td><input type='submit' value='Авторизоваться' /></td>
			</tr>
		</table>
	</div></form></body></html>";
if (isset($_GET['logout'])) // блок обрабатывающий завершение сессии
{
	if (isset($_SESSION['user_id'])){
  	  	if ((int)trim($_SESSION['user_id'])>100) {
   	    		$rapt=(int)trim($_SESSION['user_id']);
   	    		$ship_m=round(($rapt/100-floor($rapt/100))*100);
            		$askq= $pdo->prepare("SELECT locat FROM destination WHERE who = ? union select locat from destination WHERE who = ?");
            		$askq->execute(array($rapt,$ship_m));
            		if ($askq->rowCount() == 1) {
              			$ful=$pdo->prepare("SELECT fuel FROM destination WHERE who = ?");
              			$ful->execute([$rapt]);
              			$coun_f = $ful->fetchColumn();
              			$updfuel=$pdo->prepare("UPDATE destination SET `fuel` = `fuel`+ ? WHERE who = ?");
              			$updfuel->execute(array($coun_f,$ship_m));
            		} 
            		$updrap=$pdo->prepare("UPDATE destination set locat=0, sid='', tim=0 WHERE who=?");
            		$updrap->execute([$rapt]);
  	  	} else {
	        	$a=$_SESSION['user_id'];
        		$b='';
        		$upr->execute(array($b,0,$a));
  	  	}
        }
	$_SESSION=array();
//	if (ini_get("session.use_cookies")) {
//    		$params = session_get_cookie_params();
//    		setcookie(session_name(), '', time() - 42000,
//        	$params["path"], $params["domain"],
//        	$params["secure"], $params["httponly"]
//    		);
//	}
 	setcookie('login', '', 0, "/");
	setcookie('password', '', 0, "/");
//        session_destroy();
	header('Location: auth.php'); // перезагружаем файл
	exit;
}
 
if (isset($_SESSION['user_id']) and !isset($_GET['logout'])) // если юзер залогинился показываем ему нужные данные
{
	$user_id = $_SESSION['user_id']; // запоминаем id юзера из переменных сессии
	$user_name = $_SESSION['user_name']; // запоминаем имя юзера из переменных сессии
//-- Авторизация пройдена
        if ($user_id=='0') {
		header('Location: admin.php'); // перезагружаем файл
        } else {
//проверим на session_id
                $asksid=$pdo->prepare("SELECT sid,tim FROM destination WHERE who = ?");
                $askwho=$_SESSION['user_id'];
                $asksid->execute([$askwho]);
                $rsid=$asksid->fetch();
                $sid=$rsid['sid'];
                $tim=$rsid['tim'];
                $bsid=session_id();
//  or $sid=='' or $tim+2400<time()                
                if ($sid == $bsid) {
                  	$upr->execute(array($bsid,time(),$askwho));
		  	header('Location: index.php'); // перезагружаем файл
                } else {
                  	$_SESSION=array();
			session_destroy();
	 		setcookie('login', '', 0, "/");
			setcookie('password', '', 0, "/");
		  	header('Location: auth.php'); // перезагружаем файл
		}
        }
}
 
if (!empty($_POST) && !isset($_SESSION['user_id']) && !isset($_GET['logout'])) {
	// производим авторизацию если было обращение к файлу, а сессии еще не существует
	// коннект к базе с юзерами
	$login = (isset($_POST['login'])) ? trim($_POST['login']) : '';
//пилоты рапторов
	if (stripos($login, 'пилот') !== false) {
        	$stmt = $pdo->prepare("SELECT * FROM destination WHERE `name` = ?");
        	$stmt->execute([$login]);
   		$dest_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$num_rows = count($dest_data);
		$password = md5(trim($_POST['password']));
		if ($num_rows >0) {
                	$who=$dest_data[0]['name'];
			if ($dest_data[0]['pass'] == $password){
		  		$rap_id=0;
		  		$rap_name='';
		  		foreach ($dest_data as $dd) {
		    			if ($dd['locat']==0 or $dd['tim']+2400<time()) {
		        			//есть ли свободный раптор
 						$rap_id = $dd['who'];
		        			$rap_name=$dd['name'];
                    			}
		  		}
		  		if ($rap_id<>0) {
 					$_SESSION['user_id'] = $rap_id;
					$_SESSION['user_name'] = $rap_name;
 
					// если пользователь решил "запомнить себя"
					// то записываем ему в куку логин с хешем пароля на 24 часа
 					$time = 86400;
 					setcookie('login', $login, time()+$time, "/");
					setcookie('password', $password, time()+$time, "/");
					$mfleet=$pdo->prepare("SELECT locat, fuel FROM destination WHERE who= ?");
					$mship=round(($rap_id/100-floor($rap_id/100))*100);
					$mfleet->execute([$mship]);
					$motherfleet=$mfleet->fetch();
					$motfl=$motherfleet['locat'];
		        		$fuel=$motherfleet['fuel']-10;
					if ($motherfleet['fuel']>10) {
//			  			echo $fuel,"<br>",is_num($mship);
                          			$upd = $pdo->prepare("UPDATE destination set locat = ?, fuel = 10, sid= ?, tim= ? where who= ?");
                          			$upd->execute(array($motfl,session_id(),time(),$rap_id));
                          			$count = $upd->rowCount();
                          			if ($count==1) {
                            				$mupd = $pdo->prepare("UPDATE destination set fuel = ? where who= ?");
                            				$mupd->execute(array($fuel,$mship));
                            				$count = $mupd->rowCount();
			    				if ($count ==1) {
  			      					header('Location: auth.php');
			      					exit;
	    		    				} else {
	    		      					echo $mship," ",$rap_id;
	    		      					print_r($motherfleet);
          		      					die('не обновлен mothership $mship');
			    				}
			  			} else {
			    				die('не обновлен раптор $mship');
			  			}
					} else {
			                  	$_SESSION=array();
                                		echo $head;
			        		echo "Нет тилиума на заправку раптора";
						echo $foot;
						die('');
                        		}
                  		} else {
					$_SESSION=array();
                        		echo $head;
					echo "Нет свободных машин";
					echo $foot;
					die('');
                  		}
			} else {
				$_SESSION-array();
				echo $head;
				echo "В доступе отказано!";
				echo $foot;
				die('');
			}
	   	} else {
			$_SESSION=array();
			echo $head;
			echo "Несанкционированный доступ - отказано!";
			echo $foot;
			die('');
	   	}
	} else {
//ком флота или админы
        	$stmt = $pdo->prepare("SELECT * FROM destination WHERE `name` = ?");
           	$stmt->execute([$login]);
   	   	$dest_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
	   	$num_rows = count($dest_data);
	   	$password = md5(trim($_POST['password']));
//Проверяем на админа
	   	if ($login=='admin') {
	         	if ($password=='f1c1592588411002af340cbaedd6fc33') {
 				$_SESSION['user_id'] = '0';
				$_SESSION['user_name'] = $login;
				session_write_close();
 				$time = 86400;
 				setcookie('login', $login, time()+$time, "/");
				setcookie('password', $password, time()+$time, "/");
				header('Location: auth.php');
				exit;
	         	}
           	}
	   	if ($num_rows == 1) {
	        	if ($dest_data[0]['tim']+2400<time() or $dest_data[0]['sid']==session_id()) {
			// теперь хешируем введенный пароль (тем же способом что при регистрации)
                		$who=$dest_data[0]['name'];
				if ($dest_data[0]['pass'] == $password) {
				// то записываем в сессию id и name юзера, которыми воспользуемся позднее
 					$_SESSION['user_id'] = $dest_data[0]['who'];
					$_SESSION['user_name'] = $dest_data[0]['name'];
                                	$updlo = $pdo->prepare("UPDATE destination set sid= ?, tim= ? where who= ?");
                                	$updlo->execute(array(session_id(),time(),$_SESSION['user_id']));
                                        session_write_close();
					// если пользователь решил "запомнить себя"
					// то записываем ему в куку логин с хешем пароля на 24 часа
 					$time = 86400;
 					setcookie('login', $login, time()+$time, "/");
					setcookie('password', $password, time()+$time, "/");
 
					//и перезагружаем скрипт
					header('Location: auth.php');
					exit;
 
				// если вам понядобится использовании данных сесси на других страницах используйте в их начале session_start();
				} else
				{
					$_SESSION=array();
					echo $head;
					echo "В доступе отказано!";
					echo $foot;
					die('');
				}

	        	} else {
				$_SESSION=array();
				echo $head;
				echo "В рубке уже есть капитан - В доступе отказано!";
				echo $foot;
				die('');
	        	}	
		} else if ($num_rows>1)
		{
			$_SESSION=array();
			echo $head;
			echo "Обнаружено дублирование флотов, сообщите мастеру - В доступе отказано!";
			echo $foot;
			die('');
		} else {
			$_SESSION=array();	
			echo $head;
			echo "Дверь на мостик закрыта - отказано!";
			echo $foot;
			die('');
		}
	}
}
if (!isset($_SESSION['user_id'])) {
 // если сесси нет показваем форму авторизации
	print '
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Авторизация</title>
</head><body style="background-image:url(\'img/fons/hub_large.jpg\'); background-size: 100% 100%;">
	<div style="position:absolute;left:175px;top:130px;"><form action="auth.php" method="post">
		<table>
			<tr>
				<td style="color:white;">Логин:</td>
				<td><input type="text" name="login" /></td>
			</tr>
			<tr>
				<td style="color:white;">Пароль:</td>
				<td><input type="password" name="password" /></td>
			</tr>
			<tr>
				<td></td>
				<td><input type="submit" value="Авторизоваться" /></td>
			</tr>
		</table>
	</div></form></body></html>
	';
}
?>