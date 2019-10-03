<?php
session_start();
if (isset($_SESSION['user_id'])){
  	include('connect.php');
//добавляем флот
	if (isset($_POST['add_fleet'])){
  		if (isset($_POST['fla']) and $_POST['fla']<100){
			$id_flad=(int)trim($_POST['fla']);
  		        $ask_sh=$pdo->prepare("SELECT * FROM ships WHERE id=?");
	                $ask_sh->execute([$id_flad]);
			$myship = $ask_sh->fetchAll(PDO::FETCH_ASSOC);
			$flname=trim($myship[0]['name']);
    			$sti = $pdo->prepare("INSERT INTO destination (who, name, locat, timer, tim_pre, jumping, map_dest, fuel, image, radimage) VALUES (?, ?, 1, 1980, 300, 0, 0, 'COLONIAL_FLEET', 'radar-col')");
    			$sti->execute(array($id_flad,$flname));
		//Добавляем рапторы
    			$flrap = $pdo->prepare("INSERT INTO destination (who, name, locat, timer, tim_pre, jumping, map_dest, fuel, image, radimage) VALUES (?, ?, 0, 300, 180, 0, 0, 0, 'RAPTOR', 'radar-col')");
    			$name_r='пилот '.$flname;
    			$id_r=$id_flad+100;
    			$flrap->execute(array($id_r,$name_r));
   			$name_r='пилот '.$flname;
    			$id_r=$id_flad+200;
    			$flrap->execute(array($id_r,$name_r));
    			$name_r='пилот '.$flname;
    			$id_r=$id_flad+300;
    			$flrap->execute(array($id_r,$name_r));
		//Устанавливаем кораблю номер флота
    			$upship=$pdo->prepare("UPDATE `ships` SET `fleet`=? WHERE  `id`=?");
    			$upship->execute(array($id_flad,$id_flad));
    			header('Location: admin.php?fleet');
    		} else {
    			header('Location: admin.php?ships=1');
		}	
	}
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
      			header('Location: admin.php?fleet=1&fuel='.$fuel);
  		}
	}
//редактируем флот
	if (isset($_POST['save'])){
  		if (trim($_POST['pass'])<>null and trim($_POST['pass'])<>''){
    			$passwod=md5(trim($_POST['pass']));
	  	}
  		$id=(int)trim($_POST['id']);
	  	$name=trim($_POST['name']);
  		$fuel=trim($_POST['fuel']);
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
	  	$fimage='img/fleet/'.$image.'.png';
  		if (!file_exists($fimage)){
	    		$image='RAPTOR';
  		}
	  	$rimage=trim($_POST['radar']);
  		$frimage='img/radar/'.$rimage.'.gif';
	  	if (!file_exists($frimage)){
    			$rimage='radar-col';
	  	}
		$tim_pre=(int)trim($_POST['tim_pre']);
  		$updb = $pdo->prepare("UPDATE `destination` set `name` = :name, `locat` = :locat, `map_dest` = :dest, `timer`= :timer, `tim_pre`=:tim_pre,`jumping`=:jumping, `fuel` = :fuel, `image`= :image, `radimage`= :rimage where `who`=:id");
		$updb->bindParam(':name', $name);
  		$updb->bindParam(':locat', $loc);
	  	$updb->bindParam(':dest', $dest);
	  	$updb->bindParam(':tim_pre', $tim_pre);
  		$updb->bindParam(':timer', $jumptim);
  		$updb->bindParam(':jumping', $timer);
	  	$updb->bindParam(':fuel', $fuel);
  		$updb->bindParam(':image', $image);
	  	$updb->bindParam(':rimage', $rimage);
  		$updb->bindParam(':id', $id);
	  	$updb->execute();
  		if ($id>100) {header('Location: admin.php?rapt');} else {header('Location: admin.php?fleet');}
	}
} else {
  header('Location: auth.php');
}
?>