<?php
session_start();
include_once('modul/connect.php');
if (!isset($_COOKIE['user'])){
 if (isset($_SESSION['user_id'])){$fleet=$_SESSION['user_id'];}else{header('Location: users/index.php');}
} else {
	$q_fleet=$pdo->prepare("select id_f,dolj from users where id=? LIMIT 1");
	$q_fleet->execute([$_COOKIE['user']]);
	$data_fleet=$q_fleet->fetch();
        $fleet=$data_fleet['id_f'];
}
if (isset($_SESSION['user_id']) or isset($data_fleet['id_f'])){
session_write_close();
$head='<!DOCTYPE html>
<html lang="ru-RU">
<head>
<meta charset="utf-8">
<title>УПРАВЛЕНИЕ ФЛОТОМ</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script type="text/javascript" src="js/jquery.min.js"></script>
<link rel="stylesheet" href="css/base1.css">
<link rel="stylesheet" href="css/m_fleet.css">
</head>
<body>
<div id="myModal" class="modal"><div class="modal-content" id="info"></div></div>
<header>ФЛОТ: <span id="fleet"></span></header>
<div class="container">
<div class="main-content">
<div class="content-wrap">';
echo $head;
echo "<main>";
echo "<figure id='logo'><img src='img/info/col/pechat.png'>";
echo "<figcaption id='human'></figcaption>";
echo "</figure>";
echo "<nav>";
echo "<P class='but' style='display:inline-block;width:100%;padding:0px;'>ОТЧЕТ О<br>НАСЕЛЕНИИ</P>";
echo "<P class='but' style='display:inline-block;width:100%;padding:0px;' onclick='stat(2);'>НОВОСТИ</P>";
echo "<P class='but red' style='display:inline-block;width:100%;padding:0px;' onclick='stat(0);'>ВЫХОД</P>";
echo "</nav>";
echo "<div id='resurs'><div class='titl'><img src='img/sector.png'></div><p id='sector'></p>";
echo "<table><tr><td style='min-width:33%;'><img src='img/tilium.gif'></td><td style='min-width:33%;'><img src='img/water.gif'></td><td style='min-width:33%;'><img src='img/detals.gif'></td></tr>";
echo "<tr><td id='rtil'><small>100%</small></td><td id='rwat'><small>100%</small></td><td id='rcom'><small>нет</small></td></tr></table></div>";
echo "</main>";
echo "<div id='left'>";
echo "<div id='norms'><div class='titl'><img src='img/norm.png'></div>";
echo "<table><tr><th colspan=2>Норма потребления</th></tr>";
echo "<tr><td><img src='img/water.gif'></td><td><span class='sm but red'>-</span> <span id='n_w'> 1.05</span> <span class='but sm'>+</span></td></tr>";
echo "<tr><td><img src='img/detals.gif'></td><td><span class='sm but red'>-</span> <span id='n_c'>1.00</span> <span class='but sm'>+</span></td></tr>";
echo "<tr><td colspan=2><br>Норма производства</td></tr>";
echo "<tr><td></td><td><span class='but red sm'>-</span> <span id='norm'>1.00</span> <span class='but sm'>+</span></td></tr>";
echo "<tr><td colspan=2><span class='but' style='display:inline-block;width:35%;padding:0px;margin:20px 0px;'>СТАТИСТИКА</span></td></tr>";
echo "</table></div>";
echo "<div id='proiz'><div class='titl'><img src='img/dobycha.png'></div>";
echo "<table><tr><th></th><th>Корабли</th><th>Мощность</th></tr>";
echo "<tr><td style='text-align:right;'><img src='img/tilium.gif'></td><td><span class='green but' id='sh_f' onclick='ship(1);'></span></td><td><span id='ship_df'></span></td></tr>";
echo "<tr><td style='text-align:right;'><img src='img/water.gif'></td><td><span class='green but' id='sh_w' onclick='ship(2);'></span></td><td><span id='ship_dw'></span></td></tr>";
echo "<tr><td style='text-align:right;'><img src='img/detals.gif'></td><td><span class='green but' id='sh_c' onclick='ship(3);'></span></td><td><span id='ship_dc'></span></td></tr>";
echo "<tr><td style='text-align:right;'>Простаивает</td><td><span class='green but' id='sh_free' onclick='ship(3);'></span></td><td><span id='d_free'></span></td></tr>";
echo "<tr><td style='text-align:right;'>Сломан</td><td><span class='green but red off' id='sh_brake'></span></td><td><span id='d_brake'></span></td></tr>";
echo "<tr><td style='text-align:right;'>Забастовка</td><td><span class='green but red off' id='sh_fbrake'></span></td><td><span id='d_fbrake'></span></td></tr></table>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "<aside>";
echo "<div id='balans'><div class='titl'><img src='img/dobycha.png'></div>";
echo "<table><tr><th></th><th>Резерв</th><th>Производство</th><th>Потребление</th><th>Баланс</th></tr>";
echo "<tr><td><img src='img/tilium.gif'></td><td><span id='res_f'></span></td><td><span id='dob_f'></span></td><td><span id='trat_f'></span></td><td><span id='bal_f'></span></td></tr>";
echo "<tr><td><img src='img/water.gif'></td><td><span id='res_w'></span></td><td><span id='dob_w'></span></td><td><span id='trat_w'></span></td><td><span id='bal_w'></span></td></tr>";
echo "<tr><td><img src='img/detals.gif'></td><td><span id='res_c'></span></td><td><span id='dob_c'></span></td><td><span id='trat_c'></span></td><td><span id='bal_c'></span></td></tr>";
echo "<tr><td colspan='5'><span class='but' id='stat_res' onclick='stat(1);' style='display:inline-block;width:30%;padding:0px;margin:20px 0px;'>СТАТИСТИКА</span></td></tr>";
echo "</table></div>";
echo "<div id='transfer'>";
echo "<div class='titl'><img src='img/obmen.png'></div>";
echo "<table><tr><th></th><th>Передать<wbr> в другой<wbr> флот</th><th>Выдать<wbr> частному<wbr> лицу</th><th>Принять <wbr>пожертвование</th></tr>";
echo "<tr><td><img src='img/tilium.gif'></td><td>10000</td><td>800</td><td>800</td></tr>";
echo "<tr><td><img src='img/water.gif'></td><td>10000</td><td>900</td><td>800</td></tr>";
echo "<tr><td><img src='img/detals.gif'></td><td>10000</td><td>-800</td><td>800</td></tr></table>";
echo "</div>";
echo "</aside>";
echo "</div></div>";
echo "<script type='text/javascript'>
window.onload = function(){
d_ship(1);

}
function d_ship(ad) {
 var request=$.ajax({
     type: 'POST',                             
     url: 'modul/updater.php',
     data: {fleet:",$fleet,",res:0},
     success: function(json) {
	var obj=JSON.parse(json);
	wiewfleet(obj);
     }
 });
 return false;
}
var modal = document.getElementById('myModal');
window.onclick = function(event) {
    if (event.target == modal) {
        $('#myModal').fadeOut(250);
	d_ship(1);
    }
}

function ship(type){
	var request=$.ajax({
     		type: 'POST',                             
     		url: 'modul/m_fleet.php',
     		data: {stat:0,fleet:",$fleet,"},
	   	success: function(html){
             		$('#info').html(html);
     		}
 	});
	$('#myModal').fadeIn();	
}
function stat(type){
    if (type==0){window.location.href='users/index.php';}
    if (type==1 || type==2){
	var request=$.ajax({
     		type: 'POST',                             
     		url: 'modul/m_fleet.php',
     		data: {stat:type,fleet:",$fleet,"},
	   	success: function(html){
             		$('#info').html(html);
     		}
 	});
	$('#myModal').fadeIn();	
    }
}
function wiewfleet(inf){
    document.getElementById('human').innerHTML=inf.human;
    document.getElementById('fleet').innerHTML=inf.fname;
    document.getElementById('sector').innerHTML=inf.maps;
    if (inf.res_f==null){
	document.getElementById('rtil').innerHTML='';
    } else {
        document.getElementById('rtil').innerHTML='<small>'+Math.round(inf.res_f*100)+'%</small>';
    }
    if (inf.res_w==null){
	document.getElementById('rwat').innerHTML='нет';
    } else {
	document.getElementById('rwat').innerHTML='<small>'+Math.round(inf.res_w*100)+'%</small>';
    }
    if (inf.res_c==null){
	document.getElementById('rcom').innerHTML='нет';
    } else {
	document.getElementById('rcom').innerHTML='<small>'+Math.round(inf.res_c*100)+'%</small>';
    }
    document.getElementById('n_w').innerHTML=inf.norm_w+'%';
    document.getElementById('n_c').innerHTML=inf.norm_c+'%';
    document.getElementById('norm').innerHTML=inf.proiz+'%';

    document.getElementById('res_f').innerHTML=inf.reserv.fuel;
    document.getElementById('res_w').innerHTML=inf.reserv.water;
    document.getElementById('res_c').innerHTML=inf.reserv.comp;

    document.getElementById('dob_f').innerHTML=inf.work.fuel;
    document.getElementById('dob_w').innerHTML=inf.work.water;
    document.getElementById('dob_c').innerHTML=inf.work.comp;
    document.getElementById('ship_df').innerHTML=inf.work.fuel;
    document.getElementById('ship_dw').innerHTML=inf.work.water;
    document.getElementById('ship_dc').innerHTML=inf.work.comp;
    if (inf.work.cfuel==null){inf.work.cfuel=0;}
    if (inf.work.cwater==null){inf.work.cwater=0;}
    if (inf.work.ccomp==null){inf.work.ccomp=0;}
    document.getElementById('sh_f').innerHTML=inf.work.cfuel;
    document.getElementById('sh_w').innerHTML=inf.work.cwater;
    document.getElementById('sh_c').innerHTML=inf.work.ccomp;

    document.getElementById('trat_f').innerHTML=inf.rfuel;
    document.getElementById('trat_w').innerHTML=1*inf.rwater+1*inf.hwater;
    document.getElementById('trat_c').innerHTML=1*inf.rcomp+1*inf.hcomp;
    bal_f=1*inf.work.fuel-1*inf.rfuel;
    bal_w=1*inf.work.water-(1*inf.rwater+1*inf.hwater);
    bal_c=1*inf.work.comp-(1*inf.rcomp+1*inf.hcomp);
    document.getElementById('bal_f').innerHTML=bal_f;
    if (bal_f<0){document.getElementById('bal_f').style.color='red';} else {document.getElementById('bal_f').style.color='#FF9100';}
    document.getElementById('bal_w').innerHTML=bal_w;
    if (bal_w<0){document.getElementById('bal_w').style.color='red';} else {document.getElementById('bal_w').style.color='#FF9100';}
    document.getElementById('bal_c').innerHTML=bal_c;
    if (bal_c<0){document.getElementById('bal_c').style.color='red';} else {document.getElementById('bal_c').style.color='#FF9100';}
    if (inf.free.c_ship!=null){document.getElementById('sh_free').innerHTML=inf.free.c_ship;} else {document.getElementById('sh_free').innerHTML=0;}
    if (inf.free.fuel==null){inf.free.fuel=0;}
    if (inf.free.water==null){inf.free.water=0;}
    if (inf.free.comp==null){inf.free.comp=0;}
    document.getElementById('d_free').innerHTML=inf.free.fuel+' / '+inf.free.water+' / '+inf.free.comp;
    if (inf.brake.c_ship!=null){document.getElementById('sh_brake').innerHTML=inf.brake.c_ship;} else {document.getElementById('sh_brake').innerHTML=0;}
    if (inf.brake.fuel==null){inf.brake.fuel=0;}
    if (inf.brake.water==null){inf.brake.water=0;}
    if (inf.brake.comp==null){inf.brake.comp=0;}
    document.getElementById('d_brake').innerHTML=inf.brake.fuel+' / '+inf.brake.water+' / '+inf.brake.comp;

    if (inf.fbrake.c_ship!=null){document.getElementById('sh_fbrake').innerHTML=inf.fbrake.c_ship;} else {document.getElementById('sh_fbrake').innerHTML=0;}
    if (inf.fbrake.fuel==null){inf.fbrake.fuel=0;}
    if (inf.fbrake.water==null){inf.fbrake.water=0;}
    if (inf.fbrake.comp==null){inf.fbrake.comp=0;}
    document.getElementById('d_fbrake').innerHTML=inf.fbrake.fuel+' / '+inf.fbrake.water+' / '+inf.fbrake.comp;
}
</script>";
echo "</body></html>";
}
?>
