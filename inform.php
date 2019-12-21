<?php
session_start();
include_once('modul/connect.php');
include_once('modul/funct.php');
if (isset($_POST['fleet'])) {$fleet=$_POST['fleet'];}
if (isset($_GET['fleet'])) {$fleet=$_GET['fleet'];}
if (isset($fleet)) {
	$head='<!DOCTYPE Html><html lang="ru-RU">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>ИНФОРМАЦИОННАЯ ПАНЕЛЬ</title>
<link rel="stylesheet" href="css/jquery.bxslider.css">
<link href="css/info.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="js/jquery11.min.js"></script>
<script src="js/jquery.bxslider.js"></script>
</head><body>';
        $qfl=$pdo->prepare("SELECT destination.enemy as enemy, norms.p1 as proiz, moral.vera as vera, moral.hope as hope, destination.name as fname,
destination.locat as locat, ships.fleet as fleet, resurs.fuel as fuel, resurs.water as water,norms.n2 as norm_w,norms.n3 as norm_c, 
resurs.comp as comp, SUM(typeship.jfuel) as jfuel, SUM(typeship.cargo) as cargo, SUM(typeship.rfuel) as rfuel, SUM(typeship.dfuel) as dfuel, 
SUM(typeship.rwater) as rwater, round(SUM(typeship.dwater)*norms.p1*hope/10000) as dwater, SUM(typeship.rcomp) as rcomp, round(SUM(typeship.dcomp)*norms.p1*hope/10000) as dcomp,
SUM(ships.human) as human, round(SUM(ships.human)*norms.n2*0.12/vera) AS hwater, round(SUM(ships.human)*norms.n3*0.07/vera) AS hcomp
FROM ships
JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who
join resurs on ships.fleet=resurs.id_f
join norms ON ships.fleet=norms.id_f
join moral on ships.fleet=moral.id_f
WHERE destination.who=?
GROUP BY ships.fleet");
	$qtype=$pdo->prepare("SELECT ships.fleet AS fleet, typeship.`cargo` as stype, typeship.sizz, count(typeship.id) AS ncount
FROM ships
JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who
WHERE destination.who=?
GROUP BY typeship.sizz");
        $qtype->execute([$fleet]);
	$ships_d=array(1=>0,2=>0,3=>0);
        while ($ships_type=$qtype->fetch()){
		$ships_d[$ships_type['sizz']]=$ships_type['ncount'];
	}
	$ships_all=$ships_d[1]+$ships_d[2]+$ships_d[3];
	unset($sd);
//        $ships_big=count($ships_type['']);
	$qfl->execute([$fleet]);
	$fl_data=$qfl->fetch();
	echo $head;                                                                  
	if ($fl_data['enemy']==1){
		$bg='img/info/cyl/inform.jpg';
		$imnews='img/info/cyl/event.png';
		$imres='img/info/cyl/cyl_resurses.png';
		$logo='img/info/cyl/logo_cyl.png';
		$imfleet='img/info/cyl/cyl_fleets.png';
	        $imnastr='img/info/cyl/cyl_nastr.png';
		$imrealise='img/info/cyl/cyl_realise_big.png';
		$improj='img/info/cyl/cyl_project.png';
		$iminit='img/info/cyl/cyl_init.png';
		$improg='img/info/cyl/empty_cyl.png';
		$imruk='img/info/cyl/cyl_ruk.png';
		$imvla='img/info/cyl/cyl_vlast.png';
		$imsup='img/info/cyl/cyl_hold.png';
		$diz1='cnews_cyl';
	} else {
		$diz1='cnews';
		$bg='img/info/col/inform.jpg';
		$imnews='img/info/col/event.png';
		$imres='img/info/col/resurs.png';
		$logo='img/info/col/pechat.png';
		$imfleet='img/info/col/sostav_flota.png';
	        $imnastr='img/info/col/nastroenia.png';
		$imrealise='img/info/col/realise_big.png';
		$improj='img/info/col/project.png';
		$iminit='img/info/col/init.png';
		$improg='img/info/col/empty.png';
		$imruk='img/info/col/rukovod.png';
		$imvla='img/info/col/vlast.png';
		$imsup='img/info/col/supp.png';
	}
	echo '<div style="position:relative;min-height: 100%;margin-left:0px;margin-right:0px;background-image:url(\'',$bg,'\'); background-size:100% 100%;">
<div id="maket"><div id="myModal" class="modal"><div class="modal-content"></div></div>';

        mb_internal_encoding('UTF-8');
	echo "<div id='head'>ФЛОТ: ",mb_strtoupper($fl_data['fname']),"</div>";
	echo "<div id='news'><div class='titl' id='tit0'><img src='",$imnews,"'></div><div id='",$diz1,"' class='slider newss'>";
	$qnews=$pdo->prepare("SELECT * from news where fleet=0 or fleet=? order by timnews DESC");
	$qnews->execute([$fleet]);
//	echo "<div><img src='img/power_red.png' alt='' / style='width:100%;'></div>";
	while ($news = $qnews->fetch()) {
		if ($news['timnews']+7200>time()) {
			$ndate=''.date('d/m/\2\5\4\1 H:i',$news['timnews']);
			echo "<div><span class='head'>",$ndate,"</span><p>",$news['news'];
			echo "</p><span class='autor'>",$news['autor'],"</span></div>";
		}

	}
//	echo "<div style='height:20px;'><hr></div>";
	echo "</div></div>";
	if (isset($_COOKIE['user'])) {echo "<a href='users/index.php' style='display:block;'>";}
	echo "<div id='logo'><img src='",$logo,"'><p id='a_human' style='margin-top:10px;margin-bottom:5px;'>",$fl_data['human'],"</p></div>";
	if (isset($_COOKIE['user'])) {echo "</a>";}
$q_summ_dig_fuel=$pdo->prepare("SELECT round(sum(typeship.dfuel)*dig.quality*norms.p1*moral.hope/10000) as sfuel 
FROM ships 
JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who 
JOIN dig ON ships.id=dig.ship
JOIN norms on ships.fleet=norms.id_f
join moral on ships.fleet=moral.id_f
WHERE destination.locat=? AND destination.who=? AND dig.resurs=1 and ships.repair=0");
$q_summ_dig_fuel->execute(array($fl_data['locat'],$fleet));
$dfuel=$q_summ_dig_fuel->fetch();

$q_summ_dig_water=$pdo->prepare("SELECT round(sum(typeship.dwater)*dig.quality*norms.p1*moral.hope/10000) as swater FROM ships JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who JOIN dig ON ships.id=dig.ship
JOIN norms on ships.fleet=norms.id_f
join moral on ships.fleet=moral.id_f
WHERE destination.locat=? AND destination.who=? AND dig.resurs=2 and ships.repair=0");
$q_summ_dig_water->execute(array($fl_data['locat'],$fleet));
$dwater=$q_summ_dig_water->fetch();

$q_summ_dig_comp=$pdo->prepare("SELECT round(sum(typeship.dcomp)*dig.quality*norms.p1*moral.hope/10000) as scomp FROM ships JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who JOIN dig ON ships.id=dig.ship
JOIN norms on ships.fleet=norms.id_f
join moral on ships.fleet=moral.id_f
WHERE destination.locat=? AND destination.who=? AND dig.resurs=3 and ships.repair=0");
$q_summ_dig_comp->execute(array($fl_data['locat'],$fleet));
$dcomp=$q_summ_dig_comp->fetch();

	echo "<div id='resurs'><div class='titl'><img src='",$imres,"'></div><div id='cresurs'>
<table style='width:100%;height:100%;border-spacing: 0px;padding-top:4px;'><tr><th></th><th style='width:20%;'>РЕЗЕРВ</th><th style='width:20%;'>ПРОИЗВОДСТВО</th><th style='width:20%;'>Люди ПОТРЕБЛЕНИЕ</th><th style='width:20%;'>Машины ПОТРЕБЛЕНИЕ</th><th style='width:25%;' nowrap>Баланс</th></tr>
<tr><td><img src='img/tilium.gif'></td>
<td id='a_resfuel'>",$fl_data['fuel'],"</td>
<td id='a_dfuel'>",$dfuel['sfuel']," : ",round($fl_data['proiz']/100,2),"</td>
<td></td>
<td id='a_rfuel'>",$fl_data['rfuel']," / ",$fl_data['jfuel'],"</td>
<td id='a_bfuel'>",$dfuel['sfuel']-$fl_data['rfuel'],"</td></tr>
<tr><td><img src='img/water.gif'></td>
<td id='a_reswater'>",$fl_data['water'],"</td>
<td id='a_dwater'>",$dwater['swater']," : ",round($fl_data['proiz']/100,2),"</td>
<td id='a_hwater'>",$fl_data['hwater']," : ",round($fl_data['norm_w']/100,2),"</td>
<td id='a_rwater'>",$fl_data['rwater'],"</td>
<td id='a_bwater'>",$dwater['swater']-($fl_data['rwater']+$fl_data['hwater']),"</td></tr>
<tr><td><img src='img/detals.gif'></td>
<td id='a_rescomp'>",$fl_data['comp'],"</td>
<td id='a_dcomp'>",$dcomp['scomp']," : ",round($fl_data['proiz']/100,2),"</td>
<td id='a_hcomp'>",$fl_data['hcomp']," : ",round($fl_data['norm_c']/100,2),"</td>
<td id='a_rcomp'>",$fl_data['rcomp'],"</td>
<td id='a_bcomp'>",$dcomp['scomp']-($fl_data['hcomp']+$fl_data['rcomp']),"</td></tr>
</table>";
	echo "</div></div>";
$d="<img src='img/greed.gif' style='width:2px;height:35px;'>";
$widt1=round(abs(min(($fl_data['hope']-100),0))/2);
$blwidt1=46-$widt1;
$widt2=round(max(($fl_data['hope']-100),0)/2);
$blwidt2=46-$widt2;
$hope1="<img src='img/greed.gif' style='width:".$blwidt1."%;height:2px;'><img src='img/red.gif' style='width:".$widt1."%;height:25px;'>";
$hope2="<img src='img/green.gif' style='width:".$widt2."%;height:25px;'><img src='img/greed.gif' style='width:".$blwidt2."%;height:2px;'>";
$widt1=round(abs(min(($fl_data['vera']-100),0))/2);
$blwidt1=46-$widt1;
$widt2=round(max(($fl_data['vera']-100),0)/2);
$blwidt2=46-$widt2;
$vera1="<img src='img/greed.gif' style='width:".$blwidt1."%;height:2px;'><img src='img/red.gif' style='width:".$widt1."%;height:25px;'>";
$vera2="<img src='img/green.gif' style='width:".$widt2."%;height:25px;'><img src='img/greed.gif' style='width:".$blwidt2."%;height:2px;'>";
	echo "<div id='stat'><div id='statl'><div class='titl'><img src='",$imnastr,"'></div><div class='informer'>
<table style='width:100%; height:100%;border-spacing: 0px;padding:0px;margin:auto;'><tr>
<td style='width:50%;vertical-align:bottom;' nowrap colspan=2 id='a_vera'>",$vera1,$d,$vera2,"</td>
<td style='width:50%;vertical-align:bottom;' nowrap colspan=2 id='a_hope'>",$hope1,$d,$hope2,"</td></tr>
<tr><td nowrap style='padding:0;'><img src='img/info/fear.png' style='width:100%;height:auto;'></td><td style='padding:0;'><img src='img/info/relig.png' style='width:100%;height:auto;'></td><td nowrap style='padding:0;'><img src='img/info/dissap.png' style='width:100%;height:auto;'></td><td style='padding:0;'><img src='img/info/hope.png' style='width:100%;height:auto;'></td></tr></table>
</div></div><div id='statr'><div class='titl'><img src='",$imfleet,"'></div><div class='informer'>
<table style='width:100%; height:100%;border-spacing: 0px;padding:0px;'><tr>
<td id='a_hs'>ОГРОМНЫЕ<br>",$ships_d[1],"</td><td id='a_bs'>БОЛЬШИЕ<br>",$ships_d[2],"</td><td id='a_ms'>СРЕДНИЕ<br>",$ships_d[3],"</td></tr>
<tr><td colspan='3' id='a_as'>ИТОГО: ",$ships_all,"</td></tr></table></div></div></div>";
	echo "<div id='qvorum'><div class='titl' id='tit1'><img src='",$imrealise,"'></div><div class='titl2' id='tit2' nowrap><table style='margin:0 auto;width:99%;height:100%;border-spacing:1px;'><tr>
<th style='background-image:url(\"",$improj,"\");width:36%;background-size:100% 100%;'>&nbsp;</td>
<th style='background-image:url(\"",$iminit,"\");width:16%;background-size:100% 100%;'>&nbsp;</td>
<th style='background-image:url(\"",$imsup,"\");width:16%;background-size:100% 100%;'>&nbsp;</td>
<!--//<th style='background-image:url(\"",$imvla,"\");width:12.5%;background-size:100% 100%;'>&nbsp;</td>//-->
<th style='background-image:url(\"",$imruk,"\");width:16%;background-size:100% 100%;'>&nbsp;</td>
<th id='clock' style='background-image:url(\"",$improg,"\");width:16%;background-size:100% 100%;'></td></tr></table></div>
<div id='cqvorum' class='slick-vertical'>";
$q_proj=$pdo->prepare("select project.nazv as nazv, u1.name as ini, u2.name as ruk,u3.name as lob, project.flag as flag,project.timer,project.descrip from project left join users u1 on project.init=u1.id 
left join users u2 on project.rukov=u2.id left join users u3 on project.lobby=u3.id where project.id_f=? and project.flag>1 and project.flag<7");
$q_proj->execute([$fleet]);
if ($q_proj->rowCount()==0) {echo "<div>Нет проектов</div>";}
while ($lproj=$q_proj->fetch()) {
	echo "<div><table class='qvorum'><tr><td style='color:white;'>",$lproj['nazv'],"</td>";
	echo "<td style='color:white;'>",$lproj['ini'],"</td>";
	echo "<td style='color:white;'>",$lproj['lob'],"</td>";
	echo "<td style='color:white;'>",$lproj['ruk'],"</td>";
	if ($lproj['flag']==4) {echo "<td style='color:white;'>",date('H:i:s',$lproj['timer']),"</td>";} else {
	echo "<td style='color:white;'>",status($lproj['flag']),"</td>";}
	echo "</tr>";
	echo "<tr><td colspan=5 style='text-align:center;'>Описание проекта</td></tr>";
	echo "<tr><td colspan=5>",$lproj['descrip'],"</td></tr>";
	echo "</tr>";
	echo "<tr><td colspan=5 style='text-align:center;'>";
	if ($lproj['flag']<>6){echo "Планируемый результат";} else {echo "Достигнутый результат";}
	echo "</td></tr>";
	echo "<tr><td colspan=5>";
	if ($lproj['flag']<>6){echo $lproj['descrip'];}else {echo $lproj['descrip'];}
	echo "</td></tr>";
	echo "</table></div>";
} 
//<div><table class='qvorum'><tr><td>ВНЕОЧЕРЕДНЫЕ выборы в государственные органы власти</td><td>пайкон</td><td>каприка</td><td>офис президента</td><td>Гай Балтар</td><td>00:00:00 </td></tr></table></div>
//<div><table class='qvorum'><tr><td>о признании сайлонов несовершеннолетними, ущербными созданиями, нуждающимися в опеке и покровительстве и еще немного слов и еще немного слов</td><td>САГИТАРИОН</td><td>гименон</td><td>ппп</td><td>ппп</td><td>00:00:00 </td></tr></table></div>
//<div><table class='qvorum'><tr><td>о реорганизации системы государственной власти</td><td>либран</td><td>ОФИС ПРЕЗИДЕНТА</td><td>ппп</td><td>ппп</td><td>00:00:00 </td></tr></table></div>
//<div><table class='qvorum'><tr><td>выборы в государственные органы власти</td><td>пайкон</td><td>каприка</td><td>ппп</td><td>ппп</td><td>00:00:00 </td></tr></table></div>
//<div><table class='qvorum'><tr><td>о признании сайлонов несовершеннолетними</td><td>сагитарион</td><td>гименон</td><td>ппп</td><td>ппп</td><td>00:00:00 </td></tr></table></div>
//<div><table class='qvorum'><tr><td>о реорганизации системы государственной властио реорганизации системы государственной властио реорганизации системы государственной властио реорганизации системы государственной властио реорганизации системы государственной властио реорганизации системы государственной власти</td><td>либран</td><td>офис президента</td><td>ппп</td><td>ппп</td><td>00:00:00 </td></tr></table></div>
//<div><table class='qvorum'><tr><td>о реорганизации ЗАКОННОЙ системы государственной</td><td>офис президента</td><td>системы</td><td>ппп</td><td>ппп</td><td>00:00:00 </td></tr></table></div>
echo "</div></div>";

echo "</div></div>
<script type='text/javascript'>
window.onload = function(){
	window.setInterval(function(){
		var now = new Date();
		var clock = document.getElementById('clock');
		clock.innerHTML = now.toLocaleTimeString();
	}, 1000);
};
$(document).ready(function(){
	$('.slick-vertical').bxSlider({
		mode: 'vertical',
		moveSlides: 1,
		slideMargin: 5,
		touchEnabled:false,
		controls:false,
		infiniteLoop: true,
		pager:false,
		autoHover:false,
		maxSlides: 15,
		minSlides:3,
		speed: 1000,
                adaptiveHeight: true,
		auto: true,
		pause:8000,
	});
var ondoc=document.getElementsByClassName('bx-wrapper');
ondoc[0].style.top = document.getElementById('tit1').offsetHeight+document.getElementById('tit2').offsetHeight+5+'px';
});
function updater(){
        $.ajax({
            type: 'POST',                             
            url: 'modul/updater.php',
            data: {fleet:",$fleet,",res:1},
            success: function(json) {
		var obj=JSON.parse(json);
		document.getElementById('a_human').innerHTML=obj.human;
		document.getElementById('a_resfuel').innerHTML=obj.reserv.fuel;
		document.getElementById('a_reswater').innerHTML=obj.reserv.water;
		document.getElementById('a_rescomp').innerHTML=obj.reserv.comp;
		document.getElementById('a_dfuel').innerHTML=obj.work.fuel+' : '+obj.proiz/100;
		document.getElementById('a_dwater').innerHTML=obj.work.water+' : '+obj.proiz/100;
		document.getElementById('a_dcomp').innerHTML=obj.work.comp+' : '+obj.proiz/100;
		document.getElementById('a_hwater').innerHTML=obj.hwater+' : '+obj.norm_w/100;
		document.getElementById('a_hcomp').innerHTML=obj.hcomp+' : '+obj.norm_c/100;
		document.getElementById('a_rfuel').innerHTML=obj.rfuel+' / '+obj.jfuel;
		document.getElementById('a_rwater').innerHTML=obj.rwater;
		document.getElementById('a_rcomp').innerHTML=obj.rcomp;
		document.getElementById('a_bfuel').innerHTML=obj.work.fuel-obj.rfuel;
		document.getElementById('a_bwater').innerHTML=Number(obj.work.water)-(Number(obj.rwater)+Number(obj.hwater));
		document.getElementById('a_bcomp').innerHTML=Number(obj.work.comp)-(Number(obj.rcomp)+Number(obj.hcomp));
		var d='<img src=\"img/greed.gif\" style=\"width:2px;height:35px;\">';
		var widt1=Math.round(Math.abs(Math.min((obj.hope-100),0))/2);
		var blwidt1=46-widt1;
		var widt2=Math.round(Math.max((obj.hope-100),0)/2);
		var blwidt2=46-widt2;
		var hope1='<img src=\"img/greed.gif\" style=\"width:'+blwidt1+'%;height:2px;\"><img src=\"img/red.gif\" style=\"width:'+widt1+'%;height:25px;\">';
		var hope2='<img src=\"img/green.gif\" style=\"width:'+widt2+'%;height:25px;\"><img src=\"img/greed.gif\" style=\"width:'+blwidt2+'%;height:2px;\">';
		widt1=Math.round(Math.abs(Math.min((obj.vera-100),0))/2);
		blwidt1=46-widt1;
		widt2=Math.round(Math.max((obj.vera-100),0)/2);
		blwidt2=46-widt2;
		var vera1='<img src=\"img/greed.gif\" style=\"width:'+blwidt1+'%;height:2px;\"><img src=\"img/red.gif\" style=\"width:'+widt1+'%;height:25px;\">';
		var vera2='<img src=\"img/green.gif\" style=\"width:'+widt2+'%;height:25px;\"><img src=\"img/greed.gif\" style=\"width:'+blwidt2+'%;height:2px;\">';
                document.getElementById('a_vera').innerHTML=vera1+d+vera2;
                document.getElementById('a_hope').innerHTML=hope1+d+hope2;
		document.getElementById('a_as').innerHTML='ИТОГО: '+obj.ship_all;
		document.getElementById('a_hs').innerHTML='ОГРОМНЫЕ<br>'+obj.ship_h;
		document.getElementById('a_bs').innerHTML='БОЛЬШИЕ<br>'+obj.ship_b;
		document.getElementById('a_ms').innerHTML='СРЕДНИЕ<br>'+obj.ship_m;
	    }
	});
}
setInterval(updater,300000);
var obju=[1];
var count=0;
var plas=document.getElementById('cnews');
var delay=0;
var ti=setTimeout(function request() {
	delay=10000;
	if (count<1) {
		obju=ask();
		count=obju.length;
	}
	if (obju[0]!=1 && count>0){
   		$('#cnews').animate({'opacity':0},300,function(){
			if (count<1) {
				obju=ask();
				count=obju.length;
			}
			plas.innerHTML='<div><span class=\"head\">'+obju[count-1].timnews+'</span><p>'+obju[count-1].text+'</p><span class=\"autor\">'+obju[count-1].autor+'</span></div>';
			$('#cnews').animate({'opacity':1},300);
		});
/*		plas.innerHTML='<div><span class=\"head\">'+obju[count-1].timnews+'</span><p>'+obju[count-1].text+'</p><span class=\"autor\">'+obju[count-1].autor+'</span></div>'; */
/*		$('#cnews').animate({'opacity':1},500); */
/*		plas.innerHTML='<div><span class=\"head\">'+obju[count-1].timnews+'</span><p>'+obju[count-1].text+'</p><span class=\"autor\">'+obju[count-1].autor+'</span></div>';
   		$('#cnews').fadeIn(300);
*/
	}
	count=count-1;
	ti=setTimeout(request,delay,obju,count);
},delay,obju,count);

function ask(){
	$.ajax({
		type: 'POST',                             
		url: 'modul/updnews.php',
		data: {fleet:",6,"},
		success: function(json) {
			obju=JSON.parse(json);
			count=obju.length;
		}
	});
	return obju;
}
</script>
</body></html>";
} else {
    header('Location: users/index.php'); 
}
?>