<?php
//$_GET['fleet']=6;
//$_GET['locat']=1;
//$_GET['resurs']=1;
//$_GET['act']=0;
//$_GET['ship']=0;
include_once('connect.php');
include_once('funct.php');
$act=$_GET['act'];
$fleet=$_GET['fleet'];
$locat=$_GET['locat'];
$resurs=$_GET['resurs'];
$qres='typeship.dfuel DESC LIMIT 1';
$qres1='typeship.dfuel ASC LIMIT 1';
if ($resurs==1) {$qres='typeship.dfuel DESC LIMIT 1';$qres1='typeship.dfuel ASC LIMIT 1';}
if ($resurs==2) {$qres='typeship.dwater DESC LIMIT 1';$qres1='typeship.dwater ASC LIMIT 1';}
if ($resurs==3) {$qres='typeship.dcomp DESC LIMIT 1';$qres1='typeship.dcomp ASC LIMIT 1';}
//какие ресурсы можем копать
$qresurs=$pdo->prepare("select anom.id,anom.resurs,anom.quality,scanning.who from anom 
JOIN scanning ON anom.id=scanning.id_ano 
WHERE anom.map=? AND scanning.who=? AND scanning.`level`>0");
$qresurs->execute(array($locat,$fleet));

//кто может копать
$qprepare="SELECT ships.id, typeship.dfuel as sfuel,typeship.dwater as swater, typeship.dcomp as scomp
FROM ships JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who 
LEFT JOIN dig ON ships.id=dig.ship
WHERE dig.ship IS NULL AND  destination.locat=? AND destination.who=? AND ships.repair='0' ORDER BY ".$qres;
$qships=$pdo->prepare($qprepare);
$qships->execute(array($locat,$fleet));
$idships=$qships->fetch();
$idship=$idships['id'];
$upd=0;
$res_det=array('fuel'=>0,'water'=>0,'comp'=>0);
while ($res_detect=$qresurs->fetch()){
  if ($resurs==$res_detect['resurs'] and $idship<>'') {
	$id_ano=$res_detect['id'];
	$quality=$res_detect['quality'];
	$upd=1;
  }
  if ($res_detect['resurs']==1){$res_det['fuel']=1;}
  if ($res_detect['resurs']==2){$res_det['water']=1;}
  if ($res_detect['resurs']==3){$res_det['comp']=1;}
}
$widt=$res_det['fuel']+$res_det['water']+$res_det['comp'];
foreach ($res_det as $key=>$value){
	if ($value==1) {
		if ($widt==1) {$res_det[$key]=100;}
		if ($widt==2) {$res_det[$key]=50;}
		if ($widt==3) {$res_det[$key]=33.3;}
	}
}

//отправляем копать
if ($upd==1 and $act==1) {
  $q_upd_dig=$pdo->prepare("INSERT INTO dig (ship,locat,id_ano,resurs,quality,timstart) VALUES (?,?,?,?,?,unix_timestamp(NOW()))");
  $q_upd_dig->execute(array($idship,$locat,$id_ano,$resurs,$quality));
}

//снимаем с добычи
if ($act==0) {
  if ($resurs==1){
	if ($_GET['ship']==0){
		$qprepare="SELECT ships.id AS ids, ships.name AS name, typeship.cargo AS size, typeship.type AS class,
(UNIX_TIMESTAMP(NOW())-dig.timstart)*typeship.dfuel*dig.quality*norms.p1*moral.hope/9000000 AS res 
FROM ships 
JOIN typeship ON ships.`type`=typeship.id 
JOIN norms ON ships.fleet=norms.id_f 
JOIN moral ON ships.fleet=moral.id_f 
JOIN dig ON ships.id=dig.ship 
WHERE ships.fleet=? AND dig.resurs=? ORDER BY ".$qres1;
		$qwho_del=$pdo->prepare($qprepare);
		$qwho_del->execute(array($fleet,$resurs));
		$delship=$qwho_del->fetch();
	} else {
		$qprepare="SELECT ships.id AS ids, ships.name AS name, typeship.cargo AS size, typeship.type AS class,
(UNIX_TIMESTAMP(NOW())-dig.timstart)*typeship.dfuel*dig.quality*norms.p1*moral.hope/9000000 AS res
FROM ships
JOIN typeship ON ships.`type`=typeship.id
JOIN norms ON ships.fleet=norms.id_f
JOIN moral ON ships.fleet=moral.id_f
JOIN dig ON ships.id=dig.ship
WHERE ships.fleet=? AND dig.resurs=? and ships.id=?";
		$qwho_del=$pdo->prepare($qprepare);
		$qwho_del->execute(array($fleet,$resurs,$_GET['ship']));
		$delship=$qwho_del->fetch();
	}
	$idshipdel=$delship['ids'];
	$resfinish=round($delship['res']);
	$q_upd_dig=$pdo->prepare("DELETE FROM dig WHERE ship=? LIMIT 1");
	$q_upd_dig->execute([$idshipdel]);
	$text='Закончил добычу ресурсов '.$delship['name'].' ('.$delship['size'].' '.$delship['class'].')';
	resurs_upd($fleet,$text,$resfinish,0,0);
  }
  if ($resurs==2){
	if ($_GET['ship']==0){
		$qprepare="SELECT ships.id AS ids, ships.name AS name, typeship.cargo AS size, typeship.type AS class,
(UNIX_TIMESTAMP(NOW())-dig.timstart)*typeship.dwater*dig.quality*norms.p1*moral.hope/9000000 AS res
FROM ships
JOIN typeship ON ships.`type`=typeship.id
JOIN norms ON ships.fleet=norms.id_f
JOIN moral ON ships.fleet=moral.id_f
JOIN dig ON ships.id=dig.ship
WHERE ships.fleet=? AND dig.resurs=? ORDER BY ".$qres1;
		$qwho_del=$pdo->prepare($qprepare);
		$qwho_del->execute(array($fleet,$resurs));
		$delship=$qwho_del->fetch();
	} else {
		$qprepare="SELECT ships.id AS ids, ships.name AS name, typeship.cargo AS size, typeship.type AS class,
(UNIX_TIMESTAMP(NOW())-dig.timstart)*typeship.dwater*dig.quality*norms.p1*moral.hope/9000000 AS res
FROM ships
JOIN typeship ON ships.`type`=typeship.id
JOIN norms ON ships.fleet=norms.id_f
JOIN moral ON ships.fleet=moral.id_f
JOIN dig ON ships.id=dig.ship
WHERE ships.fleet=? AND dig.resurs=? and ships.id=?";
		$qwho_del=$pdo->prepare($qprepare);
		$qwho_del->execute(array($fleet,$resurs,$_GET['ship']));
		$delship=$qwho_del->fetch();
	}
	$idshipdel=$delship['ids'];
	$resfinish=round($delship['res']);
	$q_upd_dig=$pdo->prepare("DELETE FROM dig WHERE ship=? LIMIT 1");
	$q_upd_dig->execute([$idshipdel]);
	$text='Закончил добычу ресурсов '.$delship['name'].' ('.$delship['size'].' '.$delship['class'].')';
	resurs_upd($fleet,$text,0,$resfinish,0);
  }
  if ($resurs==3){
        if ($_GET['ship']==0){
		$qprepare="SELECT ships.id AS ids, ships.name AS name, typeship.cargo AS size, typeship.type AS class,
(UNIX_TIMESTAMP(NOW())-dig.timstart)*typeship.dcomp*dig.quality*norms.p1*moral.hope/9000000 AS res
FROM ships
JOIN typeship ON ships.`type`=typeship.id
JOIN norms ON ships.fleet=norms.id_f
JOIN moral ON ships.fleet=moral.id_f
JOIN dig ON ships.id=dig.ship
WHERE ships.fleet=? AND dig.resurs=? ORDER BY ".$qres1;
		$qwho_del=$pdo->prepare($qprepare);
		$qwho_del->execute(array($fleet,$resurs));
		$delship=$qwho_del->fetch();
	} else {
		$qprepare="SELECT ships.id AS ids, ships.name AS name, typeship.cargo AS size, typeship.type AS class,
(UNIX_TIMESTAMP(NOW())-dig.timstart)*typeship.dcomp*dig.quality*norms.p1*moral.hope/9000000 AS res
FROM ships
JOIN typeship ON ships.`type`=typeship.id
JOIN norms ON ships.fleet=norms.id_f
JOIN moral ON ships.fleet=moral.id_f
JOIN dig ON ships.id=dig.ship
WHERE ships.fleet=? AND dig.resurs=? and ships.id=?";
		$qwho_del=$pdo->prepare($qprepare);
		$qwho_del->execute(array($fleet,$resurs,$_GET['ship']));
		$delship=$qwho_del->fetch();
	}
	$idshipdel=$delship['ids'];
	$resfinish=round($delship['res']);
	$q_upd_dig=$pdo->prepare("DELETE FROM dig WHERE ship=? LIMIT 1");
	$q_upd_dig->execute([$idshipdel]);
	$text='Закончил добычу ресурсов '.$delship['name'].' ('.$delship['size'].' '.$delship['class'].')';
	resurs_upd($fleet,$text,0,0,$resfinish);
  }
}
//снимаем с добычи весь флот
if ($act==2) {
  $text='Флот '.ask_name($fleet).' закончил добычу ';
  if ($resurs==1){
	$qprepare="SELECT sum((unix_timestamp(NOW())-dig.timstart)*typeship.dfuel*dig.quality*norms.p1*moral.hope/9000000) AS res
FROM ships JOIN typeship ON ships.`type`=typeship.id
JOIN dig ON ships.id=dig.ship
JOIN norms ON ships.fleet=norms.id_f
JOIN moral ON ships.fleet=moral.id_f
WHERE ships.fleet=? AND dig.resurs=? AND dig.locat=?";
	$qwho_del=$pdo->prepare($qprepare);
	$qwho_del->execute(array($fleet,$resurs,$locat));
	$delship=$qwho_del->fetch();
	$resfinish=round($delship['res']);
	$q_upd_dig=$pdo->prepare("DELETE p FROM dig p WHERE p.ship IN (SELECT ships.id FROM ships WHERE ships.fleet=?) AND p.resurs=? AND p.locat=?");
	$q_upd_dig->execute(array($fleet,$resurs,$locat));

	$text=$text.' тилиума, добыто:'.$resfinish.' единиц топлива';
	resurs_upd($fleet,$text,$resfinish,0,0);
  }
  if ($resurs==2){
	$qprepare="SELECT sum((unix_timestamp(NOW())-dig.timstart)*typeship.dwater*dig.quality*norms.p1*moral.hope/9000000) AS res
FROM ships JOIN typeship ON ships.`type`=typeship.id
JOIN dig ON ships.id=dig.ship
JOIN norms ON ships.fleet=norms.id_f
JOIN moral ON ships.fleet=moral.id_f
WHERE ships.fleet=? AND dig.resurs=? AND dig.locat=?";
	$qwho_del=$pdo->prepare($qprepare);
	$qwho_del->execute(array($fleet,$resurs,$locat));
	$delship=$qwho_del->fetch();
	$resfinish=round($delship['res']);
	$q_upd_dig=$pdo->prepare("DELETE p FROM dig p WHERE p.ship IN (SELECT ships.id FROM ships WHERE ships.fleet=?) AND p.resurs=? AND p.locat=?");
	$q_upd_dig->execute(array($fleet,$resurs,$locat));

	$text=$text.' воды, добыто:'.$resfinish.' единиц воды';
	resurs_upd($fleet,$text,0,$resfinish,0);
  }
  if ($resurs==3){
	$qprepare="SELECT sum((unix_timestamp(NOW())-dig.timstart)*typeship.dcomp*dig.quality*norms.p1*moral.hope/9000000) AS res
FROM ships JOIN typeship ON ships.`type`=typeship.id
JOIN dig ON ships.id=dig.ship
JOIN norms ON ships.fleet=norms.id_f
JOIN moral ON ships.fleet=moral.id_f
WHERE ships.fleet=? AND dig.resurs=? AND dig.locat=?";
	$qwho_del=$pdo->prepare($qprepare);
	$qwho_del->execute(array($fleet,$resurs,$locat));
	$delship=$qwho_del->fetch();
	$resfinish=round($delship['res']);
	$q_upd_dig=$pdo->prepare("DELETE p FROM dig p WHERE p.ship IN (SELECT ships.id FROM ships WHERE ships.fleet=?) AND p.resurs=? AND p.locat=?");
	$q_upd_dig->execute(array($fleet,$resurs,$locat));

	$text=$text.' руды, добыто:'.$resfinish.' единиц руды';
	resurs_upd($fleet,$text,0,0,$resfinish);
  }
}
//

//кто копает 
$q_who_dig=$pdo->prepare("SELECT ships.id as ids, ships.name AS `name`, ROUND((typeship.dfuel)*dig.quality*norms.p1*moral.hope/10000) as digg
FROM ships 
JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who 
JOIN dig ON ships.id=dig.ship
JOIN norms ON ships.fleet=norms.id_f
JOIN moral ON ships.fleet=moral.id_f
WHERE destination.who=? AND dig.resurs=? and ships.repair=0");
$q_who_dig->execute(array($fleet,1));
$who_fuel=$q_who_dig->fetchAll(PDO::FETCH_ASSOC);

$q_who_dig=$pdo->prepare("SELECT ships.id as ids, ships.name AS `name`, ROUND((typeship.dwater)*dig.quality*norms.p1*moral.hope/10000) as digg
FROM ships 
JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who 
JOIN dig ON ships.id=dig.ship
JOIN norms ON ships.fleet=norms.id_f
JOIN moral ON ships.fleet=moral.id_f
WHERE destination.who=? AND dig.resurs=? and ships.repair=0");
$q_who_dig->execute(array($fleet,2));
$who_water=$q_who_dig->fetchAll(PDO::FETCH_ASSOC);

$q_who_dig=$pdo->prepare("SELECT ships.id as ids, ships.name AS `name`, ROUND((typeship.dcomp)*dig.quality*norms.p1*moral.hope/10000) as digg
FROM ships 
JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who 
JOIN dig ON ships.id=dig.ship
JOIN norms ON ships.fleet=norms.id_f
JOIN moral ON ships.fleet=moral.id_f
WHERE destination.who=? AND dig.resurs=? and ships.repair=0");
$q_who_dig->execute(array($fleet,3));
$who_comp=$q_who_dig->fetchAll(PDO::FETCH_ASSOC);

//не заняты суммарно
$q_dig_sum_ships=$pdo->prepare("SELECT sum(typeship.dfuel) as sfuel, sum(typeship.dwater) as swater, sum(typeship.dcomp) as scomp
FROM ships JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who LEFT JOIN dig ON ships.id=dig.ship
WHERE dig.ship IS NULL AND  destination.locat=? AND destination.who=? and ships.repair='0'");
$q_dig_sum_ships->execute(array($locat,$fleet));
$dig_power=$q_dig_sum_ships->fetch();

//заняты суммарно
$q_summ_dig_fuel=$pdo->prepare("SELECT round(sum(typeship.dfuel)*dig.quality*norms.p1*moral.hope/10000) as sfuel
FROM ships 
JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who 
JOIN dig ON ships.id=dig.ship
JOIN norms ON ships.fleet=norms.id_f
JOIN moral ON ships.fleet=moral.id_f
WHERE destination.locat=? AND destination.who=? AND dig.resurs=1 and ships.repair=0");
$q_summ_dig_fuel->execute(array($locat,$fleet));
$dfuel=$q_summ_dig_fuel->fetch();

$q_summ_dig_water=$pdo->prepare("SELECT round(sum(typeship.dwater)*dig.quality*norms.p1*moral.hope/10000) as swater 
FROM ships JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who 
JOIN dig ON ships.id=dig.ship
JOIN norms ON ships.fleet=norms.id_f
JOIN moral ON ships.fleet=moral.id_f
WHERE destination.locat=? AND destination.who=? AND dig.resurs=2 and ships.repair=0");
$q_summ_dig_water->execute(array($locat,$fleet));
$dwater=$q_summ_dig_water->fetch();

$q_summ_dig_comp=$pdo->prepare("SELECT round(sum(typeship.dcomp)*dig.quality*norms.p1*moral.hope/10000) as scomp
FROM ships JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who 
JOIN dig ON ships.id=dig.ship
JOIN norms ON ships.fleet=norms.id_f
JOIN moral ON ships.fleet=moral.id_f
WHERE destination.locat=? AND destination.who=? AND dig.resurs=3 and ships.repair=0");
$q_summ_dig_comp->execute(array($locat,$fleet));
$dcomp=$q_summ_dig_comp->fetch();

$cart = array("widt"=>$res_det, "name_ship_fuel" => $who_fuel,"name_ship_water"=>$who_water,"name_ship_comp"=>$who_comp,"res_fuel"=>$dig_power['sfuel'],"res_water"=>$dig_power['swater'],"res_comp"=>$dig_power['scomp'],"d_fuel"=>round($dfuel['sfuel']),"d_water"=>round($dwater['swater']),"d_comp"=>round($dcomp['scomp']));
/*
$cart=array("0"=>$adddel,"1"=>$fleet,"2"=>$locat,"3"=>$resurs);
*/
echo json_encode($cart);
?>