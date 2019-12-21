<?php
session_start();
if (isset($_SESSION['user_id'])) {
  	include('../modul/connect.php'); 
  	include('../modul/funct.php');
//прыжок

    if (isset($_GET['jump'])){
	$stmt = $pdo->prepare("SELECT resurs.fuel as rfuel, destination.enemy as enemy, destination.who as who,`name`,destination.`locat`,destination.`jumping`,
destination.timer as timir,`tim_pre`,`map_dest`,`pass`,`image`,`radimage`, destination.fuel as fuel FROM destination left join resurs on destination.who=resurs.id_f WHERE `who` = ?");
//      	$stmt = $pdo->prepare("SELECT * FROM destination WHERE `who` = ?");
      	$pos=(int)trim($_SESSION['user_id']);
      	$scan_who=round(($pos/1000-floor($pos/1000))*1000);
      	$stmt->execute([$pos]);
      	$dest_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
      	$num_rows = count($dest_data);
      	$cur_pos=0;
      	$jump=0;
      	$fuel=0;
      	$dest_pos=0;
      	if ($num_rows==1){
        	$cur_pos=$dest_data[0]['locat'];
        	$timer=$dest_data[0]['timir'];
        	$jump_do=$dest_data[0]['jumping'];
        	$jump=$timer+time();        	
//отсчет времени от момента прыжка, когда он был
//        	$jump=$timer+$jump_do;
        	$dest_pos=$dest_data[0]['map_dest'];
		if ($pos<1000){
        		$fuel=$dest_data[0]['rfuel'];
		} else {
        		$fuel=$dest_data[0]['fuel'];
		}
      	}
//снимаем с добычи
	if ($pos<1000) {
	$i=0;
	while ($i++<4){
		$qprepare="SELECT sum((unix_timestamp(NOW())-dig.timstart)/900*typeship.dfuel*dig.quality*norms.p1*moral.hope/10000) AS res
FROM ships JOIN typeship ON ships.`type`=typeship.id
JOIN dig ON ships.id=dig.ship
JOIN norms ON ships.fleet=norms.id_f
JOIN moral ON ships.fleet=moral.id_f
WHERE ships.fleet=? AND dig.resurs=? AND dig.locat=?";
		$qwho_del=$pdo->prepare($qprepare);
		$qwho_del->execute(array($pos,$i,$cur_pos));
		$delship=$qwho_del->fetch();
		if ($delship['res']<>''){
			$resfinish=round($delship['res']);
			$q_upd_dig=$pdo->prepare("DELETE p FROM dig p WHERE p.ship IN (SELECT ships.id FROM ships WHERE ships.fleet=?) AND p.resurs=? AND p.locat=?");
			$q_upd_dig->execute(array($pos,$i,$cur_pos));
			$text='Флот '.ask_name($pos).' экстренно закончил добычу ';
			if ($i==1){
				$text=$text.' тилиума, добыто:'.$resfinish.' единиц топлива';
				resurs_upd($pos,$text,$resfinish,0,0);
			}
			if ($i==2){
				$text=$text.' воды, добыто:'.$resfinish.' единиц воды';
				resurs_upd($pos,$text,0,$resfinish,0);
			}
  			if ($i==3){
				$text=$text.' руды, добыто:'.$resfinish.' единиц руды';
				resurs_upd($pos,$text,0,0,$resfinish);
		  	}
		}
	}	
	}
//прыгаем
      	$qfleet = $pdo->prepare("SELECT sum(typeship.jfuel) as fuel FROM ships JOIN typeship ON ships.type=typeship.id WHERE ships.`fleet` = ?");
      	$qfleet->execute([$pos]);
      	$fleet_data = $qfleet->fetch();
      	$fuel_need=$fleet_data['fuel'];
	$ran=100;
      	if ($pos>1000) {
      		$fuel_need=count_fuel($cur_pos,$dest_pos);
		if ($dest_data[0]['enemy']==1) {
	   		$fuel_need=1;
		} else {
			$ran=rand(1,100);
		}
      	}
      	if ($fuel_need<=$fuel) {
        	$fuel=$fuel-$fuel_need;
		if ($pos>1000) {
		        if ($ran>5){
        		$updb = $pdo->prepare("UPDATE `destination` set `locat` = :locat, `map_dest` = 0 , `jumping` = :jumping , `fuel` = :fuel where `who`=:who");
        		$updb->bindParam(':locat', $dest_pos);
        		$updb->bindParam(':jumping', $jump);  
        		$updb->bindParam(':who', $pos);
        		$updb->bindParam(':fuel', $fuel);  
        		$updb->execute();
			} else {
			//неудачный прыжок
	        		$updb = $pdo->prepare("UPDATE `destination` set `locat` = 0, `map_dest` = 0 , `jumping` = 0 , `fuel` = 0, `sid`='', `tim`= 0 where `who`=:who");
        			$updb->bindParam(':who', $pos);
	        		$updb->execute();
			}
		} else {
        		$updb = $pdo->prepare("UPDATE `destination` set `locat` = :locat, `map_dest` = 0 , `jumping` = :jumping  where `who`=:who");
        		$updb->bindParam(':locat', $dest_pos);
        		$updb->bindParam(':jumping', $jump);  
        		$updb->bindParam(':who', $pos);
        		$updb->execute();
			resurs_upd($pos,'Выполнен прыжок',-1*abs($fuel_need),0,0);
			$q_rap_pos=$pdo->prepare("select locat,fuel from destination where who =?");
			$i=1000+$pos;
			while ($i<4000){
				$q_rap_pos->execute([$i]);
				$rap_pos=$q_rap_pos->fetch();
				if ($rap_pos['locat']==$dest_pos) {
					$q_rap_update=$pdo->prepare("UPDATE destination set fuel=0, sid='', tim=0, locat=0 where who =?");
					$q_rap_update->execute([$i]);
					$text='Раптор вернулся';
					resurs_upd($pos,$text,$rap_pos['fuel'],0,0);
					lost_human($pos,-2,$text);
				}
				$i=$i+1000;
			}
		}
		if ($ran>5) {
	      		$dest=$dest_pos;
      			$stq = $pdo->prepare("SELECT count(id_ano) FROM scanning WHERE id_ano= ? and who= ?");
      			$stm = $pdo->prepare("SELECT id FROM anom WHERE map= ?");
	      		$sti = $pdo->prepare("INSERT INTO scanning (`id_ano`, `who`,tim) VALUES (?, ?,unix_timestamp(now()))");
      			$stm->execute([$dest]);
      			$anom = $stm->fetchAll();
	      		foreach ($anom as $id_ano) {
        	 		$stq->execute(array($id_ano['id'],$scan_who));
         			$coun = $stq->fetchColumn();
         			if ($coun==0) {
           //insert
	           			$sti->execute(array($id_ano['id'],$scan_who));
        	 		}
	      		}
        		header('Location: ../index.php');
        		exit;
		} else {
		//you dead
			$q_map=$pdo->prepare("select name from maps where id_map=?");
			$q_map->execute([$dest_pos]);
			$lostmap=$q_map->fetchcolumn();
		        $text='Потеряна связь с раптором, выполнявшим прыжок в сектор '.$lostmap.', скорбим о пропавших в глубинах космоса пилотах';
			nnews('',$scan_who,$text);
			$_SESSION=array();
			session_destroy();
		 	setcookie('login', '', 1, "/");
			setcookie('sess', '', 1, "/");
			header('Location: ../death.php'); // перезагружаем файл
			exit;
		}
      	} else {
        	header('Location: ../index.php');
//		die('недостаточно топлива');
		exit;
      	}
}
//отмена прыжка
    if (isset($_POST['cancel'])){
        $upd = $pdo->prepare("UPDATE destination set map_dest = 0, jumping = 0 where who= :who");
        $upd->bindParam(':who', $_SESSION['user_id']);
        $upd->execute();
        header('Location: ../index.php');
        exit;
    }
//подготовка к прыжку раптор неудачная
    if (isset($_POST['fprep'])) {
      $dest_pos=$_POST['dest'];
      $jump=time()+(int)$_POST['pre_jump'];
      $updb = $pdo->prepare("UPDATE `destination` set `jumping` = :jumping, fuel = fuel - 1  where `who`=:who");
      $updb->bindParam(':jumping', $jump);
      $updb->bindParam(':who', $_SESSION['user_id']);
      $updb->execute();
      header('Location: ../index.php');
      exit;
    }

//подготовка к прыжку
    if (isset($_POST['prep'])) {
      $jump=time()+(int)$_POST['pre_jump'];
      $dest_pos=$_POST['dest'];
      $updb = $pdo->prepare("UPDATE `destination` set `map_dest` = :map_dest , `jumping` = :jumping where `who`=:who");
      $updb->bindParam(':map_dest', $dest_pos);
      $updb->bindParam(':jumping', $jump);
      $updb->bindParam(':who', $_SESSION['user_id']);
      $updb->execute();
      header('Location: ../index.php');
      exit;
    }

  
} else {
  header('Location: ../testsess.php');
}
?>