<?php
session_start();
if (isset($_SESSION['user_id'])){
  	include_once('../modul/connect.php');
//удаляем флот
	if (isset($_POST['del'])){
  		$id=trim($_POST['id']);
  		$fuel=trim($_POST['fuel']);
  		$sti = $pdo->prepare("DELETE FROM destination WHERE who= ?");
	//удалить рапторы
                $idr=round($id+100);
       		$sti->execute([$idr]);
                $idr=round($id+200);
       		$sti->execute([$idr]);
                $idr=round($id+300);
       		$sti->execute([$idr]);
  		$sti->execute([$id]);
  		$rr=$sti->rowCount();
  		$upships = $pdo->prepare("UPDATE `ships` set `fleet` = 0 WHERE fleet= ?");
  		$upships->execute([$id]);
  		if ($rr==1){
      			header('Location: ../admin.php?fleet=1&fuel='.$fuel);
  		}
	}
//редактируем флот
	if (isset($_POST['save'])){
//  		if (trim($_POST['pass'])<>null and trim($_POST['pass'])<>''){
//    			$passwod=md5(trim($_POST['pass']));
//	  	}
  		$id=(int)trim($_POST['id']);
	  	$name=trim($_POST['name']);
  		$fuel=trim($_POST['fuel']);
  		if (!isset($_POST['water'])) {$water=0;} else {$water=trim($_POST['water']);}
  		if (!isset($_POST['comp'])) {$comp=0;} else {$comp=trim($_POST['comp']);}
	  	if (!is_numeric($fuel)){
    			$fuel=0;
	  	}
  		$loc=trim($_POST['loc']);
	  	if ($loc>=0 and $loc<31 and is_numeric($loc)){
			if ($id<100 and $loc==0) {$loc=1;}
  		} else {
			if ($id>100) {$loc=0;} else {$loc=1;}
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
  		$updb = $pdo->prepare("UPDATE `destination` set `name` = :name, `water`=:water, `comp`=:comp, `locat` = :locat, `map_dest` = :dest, `timer`= :timer, `tim_pre`=:tim_pre,`jumping`=:jumping, `fuel` = :fuel, `image`= :image, `radimage`= :rimage where `who`=:id");
		$updb->bindParam(':name', $name);
  		$updb->bindParam(':locat', $loc);
	  	$updb->bindParam(':dest', $dest);
	  	$updb->bindParam(':tim_pre', $tim_pre);
  		$updb->bindParam(':timer', $jumptim);
  		$updb->bindParam(':jumping', $timer);
	  	$updb->bindParam(':fuel', $fuel);
	  	$updb->bindParam(':water', $water);
	  	$updb->bindParam(':comp', $comp);
  		$updb->bindParam(':image', $image);
	  	$updb->bindParam(':rimage', $rimage);
  		$updb->bindParam(':id', $id);
	  	$updb->execute();
  		if ($id>100) {header('Location: ../admin.php?rapt');} else {header('Location: ../admin.php?fleet');}
	}
} else {
  header('Location: ../testsess.php');
}
?>