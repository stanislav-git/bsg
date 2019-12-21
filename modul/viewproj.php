<?php
include_once('connect.php');
include_once('funct.php');
final_project();
if (isset($_POST['act'])){
	echo "<span class='close'>&times;</span>";
	$q_ship=$pdo->prepare("select project.nazv as nazv, project.descrip as descrip,project.`result` as `result` from project where project.id=? LIMIT 1");
        $q_ship->execute([$_POST['idship']]);
	$proj_data=$q_ship->fetch();
//		document.getElementById('new_fl').style.display='none';
	if ($_POST['act']=='n_ruk' or $_POST['act']=='c_ruk'){
		$q_ask=$pdo->prepare("select nazv from project where rukov=? and flag<5 LIMIT 1");
		$q_ask->execute([$_COOKIE['user']]);
		if ($q_ask->rowCount()==0) {
			echo "<form method='post' action='jobs/project.php'><input type='hidden' name='proj' value='",$_POST['idship'],"'><p>Вы становитесь руководителем проекта ",$proj_data['nazv'],"</p><hr>";
			echo "<input type='submit' name='c_ruk' value='СОГЛАСИТЬСЯ'></form>";
		} else {
			$noproj=$q_ask->fetch();
			echo "<p>Вы уже являетесь руководителем в проекте: <b>",$noproj['nazv'],"</b></p>";
		}
	}
	if ($_POST['act']=='n_lob' or $_POST['act']=='c_lob'){
		echo "<form method='post' action='jobs/project.php'><input type='hidden' name='proj' value='",$_POST['idship'],"'><p>Вы соглашаетесь представить проект ",$proj_data['nazv']," в Кворуме.</p><hr>";
		echo "<input type='submit' name='c_lob' value='СОГЛАСИТЬСЯ'></form>";
	}
	if ($_POST['act']=='ok'){
		$q_vlast=$pdo->prepare("select name from users where id_f=? and id=?");
		$q_vlast->execute(array($_COOKIE['fleet'],$_COOKIE['fleet']));
		$vlast=$q_vlast->fetchColumn();
		echo "<form method='post' action='jobs/project.php'><input type='hidden' name='proj' value='",$_POST['idship'],"'><p><b>",$vlast,"</b> одобрил проект ",$proj_data['nazv'],"</p><hr>";
		echo "<input type='submit' name='ok' value='ЗАНЕСТИ В ПРОТОКОЛ'></form>";
	}
	if ($_POST['act']=='no'){
		$q_vlast=$pdo->prepare("select name from users where id_f=? and id=?");
		$q_vlast->execute(array($_COOKIE['fleet'],$_COOKIE['fleet']));
		$vlast=$q_vlast->fetchColumn();
		echo "<form method='post' action='jobs/project.php'><input type='hidden' name='proj' value='",$_POST['idship'],"'><p><b>",$vlast,"</b> отклонил проект ",$proj_data['nazv'],"</p><hr>";
		echo "<input type='submit' name='no' value='ЗАНЕСТИ В ПРОТОКОЛ'></form>";
	}
	if ($_POST['act']=='start' or $_POST['act']=='start1'){
//проверить проект на валидность
		$q_proj=$pdo->prepare("select project.nazv as nazv,project.init as init, project.rukov as rukov, t1.live as ruk_l, project.flag, 
project.real_result, project.type, project.ship, project.fuel, project.water, project.comp 
from project 
left join users t1 on project.init=t1.id where project.id=?");
		$q_proj->execute([$_POST['idship']]);
		$tproj=$q_proj->fetchAll();
		$err='';
		if ($tproj[0]['ruk_l']<>0 and $tproj[0]['rukov']>0) {
			$curres=resurs($_COOKIE['fleet']);
			if ($curres['fuel']<=$tproj[0]['fuel']){$err=$err.'Недостаточно тилиума, ';}
			if ($curres['water']<=$tproj[0]['water']){$err=$err.'Недостаточно воды, ';}
			if ($curres['comp']<=$tproj[0]['comp']){$err=$err.'Недостаточно запчастей, ';}
			if ($tproj[0]['type']<>0 and $_COOKIE['fleet']<>999){
				if ($tproj[0]['type']==1){
					$freeship=$pdo->prepare("SELECT ships.id as id_sh, ships.name as name_sh 
FROM ships
LEFT JOIN (
SELECT DISTINCT s1.id AS ids
FROM ships s1
INNER JOIN project p1 ON s1.id=p1.ship
WHERE s1.fleet=? AND p1.flag<5) s2 ON ships.id=s2.ids
WHERE s2.ids IS NULL AND ships.fleet=? AND ships.`repair`=0 AND (ships.type=5 OR ships.type=10)");
				        $freeship->execute(array($_COOKIE['fleet'],$_COOKIE['fleet']));
					if ($freeship->rowcount()==0){
						$err=$err.'Нет свободного инженерного корабля, ';
					} else {
						$sh=1;
					}
				}
				if ($tproj[0]['type']==2) {
					$freeship=$pdo->prepare("SELECT ships.id as id_sh, ships.name as name_sh 
FROM ships
LEFT JOIN (
SELECT DISTINCT s1.id AS ids
FROM ships s1
INNER JOIN project p1 ON s1.id=p1.ship
WHERE s1.fleet=? AND p1.flag<5) s2 ON ships.id=s2.ids
WHERE s2.ids IS NULL AND ships.fleet=? AND ships.`repair`=0 AND (ships.type=26 OR ships.type=27)");
				        $freeship->execute(array($_COOKIE['fleet'],$_COOKIE['fleet']));
					if ($freeship->rowcount()==0){
						$err=$err.'Нет свободного медицинского корабля, ';
					} else {
						$sh=1;
					}

				}
				if ($tproj[0]['type']==3) {
					$freeship=$pdo->prepare("SELECT ships.id as id_sh, ships.name as name_sh
FROM ships
LEFT JOIN (
SELECT DISTINCT s1.id AS ids
FROM ships s1
INNER JOIN project p1 ON s1.id=p1.ship
WHERE s1.fleet=? AND p1.flag<5) s2 ON ships.id=s2.ids
WHERE s2.ids IS NULL AND ships.fleet=? AND ships.`repair`=0 AND (ships.type=4 OR ships.type=9)");
				        $freeship->execute(array($_COOKIE['fleet'],$_COOKIE['fleet']));
					if ($freeship->rowcount()==0){
						$err=$err.'Нет свободного исследовательского корабля, ';
					} else {
						$sh=1;
					}
				}
			}
		} else {
			$err=$err.'Руководитель проекта отсутствует, ';
			
		}
		$q_vlast=$pdo->prepare("select nazv from project where id=?");
		$q_vlast->execute([$_POST['idship']]);
		$vlast=$q_vlast->fetchColumn();
		$zer=0;
		if ($_COOKIE['fleet']==999) {
			$q999=$pdo->query("select id from project where id_f=999 and flag=4");
			$q999->execute();
			if ($q999->rowCount()<>0) {$zer=1;$err='Имеется запущенный проект';}
		}
		if ($err=='' and $zer==0){
			echo "<form method='post' action='jobs/project.php'>
<input type='hidden' name='proj' value='",$_POST['idship'],"'><p>Запустить и профинансировать проект <b>",$proj_data['nazv'],"</b>.";
			if (isset($sh)) {echo "<br>Для выполнения проекта задействовать корабль: <select name='ship'><option disabled selected><i>Корабль</i></option>";
				while ($ship=$freeship->fetch()){
					echo "<option value='",$ship['id_sh'],"'>",$ship['name_sh'],"</option>";
				}               
				echo "</select>";
			}
			echo "</p><hr><input type='submit' name='start' value='НАЧАТЬ'></form>";
		} else {
			echo "<p>Запуск проекта <b>",$proj_data['nazv'],"</b> невозможен - ",$err,".</p>";
		}
	}
	if ($_POST['act']=='stop'){
		echo "<form method='post' action='jobs/project.php'><input type='hidden' name='proj' value='",$_POST['idship'],"'><p>Вы останавливаете работы по проекту ",$proj_data['nazv'],", ресурсы истраченные на проект <b>возвращены не будут!</b></p><hr>";
		echo "<input type='submit' name='stop' value='ПОДТВЕРДИТЬ'></form>";
	}
	if ($_POST['act']=='cancel'){
		echo "<form method='post' action='jobs/project.php'><input type='hidden' name='proj' value='",$_POST['idship'],"'><p><font color='red'>Вводится МОРАТОРИЙ на проекты типа ",$proj_data['nazv'],", сроком на один год!</p><hr>";
		echo "<input type='submit' name='cancel' value='ПОДТВЕРДИТЬ'></form>";
	}
	if ($_POST['act']=='new') {
		echo "<form method='post' action='jobs/project.php'>
<table style='width:100%;'>
<tr><td>Инициатор проекта:</td><td><input type='hidden' name='init' value='",$_COOKIE['user'],"'><b>",$_COOKIE['name'],"</b></td></tr>
<tr><td>Флот:</td><td><input type='hidden' name='fleet' value='",$_COOKIE['fleet'],"'>",ask_name($_COOKIE['fleet']),"</td></tr>
<tr><td><b>Название проекта:</b></td><td style='width:80%;'><input type='text' name='nazv' value='' style='width:90%;' required placeholder='Краткое название проекта'></td></tr>
<tr><td>Тип проекта:</td><td><select name='type'><option value='0'>Социальный</option><option value='1'>Инженерный</option><option value='2'>Медицинский</option><option value='3'>Научный</option></select></td></tr>
<tr><td>Описание проекта:</td><td><textarea name='descrip' rows='4' style='width:100%;' required placeholder='Опишите ваш проект'>",$proj_data['descrip'],"</textarea></td></tr>
<tr><td>Ожидаемый результат:</td><td><textarea name='result' rows='4' style='width:100%;' required placeholder='Опишите результаты, которые будут достигнуты'>",$proj_data['result'],"</textarea></td></tr></table>";
		echo "<hr>";
		echo "<input type='submit' name='new' value='СОЗДАТЬ ПРОЕКТ'></form>";
	}
echo "<script>
    var span = document.getElementsByClassName('close')[0];
    span.onclick = function() {
      $('#myModal').fadeOut(250);
/*      modal.style.display = 'none';*/
    }
</script>";

//	echo "<script>var modal = document.getElementById('myModal');window.onclick = function(event) {    if (event.target == modal) {        $('#myModal').fadeOut(250);    }}";

}
if (isset($_GET['ids'])) {
	if ($_GET['direct']==2){
		$q_prev=$pdo->prepare("select max(id) from project where id_f=? and id<? LIMIT 1");
		$q_prev->execute(array($_GET['fleet'],$_GET['ids']));
		$nid=$q_prev->fetchcolumn();		
		if ($nid==NULL){$nid=$_GET['ids'];}
	} else {
		$q_prev=$pdo->prepare("select min(id) from project where id_f=? and id>? LIMIT 1");
		$q_prev->execute(array($_GET['fleet'],$_GET['ids']));
		$nid=$q_prev->fetchcolumn();
		if ($nid==null){$nid=$_GET['ids'];}
	}
	$fleet=$_GET['fleet'];
	$_POST['id']=$nid;
}
//конкретный проект
//$_POST['id']=1;
//$_POST['fleet']=6;
if (isset($_POST['id'])) {
	if ($_POST['id']==0){
		$q_proj=$pdo->prepare("SELECT project.id as id from project where project.id_f=? order by project.flag,project.type,project.nazv LIMIT 1");
		$q_proj->execute([$_POST['fleet']]);
		$_POST['id']=$q_proj->fetchColumn();
	}
	$q_ship=$pdo->prepare("SELECT distinct project.id as id,project.id_f as fleet, project.nazv as nazv, project.init as init, t1.name as init_n, 
project.rukov as rukov, t2.name as rukov_n, project.lobby as lobby, t3.name as lobby_n, project.`type` as `type`, project.flag AS `status`, project.vlast AS vlast, 
t4.name as vlast_n, project.ship AS ship, project.descrip as descr, project.result as f_res, project.real_result as r_res, project.fuel as fuel, project.water as water, 
project.comp as comp, project.resurs as spec, project.timer as timer
FROM project 
left join users t4 on project.vlast=t4.id
left join users t3 on project.lobby=t3.id 
left join users t2 on project.rukov=t2.id 
left JOIN users t1 ON project.init=t1.id WHERE project.id=?");
	$q_ship->execute([$_POST['id']]);
	$proj_data=$q_ship->fetch();
	$stat='';
	$stat=status($proj_data['status']);
	if ($proj_data['fleet']<>999){
		$proj_data['f_name']=ask_name($proj_data['fleet']);
	} else {
		$proj_data['f_name']='Клуб';
	}
	if ($proj_data['type']==0) {$proj_type='Социальный';}
	if ($proj_data['type']==1) {$proj_type='Инженерный';}
	if ($proj_data['type']==2) {$proj_type='Биомедицинский';}
	if ($proj_data['type']==3) {$proj_type='Научно-технический';}
	if ($proj_data['ship']<>0){
		$q_shname=$pdo->prepare("SELECT name from ships where id=?");
		$q_shname->execute([$proj_data['ship']]);
		$ship_name=$q_shname->fetchColumn();
	} else {
		if ($proj_data['type']>0){ $ship_name='Корабль не назначен';
		} else {$ship_name='Корабль не требуется';}
	}
	$out=array(
'id'=>$proj_data['id'],
'id_f'=>$proj_data['fleet'],
'nazv'=>$proj_data['nazv'],
'fleet'=>$proj_data['f_name'],
'nameship'=>$ship_name,
'status'=>$stat,
'statu'=>$proj_data['status'],
'fuel'=>$proj_data['fuel'],
'water'=>$proj_data['water'],
'comp'=>$proj_data['comp'],
'init_n'=>$proj_data['init_n'],
'init'=>$proj_data['init'],
'rukov_n'=>$proj_data['rukov_n'],
'lobby_n'=>$proj_data['lobby_n'],
'imag'=>$proj_data['type'],
'type'=>$proj_type,
'vlast'=>$proj_data['vlast'],
'vlast_n'=>$proj_data['vlast_n'],
'spec'=>$proj_data['spec'],
'timer'=>$proj_data['timer'],
'desc'=>$proj_data['descr'],
'f_res'=>$proj_data['f_res'],
'res'=>$proj_data['r_res']

);
	echo json_encode($out);
}

if (isset($_POST['id_filt']) and isset($_POST['fil']) and isset($_POST['fl'])) {
	$id_filt=$_POST['id_filt'];
	$filt=$_POST['fil'];
	$fleet=$_POST['fl'];
	if ($filt==1){
		if ($fleet<>999){
			$q_ship=$pdo->prepare("SELECT distinct project.id as id,project.nazv as nazv
FROM project
LEFT JOIN users ON project.init=users.id
WHERE project.init=? and project.id_f<>'999' ORDER BY project.flag, project.nazv");
	        	$q_ship->execute(array($id_filt));
		} else {
			$q_ship=$pdo->prepare("SELECT distinct project.id as id,project.nazv as nazv
FROM project
LEFT JOIN users ON project.init=users.id
WHERE project.init=? and project.id_f='999' ORDER BY project.flag, project.nazv");
	        	$q_ship->execute(array($id_filt));
		}
	}
	if ($filt==2){
		if ($fleet<>999){
			$q_ship=$pdo->prepare("SELECT distinct project.id as id,project.nazv as nazv
FROM project
LEFT JOIN users ON project.rukov=users.id
WHERE project.rukov=? and project.id_f<>'999' ORDER BY project.flag, project.nazv");
        		$q_ship->execute(array($id_filt));
		} else {
			$q_ship=$pdo->prepare("SELECT distinct project.id as id,project.nazv as nazv
FROM project
LEFT JOIN users ON project.rukov=users.id
WHERE project.rukov=? and project.id_f='999' ORDER BY project.flag, project.nazv");
        		$q_ship->execute(array($id_filt));
		}
	}
	if ($filt==3){
		$q_ship=$pdo->prepare("SELECT distinct project.id as id,project.nazv as nazv, project.`type` as `type`
FROM project
WHERE project.id_f=? and project.`type`=? ORDER BY project.flag, project.nazv");
        	$q_ship->execute(array($fleet,$id_filt));
	}
	if ($filt==4){
		$q_ship=$pdo->prepare("SELECT distinct project.id as id,project.nazv as nazv
FROM project
WHERE project.id_f=? and project.`flag`=? ORDER BY project.nazv");
        	$q_ship->execute(array($fleet,$id_filt));
	}
	$nums=$q_ship->rowCount();
	$numcol=ceil($nums/2);
	$il=1;
	$d1='<div id="dships1">';
	$d2='<div id="dships2">';
	while ($ship=$q_ship->fetch()){
		if ($il<=$numcol) {$d1=$d1."<a href=# onclick='wproj(".$fleet.",".$ship['id'].",".$filt.");return false;'><span>".mb_strtoupper($ship['nazv'],'UTF-8')."</span></a><br>";}
		if ($il>$numcol) {$d2=$d2."<a href=# onclick='wproj(".$fleet.",".$ship['id'].",".$filt.");return false;'><span>".mb_strtoupper($ship['nazv'],'UTF-8')."</span></a><br>";}
		$il=$il+1;
	}
	$out=$d1.'</div>'.$d2.'</div>';
	echo $out;
}
if (isset($_POST['ship'])) {
	echo "<span class='close'>&times;</span>";
	$qproj=$pdo->prepare("SELECT project.id as idp, project.id_f as fleet, init, project.nazv as nazv, rukov,
 lobby, descrip, result, real_result, fuel, water, comp, resurs, type, ship,flag,vlast,resurs,real_result,timer 
FROM project where project.id=? LIMIT 1");
	$sh=(int)trim($_POST['ship']);
//	echo $sh;
	$qproj->execute([$sh]);
	$proj=$qproj->fetch();
//	$q_num_sh=$pdo->prepare("select count(id) as numb from ships where fleet=? group by fleet");
//	$q_num_sh->execute([$ship['fleet']]);
//	$num_sh=$q_num_sh->fetchColumn();	
	echo "<form method='post' action='jobs/project.php'><input type='hidden' name='idp' value='",$proj['idp'],"'>";
	echo "<table><tr><td colspan=2><b>Название:</b> <input type='text' name='nazv' value='",$proj['nazv'],"'></td>";
	echo "<td>Флот:</td><td><select name='fleet'>";
	$qfleet=$pdo->query("select who,name from destination where who<1000 order by name");
	$qfleet->execute();
	while ($pfl=$qfleet->fetch()) {
		echo "<option value='",$pfl['who'],"'";
		if ($pfl['who']==$proj['fleet']) {echo " selected";}
		echo ">",$pfl['name'],"</option>";
	}
	echo "<option value='999'";
	if ($proj['fleet']==999) {echo " selected";}
	echo ">Клуб</option>";
	echo "</select></td></tr>";
	echo "<tr><td>Тип:</td><td>";
	echo "<input name='pretype' type='hidden' value='",$proj['type'],"'><select name='type'>";
	for ($i=0;$i<4;$i++) {
		if ($i==0) {
			echo "<option value='",$i,"'";
			if ($i==$proj['type']){echo " selected";}
			echo ">Социальный</option>";
		}
		if ($i==1) {
			echo "<option value='",$i,"'";
			if ($i==$proj['type']){echo " selected";}
			echo ">Инженерный</option>";
		}
		if ($i==2) {
			echo "<option value='",$i,"'";
			if ($i==$proj['type']){echo " selected";}
			echo ">Медицинский</option>";
		}
		if ($i==3) {
			echo "<option value='",$i,"'";
			if ($i==$proj['type']){echo " selected";}
			echo ">Научный</option>";
		}
	}
	echo "</select></td>";
	echo "<td>Инициатор:</td><td><select name='init'>";
	$qname=$pdo->query("select id,name from users where live=1 order by name");
	$qname->execute();
	echo "<option value='0'";
	if ($proj['init']==0) {echo " selected";}
	echo ">Нет</option>";
	while ($ini_u=$qname->fetch()) {
		echo "<option value='",$ini_u['id'],"'";
		if ($ini_u['id']==$proj['init']) {echo " selected";}
		echo ">",$ini_u['name'],"</option>";
	}
	echo "</select></td></tr>";
	if ($proj['type']<>0 and $proj['fleet']<>999){
		echo "<tr><td>Корабль:</td><td>";
		if ($proj['type']==1){
			$freeship=$pdo->prepare("SELECT ships.id as id_sh, ships.name as name_sh 
FROM ships
LEFT JOIN (
SELECT DISTINCT s1.id AS ids
FROM ships s1
INNER JOIN project p1 ON s1.id=p1.ship
WHERE s1.fleet=? AND p1.flag<5 and p1.ship<>?) s2 ON ships.id=s2.ids
WHERE s2.ids IS NULL AND ships.fleet=? AND ships.`repair`=0 AND (ships.type=5 OR ships.type=10 or ships.type=1 or ships.type=20)");
		        $freeship->execute(array($proj['fleet'],$proj['ship'],$proj['fleet']));
		}
		if ($proj['type']==2){
			$freeship=$pdo->prepare("SELECT ships.id as id_sh, ships.name as name_sh 
FROM ships
LEFT JOIN (
SELECT DISTINCT s1.id AS ids
FROM ships s1
INNER JOIN project p1 ON s1.id=p1.ship
WHERE s1.fleet=? AND p1.flag<5 and p1.ship<>?) s2 ON ships.id=s2.ids
WHERE s2.ids IS NULL AND ships.fleet=? AND ships.`repair`=0 AND (ships.type=26 OR ships.type=27 or ships.type=1 or ships.type=20)");
		        $freeship->execute(array($proj['fleet'],$proj['ship'],$proj['fleet']));
		}
		if ($proj['type']==3){
			$freeship=$pdo->prepare("SELECT ships.id as id_sh, ships.name as name_sh 
FROM ships
LEFT JOIN (
SELECT DISTINCT s1.id AS ids
FROM ships s1
INNER JOIN project p1 ON s1.id=p1.ship
WHERE s1.fleet=? AND p1.flag<5 and p1.ship<>?) s2 ON ships.id=s2.ids
WHERE s2.ids IS NULL AND ships.fleet=? AND ships.`repair`=0 AND (ships.type=4 OR ships.type=9 or ships.type=1 or ships.type=20)");
		        $freeship->execute(array($proj['fleet'],$proj['ship'],$proj['fleet']));
		}
		echo "<select name='ship'><option value='0'>не назначен</option>";
		while ($ship=$freeship->fetch()){
			echo "<option value='",$ship['id_sh'],"'";
			if ($proj['ship']==$ship['id_sh']) {echo " selected";}
			echo ">",$ship['name_sh'],"</option>";
		}               
		echo "</select>";
		echo "</td>";
	} else {
		echo "<tr><td>Корабль:</td><td>нет</td>";
	}
	echo "<td>Руководитель:</td><td><select name='rukov'>";
	$qname->execute();
	echo "<option value='0'";
	if ($proj['rukov']==0) {echo " selected";}
	echo ">Нет</option>";
	while ($ruk_u=$qname->fetch()) {
		echo "<option value='",$ruk_u['id'],"'";
		if ($ruk_u['id']==$proj['rukov']) {echo " selected";}
		echo ">",$ruk_u['name'],"</option>";
	}
	echo "</select></td></tr>";
	echo "<tr><td>Власть:</td><td><select name='vlast'>";
	$qnam=$pdo->query("select id,name from users where dolj=1004 order by name");
	$qnam->execute();
	while ($vlas_u=$qnam->fetch()) {
		echo "<option value='",$vlas_u['id'],"'";
		if ($vlas_u['id']==$proj['vlast']) {echo " selected";}
		echo ">",$vlas_u['name'],"</option>";
	}
	echo "</select></td>";
	echo "<td>Поддержка:</td><td><select name='lobby'>";
	echo "<option value='0'";
	if ($proj['lobby']==0) {echo " selected";}
	echo ">Нет</option>";
	$qname->execute();
	while ($lob_u=$qname->fetch()) {
		echo "<option value='",$lob_u['id'],"'";
		if ($lob_u['id']==$proj['lobby']) {echo " selected";}
		echo ">",$lob_u['name'],"</option>";
	}
	echo "</select></td></tr>";
	echo "<tr><td colspan=4><b>Описание</b></td></tr>";
	echo "<tr><td colspan=4><textarea name='descrip' style='width:99%;'>",$proj['descrip'],"</textarea></td></tr>";
	echo "<tr><td colspan=4><b>Предполагаемый результат</b></td></tr>";
	echo "<tr><td colspan=4><textarea name='result' style='width:99%;'>",$proj['result'],"</textarea></td></tr>";
	echo "<tr><td colspan=4><hr></td></tr>";
	echo "<tr><td colspan=3>";
	echo "<img src='img/tilium.gif' style='height:1.5em;width:auto;'> <input type='number' name='fuel' min=0 max=999999 style='width:4em;' value='",$proj['fuel'],"'>, ";
	echo "<img src='img/water.gif' style='height:1.5em;width:auto;'> <input type='number' name='water' min=0 max=999999 style='width:4em;' value='",$proj['water'],"'>, ";
	echo "<img src='img/detals.gif' style='height:1.5em;width:auto;'> <input type='number' name='comp' min=0 max=999999 style='width:4em;' value='",$proj['comp'],"'></td>";
	echo "<td>Таймер: <input type='number' name='timer' min=0 max=1000000 step=1 style='width:5em;' value='";
	if ($proj['flag']==4) {echo ($proj['timer']-time())/60,"'>мин";}
	else {echo $proj['timer']/60,"'>мин";}
	echo "</td></tr>";
	echo "<tr><td colspan=4><b>Уникальные компоненты</b></td></tr>";
	echo "<tr><td colspan=4><textarea name='resurs' style='width:99%;'>",$proj['resurs'],"</textarea></td></tr>";
	echo "<tr><td colspan=4><b>Полученный результат</b></td></tr>";
	echo "<tr><td colspan=4><textarea name='real_result' style='width:99%;'>",$proj['real_result'],"</textarea></td></tr>";
	echo "<tr><td colspan=4>Сложность: <select name='trubl'>";
	echo "<option value='0'>Легко</option>";
	echo "<option value='1'>Средне</option>";
	echo "<option value='2'>Сложно</option>";
	echo "<option value='3'>Очень сложно</option>";
	echo "<option value='4'>Невозможно</option></select>&nbsp; Статус: <select name='flag'>";
	for ($j=0;$j<9;$j++){
		echo "<option value='",$j,"'";
		if ($j==$proj['flag']) {echo " selected";}
		echo ">",status($j),"</option>";
	}
	echo "</select>&nbsp;&nbsp; <input type='submit' name='save' value='СОХРАНИТЬ'></td></tr>";
	echo "</table></form>";
echo "<script>
    var span = document.getElementsByClassName('close')[0];
    span.onclick = function() {
      modal.style.display = 'none';
    }
</script>";

}
?>