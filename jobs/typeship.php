<?php
session_start();
if (isset($_SESSION['user_id'])){
	include_once('../modul/connect.php');
	include_once('../modul/funct.php');
	if (isset($_POST['add_type'])){
		if (trim($_POST['type'])<>''){
		        $q_max=$pdo->query("select max(id) from typeship");
			$q_max->execute();
			$maxtype=$q_max->fetchColumn();
			$maxtype=$maxtype+1;
			if ($_POST['sizz']==1) {$size='Огромный';}
			if ($_POST['sizz']==2) {$size='Большой';}
			if ($_POST['sizz']==3) {$size='Средний';}
			$upd_type=$pdo->prepare("INSERT INTO typeship set purp=?, cargo=?, sizz=?, type=?, jfuel=?, rfuel=?, rwater=?,rcomp=?,dfuel=?,dwater=?,dcomp=?,nfuel=?,nwater=?,ncomp=?, id=?");
			$upd_type->execute(array($maxtype,$size,$_POST['sizz'],trim($_POST['type']),$_POST['jump'],$_POST['rfuel'],$_POST['rwater'],$_POST['rcomp'],$_POST['dfuel'],$_POST['dwater'],$_POST['dcomp'],$_POST['nfuel'],$_POST['nwater'],$_POST['ncomp'],$maxtype));
		}
		header('Location: ../admin.php?typeship');
		exit;
	}
	if (isset($_POST['sav_type'])) {
		$idtype=$_POST['idt'];
		$upd_type=$pdo->prepare("UPDATE typeship set type=?, jfuel=?, rfuel=?, rwater=?,rcomp=?,dfuel=?,dwater=?,dcomp=?,nfuel=?,nwater=?,ncomp=? WHERE id=?");
		$upd_type->execute(array(trim($_POST['type']),$_POST['jump'],$_POST['rfuel'],$_POST['rwater'],$_POST['rcomp'],$_POST['dfuel'],$_POST['dwater'],$_POST['dcomp'],$_POST['nfuel'],$_POST['nwater'],$_POST['ncomp'],$_POST['idt']));
		header('Location: ../admin.php?typeship');
		exit;
	}
}
header('Location: ../testsess.php');
?>