<?php
//$_POST['fleet']=6;
//$_POST['news']=1;
//$_POST['res']=0;
if (isset($_POST)){
	include_once('connect.php');
	include_once('funct.php');
	$answ=array("fleet" => $_POST['fleet']);
	if (isset($_POST['news'])){
		$q_news=$pdo->prepare("SELECT * FROM news where (fleet=? or fleet=0) and timnews+7200>unix_timestamp(NOW()) order by timnews DESC");
		$q_news->execute([$_POST['fleet']]);
		$answ['cnews']=$q_news->rowCount();
		$i=0;
		$answ['tnews']=array();
		while ($nnews=$q_news->fetch()){
			$answ['tnews'][$i]['timnews']=$nnews['timnews'];
			$answ['tnews'][$i]['autor']=$nnews['autor'];
			$answ['tnews'][$i]['text']=$nnews['news'];
			$i++;
		}
	}
	if (isset($_POST['res'])){
		if ($_POST['res']==1) {
			$q_dat=$pdo->query("select id_f,timer from resurs");
			$q_dat->execute();
			while ($dater=$q_dat->fetch()) {
//Интервал обновлений
				if (time()-$dater['timer']>900) {
					$tdat='Итог на '.date('H:i');
					$q_fuel=$pdo->prepare("SELECT sum((unix_timestamp(NOW())-dig.timstart)*typeship.dfuel*dig.quality*norms.p1*moral.hope/9000000) AS res
FROM ships JOIN typeship ON ships.`type`=typeship.id
JOIN dig ON ships.id=dig.ship
JOIN norms ON ships.fleet=norms.id_f
JOIN moral ON ships.fleet=moral.id_f
WHERE ships.fleet=? AND dig.resurs=1 and ships.repair=0");
					$q_fuel->execute([$dater['id_f']]);                               
					$d_fuel=round($q_fuel->fetchColumn());
					$u_fuel=$pdo->prepare("UPDATE dig JOIN ships ON dig.ship=ships.id SET dig.timstart=unix_timestamp(NOW()) WHERE ships.fleet=? AND dig.resurs=1");
					$u_fuel->execute([$dater['id_f']]);
//				echo $d_fuel," ";
					$q_water=$pdo->prepare("SELECT sum((unix_timestamp(NOW())-dig.timstart)*typeship.dwater*dig.quality*norms.p1*moral.hope/9000000) AS res
FROM ships JOIN typeship ON ships.`type`=typeship.id
JOIN dig ON ships.id=dig.ship
JOIN norms ON ships.fleet=norms.id_f
JOIN moral ON ships.fleet=moral.id_f
WHERE ships.fleet=? AND dig.resurs=2 and ships.repair=0");
					$q_water->execute([$dater['id_f']]);
					$d_water=round($q_water->fetchColumn());
					$u_water=$pdo->prepare("UPDATE dig JOIN ships ON dig.ship=ships.id SET dig.timstart=unix_timestamp(NOW()) WHERE ships.fleet=? AND dig.resurs=2");
					$u_water->execute([$dater['id_f']]);
//				echo $d_water," ";
					$q_comp=$pdo->prepare("SELECT sum((unix_timestamp(NOW())-dig.timstart)*typeship.dcomp*dig.quality*norms.p1*moral.hope/9000000) AS res
FROM ships JOIN typeship ON ships.`type`=typeship.id
JOIN dig ON ships.id=dig.ship
JOIN norms ON ships.fleet=norms.id_f
JOIN moral ON ships.fleet=moral.id_f
WHERE ships.fleet=? AND dig.resurs=3 and ships.repair=0");
					$q_comp->execute([$dater['id_f']]);
					$d_comp=round($q_comp->fetchColumn());
					$u_comp=$pdo->prepare("UPDATE dig JOIN ships ON dig.ship=ships.id SET dig.timstart=unix_timestamp(NOW()) WHERE ships.fleet=? AND dig.resurs=3");
					$u_comp->execute([$dater['id_f']]);
//				echo $d_comp," ";
					$res=resurs_upd($dater['id_f'],$tdat,$d_fuel,$d_water,$d_comp);
					if ($dater['id_f']==$_POST['fleet']) {$answ['reserv']=$res;}
				} else {
					if ($dater['id_f']==$_POST['fleet']) {$answ['reserv']=resurs($dater['id_f']);}
				}
			}
			$q_summ_dig_fuel=$pdo->prepare("SELECT round(sum(typeship.dfuel)*dig.quality*norms.p1*moral.hope/10000) as sfuel 
FROM ships 
JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who 
JOIN dig ON ships.id=dig.ship
JOIN norms on ships.fleet=norms.id_f
join moral on ships.fleet=moral.id_f
WHERE destination.who=? AND dig.resurs=1 and ships.repair=0");
			$q_summ_dig_fuel->execute([$_POST['fleet']]);
			$dfuel=$q_summ_dig_fuel->fetch();

			$q_summ_dig_water=$pdo->prepare("SELECT round(sum(typeship.dwater)*dig.quality*norms.p1*moral.hope/10000) as swater 
FROM ships JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who JOIN dig ON ships.id=dig.ship
JOIN norms on ships.fleet=norms.id_f
join moral on ships.fleet=moral.id_f
WHERE destination.who=? AND dig.resurs=2 and ships.repair=0");
			$q_summ_dig_water->execute([$_POST['fleet']]);
			$dwater=$q_summ_dig_water->fetch();

			$q_summ_dig_comp=$pdo->prepare("SELECT round(sum(typeship.dcomp)*dig.quality*norms.p1*moral.hope/10000) as scomp 
FROM ships JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who JOIN dig ON ships.id=dig.ship
JOIN norms on ships.fleet=norms.id_f
join moral on ships.fleet=moral.id_f
WHERE destination.who=? AND dig.resurs=3 and ships.repair=0");
			$q_summ_dig_comp->execute([$_POST['fleet']]);
			$dcomp=$q_summ_dig_comp->fetch();
			$answ['work']=array('fuel'=>$dfuel['sfuel'],'water'=>$dwater['swater'],'comp'=>$dcomp['scomp']);

			$q_data=$pdo->prepare("SELECT norms.p1 as proiz, moral.vera as vera, moral.hope as hope, destination.name as fname,
ships.fleet as fleet, norms.n2 as norm_w,norms.n3 as norm_c, 
SUM(typeship.jfuel) as jfuel, SUM(typeship.rfuel) as rfuel, SUM(typeship.dfuel) as dfuel, 
SUM(typeship.rwater) as rwater, round(SUM(typeship.dwater)*norms.p1*hope/10000) as dwater, SUM(typeship.rcomp) as rcomp, round(SUM(typeship.dcomp)*norms.p1*hope/10000) as dcomp,
SUM(ships.human) as human, round(SUM(ships.human)*norms.n2*0.12/moral.vera) AS hwater, round(SUM(ships.human)*norms.n3*0.07/moral.vera) AS hcomp
FROM ships
JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who
join resurs on ships.fleet=resurs.id_f
join norms ON ships.fleet=norms.id_f
join moral on ships.fleet=moral.id_f
WHERE destination.who=?
GROUP BY ships.fleet");
	        	$q_data->execute([$_POST['fleet']]);
			$f_data=$q_data->fetchAll(PDO::FETCH_ASSOC);
			$answ=$answ+$f_data[0];
			$qtype=$pdo->prepare("SELECT ships.fleet AS fleet, typeship.`cargo` as stype, typeship.sizz, count(typeship.id) AS ncount
FROM ships
JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who
WHERE destination.who=?
GROUP BY typeship.sizz");
		        $qtype->execute([$_POST['fleet']]);
			$ships_d=array(1=>0,2=>0,3=>0);
		        while ($ships_type=$qtype->fetch()){
				$ships_d[$ships_type['sizz']]=$ships_type['ncount'];
			}
			$ships_all=$ships_d[1]+$ships_d[2]+$ships_d[3];
			unset($sd);
			$answ=$answ+array('ship_all'=>$ships_all,'ship_h'=>$ships_d[1],'ship_b'=>$ships_d[2],'ship_m'=>$ships_d[3]);
		} else {
//для управления флотом
//ТЕКУЩИЙ РЕЗЕРВ			
			$answ['reserv']=resurs($_POST['fleet']);

			$q_summ_dig_fuel=$pdo->prepare("SELECT count(ships.id) as c_fuel, round(sum(typeship.dfuel)*dig.quality*norms.p1*moral.hope/10000) as sfuel 
FROM ships 
JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who 
JOIN dig ON ships.id=dig.ship
JOIN norms on ships.fleet=norms.id_f
join moral on ships.fleet=moral.id_f
WHERE destination.who=? AND dig.resurs=1 and ships.repair=0");
			$q_summ_dig_fuel->execute([$_POST['fleet']]);
			$dfuel=$q_summ_dig_fuel->fetch();

			$q_summ_dig_water=$pdo->prepare("SELECT count(ships.id) as c_water, round(sum(typeship.dwater)*dig.quality*norms.p1*moral.hope/10000) as swater 
FROM ships JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who JOIN dig ON ships.id=dig.ship
JOIN norms on ships.fleet=norms.id_f
join moral on ships.fleet=moral.id_f
WHERE destination.who=? AND dig.resurs=2 and ships.repair=0");
			$q_summ_dig_water->execute([$_POST['fleet']]);
			$dwater=$q_summ_dig_water->fetch();

			$q_summ_dig_comp=$pdo->prepare("SELECT count(ships.id) as c_comp, round(sum(typeship.dcomp)*dig.quality*norms.p1*moral.hope/10000) as scomp 
FROM ships JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who JOIN dig ON ships.id=dig.ship
JOIN norms on ships.fleet=norms.id_f
join moral on ships.fleet=moral.id_f
WHERE destination.who=? AND dig.resurs=3 and ships.repair=0");
			$q_summ_dig_comp->execute([$_POST['fleet']]);
			$dcomp=$q_summ_dig_comp->fetch();
//Добываем
			$answ['work']=array('fuel'=>$dfuel['sfuel'],'water'=>$dwater['swater'],'comp'=>$dcomp['scomp'],'cfuel'=>$dfuel['c_fuel'],'cwater'=>$dwater['c_water'],'ccomp'=>$dcomp['c_comp']);

			$q_data=$pdo->prepare("SELECT norms.p1 as proiz, moral.vera as vera, moral.hope as hope, destination.name as fname,
ships.fleet as fleet, norms.n2 as norm_w,norms.n3 as norm_c, 
SUM(typeship.jfuel) as jfuel, SUM(typeship.rfuel) as rfuel, SUM(typeship.rwater) as rwater, SUM(typeship.rcomp) as rcomp,
SUM(ships.human) as human, round(SUM(ships.human)*norms.n2*0.12/moral.vera) AS hwater, round(SUM(ships.human)*norms.n3*0.07/moral.vera) AS hcomp
FROM ships
JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who
join norms ON ships.fleet=norms.id_f
join moral on ships.fleet=moral.id_f
WHERE destination.who=?
GROUP BY ships.fleet");
	        	$q_data->execute([$_POST['fleet']]);
			$f_data=$q_data->fetchAll(PDO::FETCH_ASSOC);
//Кучка данных о тратах и нормах
			$answ=$answ+$f_data[0];
			$q_free=$pdo->prepare("SELECT count(ships.id) as c_ship,round(sum(typeship.dfuel)*norms.p1*moral.hope/10000) as fuel, 
round(sum(typeship.dwater)*norms.p1*moral.hope/10000) as water, round(sum(typeship.dcomp)*norms.p1*moral.hope/10000) as comp  
FROM ships JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who
JOIN norms on ships.fleet=norms.id_f
join moral on ships.fleet=moral.id_f
LEFT JOIN dig on ships.id=dig.ship
WHERE dig.ship IS NULL and destination.who=? and ships.repair=0");
			$q_free->execute([$_POST['fleet']]);
			$shfree=$q_free->fetchALL(PDO::FETCH_ASSOC);
			$answ['free']=$shfree[0];

			$q_brake=$pdo->prepare("SELECT count(ships.id) as c_ship,round(sum(typeship.dfuel)*norms.p1*moral.hope/10000) as fuel, 
round(sum(typeship.dwater)*norms.p1*moral.hope/10000) as water, round(sum(typeship.dcomp)*norms.p1*moral.hope/10000) as comp  
FROM ships JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who
JOIN norms on ships.fleet=norms.id_f
join moral on ships.fleet=moral.id_f
LEFT JOIN dig on ships.id=dig.ship
WHERE dig.ship IS NULL and destination.who=? and ships.repair=1");
			$q_brake->execute([$_POST['fleet']]);
			$shbrake=$q_brake->fetchALL(PDO::FETCH_ASSOC);
			$answ['brake']=$shbrake[0];

			$q_fbrake=$pdo->prepare("SELECT count(ships.id) as c_ship,round(sum(typeship.dfuel)*norms.p1*moral.hope/10000) as fuel, 
round(sum(typeship.dwater)*norms.p1*moral.hope/10000) as water, round(sum(typeship.dcomp)*norms.p1*moral.hope/10000) as comp  
FROM ships JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who
JOIN norms on ships.fleet=norms.id_f
join moral on ships.fleet=moral.id_f
LEFT JOIN dig on ships.id=dig.ship
WHERE dig.ship IS NULL and destination.who=? and ships.repair=2");
			$q_fbrake->execute([$_POST['fleet']]);
			$shfbrake=$q_fbrake->fetchALL(PDO::FETCH_ASSOC);
			$answ['fbrake']=$shfbrake[0];

//Даннае о локации
			$stq = $pdo->prepare("SELECT DISTINCT anom.`resurs` as res, anom.quality as qual ,maps.name as mname 
FROM destination
JOIN scanning ON destination.who=scanning.who
JOIN anom ON scanning.id_ano=anom.id
JOIN maps ON destination.locat=maps.id_map
WHERE destination.who=? AND destination.locat=anom.map AND scanning.`level`>0
ORDER BY anom.`resurs`");
			$stq->execute([$_POST['fleet']]);
			$maps='';
			while ($qmap=$stq->fetch()){
				if ($qmap['res']==1) {$maps=$qmap['mname'];$answ['res_f']=$qmap['qual'];}
				if ($qmap['res']==2) {$maps=$qmap['mname'];$answ['res_w']=$qmap['qual'];}
				if ($qmap['res']==3) {$maps=$qmap['mname'];$answ['res_c']=$qmap['qual'];}
			}
			$answ['maps']=$maps;
		}
	}
	echo json_encode($answ);
}
?>