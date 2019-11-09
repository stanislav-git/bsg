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
<link href="css/info.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="css/jquery.bxslider.css">
<script type="text/javascript" src="js/jquery11.min.js"></script>
<script src="js/jquery.bxslider.js"></script>
</head><body>';
        $qfl=$pdo->prepare("SELECT destination.enemy as enemy, norms.p1 as proiz, moral.vera as vera, moral.hope as hope, destination.name as fname,
destination.locat as locat, ships.fleet as fleet, resurs.fuel as fuel, resurs.water as water,norms.n2 as norm_w,norms.n3 as norm_c, 
resurs.comp as comp, SUM(typeship.jfuel) as jfuel, SUM(typeship.cargo) as cargo, SUM(typeship.rfuel) as rfuel, SUM(typeship.dfuel) as dfuel, 
SUM(typeship.rwater) as rwater, round(SUM(typeship.dwater)*norms.p1*hope/10000) as dwater, round(SUM(typeship.rcomp)*norms.p1*hope/10000) as rcomp, round(SUM(typeship.dcomp)*norms.p1*hope/10000) as dcomp,
SUM(ships.human) as human, round(SUM(ships.human)*norms.n2*vera*0.2/10000) AS hwater, round(SUM(ships.human)*norms.n3*vera*0.1/10000) AS hcomp
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
	echo "<div id='news'><div class='titl'><img src='",$imnews,"'></div><div id='",$diz1,"' class='slider'>";
	$qnews=$pdo->prepare("SELECT * from news where fleet=0 or fleet=? order by timnews LIMIT 10");
	$qnews->execute([$fleet]);
//	echo "<div><img src='img/power_red.png' alt='' / style='width:100%;'></div>";
	while ($news = $qnews->fetch()) {
		echo "<div><p>",$news['news'],"</p>";
		echo "<span>",$news['autor'],"</span></div>";

	}
	echo "</div></div>";
	echo "<div id='logo'><img src='",$logo,"'><p style='margin-top:10px;margin-bottom:5px;'>",$fl_data['human'],"</p></div>";

$q_summ_dig_fuel=$pdo->prepare("SELECT round(sum(typeship.dfuel)*dig.quality*norms.p1*moral.vera/10000) as sfuel 
FROM ships 
JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who 
JOIN dig ON ships.id=dig.ship
JOIN norms on ships.fleet=norms.id_f
join moral on ships.fleet=moral.id_f
WHERE destination.locat=? AND destination.who=? AND dig.resurs=1 and ships.repair=0");
$q_summ_dig_fuel->execute(array($fl_data['locat'],$fleet));
$dfuel=$q_summ_dig_fuel->fetch();

$q_summ_dig_water=$pdo->prepare("SELECT round(sum(typeship.dwater)*dig.quality*norms.p1*moral.vera/10000) as swater FROM ships JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who JOIN dig ON ships.id=dig.ship
JOIN norms on ships.fleet=norms.id_f
join moral on ships.fleet=moral.id_f
WHERE destination.locat=? AND destination.who=? AND dig.resurs=2 and ships.repair=0");
$q_summ_dig_water->execute(array($fl_data['locat'],$fleet));
$dwater=$q_summ_dig_water->fetch();

$q_summ_dig_comp=$pdo->prepare("SELECT round(sum(typeship.dcomp)*dig.quality*norms.p1*moral.vera/10000) as scomp FROM ships JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who JOIN dig ON ships.id=dig.ship
JOIN norms on ships.fleet=norms.id_f
join moral on ships.fleet=moral.id_f
WHERE destination.locat=? AND destination.who=? AND dig.resurs=3 and ships.repair=0");
$q_summ_dig_comp->execute(array($fl_data['locat'],$fleet));
$dcomp=$q_summ_dig_comp->fetch();

	echo "<div id='resurs'><div class='titl'><img src='",$imres,"'></div><div id='cresurs'>
<table style='width:100%;height:100%;border-spacing: 0px;padding-top:4px;'><tr><td></td><td style='width:22%;'><small>РЕЗЕРВ</small></td><td style='width:22%;'><small>ПРОИЗВОДСТВО</small></td><td style='width:22%;'><small>ПОТРЕБЛЕНИЕ</small></td><td style='width:22%;' nowrap><small>ТО / ПРЫЖОК</small></td></tr>
<tr><td><img src='img/tilium.gif'></td><td>",$fl_data['fuel'],"</td><td>",$dfuel['sfuel']," : ",round($fl_data['proiz']/100,2),"</td><td></td><td>",$fl_data['rfuel']," / ",$fl_data['jfuel'],"</td></tr>
<tr><td><img src='img/water.gif'></td><td>",$fl_data['water'],"</td><td>",$dwater['swater']," : ",round($fl_data['proiz']/100,2),"</td><td>",$fl_data['hwater']," : ",round($fl_data['norm_w']/100,2),"</td><td>",$fl_data['rwater'],"</td></tr>
<tr><td><img src='img/detals.gif'></td><td>",$fl_data['comp'],"</td><td>",$dcomp['scomp']," : ",round($fl_data['proiz']/100,2),"</td><td>",$fl_data['hcomp']," : ",round($fl_data['norm_c']/100,2),"</td><td>",$fl_data['rcomp'],"</td></tr>
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
<td style='width:50%;vertical-align:bottom;' nowrap colspan=2>",$vera1,$d,$vera2,"</td>
<td style='width:50%;vertical-align:bottom;' nowrap colspan=2>",$hope1,$d,$hope2,"</td></tr>
<tr><td nowrap style='padding:0;'><img src='img/info/0_fear.png' style='width:100%;height:auto;'></td><td style='padding:0;'><img src='img/info/0_fate.png' style='width:100%;height:auto;'></td><td nowrap style='padding:0;'><img src='img/info/0_disap.png' style='width:100%;height:auto;'></td><td style='padding:0;'><img src='img/info/0_hope.png' style='width:100%;height:auto;'></td></tr></table>
</div></div><div id='statr'><div class='titl'><img src='",$imfleet,"'></div><div class='informer'>
<table style='width:100%; height:100%;border-spacing: 0px;padding:0px;'><tr>
<td>ОГРОМНЫЕ<br>",$ships_d[1],"</td><td>БОЛЬШИЕ<br>",$ships_d[2],"</td><td>СРЕДНИЕ<br>",$ships_d[3],"</td></tr>
<tr><td colspan='3'>ИТОГО: ",$ships_all,"</td></tr></table></div></div></div>";
	echo "<div id='qvorum'><div class='titl'><img src='",$imrealise,"'></div><div class='titl2' nowrap><table style='margin:0 auto;width:99%;height:100%;border-spacing:1px;'><tr>
<td style='background-image:url(\"",$improj,"\");'>&nbsp;</td>
<td style='background-image:url(\"",$iminit,"\");'>&nbsp;</td>
<td style='background-image:url(\"",$imsup,"\");'>&nbsp;</td>
<td style='background-image:url(\"",$imvla,"\");'>&nbsp;</td>
<td style='background-image:url(\"",$imruk,"\");'>&nbsp;</td>
<td id='clock' style='background-image:url(\"",$improg,"\");'></td></tr></table></div>
<div id='cqvorum' class='slick-vertical'>
<div><table class='qvorum'><tr><td>пайкон</td><td>каприка</td><td>ВНЕОЧЕРЕДНЫЕ выборы в государственные органы власти</td><td>ппп</td><td>ппп</td><td>00:00:00 </td></tr></table></div>
<div><table class='qvorum'><tr><td>САГИТАРИОН</td><td>гименон</td><td>о признании сайлонов несовершеннолетними</td><td>ппп</td><td>ппп</td><td>00:00:00 </td></tr></table></div>
<div><table class='qvorum'><tr><td>либран</td><td>ОФИС ПРЕЗИДЕНТА</td><td>о реорганизации системы государственной власти</td><td>ппп</td><td>ппп</td><td>00:00:00 </td></tr></table></div>
<div><table class='qvorum'><tr><td>пайкон</td><td>каприка</td><td>выборы в государственные органы власти</td><td>ппп</td><td>ппп</td><td>00:00:00 </td></tr></table></div>
<div><table class='qvorum'><tr><td>сагитарион</td><td>гименон</td><td>о признании сайлонов несовершеннолетними</td><td>ппп</td><td>ппп</td><td>00:00:00 </td></tr></table></div>
<div><table class='qvorum'><tr><td>либран</td><td>офис президента</td><td>о реорганизации системы государственной власти</td><td>ппп</td><td>ппп</td><td>00:00:00 </td></tr></table></div>
<div><table class='qvorum'><tr><td>пайкон</td><td>каприка</td><td>выборы в государственные органы власти</td><td>ппп</td><td>ппп</td><td>00:00:00 </td></tr></table></div>
<div><table class='qvorum'><tr><td>сагитарион</td><td>гименон</td><td>о признании сайлонов о признании сайлонов несовершеннолетними</td><td>ппп</td><td>ппп</td><td>00:00:00 </td></tr></table></div>
<div><table class='qvorum'><tr><td>либран</td><td>офис президента</td><td>о реорганизации системы государственной власти</td><td>ппп</td><td>ппп</td><td>00:00:00 </td></tr></table></div>
<div><table class='qvorum'><tr><td>пайкон</td><td>каприка</td><td>выборы в государственные органы власти</td><td>ппп</td><td>ппп</td><td>00:00:00 </td></tr></table></div>
<div><table class='qvorum'><tr><td>сагитарион</td><td>гименон</td><td>о признании сайлонов несовершеннолетними</td><td>ппп</td><td>ппп</td><td>00:00:00 </td></tr></table></div>
<div><table class='qvorum'><tr><td>о реорганизации ЗАКОННОЙ системы государственной</td><td>офис президента</td><td>о реорганизации системы государственной власти</td><td>ппп</td><td>ппп</td><td>00:00:00 </td></tr></table></div>
</div></div>";

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
		slideMargin: 10,
		controls:false,
		infiniteLoop: true,
		pager:false,
		autoHover:true,
		minSlides: 2,
		maxSlides: 3,
		speed: 1000,
                adaptiveHeight: true,
		auto: true,
	});
});
$(document).ready(function(){
	$('.slider').bxSlider({
		mode: 'vertical',
		moveSlides: 1,
		slideMargin: 10,
		controls:false,
		infiniteLoop: true,
		pager:false,
		autoHover:true,
		minSlides: 2,
		maxSlides: 3,
		speed: 1000,
                adaptiveHeight: true,
		auto: true,
	});
});


/*
$(function(){
      $('.slick-vertical').slick({
		draggable:false,
                vertical: true,
		accessibility: false,
                verticalSwiping: true,
                slidesToShow: 2,
                autoplay: true,
		arrows: false,
		adaptiveHeight:true,
		autoplaySpeed:4000,
		waitForAnimate:false,
		touchMove:false,
		swipe:false,
      });
});
$(function(){
	$('.vertical').slick({
  draggable:false,
  verticalSwiping: true,
  slidesToShow: 2,
  autoplay: true,
  adaptiveHeight:true,
  arrows: false,
  dots: false,
  focusOnSelect: true,
  vertical: true
	});
}); */
</script>
</body></html>";
} else {
    header('Location: users/index.php'); 
}
?>