<?php
session_start();
if (isset($_SESSION['user_id'])){
	include_once('../modul/connect.php');
	if (isset($_POST['killuser'])){
		$id=$_POST['id'];
		$upd=$pdo->prepare("UPDATE users set live=0 where id=?");
		$upd->execute([$id]);
		header('Location: ../admin.php?person');
		exit;
	}
	if (isset($_POST['liveuser'])){
		$id=$_POST['id'];
		$upd=$pdo->prepare("UPDATE users set live=1 where id=?");
		$upd->execute([$id]);
		header('Location: ../admin.php?person');
		exit;
	}
	if (isset($_POST['addusers'])){
		$name=trim($_POST['user']);
		$enemy=$_POST['sylon'];
		$access=$_POST['access'];
		$d=round($access/1000)*1000;
		if ($d==$access) {$kom=(int)$_POST['id_f'];} else {$kom=-1;}
		$upd=$pdo->prepare("INSERT INTO users (name, dolj, access,enemy,id_f) VALUES (?,?,?,?,?)");
		$upd->execute(array($name,$access,$kom,$enemy,$_POST['id_f']));
		header('Location: ../admin.php?person');
		exit;
	}
	if (isset($_POST['editusers'])){
		$id=$_POST['id'];
		$name=trim($_POST['user']);
		$enemy=$_POST['sylon'];
		$access=$_POST['access'];
		$d=round($access/1000)*1000;
		if ($d==$access) {$kom=(int)$_POST['id_f'];} else {$kom=-1;}
		$upd=$pdo->prepare("UPDATE users set name=?, dolj=?,enemy=?,id_f=?,access=? where id=?");
		$upd->execute(array($name,$access,$enemy,$_POST['id_f'],$kom,$id));
		header('Location: ../admin.php?person');
		exit;
	}
	header('Location: ../admin.php');
	exit;
}
header('Location: ../testsess.php');
?>