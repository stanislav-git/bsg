<?php
require_once('../modul/connect.php');
if (isset($_POST['del'])) {
	$del_news=$pdo->prepare("DELETE FROM news WHERE timnews=? LIMIT 1");
	$del_news->execute([$_POST['mark']]);
	header('Location: ../admin.php?news=1');
}
if (isset($_POST['save'])) {
	$q_upd=$pdo->prepare("update news set autor=?,news=?,fleet=?,timnews=? where timnews=?");
	$q_upd->execute(array($_POST['autor'],$_POST['news'],$_POST['fleet'],strtotime($_POST['tim']),$_POST['mark']));
	header('Location: ../admin.php?news=1');
}
if (isset($_POST['add'])) {
	$q_upd=$pdo->prepare("insert into news (autor,news,fleet,timnews) VALUES (?,?,?,?)");
	$q_upd->execute(array($_POST['autor'],$_POST['news'],$_POST['fleet'],strtotime($_POST['tim'])));
	header('Location: ../admin.php?news=1');
}
?>