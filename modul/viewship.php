<?php
include_once('connect.php');
if (isset($_POST['act'])){
	$q_ship=$pdo->prepare("select ships.name as s_name,ships.human as human,users.name as u_name, typeship.purp as purp,typeship.sizz as sizz from ships join typeship on ships.type=typeship.id left join users on ships.user=users.id where ships.id=? LIMIT 1");
        $q_ship->execute([$_POST['idship']]);
	$ship_data=$q_ship->fetch();
//		document.getElementById('new_fl').style.display='none';
//В новый флот
        if ($_POST['act']=='new_fl'){
		$q_curf=$pdo->prepare("select name as f_name from destination where who=? LIMIT 1");
		$q_curf->execute([$_POST['fl']]);
		$cfl_name=$q_curf->fetchcolumn();
		echo "<form method='post' action='jobs/manage_ships.php'><p>Вы покидаете флот ",$cfl_name,"!</p><p>Укажите название Вашего флота: <input type='text' name='newname_fl' required value='",$ship_data['s_name'],"'><br>
<small>(нельзя использовать названия других кораблей, и названия существующих флотов)</small></p>
<p>Укажите пароль для входа в рубку: <input type='text' name='pass_fl' value='' required></p>";
		echo "<input type='hidden' name='fleet' value='",$_POST['fl'],"'><input type='hidden' name='ids' value='",$_POST['idship'],"'>";
		if ($_POST['idship']==$_POST['fl']){
//Флагманом будет назначен:
			$q_flag=$pdo->prepare("select ships.id as ids,ships.name as s_name, typeship.cargo as cargo, typeship.type as type
from ships join typeship on ships.type=typeship.id where ships.id<>? and ships.fleet=? and ships.repair=0 order by typeship.sizz,typeship.purp LIMIT 10");
			$q_flag->execute(array($_POST['idship'],$_POST['fl']));
			if ($q_flag->rowcount()>0) {
				echo "<p>Назначить флагманом: <select name='new_flag'>";
				while ($flag=$q_flag->fetch()){
					echo "<option value='",$flag['ids'],"'>",$flag['s_name'],"</option>";
				}
				echo "</select></p>";
			} else {

			}
		}	
	        echo "<hr></p>";
		echo "<input type='submit' name='new_fl_' value='ПОКИНУТЬ ФЛОТ'></form>";
	}
//Сменить флот
	if ($_POST['act']=='ch_fl'){
		$q_fl=$pdo->prepare("SELECT t1.name AS fl_name, t2.name AS new_fl, t2.who as n_fl, users.name AS u_name
FROM ships
JOIN destination t1 ON ships.fleet=t1.who
JOIN destination t2 ON t1.locat=t2.locat
JOIN ships s2 ON t2.who=s2.id
JOIN users ON t2.who=users.access
WHERE ships.id=? AND t1.who<>t2.who");
		$q_fl->execute([$_POST['idship']]);
		if ($q_fl->rowcount()>0){
			echo "<form method='post' action='jobs/manage_ships.php'><p>Произвести переход во флот: <select name='new_fl'><option disabled selected>Выбор флота</option>";
			while ($n_fleet=$q_fl->fetch()){
				echo "<option value='",$n_fleet['n_fl'],"'>",$n_fleet['new_fl']," - (",$n_fleet['u_name'],")</option>";
			}
	        	echo "</select></p><p>Внимание! договоренность с командующим выбранного флота должна быть достигнута ДО ПЕРЕХОДА!<input type='hidden' name='fleet' value='",$_POST['fl'],"'><input type='hidden' name='ids' value='",$_POST['idship'],"'></p><hr>";
			echo "<input type='submit' name='ch_fl_' value='ПЕРЕЙТИ в ДРУГОЙ ФЛОТ'></form>";
		} else {
			echo "<p>В секторе отсутствуют другие флоты. Переход в другой флот невозможен.</p>";
		}
	}
//Разобрать
	if ($_POST['act']=='razob'){
		$q_inj=$pdo->prepare("SELECT ships.id, ships.name
FROM ships
JOIN typeship ON ships.type=typeship.id
LEFT JOIN project ON ships.id=project.ship
WHERE (project.ship IS NULL OR project.timer<UNIX_TIMESTAMP(NOW())) AND typeship.purp=5 AND ships.repair=0 AND ships.fleet=?");
		$q_inj->execute([$_POST['fl']]);
		if ($q_inj->rowcount()>0) {
	        if ($_POST['fl']==$_POST['idship']){echo "<p>Невозможно разобрать флагман! Люди вас не поймут.</p>";} else {
			echo "<p>Произвести демонтаж корабля ",$ship_data['s_name']," и расселение ",$ship_data['human']," человек по кораблям флота?<hr></p>";
		        echo "<form method='post' action='jobs/manage_ships.php'><input type='hidden' name='fleet' value='",$_POST['fl'],"'><input type='hidden' name='ids' value='",$_POST['idship'],"'>";
			echo "<input type='submit' name='razob_' value='РАЗОБРАТЬ КОРАБЛЬ'></form>";
			}
		} else {
			echo "<p>Невозможно произвести разбор корабля, нет свободного инженерного корабля во флоте</p>";
		}
	}
//Перестроить
	if ($_POST['act']=='peres'){
		if ($ship_data['purp']==3){
		echo "<form method='post' action='jobs/manage_ships.php'><p>Инициировать проект перестройки корабля ",$ship_data['s_name']," в ";
		echo "<select name='purp'><option disabled selected>Тип корабля</option>";
		$q_type=$pdo->prepare("select id,type,purp from typeship where sizz=? and purp<>?");
		$q_type->execute(array($ship_data['sizz'],$ship_data['purp']));
		while ($type=$q_type->fetch()){
			echo "<option value='",$type['id'],"'>",$type['type'],"</option>";
		}
		echo "</select>";
	        echo "<input type='hidden' name='fleet' value='",$_POST['fl'],"'><input type='hidden' name='ids' value='",$_POST['idship'],"'>";
		echo "<input type='submit' name='peres_' value='ИНИЦИИРОВАТЬ ПРОЕКТ'></form>";
		} else {
			echo "<p>Корабль нельзя перестроить!</p>";
		}
	}
	if ($_POST['act']=='arest'){
		echo "<p>Произвести арест корабля ",$ship_data['s_name']," c ",$ship_data['human']," людьми на борту,<br> принадлежащего ",$ship_data['u_name'],"?<hr></p>";
	        echo "<form method='post' action='jobs/manage_ships.php'><input type='hidden' name='fleet' value='",$_POST['fl'],"'><input type='hidden' name='ids' value='",$_POST['idship'],"'>";
		echo "<input type='submit' name='arest_' value='ПРОИЗВЕСТИ АРЕСТ'></form>";
	}
	if ($_POST['act']=='del'){
		if ($_POST['fl']==$_POST['idship']){
		        echo "<p>Люди отказываются выполнять приказ \"открыть огонь\" по флагману флота!</p>";
		} else {
			echo "<p>Открыть огонь по кораблю ",$ship_data['s_name']," c ",$ship_data['human']," людьми на борту,<br> принадлежащему ",$ship_data['u_name'],"?<hr></p>";
		        echo "<form method='post' action='jobs/manage_ships.php'><input type='hidden' name='fleet' value='",$_POST['fl'],"'><input type='hidden' name='ids' value='",$_POST['idship'],"'>";
			echo "<input type='submit' name='del_' value='ОТКРЫТЬ ОГОНЬ!'></form>";
		}
	}
	if ($_POST['act']=='ch_vlad'){
		echo "<form method='post' action='jobs/manage_ships.php'><p>Настоящим, Вы соглашаетесь передать корабль ",$ship_data['s_name']," c ",$ship_data['human']," людьми на борту,<br> в собственность: ";
		$q_vlad=$pdo->prepare('SELECT distinct users.id,users.name FROM users left JOIN ships ON users.id_f=ships.fleet WHERE users.id_f=? OR ships.id=? ORDER BY users.id_f,users.name');
		$q_vlad->execute(array($_POST['fl'],$_POST['idship']));
		echo "<select name='vlad'><option disabled selected>Новый собственник</option>";
		while ($user=$q_vlad->fetch()){
			echo "<option value='",$user['id'],"'>",$user['name'],"</option>";
		}
		echo "</select><hr></p>";
	        echo "<input type='hidden' name='fleet' value='",$_POST['fl'],"'><input type='hidden' name='ids' value='",$_POST['idship'],"'>";
		echo "<input type='submit' name='ch_vlad_' value='ПЕРЕДАТЬ ПРАВО ВЛАДЕНИЯ'></form>";
	}

}
if (isset($_GET['ids'])) {
	if ($_GET['direct']==2){
		$q_prev=$pdo->prepare("select max(id) from ships where fleet=? and id<? LIMIT 1");
		$q_prev->execute(array($_GET['fleet'],$_GET['ids']));
		$nid=$q_prev->fetchcolumn();		
		if ($nid==NULL){$nid=$_GET['ids'];}
	} else {
		$q_prev=$pdo->prepare("select min(id) from ships where fleet=? and id>? LIMIT 1");
		$q_prev->execute(array($_GET['fleet'],$_GET['ids']));
		$nid=$q_prev->fetchcolumn();
		if ($nid==null){$nid=$_GET['ids'];}
	}
	$q_count=$pdo->prepare("select count(id) from ships where fleet=?");
	$q_coun=$pdo->prepare("select count(id) from ships where fleet=? and id<? ORDER BY id");
	$q_count->execute([$_GET['fleet']]);
	$q_coun->execute(array($_GET['fleet'],$nid));
	$smax=$q_count->fetchColumn();
	$scur=$q_coun->fetchColumn();
	$count_s=1+$scur.'/'.$smax;
	$fleet=$_GET['fleet'];
	$_POST['id']=$nid;
	$_POST['fleet']=$fleet;
}
if (isset($_POST['id'])) {
	$q_ship=$pdo->prepare("SELECT distinct ships.id as id, ships.name as s_name, destination.name as f_name, ships.image as imag,
users.name as u_name, ships.human as human,typeship.cargo as sizz,typeship.type as type, typeship.rfuel as rfuel,typeship.rwater as rwater,
typeship.rcomp as rcomp,typeship.nfuel as nfuel,typeship.nwater as nwater,typeship.ncomp as ncomp,typeship.jfuel as jfuel,
round(typeship.dfuel*norms.p1/100) as dfuel, round(typeship.dwater*norms.p1/100) as dwater,round(typeship.dcomp*norms.p1/100) as dcomp, 
round(ships.human*0.12*norms.n2/100) as h_water, round(ships.human*0.07*norms.n3/100) as h_comp, ships.descparts as descp, ships.spec as spec,
dig.`resurs` AS res,ships.`repair` AS rep, ships.user as id_u, ships.fleet as f_id,destination.locat as locat,project.nazv as p_name, project.flag as p_flag
from ships
join typeship on ships.type=typeship.id 
join norms on ships.fleet=norms.id_f
left join dig on ships.id=dig.ship 
left join destination on ships.fleet=destination.who 
left join users on ships.user=users.id
left join project on ships.id=project.ship 
where ships.id=?");
	$q_ship->execute([$_POST['id']]);
	$ship_data=$q_ship->fetch();
	$q_loc=$pdo->prepare("select locat from destination where who=?");
	$q_loc->execute([$_POST['fleet']]);
	$cur_loc=$q_loc->fetchColumn();
if ($ship_data['p_flag']>0 and $ship_data['p_flag']<7) {$proj_cur=$ship_data['p_name'].' ('.status($ship_data['p_flag']).')';} else {$proj_cur='нет';}
if ($cur_loc==$ship_data['locat']) {$edit=1;} else {$edit=0;}
if ($ship_data['f_name']==null or $ship_data['f_name']==''){$ship_data['f_name']='Местонахождение неизвестно';}
$stat='';
$butt_z='ЗАБАСТОВКА';
if (!isset($count_s)){$count_s='_/_';}
if ($ship_data['rep']==3){$stat='Арестован';}
if ($ship_data['rep']==2){$stat='Забастовка';$butt_z='ОТМЕНИТЬ ЗАБАСТОВКУ';}
if ($ship_data['rep']==1){$stat='Требуется ремонт';}
if ($ship_data['rep']==0){$stat='Простаивает';}
if ($ship_data['res']==1){$stat='Добывает тилиум';}
if ($ship_data['res']==2){$stat='Добывает воду';}
if ($ship_data['res']==3){$stat='Производит ТО';}
	$out=array(
'id_s'=>$ship_data['id'],
'id_u'=>$ship_data['id_u'],
'edit'=>$edit,
'id_f'=>$ship_data['f_id'],
'imag'=>$ship_data['imag'],
'fleet'=>$ship_data['f_name'],
'nameship'=>$ship_data['s_name'],
'human'=>$ship_data['human'],
'status'=>$stat,
'counter'=>$count_s,
'proj_c'=>$proj_cur,
'sizz'=>$ship_data['sizz'],
'type'=>$ship_data['type'],
'rfuel'=>$ship_data['rfuel'],
'rwater'=>$ship_data['rwater'],
'rcomp'=>$ship_data['rcomp'],
'nfuel'=>$ship_data['nfuel'],
'nwater'=>$ship_data['nwater'],
'ncomp'=>$ship_data['ncomp'],
'dfuel'=>$ship_data['dfuel'],
'obsl'=>"<img src='img/water.gif' style='width:14px;height:auto;'> ".$ship_data['h_water'].", &nbsp;&nbsp;<img src='img/detals.gif' style='width:14px;height:auto;'> ".$ship_data['h_comp'],
'dwater'=>$ship_data['dwater'],
'dcomp'=>$ship_data['dcomp'],
'jfuel'=>$ship_data['jfuel'],
'unic'=>trim($ship_data['descp']),
'spec'=>trim($ship_data['spec']),
'butt_z'=>$butt_z,
'ruler'=>$ship_data['u_name']
);
	echo json_encode($out);
}

if (isset($_POST['id_filt']) and isset($_POST['fil']) and isset($_POST['fl'])) {
	$my=$_POST['my'];	
	$id_filt=$_POST['id_filt'];
	$filt=$_POST['fil'];
	$fleet=$_POST['fl'];
	if ($filt==1){
		if ($my==0 or $my<>$id_filt){
		$q_ship=$pdo->prepare("SELECT distinct ships.id as id,ships.name as s_name, typeship.cargo as cargo,typeship.type as type, typeship.sizz as sizz
FROM ships
join typeship on ships.type=typeship.id
LEFT JOIN users ON ships.user=users.id
WHERE ships.user=? and ships.fleet=? ORDER BY ships.name");
        	$q_ship->execute(array($id_filt,$fleet));
		} else {
		$q_ship=$pdo->prepare("SELECT distinct ships.id as id,ships.name as s_name, typeship.cargo as cargo,typeship.type as type, typeship.sizz as sizz
FROM ships
join typeship on ships.type=typeship.id
LEFT JOIN users ON ships.user=users.id
WHERE ships.user=? ORDER BY ships.name");
        	$q_ship->execute(array($id_filt));
		}
	}
	if ($filt==2){
		$q_ship=$pdo->prepare("SELECT distinct ships.id as id,ships.name as s_name, typeship.cargo as cargo,typeship.type as type, typeship.sizz as sizz
FROM ships
join typeship on ships.type=typeship.id
LEFT JOIN users ON ships.user=users.id
WHERE ships.fleet=? and typeship.sizz=? ORDER BY ships.name");
        	$q_ship->execute(array($fleet,$id_filt));
	}
	if ($filt==3){
		$q_ship=$pdo->prepare("SELECT distinct ships.id as id,ships.name as s_name, typeship.cargo as cargo,typeship.type as type, typeship.sizz as sizz
FROM ships
join typeship on ships.type=typeship.id
LEFT JOIN users ON ships.user=users.id
WHERE ships.fleet=? and typeship.purp=? ORDER BY ships.name");
        	$q_ship->execute(array($fleet,$id_filt));
	}
	$nums=$q_ship->rowCount();
	$numcol=ceil($nums/3);
	$il=1;
	$d1='<div id="dships1">';
	$d2='<div id="dships2">';
	$d3='<div id="dships3">';
	while ($ship=$q_ship->fetch()){
		if ($il<=$numcol) {$d1=$d1."<a href=# onclick='wship(".$fleet.",".$ship['id'].");return false;'><span>".mb_strtoupper($ship['s_name'],'UTF-8')."</span></a><br>";}
		if ($numcol<$il and $il<=($numcol*2)) {$d2=$d2."<a href=# onclick='wship(".$fleet.",".$ship['id'].");return false;'><span>".mb_strtoupper($ship['s_name'],'UTF-8')."</span></a><br>";}
		if (($numcol*2)<$il and $il<=($numcol*3)) {$d3=$d3."<a href=# onclick='wship(".$fleet.",".$ship['id'].");return false;'><span>".mb_strtoupper($ship['s_name'],'UTF-8')."</span></a><br>";}
		$il=$il+1;
	}
	$out=$d1.'</div>'.$d2.'</div>'.$d3.'</div>';
	echo $out;
}
?>