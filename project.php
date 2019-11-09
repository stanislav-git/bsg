<?php
session_start();
include_once('modul/connect.php');
if (isset($_COOKIE['user'])){
	$q_fleet=$pdo->prepare("select id_f,dolj from users where id=? LIMIT 1");
	$q_fleet->execute([$_COOKIE['user']]);
	$data_fleet=$q_fleet->fetch();
}
if (isset($_SESSION['user_id']) or isset($data_fleet['id_f'])){
session_write_close();
$head='<!DOCTYPE html>
<html lang="ru-RU">
<head>
<meta charset="utf-8">
<title>ПРОЕКТЫ</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script type="text/javascript" src="js/jquery.min.js"></script>
<link rel="stylesheet" href="css/base1.css">
<link rel="stylesheet" href="css/project.css">
</head>
<body>
<div id="myModal" class="modal"><div class="modal-content" id="info"></div></div>
<header>ПРОЕКТЫ: </header>
<div class="container">
<div class="main-content">
<div class="content-wrap">';
echo $head;

?>