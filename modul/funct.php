<?php
function dolj($my){
	$dolj=array(
	0=>'нет',
	1000=>'Президент',
	1001=>'Помощник Президента',
	1002=>'Делегат Кворума',
	1003=>'Специалист',
	1004=>'Законодательная власть',
	2000=>'Командир',
	2001=>'Старший Помощник',
	2002=>'Пилот',
	2003=>'Военный',
	3000=>'Представитель Совета',
	4000=>'Представитель Мятежного Совета'
	);
	if (!isset($dolj[$my])){return 'нет';} else {return $dolj[$my];}
}
function complit($who){
	global $pdo;
	$search = trim($who);
	$query = $pdo->prepare("SELECT users.id as id,users.id_f as id_f, destination.name as fleet,
users.access as access, users.enemy as enemy,users.name as name, users.dolj as dolj 
FROM users join destination on users.id_f=destination.who WHERE users.live=1 and users.name=?");
	$query ->execute([$search]);
	$row=$query->fetch();
	$result_search = array('user' => $row['name'],'id' => $row['id'],'id_f'=>$row['id_f'],'fleet' => $row['fleet'],'access' => $row['access'],'enemy' => $row['enemy'],'dolj'=>$row['dolj']);
	return $result_search;
}
function search_autocomplete($who){
	global $pdo;
	$search = trim($who);
	$query = $pdo->prepare("SELECT name FROM users WHERE live=1 and name LIKE ?");
        $query->bindValue(1, "%$search%", PDO::PARAM_STR);
	$query ->execute();
	$result_search = array();
	while($row = $query->fetch()){
		$result_search[] = array('label' => $row['name']);
	}
	return $result_search;
}

function count_fuel($a1,$a2)
{
  $row1=7;
  if ($a1<37) {$row1=6;}
  if ($a1<31) {$row1=5;}
  if ($a1<25) {$row1=4;}
  if ($a1<19) {$row1=3;}
  if ($a1<13) {$row1=2;}
  if ($a1<7) {$row1=1;}
  $row2=7;
  if ($a2<37) {$row2=6;}
  if ($a2<31) {$row2=5;}
  if ($a2<25) {$row2=4;}
  if ($a2<19) {$row2=3;}
  if ($a2<13) {$row2=2;}
  if ($a2<7) {$row2=1;}
  $rcol=abs(($a1-(($row1-1)*6))-($a2-(($row2-1)*6)));
  $rrow=abs($row1-$row2);
  if ($rrow>$rcol) {
	$ret=$rrow;
  } else {
	$ret=$rcol;
  }
  return $ret;
}

function ask_name($a1){
//переписать с учетом enemy
	global $pdo;
	$b1=round(($a1/1000-floor($a1/1000))*1000);
	if ($b1<>0) {
	$q_nam_fleet = $pdo->prepare("SELECT name,enemy FROM destination WHERE `who` = ?");
	$q_nam_fleet->execute([$b1]);
	$nam_fleet=$q_nam_fleet->fetch();
	$ret=$nam_fleet['name'];
	if ($a1>1000){
		if ($nam_fleet['enemy']==1) {
		   $ret='Рейдер Сайлонов';
		} else {
		   $ret='Раптор '.$nam_fleet['name'];
		}
	}
	} else {$ret='Общий';}
	return $ret;
}
function class_size($myship) {
	global $pdo;
	$qsize=$pdo->query("SELECT sizz,cargo from typeship group by sizz order by sizz");
	$qsize->execute();
	$size=$qsize->fetchAll(PDO::FETCH_ASSOC);
	if ($myship['sizz']>=0) {
                $mysizz=$myship['sizz'];
		$qclass=$pdo->prepare("SELECT purp,type from typeship where sizz=? group by purp order by purp");
		$qclass->execute([$mysizz]);
		$class=$qclass->fetchAll(PDO::FETCH_ASSOC);
	} else {
		$qclass=$pdo->query("SELECT purp,type from typeship group by purp order by purp");
		$qclass->execute();
		$class=$qclass->fetchAll(PDO::FETCH_ASSOC);
	}
	echo " <select id='size' name='size' style='background-color:gray;color:#ffffff;'>";
	foreach ($size as $sl){
		echo "<option value='",$sl['sizz'],"'";
		if ($myship['sizz']==$sl['sizz']) {echo " selected";}
		echo ">",$sl['cargo'],"</option>";
	}		
	echo "</select>";
	unset($sl);
	echo " <select id='class' name='class'>";
	foreach ($class as $cl){
		echo "<option value='",$cl['purp'],"'";
      		if ($myship['purp']==$cl['purp']) {echo " selected";}
		echo ">",$cl['type'],"</option>";
	}		
        echo "</select>";
        unset($cl);
}
function resurs($id) {
	global $pdo;
	$qresurs=$pdo->prepare("select fuel,water,comp from resurs where id_f=?");
	$qresurs->execute([$id]);
	$resurs=$qresurs->fetchAll(PDO::FETCH_ASSOC);
        $ret = array("fuel"=>$resurs[0]['fuel'], "water"=>$resurs[0]['water'], "comp"=>$resurs[0]['comp']);
	return $ret;
}

function resurs_upd($id_f,$text,$fuel,$water,$comp) {
	global $pdo;
$q_res=$pdo->prepare("SELECT resurs.fuel AS rfuel, resurs.water AS rwater, resurs.comp AS rcomp, resurs.timer as tim
FROM resurs WHERE resurs.id_f=?");
$q_res->execute([$id_f]);
$oldd_res=$q_res->fetch();

$qrash=$pdo->prepare("SELECT round(sum(typeship.rfuel)*((UNIX_TIMESTAMP(NOW())-resurs.timer)/900)) AS rfuel,
round(((UNIX_TIMESTAMP(NOW())-resurs.timer)/900)*(SUM(typeship.rwater)+SUM(ships.human)*0.12*norms.n2/moral.vera)) AS rwater,
round(((UNIX_TIMESTAMP(NOW())-resurs.timer)/900)*(SUM(typeship.rcomp)+SUM(ships.human)*0.07*norms.n3/moral.vera)) AS rcomp 
FROM ships
JOIN typeship ON ships.`type`=typeship.id
JOIN resurs ON ships.fleet=resurs.id_f
JOIN moral ON ships.fleet=moral.id_f
JOIN norms ON ships.fleet=norms.id_f
WHERE ships.fleet=?");
$qrash->execute([$id_f]);
$rash=$qrash->fetch();

$q_upd_res=$pdo->prepare("SELECT round(resurs.fuel-sum(typeship.rfuel)*((UNIX_TIMESTAMP(NOW())-resurs.timer)/900)) AS rfuel,
round(resurs.water-(((UNIX_TIMESTAMP(NOW())-resurs.timer)/900)*(SUM(typeship.rwater)+SUM(ships.human)*0.12*norms.n2/moral.vera))) AS rwater,
round(resurs.comp-(((UNIX_TIMESTAMP(NOW())-resurs.timer)/900)*(SUM(typeship.rcomp)+SUM(ships.human)*0.07*norms.n3/moral.vera))) AS rcomp 
FROM ships
JOIN typeship ON ships.`type`=typeship.id
JOIN resurs ON ships.fleet=resurs.id_f
JOIN moral ON ships.fleet=moral.id_f
JOIN norms ON ships.fleet=norms.id_f
WHERE ships.fleet=?");
	$q_upd_res->execute([$id_f]);
	$upd_res=$q_upd_res->fetch();
	$r_fuel=$upd_res['rfuel']+$fuel;
	$r_water=$upd_res['rwater']+$water;
	$r_comp=$upd_res['rcomp']+$comp;
	if ($fuel<0){$rash['rfuel']=$rash['rfuel']-$fuel;$fuel=0;}
	if ($water<0){$rash['rwater']=$rash['rwater']-$water;$water=0;}
	if ($comp<0){$rash['rcomp']=$rash['rcomp']-$comp;$comp=0;}
	$text=$text."<br>Расход: ".$rash['rfuel']."/".$rash['rwater']."/".$rash['rcomp']." Приход ".$fuel."/".$water."/".$comp;
	$upd_sh=$pdo->prepare("UPDATE resurs set fuel = ?, water = ?, comp=?, timer = UNIX_TIMESTAMP(NOW()) where id_f=?");
	$upd_sh->execute(array($r_fuel,$r_water,$r_comp,$id_f));
	$hist_res=$pdo->prepare("INSERT INTO hist_resurs (timer, id_f, fuel, water, comp, descr) VALUES (UNIX_TIMESTAMP(NOW()), ?, ?, ?, ?, ?)");
	$hist_res->execute(array($id_f,$r_fuel,$r_water,$r_comp,trim($text)));
	$ret=array('fuel'=>$r_fuel,'water'=>$r_water,'comp'=>$r_comp);
	return $ret;
}
function control_name($name,$id){
	global $pdo;
//ПРОВЕРИТЬ КОРРЕКТНОСТЬ ИМЕНИ! - ok
	$check_name=$pdo->prepare("select name from destination where name=? and who<>?");
	$check_name->execute(array($name,$id));
	if ($check_name->rowCount()<>0) {
		$qname=$pdo->prepare("select name from ships where id=?");
		$qname->execute($id);
		$name=$qname->fetchColumn();
	}
	$check_name=$pdo->prepare("select name from ships where name=? and id<>?");
	$check_name->execute(array($name,$id));
	if ($check_name->rowCount()<>0) {
		$qname=$pdo->prepare("select name from ships where id=?");
		$qname->execute($id);
		$name=$qname->fetchColumn();
	}
	return $name;
}

function lost_human($fleet,$num,$text) {
	global $pdo;
	$h_upd=$pdo->prepare("UPDATE ships set human=human-? where id=?");
	$h_upd->execute(array($num,$fleet));
	$q_count=$pdo->query("select sum(ships.human) from ships join destination on ships.fleet=destination.who where ships.fleet>0 and destination.enemy<>1");
	$q_count->execute();
	$coun_h=$q_count->fetchcolumn();
	$q_human=$pdo->query("select human from hist_human where apr=1 order by tim DESC LIMIT 1");
	$q_human->execute();
	$human=$q_human->fetchcolumn();
	$q_human=$pdo->prepare("insert into hist_human (human,tim,text) VALUES (?,unix_timestamp(NOW()),?)");
	$q_human->execute(array($coun_h,$text));
	if (floor(($human-$coun_h)/(2*$human/100))>=1){
//падение морали
		$mor=5*floor(($human-$coun_h)/(2*$human/100));
		$q_mor=$pdo->prepare("select hope,vera from moral where id_f=?");
		$q_mor->execute([$fleet]);
		$hope=$q_mor->fetch();
		$upd_mor=$pdo->prepare("insert into hist_moral (id_f,hope,vera,timstamp,text) VALUES (?,?,?,unix_timestamp(NOW()),?)");
		$upd_mor->execute(array($fleet,$hope['hope'],$hope['vera'],$text));
		$q_mor=$pdo->prepare("update moral set vera=?,hope=? where id_f=?");
		$q_mor->execute(array($hope['vera'],$hope['hope']-$mor,$fleet));
		$q_upd=$pdo->prepare("update hist_human set apr=1 where apr=0");
		$q_upd->execute();	
		$hews='По данным переписи населения, численность человечества уменьшилась до '.$coun_h.' '.get_rus($coun_h,array('человека','человек','человек'));
		nnews('',0,$hews);
	} else if (abs(floor(($human-$coun_h)/(2*$human/100)))>=1) {
//если растет?
		$mor=5*abs(floor(($human-$coun_h)/(2*$human/100)));
		$q_mor=$pdo->prepare("select hope,vera from moral where id_f=?");
		$q_mor->execute([$fleet]);
		$hope=$q_mor->fetch();
		$upd_mor=$pdo->prepare("insert into hist_moral (id_f,hope,vera,timstamp,text) VALUES (?,?,?,unix_timestamp(NOW()),?)");
		$upd_mor->execute(array($fleet,$hope['hope'],$hope['vera'],$text));
		$q_mor=$pdo->prepare("update moral set vera=?,hope=? where id_f=?");
		$q_mor->execute(array($hope['vera'],$hope['hope']+$mor,$fleet));
		$q_upd=$pdo->prepare("update hist_human set apr=1 where apr=0");
		$q_upd->execute();	
		$hews='По данным переписи населения, численность человечества увеличилась до '.$coun_h.' '.get_rus($coun_h,array('человека','человек','человек'));
		nnews('',0,$hews);
	}
}

function get_rus($fd, $forms) {
    if (!is_int($fd)&&is_float($fd))//а уж число ли это?
       return $forms[2];
    elseif(is_int($fd))
   {
       $prc = abs($fd) % 100;
       $prc_sec = $prc % 10;
       if ($prc_add == 1)
          return $forms[0];
       if ($prc > 10 && $prc < 20)
          return $forms[2];
       if ($prc_add > 1 && $prc_add < 5)
          return $forms[1];
       return $forms[2];
    };
    return false;//нефик подсовывать ерунду
}

function nnews($autor,$fleet,$text) {
	global $pdo;
	if ($autor=='') {$autor='ВВС';}
	$upd_news=$pdo->prepare("INSERT INTO news (fleet,autor,news,timnews) VALUES (?,?,?,unix_timestamp(NOW()))");
	$upd_news->execute(array($fleet,$autor,$text));
}
function master_inform($a){

}
function status($stat){
	$b=array('создан','поддержан','согласован','утвержден','начат','остановлен','окончен','запрещен','отклонен');
	$c=$b[$stat];
	return $c;
}
function final_project(){
	global $pdo;
	$q_upd=$pdo->query("update project set flag=7 where flag=4 and timer<unix_timestamp(NOW())");
	$q_upd->execute();	
}
?>