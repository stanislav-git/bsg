<?php
//print_r($_POST);
//print_r($_COOKIE);
include_once('../modul/connect.php');
include_once('../modul/funct.php');
if (isset($_COOKIE['name'])){
if (isset($_POST['proj']) or isset($_POST['new'])){
	$user=$_COOKIE['user'];
	if (isset($_POST['proj'])) {$project=$_POST['proj'];}
	if (isset($_POST['c_ruk'])) {
		$q_contr=$pdo->prepare("select flag,lobby from project where id=?");
		$q_contr->execute([$project]);
                $contr=$q_contr->fetch();
		if (4>$contr['flag']) {
			$q_prep=$pdo->prepare("update project set rukov=0 where rukov=?");
			$q_prep->execute([$user]);	
			if ($contr['lobby']<>0 and $contr['flag']==0) {
				$q_upd=$pdo->prepare("UPDATE project SET rukov=?, flag=1 where id=?");
				$q_upd->execute(array($user,$project));
				master_inform($project);
			} else {
				$q_upd=$pdo->prepare("UPDATE project SET rukov=? where id=?");
				$q_upd->execute(array($user,$project));
			}
	       		header('Location: ../project.php?proj='.$project);
		}
	}
	if (isset($_POST['c_lob'])) {
		$q_contr=$pdo->prepare("select flag,rukov from project where id=?");
		$q_contr->execute([$project]);
                $contr=$q_contr->fetch();
		if (4>$contr['flag']) {
			if ($contr['rukov']<>0 and $contr['flag']==0) {
				$q_upd=$pdo->prepare("UPDATE project SET lobby=?, flag=1 where id=?");
				$q_upd->execute(array($user,$project));
				master_inform($project);
			} else {
				$q_upd=$pdo->prepare("UPDATE project SET lobby=? where id=?");
				$q_upd->execute(array($user,$project));
			}
	       		header('Location: ../project.php?proj='.$project);
		}
	}
	if (isset($_POST['ok'])) {
		$q_contr=$pdo->prepare("select flag from project where id=?");
		$q_contr->execute([$project]);
                $contr=$q_contr->fetch();
		if ($contr['flag']==2 or $contr['flag']==8) {
			$q_upd=$pdo->prepare("UPDATE project SET flag=3 where id=?");
			$q_upd->execute([$project]);
//			master_inform($project);
	       		header('Location: ../project.php?proj='.$project);
		}
	}
	if (isset($_POST['no'])) {
		$q_contr=$pdo->prepare("select flag from project where id=?");
		$q_contr->execute([$project]);
                $contr=$q_contr->fetch();
		if ($contr['flag']==2) {
			$q_upd=$pdo->prepare("UPDATE project SET flag=8 where id=?");
			$q_upd->execute([$project]);
//			master_inform($project);
	       		header('Location: ../project.php?proj='.$project);
		}
	}
	if (isset($_POST['cancel'])) {
		$q_upd=$pdo->prepare("UPDATE project SET flag=7 where id=?");
		$q_upd->execute([$project]);
//			master_inform($project);
       		header('Location: ../project.php?proj='.$project);
	}
	if (isset($_POST['stop'])) {

		$q_upd=$pdo->prepare("UPDATE project SET flag=5,timer=timer-unix_timestamp(NOW()) where id=?");
		$q_upd->execute([$project]);
//			master_inform($project);
       		header('Location: ../project.php?proj='.$project);
	}
	if (isset($_POST['start'])) {
//		print_r($_POST);
		$q_contr=$pdo->prepare("select nazv,timer,ship,type,flag,fuel,water,comp,rukov from project where id=?");
		$q_contr->execute([$project]);
                $contr=$q_contr->fetch();
		if ($contr['rukov']<>0)	{
			$q_frukov=$pdo->prepare("select id from project where rukov=? and flag<5 and id<>?");
			$q_frukov->execute(array($contr['rukov'],$project));
			$err='';
			if ($q_frukov->rowCount()==0){
				$curres=resurs($_COOKIE['fleet']);
				if ($curres['fuel']>$_POST['fuel'] and $curres['water']>$_POST['water'] and $curres['comp']>$_POST['comp']) {
					if ($contr['type']<>0) {
						$q_ship=$pdo->prepare("select project.id as ids,ships.repair as rep from project join ships on project.ship=ships.id where project.ship=? and project.flag<5");
						$q_ship->execute([$_POST['ship']]);
						if ($q_ship->rowCount()==0){
							$p_ship=$q_ship->fetch();
							if ($p_ship['rep']==0){
								$timer=time()+$contr['timer'];
								$text='Запуск проекта '.$contr['nazv'];
								resurs_upd($_COOKIE['fleet'],$text,$_POST['fuel']*-1,$_POST['water']*-1,$_POST['comp']*-1);
								$q_upd=$pdo->prepare("update project set flag=4,timer=?,ship=? where id=?");
								$q_upd->execute(array($timer,$_POST['ship'],$project));
						       		header('Location: ../project.php?proj='.$project);
							} else {$err=$err.'Корабль неисправен, ';}
					        } else {$err=$err.'Корабль занят другим проектом, ';}
					} else {
						$timer=time()+$contr['timer'];
						$text='Запуск проекта '.$contr['nazv'];
						resurs_upd($_COOKIE['fleet'],$text,$_POST['fuel']*-1,$_POST['water']*-1,$_POST['comp']*-1);
						$q_upd=$pdo->prepare("update project set flag=4,timer=? where id=?");
						$q_upd->execute(array($timer,$project));
				       		header('Location: ../project.php?proj='.$project);
//ok
					}
				} else {
					$err=$err.'Недостаточно ресурсов, ';
				}
      			} else {
				$err=$err.'Руководитель занят, ';
			}
		}
		if ($err<>''){
			$err=urlencode($err);	
			header('Location: ../project.php?err='.$err);
		}
	}
	if (isset($_POST['new'])) {
//		$upd_news=$pdo->prepare("INSERT INTO news (fleet,autor,news,timnews7) VALUES (?,'BBC',?,unix_timestamp(NOW()))");

		$q_upd=$pdo->prepare("insert into project (nazv,init,type,id_f,descrip,result,flag,vlast) VALUES (?,?,?,?,?,?,0,?)");
		$q_upd->execute(array($_POST['nazv'],$_POST['init'],$_POST['type'],$_POST['fleet'],$_POST['descrip'],$_POST['result'],$_POST['fleet']));
		$indb=$pdo->lastInsertId();
       		header('Location: ../project.php?proj='.$indb);
	}
}
}
if (isset($_POST['idp'])){
//редактировать из админки
//print_r($_POST);
	if (isset($_POST['save'])) {
		$q_upd=$pdo->prepare("update project set timer=:timer,fuel=:fuel,water=:water,comp=:comp,flag=:flag,resurs=:resurs,real_result=:real_result,descrip=:descrip,result=:result,vlast=:vlast,type=:type,id_f=:fleet,nazv=:nazv,init=:init,rukov=:rukov,lobby=:lobby where id=:id");
       		$q_upd->bindParam(':fleet', $_POST['fleet']);
       		$q_upd->bindParam(':nazv', $_POST['nazv']);  
       		$q_upd->bindParam(':init', $_POST['init']);
       		$q_upd->bindParam(':rukov', $_POST['rukov']);
       		$q_upd->bindParam(':lobby', $_POST['lobby']);
       		$q_upd->bindParam(':vlast', $_POST['vlast']);
       		$q_upd->bindParam(':type', $_POST['type']);
       		$q_upd->bindParam(':descrip', $_POST['descrip']);
       		$q_upd->bindParam(':result', $_POST['result']);
       		$q_upd->bindParam(':real_result', $_POST['real_result']);
       		$q_upd->bindParam(':resurs', $_POST['resurs']);
       		$q_upd->bindParam(':id', $_POST['idp']);
       		$q_upd->bindParam(':fuel', $_POST['fuel']);
       		$q_upd->bindParam(':water', $_POST['water']);
       		$q_upd->bindParam(':comp', $_POST['comp']);
		if ($_POST['flag']==4){$timer=$_POST['timer']*60+time();} else {$timer=$_POST['timer']*60;}
       		$q_upd->bindParam(':timer', $timer);

       		$q_upd->bindParam(':flag', $_POST['flag']);
		$q_upd->execute();
		if ($_POST['type']==0) {
			$q_proj=$pdo->prepare("update project set ship=0 where id=?");
			$q_proj->execute([$_POST['idp']]);
		} else {
			if (isset($_POST['ship'])) {
				if ($_POST['type']==$_POST['pretype']) {
					$q_proj=$pdo->prepare("update project set ship=? where id=?");
					$q_proj->execute(array($_POST['ship'],$_POST['idp']));
				} else {
                       			$q_proj=$pdo->prepare("update project set ship=0 where id=?");
					$q_proj->execute([$_POST['idp']]);
				}
			}
		}
		header('Location: ../admin.php?project=1');
	}
	if (isset($_POST['del'])){
		$q_proj=$pdo->prepare("DELETE FROM project where id=? LIMIT 1");
		$q_proj->execute([$_POST['idp']]);
		header('Location: ../admin.php?project=1');
	}
}
?>