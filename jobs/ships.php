<?php
session_start();
include_once('../modul/connect.php');
include_once('../modul/funct.php');

if (isset($_COOKIE['user'])){
	$q_fleet=$pdo->prepare("select id_f,dolj from users where id=? LIMIT 1");
	$q_fleet->execute([$_COOKIE['user']]);
	$data_fleet=$q_fleet->fetch();
}

if (isset($_SESSION['user_id']) or isset($_COOKIE['user'])){
	include_once('../modul/funct.php');
//
	if (isset($_POST['addfleet'])){
  		if (isset($_POST['ids']) and $_POST['ids']<1000){
			$id_flad=(int)trim($_POST['ids']);
//  		        $ask_sh=$pdo->prepare("SELECT name FROM ships WHERE id=?");
//	                $ask_sh->execute([$id_flad]);
//			$myship = $ask_sh->fetchAll(PDO::FETCH_ASSOC);
				$flname=trim($_POST['flname']);
    				$sti = $pdo->prepare("INSERT INTO destination (who, name, locat, timer, tim_pre, jumping, map_dest, image, radimage) VALUES (?, ?, 1, 1980, 300, 0, 0, 'colonial.png', 'radar-col.gif')");
    				$sti->execute(array($id_flad,$flname));
		//Добавляем рапторы
	    			$flrap = $pdo->prepare("INSERT INTO destination (who, name, locat, timer, tim_pre, jumping, map_dest, image, radimage) VALUES (?, ?, 0, 300, 180, 0, 0, 'raptor.png', 'radar-col.gif')");
    				$name_r='пилот '.$flname;
    				$id_r=$id_flad+1000;
	    			$flrap->execute(array($id_r,$name_r));
   				$name_r='пилот '.$flname;
    				$id_r=$id_flad+2000;
    				$flrap->execute(array($id_r,$name_r));
	    			$name_r='пилот '.$flname;
    				$id_r=$id_flad+3000;
				$flrap->execute(array($id_r,$name_r));
		//заводим ресурсы и нормы
        	    		$ins_res = $pdo->prepare("INSERT INTO resurs (id_f, fuel, water, comp, timer) VALUES (?, 50, 50, 50, unix_timestamp(now()))");
				$ins_res->execute([$id_flad]);
        	    		$ins_norm = $pdo->prepare("INSERT INTO norms (id_f, n2, n3, p1) VALUES (?, 100, 100, 100)");
				$ins_norm->execute([$id_flad]);
        	    		$ins_norm = $pdo->prepare("INSERT INTO moral (id_f, vera, hope) VALUES (?, 100, 100)");
				$ins_norm->execute([$id_flad]);
        	    		$ins_norm = $pdo->prepare("INSERT INTO hist_norms (id_f, n2, n3, p1, n2max, n3max, p1min) VALUES (?, 100, 100,100,100,100,100)");
				$ins_norm->execute([$id_flad]);

		//Устанавливаем кораблю номер флота
    				$upship=$pdo->prepare("UPDATE `ships` SET `fleet`=? WHERE  `id`=?");
	    			$upship->execute(array($id_flad,$id_flad));
    				header('Location: ../admin.php?fleet');
    		} else {
    			header('Location: ../admin.php?ships=1');
		}	
	}
//Удаляем корабль
	if (isset($_POST['delship'])){
		$ship=(int)trim($_POST['ids']);
//МОРАЛЬ, если флот не 0.

		$del_sh=$pdo->prepare("DELETE FROM ships WHERE id=? LIMIT 1");
		$del_sh->execute([$ship]);
		header('Location: ../admin.php?ships');
	}
//coxpaнить детали
	if (isset($_POST['detail'])){
		$ship=$_POST['ids'];
		$descparts=trim($_POST['descparts']);
		$user=$_POST['user'];
		$spec=trim($_POST['spec']);
		$image=trim($_POST['imag']);
		$fimage='../img/ships/'.$image;
		if (!file_exists($fimage)) {
			$image='';
		}
		$ed_s=$pdo->prepare("UPDATE ships SET `image`=?, `user`= ?,`descparts`= ?, `spec`= ? WHERE `id`= ?");
	        $ed_s->execute(array($image,$user,$descparts,$spec,$ship));
		header('Location: ../admin.php?ships');
	}
//забастовка
	if (isset($_POST['fbreakship'])){
		$ship=$_POST['ids'];
		$ed_s=$pdo->prepare("UPDATE ships SET `repair`=2 WHERE `id`= ?");
	        $ed_s->execute([$ship]);
		$qprepare=$pdo->prepare("SELECT ships.fleet AS fleet, ships.`name` AS `name`, typeship.cargo AS size, typeship.type AS class,
dig.`resurs` AS `resurs`, (UNIX_TIMESTAMP(NOW())-dig.timstart)/900*typeship.dfuel*dig.quality*norms.p1*moral.hope/10000 AS res
FROM ships
JOIN typeship ON ships.`type`=typeship.id
JOIN norms ON ships.fleet=norms.id_f
JOIN moral ON ships.fleet=moral.id_f
JOIN dig ON ships.id=dig.ship
WHERE ships.id=?");
		$qprepare->execute([$ship]);
		if ($qprepare->rowcount()>0) {
			$dig=$qprepare->fetch();
			$resfinish=round($dig['res']);
  			$q_upd_dig=$pdo->prepare("DELETE FROM dig WHERE ship=? LIMIT 1");
  			$q_upd_dig->execute([$ship]);
  			$text='Начал забастовку '.$dig['name'].' ('.$dig['size'].' '.$dig['class'].')';
			if ($dig['resurs']==1){
				resurs_upd($dig['fleet'],$text,$resfinish,0,0);
  			}
  			if ($dig['resurs']==2){
				resurs_upd($dig['fleet'],$text,0,$resfinish,0);
  			}
  			if ($dig['resurs']==3){
				resurs_upd($dig['fleet'],$text,0,0,$resfinish);
  			}
		} else {
			$q_dig=$pdo->prepare("select name,fleet from ships where id=?");
		        $q_dig->execute([$ship]);
			$dig=$q_dig->fetch();
		}
		$news='Корабль '.$dig['name'].' объявил о забастовке, пока не будут улучшены условия труда';
		$upd_news=$pdo->prepare("INSERT INTO news (fleet,autor,news,timnews) VALUES (?,'BBC',?,unix_timestamp(NOW()))");
		$upd_news->execute(array($dig['fleet'],$news));
  		$q_hmoral=$pdo->prepare("INSERT INTO hist_moral (hist_moral.id_f,hist_moral.vera,hist_moral.hope,hist_moral.timstamt) SELECT moral.id_f,moral.vera,moral.hope,UNIX_TIMESTAMP(NOW()) AS timstamp from moral WHERE moral.`id_f`= ?");
		$q_hmoral->execute([$dig['fleet']]);
  		$q_moral=$pdo->prepare("UPDATE moral SET `hope`=`hope`- 2 WHERE `id_f`= ?");	
		$q_moral->execute([$dig['fleet']]);
		if (isset($_POST['act'])){
			$ok=array('id_s'=>$ship);
			echo json_encode($ok);
		} else {
			header('Location: ../admin.php?ships');
		}
	}
//сломан
	if (isset($_POST['breakship'])){
		$ship=$_POST['ids'];
		$ed_s=$pdo->prepare("UPDATE ships SET `repair`=1 WHERE `id`= ?");
	        $ed_s->execute([$ship]);
		header('Location: ../admin.php?ships');
	}
//починить
	if (isset($_POST['repair'])){
		$ship=$_POST['ids'];
		$ed_s=$pdo->prepare("UPDATE ships SET `repair`=0 WHERE `id`= ?");
	        $ed_s->execute([$ship]);
		$q_dig=$pdo->prepare("select name,fleet from ships where id=?");
	        $q_dig->execute([$ship]);
		$dig=$q_dig->fetch();
		$news='Корабль '.$dig['name'].' возобновил работу';
		$upd_news=$pdo->prepare("INSERT INTO news (fleet,autor,news,timnews) VALUES (?,'BBC',?,unix_timestamp(NOW()))");
		$upd_news->execute(array($dig['fleet'],$news));
		if (isset($_POST['act'])){
			$ok=array('id_s'=>$ship);
			echo json_encode($ok);
		} else {
			header('Location: ../admin.php?ships');
		}
	}
//назначить флагманом
	if (isset($_POST['flag'])){
		$ship=trim($_POST['ids']);
		$q_curr_f=$pdo->prepare("select destination.who from destination join ships on destination.who=ships.fleet where ships.id=? LIMIT 1");
		$q_curr_f->execute([$ship]);
		$curr_f=$q_curr_f->fetchColumn();
		if ($curr_f<>$ship){
			$upd_f=$pdo->prepare("UPDATE destination set destination.who=?, destination.name=(select ships.name from ships where ships.id=?) where destination.who=?");
			$upd_f->execute(array($ship,$ship,$curr_f));
			$rapt1=1000+$ship;
			$rapt1old=1000+$curr_f;
			$namerapt='пилот '.ask_name($ship);
			$upd_f=$pdo->prepare("UPDATE destination set destination.who=?, destination.name=? where destination.who=?");
			$upd_f->execute(array($rapt1,$namerapt,$rapt1old));
			$rapt1=2000+$ship;
			$rapt1old=2000+$curr_f;
			$upd_f->execute(array($rapt1,$namerapt,$rapt1old));
			$rapt1=3000+$ship;
			$rapt1old=3000+$curr_f;
			$upd_f->execute(array($rapt1,$namerapt,$rapt1old));
			$upd_s=$pdo->prepare("UPDATE ships set fleet=? where fleet=?");
			$upd_s->execute(array($ship,$curr_f));
			$upd_r=$pdo->prepare("UPDATE hist_norms set id_f=? where id_f=?");
			$upd_r->execute(array($ship,$curr_f));
			$upd_r=$pdo->prepare("UPDATE norms set id_f=? where id_f=?");
			$upd_r->execute(array($ship,$curr_f));
			$upd_r=$pdo->prepare("UPDATE moral set id_f=? where id_f=?");
			$upd_r->execute(array($ship,$curr_f));
			$upd_r=$pdo->prepare("UPDATE resurs set id_f=? where id_f=?");
			$upd_r->execute(array($ship,$curr_f));
			$upd_r=$pdo->prepare("UPDATE scanning set who=? where who=?");
			$upd_r->execute(array($ship,$curr_f));
			$upd_r=$pdo->prepare("UPDATE news set fleet=? where fleet=?");
			$upd_r->execute(array($ship,$curr_f));
		}
		header('Location: ../admin.php?ships');
	}
//В отдельный флот
	if (isset($_POST['fleet_d'])){
		$ship=(int)trim($_POST['ids']);
		$q_date=$pdo->prepare("SELECT ships.`name` AS `name`, typeship.sizz as sizz, destination.enemy as enemy, resurs.fuel, resurs.water,
resurs.comp, norms.n2,norms.n3,norms.p1, moral.vera AS vera, moral.hope AS hope,
destination.locat AS locat, ships.fleet AS fleet
FROM ships
JOIN destination ON ships.fleet=destination.who
join typeship on ships.type=typeship.id
JOIN moral ON ships.fleet=moral.id_f
JOIN resurs ON ships.fleet=resurs.id_f
JOIN norms ON ships.fleet=norms.id_f
WHERE ships.id=?");
		$q_date->execute([$ship]);
		$old_date=$q_date->fetch();
		$locat=$old_date['locat'];
		$old_fl=$old_date['fleet'];
		$name=$old_date['name'];
		$fuel=$old_date['fuel'];
		$water=$old_date['water'];
		$comp=$old_date['comp'];
		$vera=$old_date['vera'];
		$enemy=$old_date['enemy'];
		$hope=$old_date['hope'];
		$n2=$old_date['n2'];
		$n3=$old_date['n3'];
		$p1=$old_date['p1'];
		if ($enemy==1){
			$imag='baseship-1.png';
			$rap='raider.png';
			$radar='radar-cln.gif';
		} else {
			$imag='colonial.png';
			$rap='raptor.png';
			$radar='radar-col.gif';
		}
		$q_num_s=$pdo->prepare("select count(id) from ships where fleet=? group by fleet");
		$q_num_s->execute([$old_fl]);
		$num_s=$q_num_s->fetchColumn();
		if ($old_date['sizz']==1) {
			$fuel=round($fuel/$num_s*1.6);
			$water=round($water/$num_s*1.6);
			$comp=round($comp/$num_s*1.6);
		}
		if ($old_date['sizz']==2) {
			$fuel=round($fuel/$num_s*1.2);
			$water=round($water/$num_s*1.2);
			$comp=round($comp/$num_s*1.2);
		}
		if ($old_date['sizz']==3) {
			$fuel=round($fuel/$num_s);
			$water=round($water/$num_s);
			$comp=round($comp/$num_s);
		}
   		$ins_res = $pdo->prepare("INSERT INTO resurs (id_f, fuel, water, comp, timer) VALUES (?, ?, ?, ?, unix_timestamp(now()))");
		$ins_res->execute(array($ship,$fuel,$water,$comp));
		
		$sti = $pdo->prepare("INSERT INTO destination (enemy, who, name, locat, timer, tim_pre, jumping, map_dest, image, radimage) VALUES (?, ?, ?, ?, 1980, 300, 0, 0, ?, ?)");
		$sti->execute(array($enemy,$ship,$name,$locat,$imag,$radar));
		
		$flrap = $pdo->prepare("INSERT INTO destination (enemy, who, name, locat, timer, tim_pre, jumping, map_dest, image, radimage) VALUES (?, ?, ?, 0, 300, 180, 0, 0, ?, ?)");
		$name_r='пилот '.$name;
    		$id_r=$ship+1000;
		$flrap->execute(array($enemy,$id_r,$name_r,$rap,$radar));
    		$id_r=$ship+2000;
		$flrap->execute(array($enemy,$id_r,$name_r,$rap,$radar));
    		$id_r=$ship+3000;
		$flrap->execute(array($enemy,$id_r,$name_r,$rap,$radar));
        	$ins_norm = $pdo->prepare("INSERT INTO norms (id_f, n2, n3, p1) VALUES (?, ?, ?, ?)");
		$ins_norm->execute(array($ship,$n2,$n3,$p1));
        	$ins_norm = $pdo->prepare("INSERT INTO moral (id_f, vera, hope) VALUES (?, ?, ?)");
		$ins_norm->execute(array($ship,$vera,$hope));
        	$ins_norm = $pdo->prepare("INSERT INTO hist_norms (id_f, n2, n3, p1, n2max, n3max, p1min) VALUES (?, ?, ?,?,?,?,?)");
		$ins_norm->execute(array($ship,$n2,$n3,$p1,$n2,$n3,$p1));
//копируем данные по сканированию
		$clone_scan=$pdo->prepare("SELECT `id_ano`,`level`,`report` FROM scanning WHERE who = ?");
		$clone_scan->execute([$old_fl]);
		$n_scan=$pdo->prepare("INSERT INTO scanning (who, `id_ano`,`level`,`report`) VALUES(?,?,?,?)");
		while ($c_scan=$clone_scan->fetch()){
			$n_scan->execute(array($ship,$c_scan['id_ano'],$c_scan['level'],$c_scan['report']));
		}
//Устанавливаем кораблю номер флота
		$upship=$pdo->prepare("UPDATE `ships` SET `fleet`=? WHERE  `id`=?");
		$upship->execute(array($ship,$ship));
		header('Location: ../admin.php?fleet');
	}
//разобрать
	if (isset($_POST['parseship'])){
		$fleet=$_POST['fleet'];
		$ids=$_POST['ids'];	
		$q_res=$pdo->prepare("select users.name as uname, ships.name as name, ships.human as human, typeship.type as class,
ships.descparts as descpart, typeship.cargo as size, typeship.nfuel as nfuel, typeship.nwater as nwater, typeship.ncomp as ncomp 
from typeship join ships on typeship.id=ships.type join users on ships.user=users.id where ships.id=? LIMIT 1");
		$q_res->execute([$ids]);
		$res=$q_res->fetch();
		$desc='Разобран '.$res['size'].' '.$res['class'].' - '.$res['name'].', Ресурсы пошли на нужды флота, уникальные комплектующие: ('.trim($res['descpart']).') переданы владельцу ('.$res['uname'].')';
		resurs_upd($fleet,$desc,$res['nfuel'],$res['nwater'],$res['ncomp']);

		$q_human=$pdo->prepare("SELECT human from ships where id =?");
		$q_human->execute([$fleet]);
		$human=$res['human']+$q_human->fetchColumn();
		$q_upd_human=$pdo->prepare("UPDATE ships set human = ? where id=?");
		$q_upd_human->execute(array($human,$fleet));
		$q_del_ship=$pdo->prepare("DELETE FROM ships where id=? LIMIT 1");
		$q_del_ship->execute(array($ids));
//МОРАЛЬ
		header('Location: ../admin.php?ships');
	}
//добавить корабль
	if (isset($_POST['add_ship'])) {
		$ship=trim($_POST['ship']);
		if (mb_strlen($ship,'UTF-8')>2){
			$class=$_POST['class'];
			$size=$_POST['size'];
			$qcs=$pdo->prepare("SELECT id from typeship where sizz=? and purp=? LIMIT 1");
			$qcs->execute(array($size,$class));
			$cs=$qcs->fetchColumn();
			$human=$_POST['human'];
			$add_s=$pdo->prepare("INSERT INTO ships (name,type,human,fleet) VALUES (?,?,?,'0')");
		        $add_s->execute(array($ship,$cs,$human));
			header('Location: ../admin.php?ships');
		} else {
			header('Location: ../admin.php?ships=2');

		}
	}
//Редактируем корабли
        if (isset($_POST['savship'])){
		if (mb_strlen(trim($_POST['flname']),'UTF-8')>2) {
//			print_r($_POST);
        		$ship=$_POST['flname'];
	        	$ids=$_POST['ids'];
        		$class=$_POST['class'];
			$size=$_POST['size'];
			$qcs=$pdo->prepare("SELECT id from typeship where sizz=? and purp=? LIMIT 1");
			$qcs->execute(array($size,$class));
			if ($qcs->rowCount()==1) {$cs=$qcs->fetchColumn();} else {$cs=1;}
        		$human=$_POST['human'];
	        	$fleet=$_POST['fleet'];
			$ed_s=$pdo->prepare("UPDATE ships SET name= ?,type= ?, human= ?, fleet= ? WHERE id= ?");
		        $ed_s->execute(array($ship,$cs,$human,$fleet,$ids));
		} 
		header('Location: ../admin.php?ships');
        }
} else {
	header('../Location: testsess.php');
}
?>