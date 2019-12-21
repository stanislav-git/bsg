<?php
include_once('../modul/connect.php');
include_once('../modul/funct.php');
if (isset($_POST['ids'])) {
	if (isset($_POST['del_'])) {
		//Удаляем корабль
		$ship=(int)trim($_POST['ids']);
//МОРАЛЬ, если флот не 0.
		$qdship=$pdo->prepare("select ships.name as name,ships.human as human,users.name as vlad,ships.type as type,typeship.type as ntype from ships join typeship on ships.type=typeship.id left join users on ships.user=users.id where ships.id=?");
		$qdship->execute([$ship]);
		$dship=$qdship->fetch();
		$text='УНИЧТОЖЕН КОРАБЛЬ!<br>'.dolj($_COOKIE['access']).' '.$_COOKIE['name'].' приказал уничтожить корабль '.$dship['name'].' ('.$dship['ntype'].') c '.$dship['human'].' человек на борту, принадлежавший '.$dship['vlad'].'.<br>Приказ был выполнен.';
                nnews('ВВС',$_POST['fleet'],$text); //постим новость
        	$moral=0;
		if ($dship['type']==2 or $dship['type']==24 or $dship['type']==29 or $dship['type']==23 or $dship['type']==28){
			$moral=2;
		}
		if ($dship['type']==1 or $dship['type']==20){
			$moral=20;
		}
		if ($dship['type']==21 or $dship['type']==22 or $dship['type']==5 or $dship['type']==10 or $dship['type']==9 or $dship['type']==4 or $dship['type']==26 or $dship['type']==27 or $dship['type']==25 or $dship['type']==7 or $dship['type']==12 or $dship['type']==11 or $dship['type']==6){
			$moral=10;
		}
		lost_human($ship,$dship['human'],$text);
  		$q_hmoral=$pdo->prepare("INSERT INTO hist_moral (hist_moral.id_f,hist_moral.vera,hist_moral.hope,hist_moral.timstamp,hist_moral.text) SELECT moral.id_f,moral.vera,moral.hope,UNIX_TIMESTAMP(NOW()) AS timstamp,? as tex from moral WHERE moral.`id_f`= ?");
		$q_hmoral->execute(array($text,$_POST['fleet']));
  		$q_moral=$pdo->prepare("UPDATE moral SET `hope`=`hope`- ? WHERE `id_f`= ?");	
		$q_moral->execute(array($moral,$_POST['fleet']));
		$del_sh=$pdo->prepare("UPDATE ships set fleet=0,user=0 WHERE id=? LIMIT 1");
		$del_sh->execute([$ship]);
		$del_dig=$pdo->prepare("DELETE FROM dig where ship=?");
		$del_dig->execute([$ship]);
		$upd_proj=$pdo->prepare("update project set ship=0,flag=5 where ship=? and flag=4");
		$upd_proj->execute([$ship]);		
		$upd_proj1=$pdo->prepare("update project set ship=0 where ship=?");
		$upd_proj1->execute([$ship]);		

//		$upd_proj=$pdo->prepare("UPDATE project set ship=0 where id_f=? and ship=? and flag<7");
//		$upd_proj->execute(array($_POST['fleet'],$ship));

		header('Location: ../manage_ships.php');
	}
//Разобрать
	if (isset($_POST['razob_'])){
		$fleet=$_POST['fleet'];
		$ids=$_POST['ids'];	
		$q_res=$pdo->prepare("select users.name as uname, ships.fleet as fleet, ships.name as name, ships.human as human, typeship.type as class, ships.type as type,
ships.descparts as descpart, typeship.cargo as size, typeship.nfuel as nfuel, typeship.nwater as nwater, typeship.ncomp as ncomp 
from typeship join ships on typeship.id=ships.type left join users on ships.user=users.id where ships.id=? LIMIT 1");
		$q_res->execute([$ids]);
		$res=$q_res->fetch();
		$desc='Разобран '.$res['size'].' '.$res['class'].' - '.$res['name'];
		$desc1=$desc.', Ресурсы пошли на нужды флота, уникальные комплектующие: ('.trim($res['descpart']).') переданы владельцу (';
		if ($res['uname']==NULL) {$desc.'Командуру флота)';} else {$desc.$res['uname'].')';}
		resurs_upd($res['fleet'],$desc1,$res['nfuel'],$res['nwater'],$res['ncomp']);
//Падение морали от уничтожения
        	$moral=0;
		if ($res['type']==2 or $res['type']==24 or $res['type']==29 or $res['type']==23 or $res['type']==28){
			$moral=2;
		}
		if ($res['type']==1 or $res['type']==20 or $res['type']==21){
			$moral=20;
		}
		if ($res['type']==22 or $res['type']==5 or $res['type']==10 or $res['type']==9 or $res['type']==4 or $res['type']==26 or $res['type']==27 or $res['type']==25 or $res['type']==7 or $res['type']==12 or $res['type']==11 or $res['type']==6){
			$moral=10;
		}
  		$q_hmoral=$pdo->prepare("INSERT INTO hist_moral (hist_moral.id_f,hist_moral.vera,hist_moral.hope,hist_moral.timstamp,hist_moral.text) SELECT moral.id_f,moral.vera,moral.hope,UNIX_TIMESTAMP(NOW()) AS timstamp,? as tex from moral WHERE moral.`id_f`= ?");
		$q_hmoral->execute(array($desc,$fleet));
  		$q_moral=$pdo->prepare("UPDATE moral SET `hope`=`hope`- ? WHERE `id_f`= ?");	
		$q_moral->execute(array($moral,$fleet));
//падение морали от уплотнения
		if ($res['human']>50){
			if ($res['human']>50 and $res['human']<300){
				$moral=1;
			}
			if ($res['human']>=300 and $res['human']<700){
				$moral=2;
			}
			if ($res['human']>=700){
				$moral=3;
			}
			$del_dig=$pdo->prepare("DELETE FROM dig where ship=?");
			$del_dig->execute([$ids]);
			$upd_proj=$pdo->prepare("update project set ship=0,flag=5 where ship=? and flag=4");
			$upd_proj->execute([$ids]);		
			$upd_proj1=$pdo->prepare("update project set ship=0 where ship=?");
			$upd_proj1->execute([$ids]);		

	  		$q_hmoral=$pdo->prepare("INSERT INTO hist_moral (hist_moral.id_f,hist_moral.vera,hist_moral.hope,hist_moral.timstamp,hist_moral.text) SELECT moral.id_f,moral.vera,moral.hope,UNIX_TIMESTAMP(NOW()) AS timstamp,? as tex from moral WHERE moral.`id_f`= ?");
			$q_hmoral->execute(array('Произведено уплотнение населения',$fleet));
	  		$q_moral=$pdo->prepare("UPDATE moral SET `hope`=`hope`- ? WHERE `id_f`= ?");	
			$q_moral->execute(array($moral,$fleet));
		
		}
		$q_del_ship=$pdo->prepare("DELETE FROM ships where id=? LIMIT 1");
		$q_del_ship->execute(array($ids));
		$q_hum=$pdo->prepare("select count(id) as nship from ships where fleet=? and repair=0");
		$q_hum->execute([$res['fleet']]);
		$hum=$q_hum->fetchColumn();
		if ($hum<>0) {
			$inshiphum=floor($res['human']/$hum);
			$flaghum=$res['human']-$inshiphum*$hum;
			$q_upd_human=$pdo->prepare("UPDATE ships set human = human+ ? where fleet=?");
			$q_upd_human->execute(array($inshiphum,$res['fleet']));
			$q_upd_huma=$pdo->prepare("UPDATE ships set human = human+ ? where id=?");
			$q_upd_huma->execute(array($flaghum,$res['fleet']));
		} else {
			$inshiphum=0;
			$flaghum=$res['human'];
			$q_upd_hum=$pdo->prepare("UPDATE ships set human = human+ ? where id=?");
			$q_upd_hum->execute(array($flaghum,$res['fleet']));
		}
		header('Location: ../manage_ships.php');
	}
//Арест корабля и падение морали
	if (isset($_POST['arest_'])){
		$upd_sh=$pdo->prepare("update ships set user=? where id=?");
		$upd_sh->execute(array($_POST['fleet'],$_POST['ids']));
		$qdship=$pdo->prepare("select ships.name as name,ships.human as human,users.name as vlad,ships.type as type,typeship.type as ntype from ships join typeship on ships.type=typeship.id left join users on ships.user=users.id where ships.id=?");
		$qdship->execute([$_POST['ids']]);
		$dship=$qdship->fetch();
  		$q_hmoral=$pdo->prepare("INSERT INTO hist_moral (hist_moral.id_f,hist_moral.vera,hist_moral.hope,hist_moral.timstamp,hist_moral.text) SELECT moral.id_f,moral.vera,moral.hope,UNIX_TIMESTAMP(NOW()) AS timstamp,? as tex from moral WHERE moral.`id_f`= ?");
		$desc='Арест корабля!<br>'.dolj($_COOKIE['access']).' '.$_COOKIE['name'].' приказал арестовать корабль '.$dship['name'].' ('.$dship['ntype'].') c '.$dship['human'].' человек на борту, принадлежавший '.$dship['vlad'].'.<br>Корабль перешел в собственность командования флота.';
		$moral=5;
		$q_hmoral->execute(array($desc,$_POST['fleet']));
  		$q_moral=$pdo->prepare("UPDATE moral SET `hope`=`hope`- ? WHERE `id_f`= ?");	
		$q_moral->execute(array($moral,$_POST['fleet']));
                nnews('ВВС',$_POST['fleet'],$desc); //постим новость
		header('Location: ../manage_ships.php');
	}
//new_fl_
	if (isset($_POST['new_fl_'])){
//newname_fl - имя нового флота
//new_flag - флагман в старом флоте, если 
		$ship=(int)trim($_POST['ids']);
		$q_date=$pdo->prepare("SELECT ships.`name` AS `name`, destination.name as fname, ships.user as kag,typeship.sizz as sizz, destination.enemy as enemy, resurs.fuel, resurs.water,
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
//установить флагман, если он уходит
if ($ship==$old_fl and $num_s>1){
	$ship_n=$_POST['new_flag'];
	$old_name_fl=control_name($old_date['fname'],$old_fl);
	$upd_f=$pdo->prepare("UPDATE destination set destination.who=?, destination.name=? where destination.who=?");
	$upd_f->execute(array($ship_n,$old_name_fl,$old_fl));
	$rapt1=1000+$ship_n;
	$rapt1old=1000+$old_fl;
	$namerapt='пилот '.$old_name_fl;
	$upd_f=$pdo->prepare("UPDATE destination set destination.who=?, destination.name=? where destination.who=?");
	$upd_f->execute(array($rapt1,$namerapt,$rapt1old));
	$rapt1=2000+$ship_n;
	$rapt1old=2000+$old_fl;
	$upd_f->execute(array($rapt1,$namerapt,$rapt1old));
	$rapt1=3000+$ship_n;
	$rapt1old=3000+$old_fl;
	$upd_f->execute(array($rapt1,$namerapt,$rapt1old));
	$upd_s=$pdo->prepare("UPDATE ships set fleet=? where fleet=?");
	$upd_s->execute(array($ship_n,$old_fl));
	$upd_r=$pdo->prepare("UPDATE norms set id_f=? where id_f=?");
	$upd_r->execute(array($ship_n,$old_fl));
	$upd_r=$pdo->prepare("UPDATE moral set id_f=? where id_f=?");
	$upd_r->execute(array($ship_n,$old_fl));
	$upd_r=$pdo->prepare("UPDATE resurs set id_f=? where id_f=?");
	$upd_r->execute(array($ship_n,$old_fl));
	$upd_r=$pdo->prepare("UPDATE scanning set who=? where who=?");
	$upd_r->execute(array($ship_n,$old_fl));
	$q_proj=$pdo->prepare("update project set id_f=?,vlast=? where id_f=?");
	$q_proj->execute(array($ship_n,$ship_n,$old_fl));
	$q_user=$pdo->prepare("update users set id_f=? where id_f=?");
	$q_user->execute(array($ship_n,$old_fl));
	$q_user1=$pdo->prepare("update users set access=? where access=?");
	$q_user1->execute(array($ship_n,$old_fl));
	$q_user2=$pdo->prepare("update users set id=? where id=?");
	$q_user2->execute(array($ship_n,$old_fl));
	$q_log1=$pdo->prepare("update hist_resurs set id_f=? where id_f=?");
	$q_log1->execute(array($ship_n,$old_fl));
	$q_log2=$pdo->prepare("update hist_moral set id_f=? where id_f=?");
	$q_log2->execute(array($ship_n,$old_fl));
	$upd_r=$pdo->prepare("UPDATE hist_norms set id_f=? where id_f=?");
	$upd_r->execute(array($ship_n,$old_fl));
	$upd_r=$pdo->prepare("UPDATE news set fleet=? where fleet=?");
	$upd_r->execute(array($ship_n,$old_fl));
}
//
if ($num_s>1){		
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
	$name_nf=control_name($_POST['newname_fl'],$ship);// - имя нового флота

	$sti = $pdo->prepare("INSERT INTO destination (pass, enemy, who, name, locat, timer, tim_pre, jumping, map_dest, image, radimage) VALUES (?, ?, ?, ?, ?, 1980, 300, 0, 0, ?, ?)");
	$pass=md5($_POST['pass_fl']);
	$sti->execute(array($pass, $enemy,$ship,$name_nf,$locat,$imag,$radar));
	$text='Корабль '.$name.' вышел из состава флота';
	resurs_upd($old_fl,$text,-1*$fuel,-1*$water,-1*$comp);
		
	$flrap = $pdo->prepare("INSERT INTO destination (pass, enemy, who, name, locat, timer, tim_pre, jumping, map_dest, image, radimage) VALUES (?, ?, ?, ?, 0, 300, 180, 0, 0, ?, ?)");
	$name_r='пилот '.$name_nf;
	$id_r=$ship+1000;
	$flrap->execute(array($pass, $enemy,$id_r,$name_r,$rap,$radar));
	$id_r=$ship+2000;
	$flrap->execute(array($pass, $enemy,$id_r,$name_r,$rap,$radar));
	$id_r=$ship+3000;
	$flrap->execute(array($pass, $enemy,$id_r,$name_r,$rap,$radar));
	$ins_proj=$pdo->prepare("INSERT INTO project (id_f,nazv,init,rukov,lobby,vlast,descrip,real_result,flag,type) VALUES (?,'Создание флота',?,?,?,?,'Создание флота','Флот создан',6,0)");
	$ins_proj->execute(array($ship,$ship,$ship,$ship,$ship));
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
//перемещаем владельца корабля - адмиралом
	$quser=$pdo->prepare("update users set access=?,dolj=2000,id_f=? where id=?");
	$quser->execute(array($ship,$ship,$old_date['kag']));
//создаем зак.власть
    	$ins_vlast = $pdo->prepare("INSERT INTO users (id, id_f, name, access, dolj) VALUES (?, ?, ?, -1,1004)");
	$vlast='Командование '.$name_nf;
	$ins_vlast->execute(array($ship,$ship,$vlast));
} else {
//только переименовать
	$old_name_fl=control_name($old_date['fname'],$old_fl);
	$upd_f=$pdo->prepare("UPDATE destination set destination.name=? where destination.who=?");
	$upd_f->execute(array($old_name_fl,$old_fl));
	$rapt1old=1000+$old_fl;
	$namerapt='пилот '.$old_name_fl;
	$upd_f=$pdo->prepare("UPDATE destination set destination.name=? where destination.who=?");
	$upd_f->execute(array($namerapt,$rapt1old));
	$rapt1old=2000+$old_fl;
	$upd_f->execute(array($namerapt,$rapt1old));
	$rapt1old=3000+$old_fl;
	$upd_f->execute(array($namerapt,$rapt1old));
}
		header('Location: ../manage_ships.php');
	}
	if (isset($_POST['ch_fl_'])){
//Сменить флот
//ch_fl_
		$q_date=$pdo->prepare("SELECT ships.`name` AS `name`, destination.name as fname, ships.user as kag, 
typeship.sizz as sizz, destination.enemy as enemy, resurs.fuel, resurs.water,
resurs.comp, destination.locat AS locat, ships.fleet AS fleet
FROM ships
JOIN destination ON ships.fleet=destination.who
join typeship on ships.type=typeship.id
JOIN resurs ON ships.fleet=resurs.id_f
WHERE ships.id=?");
		$q_date->execute([$_POST['ids']]);
		$old_date=$q_date->fetch();
		$locat=$old_date['locat'];
		$old_fl=$old_date['fleet'];
		$name=$old_date['name'];
		$fuel=$old_date['fuel'];
		$water=$old_date['water'];
		$comp=$old_date['comp'];
		$enemy=$old_date['enemy'];
		$q_num_s=$pdo->prepare("select count(id) from ships where fleet=? group by fleet");
		$q_num_s->execute([$old_fl]);
		$num_s=$q_num_s->fetchColumn();
		if ($num_s>1){
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
			$upd_fl=$pdo->prepare("update ships set fleet=? where id=?");
			$upd_fl->execute(array($_POST['new_fl'],$_POST['ids']));
			$text='Корабль '.$name.' перешел из флота '.$old_date['fname'].' во флот '.ask_name($_POST['new_fl']);
			resurs_upd($_POST['fleet'],$text,-1*$fuel,-1*$water,-1*$comp);
			resurs_upd($_POST['new_fl'],$text,$fuel,$water,$comp);
			nnews($_COOKIE['name'],$_POST['fleet'],$text);
			nnews($_COOKIE['name'],$_POST['new_fl'],$text);

			if ($_POST['ids']==$_POST['fleet']){
				$sel_fl=$pdo->prepare("select id from ships where fleet=? order by repair, type LIMIT 1");
				$sel_fl->execute([$_POST['fleet']]);
//устанасвиваем флагман
			$ship_n=$sel_fl->fetchColumn();
			$upd_f=$pdo->prepare("UPDATE destination set destination.who=? where destination.who=?");
			$upd_f->execute(array($ship_n,$old_fl));
			$old_name_fl=control_name($old_date['fname'],$ship_n);
			$upd_f=$pdo->prepare("UPDATE destination set destination.name=? where destination.who=?");
			$upd_f->execute(array($old_name_fl,$ship_n));

			$rapt1=1000+$ship_n;
			$rapt1old=1000+$old_fl;
			$namerapt='пилот '.$old_name_fl;
			$upd_f=$pdo->prepare("UPDATE destination set destination.who=?, destination.name=? where destination.who=?");
			$upd_f->execute(array($rapt1,$namerapt,$rapt1old));
			$rapt1=2000+$ship_n;
			$rapt1old=2000+$old_fl;
			$upd_f->execute(array($rapt1,$namerapt,$rapt1old));
			$rapt1=3000+$ship_n;
			$rapt1old=3000+$old_fl;
			$upd_f->execute(array($rapt1,$namerapt,$rapt1old));
			$upd_s=$pdo->prepare("UPDATE ships set fleet=? where fleet=?");
			$upd_s->execute(array($ship_n,$old_fl));
			$upd_r=$pdo->prepare("UPDATE norms set id_f=? where id_f=?");
			$upd_r->execute(array($ship_n,$old_fl));
			$upd_r=$pdo->prepare("UPDATE moral set id_f=? where id_f=?");
			$upd_r->execute(array($ship_n,$old_fl));
			$upd_r=$pdo->prepare("UPDATE resurs set id_f=? where id_f=?");
			$upd_r->execute(array($ship_n,$old_fl));
			$upd_r=$pdo->prepare("UPDATE scanning set who=? where who=?");
			$upd_r->execute(array($ship_n,$old_fl));
			$q_proj=$pdo->prepare("update project set id_f=?,vlast=? where id_f=?");
			$q_proj->execute(array($ship_n,$ship_n,$old_fl));
			$q_user=$pdo->prepare("update users set id_f=? where id_f=?");
			$q_user->execute(array($ship_n,$old_fl));
			$q_user1=$pdo->prepare("update users set access=? where access=?");
			$q_user1->execute(array($ship_n,$old_fl));
//			$q_scan=$pdo->prepare("SELECT s1.id_ano,s1.who,s1.level,s1.tim
//FROM scanning s1
//INNER JOIN scanning s2 ON s1.id_ano=s2.id_ano
//WHERE s1.who=? AND s2.who=? AND s1.tim<s2.tim");

			$q_user2=$pdo->prepare("update users set id=? where id=?");
			$q_user2->execute(array($ship_n,$old_fl));
			$q_log1=$pdo->prepare("update hist_resurs set id_f=? where id_f=?");
			$q_log1->execute(array($ship_n,$old_fl));
			$q_log2=$pdo->prepare("update hist_moral set id_f=? where id_f=?");
			$q_log2->execute(array($ship_n,$old_fl));
			$upd_r=$pdo->prepare("UPDATE hist_norms set id_f=? where id_f=?");
			$upd_r->execute(array($ship_n,$old_fl));
        	        }       	

		} else {
			$upd_fl=$pdo->prepare("update ships set fleet=? where id=?");
			$upd_fl->execute(array($_POST['new_fl'],$_POST['ids']));
			$upd_fl=$pdo->prepare("update ships set user=? where user=?");
			$upd_fl->execute(array($_POST['new_fl'],$old_fl));
			$text='Корабль '.$name.' перешел из флота '.$old_date['fname'].' во флот '.ask_name($_POST['new_fl']);
			resurs_upd($_POST['new_fl'],$text,$fuel,$water,$comp);
			nnews($_COOKIE['name'],$_POST['new_fl'],$text);
			$upd_r=$pdo->prepare("UPDATE news set fleet=? where fleet=?");
			$upd_r->execute(array($_POST['new_fl'],$old_fl));
			$upd_r=$pdo->prepare("UPDATE scanning set who=? where who=?");
			$upd_r->execute(array($_POST['new_fl'],$old_fl));
			$del_proj=$pdo->prepare("delete from project where id_f=? and flag>5");
			$del_proj->execute([$old_fl]);
			$q_proj=$pdo->prepare("update project set id_f=?,vlast=? where id_f=?");
			$q_proj->execute(array($_POST['new_fl'],$_POST['new_fl'],$old_fl));
			$q_user=$pdo->prepare("update users set id_f=? where id_f=? and id>100");
			$q_user->execute(array($_POST['new_fl'],$old_fl));
			$q_user1=$pdo->prepare("update users set access='-1',dolj='0' where access=?");
			$q_user1->execute([$old_fl]);
			$q_del=$pdo->prepare("delete from destination where who=?");
			$q_del->execute([$old_fl]);
			$rap1=$old_fl+1000;
			$q_del->execute([$rap1]);
			$rap1=$old_fl+2000;
			$q_del->execute([$rap1]);
			$rap1=$old_fl+3000;
			$q_del->execute([$rap1]);
			$q_del=$pdo->prepare("delete from resurs where id_f=?");
			$q_del->execute([$old_fl]);
			$q_del=$pdo->prepare("delete from moral where id_f=?");
			$q_del->execute([$old_fl]);
			$q_del=$pdo->prepare("delete from norms where id_f=?");
			$q_del->execute([$old_fl]);
			$q_del=$pdo->prepare("delete from users where id=?");
			$q_del->execute([$old_fl]);
		}
		header('Location: ../manage_ships.php');
	}
	if (isset($_POST['ch_vlad_'])){
		$upd_sh=$pdo->prepare("update ships set user=? where id=?");
		$upd_sh->execute(array($_POST['vlad'],$_POST['ids']));
		header('Location: ../manage_ships.php');
	}
//peres_
	if (isset($_POST['peres_'])){
		//$_POST['purp'];
		//$_POST['fleet'];
		//$_POST['ids'];
		$ins_proj=$pdo->prepare("insert into project (id_f,nazv,init,rukov,vlast,descrip,result,real_result,timer,flag,type,ship) VALUES (?,?,?,?,?,?,?,?,?,0,1,0)");
		$timer=30;
		$q_cur_sh=$pdo->prepare("select ships.id as ids,ships.name as name_sh,typeship.cargo as cargo, typeship.type as typ from ships join typeship on ships.type=typeship.id where ships.id=?");
		$q_cur_sh->execute([$_POST['ids']]);
		$cur_sh=$q_cur_sh->fetch();
		$q_type=$pdo->prepare("select cargo,type from typeship where id=?");
		$q_type->execute([$_POST['purp']]);
		$new_type=$q_type->fetch();
		$nazv='Перестройка корабля '.$cur_sh['name_sh'];
		$descrip='Перестройка корабля '.$cur_sh['name_sh'].' ('.$cur_sh['cargo'].' '.$cur_sh['typ'].') в '.$new_type['cargo'].' '.$new_type['type'];
		$res='Перестройка корабля '.$cur_sh['name_sh'].' ('.$cur_sh['cargo'].' '.$cur_sh['typ'].') в '.$new_type['cargo'].' '.$new_type['type'];
		$rres='Перестройка корабля '.$cur_sh['name_sh'].' ('.$cur_sh['cargo'].' '.$cur_sh['typ'].') в '.$new_type['cargo'].' '.$new_type['type'];
		$ins_proj->execute(array($_POST['fleet'],$nazv,$_COOKIE['user'],$_COOKIE['user'],$_POST['fleet'],$descrip,$res,$rres,$timer));
		$id = $pdo->lastInsertId();
		header('Location: ../project.php?proj='.$id);
	}
}
?>