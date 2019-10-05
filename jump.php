<?php
session_start();
if (isset($_SESSION['user_id'])) {
  	include('connect.php'); 
  	include('funct.php');
//прыжок

    if (isset($_GET['jump'])){
      	$stmt = $pdo->prepare("SELECT * FROM destination WHERE `who` = ?");
      	$pos=(int)trim($_SESSION['user_id']);
      	$scan_who=round(($pos/100-floor($pos/100))*100);
      	$stmt->execute([$pos]);
      	$dest_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
      	$num_rows = count($dest_data);
      	$cur_pos=0;
      	$jump=0;
      	$fuel=0;
      	$dest_pos=0;
      	if ($num_rows==1){
        	$cur_pos=$dest_data[0]['locat'];
        	$timer=$dest_data[0]['timer'];
        	$jump_do=$dest_data[0]['jumping'];
        	$jump=$timer+$jump_do;

        	$dest_pos=$dest_data[0]['map_dest'];
        	$fuel=$dest_data[0]['fuel'];
      	}	 
      	$qfleet = $pdo->prepare("SELECT sum(fuel) as fuel FROM ships WHERE `fleet` = ?");
      	$qfleet->execute([$pos]);
      	$fleet_data = $qfleet->fetch();
      	$fuel_need=$fleet_data['fuel'];
      	if ($pos>100) {
      		$fuel_need=count_fuel($cur_pos,$dest_pos);
		if (strpos($dest_data[0]['name'], "айлон") !== false) {
	   		$fuel_need=1;
		}
      	}
      	if ($fuel_need<=$fuel) {
        	$fuel=$fuel-$fuel_need;
        	$updb = $pdo->prepare("UPDATE `destination` set `locat` = :locat, `map_dest` = 0 , `jumping` = :jumping , `fuel` = :fuel where `who`=:who");
        	$updb->bindParam(':locat', $dest_pos);
        	$updb->bindParam(':jumping', $jump);  
        	$updb->bindParam(':who', $pos);
        	$updb->bindParam(':fuel', $fuel);  
        	$updb->execute();
      		$dest=$dest_pos;
      		$stq = $pdo->prepare("SELECT count(id_ano) FROM scanning WHERE id_ano= ? and who= ?");
      		$stm = $pdo->prepare("SELECT id FROM anom WHERE map= ?");
      		$sti = $pdo->prepare("INSERT INTO scanning (`id_ano`, `who`) VALUES (?, ?)");
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
        	header('Location: index.php');
        	exit;
      	} else {
		die('недостаточно топлива');
      	}
}
//отмена прыжка
    if (isset($_POST['cancel'])){
        $upd = $pdo->prepare("UPDATE destination set map_dest = 0, jumping = 0 where who= :who");
        $upd->bindParam(':who', $_SESSION['user_id']);
        $upd->execute();
        header('Location: index.php');
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
      header('Location: index.php');
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
      header('Location: index.php');
      exit;
    }

  
} else {
  header('Location: auth.php');
}
?>