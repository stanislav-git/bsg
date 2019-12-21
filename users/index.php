<?php
include_once('../modul/connect.php');
include_once('../modul/funct.php');
if (isset($_GET['logout'])) {
	setcookie('fleet','',time()-3600,'/');
	setcookie('user','',time()-3600,'/');
	setcookie('access','',time()-3600,'/');
	setcookie('name','',time()-3600,'/');
	unset($_COOKIE['user']);
	unset($_COOKIE['access']);
	unset($_COOKIE['name']);
	unset($_COOKIE['fleet']);
	header('Location: index.php');
}
if (isset($_COOKIE['name'])) {
       	$search=complit(trim($_COOKIE['name']));
	$time = 86400;
	setcookie('user', $search['id'], time()+$time, "/");
	setcookie('access',$search['dolj'], time()+$time,"/");
	setcookie('name', $search['user'], time()+$time, "/");
	setcookie('fleet',$search['id_f'], time()+$time, "/");
} else {
	if (isset($_GET['user'])){
		if(!empty($_GET['user'])){
        		$search=complit(trim($_GET['user']));
			$time = 86400;
			setcookie('user', $search['id'], time()+$time, "/");
			setcookie('access',$search['dolj'], time()+$time,"/");
			setcookie('name', $search['user'], time()+$time, "/");
			setcookie('fleet',$search['id_f'], time()+$time, "/");
		}
	}
}
echo '<!DOCTYPE html>
<html lang="ru-RU">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Личный кабинет</title>
<!--//	<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet"> //-->
	<link rel="stylesheet" href="../css/jquery-ui.min.css">
	<link rel="stylesheet" href="../css/users.css">
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<body>';
echo "<div style='position:relative;min-height: 100%;margin-left:0px;margin-right:0px;background-image:url(\"../img/kosmos.jpg\"); background-size:100% 100%;'>
<div id='maket'>
<div id='centr'><div id='block1'>";
if (isset($search['user'])){echo '<a href="index.php?logout=1" title="Выход" style="display:block;margin-bottom:4px;">';}
echo "<div class='image' style='margin-top:0px;'><img src='../img/name.png'></div>";
if (isset($search['user'])){echo '</a>';}
echo '<div class="container cont">';
if (isset($search['user'])){echo '<div class="col-sm-offset-2 col-sm-6"><input type="text" value="',$search['user'],'" disabled></div>';}
else {echo '<form class="form-horizontal" method="post" id="form">
	<div class="form-group">
		<div class="col-sm-6"><input type="text" class="form-control" id="search" name="search" placeholder="Поиск..."></div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-6">
			<div></div>
		</div>
	</div>
</form>';}
echo '</div>
<div class="image"><img src="../img/dolj.png"></div>
<div class="cont"><input type="text" disabled value="';if (isset($search)){echo dolj($search['dolj']);}echo'"></div>
<div class="image"><img src="../img/myfleet.png"></div>
<div class="cont"><input type="hidden" name="id_f" value="';if (isset($search)){echo $search['id_f'];}echo'"><input type="text" disabled value="';if (isset($search)){echo $search['fleet'];}echo'"></div>
</div><div id="block2">';
if (isset($search)){
echo '<div class="butt">ПРОСМОТРЕТЬ ОТЧЕТЫ</div>';
} else {
echo '<div class="butt off">ПРОСМОТРЕТЬ ОТЧЕТЫ</div>';
}
if (isset($search)){
echo '<a href="../project.php" style="display:block;margin-bottom:0px;"><div class="butt">УПРАВЛЕНИЕ ПРОЕКТАМИ</div></a>';
} else {
echo '<div class="butt off">УПРАВЛЕНИЕ ПРОЕКТАМИ</div>';
}
if (isset($search)){
echo '<a href="../manage_ships.php" style="display:block;margin-bottom:0px;"><div class="butt">УПРАВЛЕНИЕ КОРАБЛЯМИ</div></a>';
} else {
echo '<div class="butt off">УПРАВЛЕНИЕ КОРАБЛЯМИ</div>';
}
if (isset($search)){if (($search['dolj']<>0) and ((round($search['dolj']/1000)==$search['dolj']/1000) or (round(($search['dolj']-1)/1000)==(($search['dolj']-1)/1000)))) {echo'<a href="../manage_fleet.php" stype="display:block;margin-bottom:0px;">
<div class="butt">УПРАВЛЕНИЕ РЕСУРСАМИ</div></a>';} else {echo '<div class="butt off">УПРАВЛЕНИЕ РЕСУРСАМИ</div>';}} else {echo '<div class="butt off">УПРАВЛЕНИЕ РЕСУРСАМИ</div>';}
if (isset($search['user'])){echo '<form method="post" id="info" action="../inform.php"><input type="hidden" name="fleet" value="',$search['id_f'],'"><a href="#" onclick="document.getElementById(\'info\').submit();return false;" style="display:block;margin-bottom:0px;"><div class="butt">ИНФОРМАЦИОННАЯ ПАНЕЛЬ</div></a></form>';} else {
echo '<div class="butt off">ИНФОРМАЦИОННАЯ ПАНЕЛЬ</div>';
}
if (isset($search['user'])) {
	if (($search['dolj']<>0) and ((round($search['dolj']/1000)==$search['dolj']/1000) or (round(($search['dolj']-1)/1000)==(($search['dolj']-1)/1000)))) {
		echo '<a href="../index.php" style="display:block;margin-bottom:0px;"><div class="butt">ПАНЕЛЬ НАВИГАЦИИ</div>';
	} elseif($search['dolj']==2002) {
		echo '<a href="../testsess.php" style="display:block;margin-bottom:0px;"><div class="butt">ПАНЕЛЬ НАВИГАЦИИ</div>';
	} else {	
		echo '<div class="butt off">ПАНЕЛЬ НАВИГАЦИИ</div>';
	} 
} else {
	echo '<div class="butt off">ПАНЕЛЬ НАВИГАЦИИ</div>';
}
if (isset($search['user'])){echo '</a>';}
echo '</div></div>
</div></div>';
//echo '<script>';
//echo 'alert(',print_r($_COOKIE),');';
//echo '</script>';
if (!isset($search['user'])){
echo '<script src="../js/jquery11.min.js"></script>';
//echo '<script src="../js/bootstrap/js/bootstrap.min.js"></script>';
echo '<script src="../js/jquery-ui.min.js"></script>';
echo '<script>';
echo 'var ac=$("#search").autocomplete({
	source: "search.php",
	minLength: 1,
	select: function( event, ui ){
		window.location.href="index.php?user="+ui.item.value;
	}
});';
echo '</script>';
}
echo '</body>
</html>';
?>