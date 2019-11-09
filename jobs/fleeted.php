<?php
session_start();
if (isset($_SESSION['user_id'])){
  	include_once('../modul/connect.php');
	include_once('../modul/funct.php');
//удаляем флот
	if (isset($_POST['del'])){
  		$id=trim($_POST['id']);
		$curres=resurs($id);
//		print_r($currus);
		$news='В результате уничтожения флота '.ask_name($id).' было потеряно: (тилиума:'.$curres['fuel'].
', воды:'.$curres['water'].', запчастей:'.$curres['comp'].')';
		$upd_news=$pdo->prepare("INSERT INTO news (fleet,autor,news,timnews) VALUES (?,'BBC',?,unix_timestamp(NOW()))");
		$upd_news->execute(array($id,$news));
//  		$fuel=trim($_POST['fuel']);
  		$sti = $pdo->prepare("DELETE FROM destination WHERE who= ?");
	//удалить рапторы
                $idr=round($id+1000);
       		$sti->execute([$idr]);
                $idr=round($id+2000);
       		$sti->execute([$idr]);
                $idr=round($id+3000);
       		$sti->execute([$idr]);
  		$sti->execute([$id]);
  		$rr=$sti->rowCount();
  		$upships = $pdo->prepare("UPDATE `ships` set `fleet` = 0 WHERE fleet= ?");
  		$upships->execute([$id]);
        //удаляем записи норм и ресурсов
  		$sti = $pdo->prepare("DELETE FROM resurs WHERE id_f= ?");
  		$sti->execute([$id]);
  		$sti = $pdo->prepare("DELETE FROM norms WHERE id_f= ?");
  		$sti->execute([$id]);
  		$sti = $pdo->prepare("DELETE FROM moral WHERE id_f= ?");
  		$sti->execute([$id]);
  		$sti = $pdo->prepare("DELETE FROM hist_norms WHERE id_f= ?");
  		$sti->execute([$id]);
  		if ($rr==1){
      			header('Location: ../admin.php?ships');
  		}
	}
//редактируем флот
	if (isset($_POST['save'])){
//  		if (trim($_POST['pass'])<>null and trim($_POST['pass'])<>''){
//    			$passwod=md5(trim($_POST['pass']));
//	  	}
  		$id=(int)trim($_POST['id']);
	  	$name=trim($_POST['name']);
//ПРОВЕРИТЬ КОРРЕКТНОСТЬ ИМЕНИ!



		if ($id<1000 and isset($_POST['fuel'])){
			$resurs=resurs($id);
//			print_r($resurs);
  			$fuel=$_POST['fuel'];
			$water=$_POST['water'];
			$comp=$_POST['comp'];
			if ($_POST['ofuel']==$resurs['fuel'] and $_POST['owater']==$resurs['water'] and $_POST['ocomp']==$resurs['comp']) {
				$upd_res=$pdo->prepare("UPDATE resurs set fuel=?, water=?,comp=? WHERE id_f=?");
				$upd_res->execute(array($fuel,$water,$comp,$id));
			}
		}
		if ($id>1000) {
			$fuel=$_POST['fuel'];
			$upd_res=$pdo->prepare("UPDATE destination set fuel=? WHERE who=?");
			$upd_res->execute(array($fuel,$id));
		}
		if (isset($_POST['enemy'])){$enemy=1;} else{$enemy=0;}
  		$loc=trim($_POST['loc']);
	  	if ($loc>=0 and $loc<31 and is_numeric($loc)){
			if ($id<1000 and $loc==0) {$loc=1;}
  		} else {
			if ($id>1000) {$loc=0;} else {$loc=1;}
	  	}
  		$dest=trim($_POST['dest']);
	  	if ($dest>=0 and $dest<31 and is_numeric($dest)){

  		} else {
	    		$dest=0;
  		}
	  	$jumptim=trim($_POST['timer']);
  		if ($jumptim>=10 and $jumptim<10000 and is_numeric($jumptim)){
  		
	  	} else {
    			$jumptim=60;
	  	}
  		$timer=trim($_POST['jumptim']);
	  	if ($timer>=0 and $timer<(time()+3540) and is_numeric($timer)){

  		} else {
    			$timer=0;
	  	}
  		$image=trim($_POST['imag']);
	  	$fimage='../img/fleet/'.$image;
  		if (!file_exists($fimage)){
	    		$image='RAPTOR.png';
  		}
	  	$rimage=trim($_POST['radar']);
  		$frimage='../img/radar/'.$rimage;
	  	if (!file_exists($frimage)){
    			$rimage='radar-col.gif';
	  	}
		$tim_pre=(int)trim($_POST['tim_pre']);
  		$updb = $pdo->prepare("UPDATE `destination` set `enemy`=:enemy,`name` = :name, `locat` = :locat, `map_dest` = :dest, `timer`= :timer, `tim_pre`=:tim_pre,`jumping`=:jumping, `image`= :image, `radimage`= :rimage where `who`=:id");
		$updb->bindParam(':name', $name);
  		$updb->bindParam(':enemy', $enemy);
  		$updb->bindParam(':locat', $loc);
	  	$updb->bindParam(':dest', $dest);
	  	$updb->bindParam(':tim_pre', $tim_pre);
  		$updb->bindParam(':timer', $jumptim);
  		$updb->bindParam(':jumping', $timer);
//	  	$updb->bindParam(':fuel', $fuel);
  		$updb->bindParam(':image', $image);
	  	$updb->bindParam(':rimage', $rimage);
  		$updb->bindParam(':id', $id);
	  	$updb->execute();
  		if ($id>1000) {header('Location: ../admin.php?rapt');} else {header('Location: ../admin.php?fleet');}
	}
} else {
  header('Location: ../testsess.php');
}
?>