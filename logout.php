<?php
include_once('modul/connect.php');
if (isset($_GET['logout'])){
	session_start();
	if (isset($_SESSION['user_id'])){
		$upr=$pdo->prepare("UPDATE destination SET sid = ? , tim = ? WHERE who = ?");
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
	session_destroy();
 	setcookie('login', '', 1, "/");
	setcookie('sess', '', 1, "/");
	header('Location: testsess.php?err=0'); // перезагружаем файл
	exit;
}
?>