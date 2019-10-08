<?php
session_start();
include('connect.php');
if (isset($_COOKIE['sess']) or isset($_SESSION['user_id'])) {
        if (isset($_SESSION['user_id'])){
		$sess=session_id();
	} else {
		$sess=$_COOKIE['sess'];
	}
//проверяем, валидная ли кука по времени в БД, может кто уже зашел
	$quser=$pdo->prepare("SELECT who,name,pass,locat,sid,tim FROM destination WHERE sid=?");
	$quser->execute([$sess]);
	$ask_user = $quser->fetchAll(PDO::FETCH_ASSOC);
	$num_rows = count($ask_user);
	if ($num_rows<>1) {
		if (isset($_SESSION['user_id'])){
			$_SESSION=array();
			session_destroy();
		}
 		setcookie('login', '', 1, "/");
		setcookie('sess', '', 1, "/");
		header('Location: testsess.php?err=6'); // перезагружаем файл
		exit;
	}
	$tr=0;
	if (isset($_SESSION['user_id'])){
		if ($_SESSION['user_id']==$ask_user[0]['who']) {
			$tr=1;
		}
	}
	if (isset($_COOKIE['sess'])){
		if ($_COOKIE['login']==$ask_user[0]['name']){
			$tr=1;
		}
	}
	if ($tr==1) {
//сессия просрочена, обновляем
		if (isset($_SESSION['user_id'])){
			setcookie('sess',$sess, time()+$ttlcookie, "/");
		} else {
			session_id($_COOKIE['sess']);
			setcookie('sess',$sess, time()+$ttlcookie, "/");
			session_start();
		}
		$who=$_SESSION['user_id']=$ask_user[0]['who'];
               	$updlo = $pdo->prepare("UPDATE destination set sid= ?, tim= ? where who= ?");
               	$updlo->execute(array($sess,time(),$who));
		header('Location: index.php'); // перезагружаем файл
		exit;
	}
	if (isset($_SESSION['user_id'])){
		$_SESSION=array();
		session_destroy();
	}
	setcookie('login', '', 1, "/");
	setcookie('sess', '', 1, "/");
	header('Location: testsess.php?err=8'); // перезагружаем файл
	exit;
} else {
	if (isset($_SESSION['user_id'])){
		$_SESSION=array();
		session_destroy();
	}
	setcookie('login', '', 1, "/");
	setcookie('sess', '', 1, "/");
	header('Location: testsess.php?err=0'); // перезагружаем файл
	exit;
}
?>