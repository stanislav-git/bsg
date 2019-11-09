<?php
include_once('modul/connect.php');
include_once('modul/funct.php');
if (isset($_GET['logout'])){
	session_start();
	if (isset($_SESSION['user_id'])){
		$upr=$pdo->prepare("UPDATE destination SET sid = ? , tim = ? WHERE who = ?");
		$death=0;
  		if ((int)trim($_SESSION['user_id'])>1000) {
  			$rapt=(int)trim($_SESSION['user_id']);
    			$ship_m=round(($rapt/1000-floor($rapt/1000))*1000);
       			$askq= $pdo->prepare("SELECT locat FROM destination WHERE who = ? union select locat from destination WHERE who = ?");
       			$askq->execute(array($rapt,$ship_m));
       			if ($askq->rowCount() == 1) {
       				$ful=$pdo->prepare("SELECT fuel FROM destination WHERE who = ?");
				$ful->execute([$rapt]);
       				$coun_f = $ful->fetchColumn();
 				resurs_upd($ship_m,'Раптор вернулся',$coun_f,0,0);
				lost_human($ship_m,-2,'Раптор вернулся');
      			} else {
      				$death=1;
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
	session_destroy();
 	setcookie('login', '', 1, "/");
	setcookie('sess', '', 1, "/");
	if ($death==0){
		header('Location: testsess.php?err=0'); // перезагружаем файл
	} else {
		header('Location: death.php'); // перезагружаем файл
	}
	exit;
}
?>