<?php
session_start();
include_once('modul/connect.php');
if (isset($_COOKIE['user'])){
	$q_fleet=$pdo->prepare("select id_f,dolj from users where id=? LIMIT 1");
	$q_fleet->execute([$_COOKIE['user']]);
	$data_fleet=$q_fleet->fetch();
}
if (isset($_SESSION['user_id']) or isset($data_fleet['id_f'])){
session_write_close();
$head='<!DOCTYPE html>
<html lang="ru-RU">
<head>
<meta charset="utf-8">
<title>УПРАВЛЕНИЕ КОРАБЛЯМИ</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script type="text/javascript" src="js/jquery.min.js"></script>
<link rel="stylesheet" href="css/base1.css">
<link rel="stylesheet" href="css/m_ship.css">
</head>
<body>
<div id="myModal" class="modal"><div class="modal-content" id="info"></div></div>
<header>УПРАВЛЕНИЕ КОРАБЛЯМИ</header>
<div class="container">
<div class="main-content">
<a href="users/index.php"><div id="logoff"><img src="img/power_red.png"></div></a>
<div class="content-wrap">
<div id="counter" style="color:white;">1/46</div>
<figure id="karusel">';
echo $head;
if (isset($_SESSION['user_id'])){$fleet=$_SESSION['user_id'];$access=1000;$my=0;}
if (isset($data_fleet['id_f'])){$fleet=$data_fleet['id_f'];$access=$data_fleet['dolj'];$my=$_COOKIE['user'];}
$q_ship=$pdo->prepare("SELECT distinct ships.id as id,typeship.cargo as cargo, typeship.sizz as sizz, typeship.`type` as type,
typeship.purp as purp, users.name as u_name, ships.user as ships_u, ships.fleet as fleet, ships.image as image
FROM ships
JOIN typeship ON ships.`type`=typeship.id
LEFT JOIN users ON ships.user=users.id
WHERE ships.id=?");
$q_ship->execute([$fleet]);
$cur_ship=$q_ship->fetch();
echo "<a href=# style='display:block;' title='Листать вперед' onclick='list(",$fleet,",1,",$cur_ship['id'],");return false;'><div id='rarrow'></div></a>";
echo "<img src='img/ships/",$cur_ship['image'],"'>
<a href=# style='display:block;' title='Листать назад' onclick='list(",$fleet,",2,",$cur_ship['id'],");return false;'><div id='larrow'></div></a>
</figure>";
if ($my<>0){
	$q_vlad=$pdo->prepare("SELECT distinct ships.id as id,users.name as u_name,ships.user as u_id, ships.fleet as fleet
FROM ships
LEFT JOIN users ON ships.user=users.id
WHERE ships.fleet=? OR ships.user=? GROUP BY users.id ORDER BY users.name");
	$q_vlad->execute(array($fleet,$my));
} else {
	$q_vlad=$pdo->prepare("SELECT distinct ships.id as id,users.name as u_name,ships.user as u_id, ships.fleet as fleet
FROM ships
LEFT JOIN users ON ships.user=users.id
WHERE ships.fleet=? GROUP BY users.id ORDER BY users.name");
	$q_vlad->execute([$fleet]);
}
echo "<nav>
<form>
<select id='rul' name='rul' onchange='shipchange(this,1,",$fleet,");'><option disabled selected>Владелец</option>";
while ($vlad=$q_vlad->fetch()){
	if ($vlad['u_id']==0 or $vlad['u_id']==NULL){
		echo "<option value='0'>имущество флота</option>";
	} else {
		echo "<option value='",$vlad['u_id'],"'>",$vlad['u_name'],"</option>";
	}
}
echo "</select>
<select id='size' name='size' onchange='shipchange(this,2,",$fleet,");'><option disabled selected>Размер</option>";
$q_sizz=$pdo->prepare("SELECT typeship.sizz as sizz, typeship.cargo as cargo, ships.fleet as fleet
FROM ships
join typeship on ships.type=typeship.id
WHERE ships.fleet=? GROUP BY typeship.sizz ORDER BY typeship.sizz");
$q_sizz->execute([$fleet]);
while ($sizz=$q_sizz->fetch()){
	echo "<option value='",$sizz['sizz'],"'>",$sizz['cargo'],"</option>";
}
echo "</select>
<select id='purp' name='purp' onchange='shipchange(this,3,",$fleet,");'><option disabled selected>Тип</option>";
$q_purp=$pdo->prepare("SELECT typeship.purp as purp, typeship.type as type, ships.fleet as fleet
FROM ships
join typeship on ships.type=typeship.id
WHERE ships.fleet=? GROUP BY typeship.purp ORDER BY typeship.type");
$q_purp->execute([$fleet]);
while ($purp=$q_purp->fetch()){
	echo "<option value='",$purp['purp'],"'>",$purp['type'],"</option>";
}
echo "</select>
</form>
</nav>
<main><div class='wrap' id='ship'></div></main>
</div>
<aside>
<div id='detail'>
<div style='margin-bottom:30px'>
<p>ФЛОТ: <span id='fleet'></span></p>
<p>НАЗВАНИЕ: <span id='nameship'></span></p>
<p>ВЛАДЕЛЕЦ: <span id='ruler'></span></p>
</div>
<div style='margin-bottom:30px'>
<p>РАЗМЕР: <span id='sizz'></span></p>
<p>ТИП: <span id='type'></span></p>
<p>НАСЕЛЕНИЕ: <span id='human'></span></p>
<p>СНАБЖЕНИЕ: <span id='dig'></span></p>
<p>СТАТУС: <span id='status'></span></p>
<p>ПРОЕКТ: <span id='project'></span></p>
</div>
<div>
<p>ПРОИЗВОДСТВО: <img src='img/tilium.gif' style='width:14px;height:auto;'> <span id='dfuel'></span>,&nbsp;&nbsp; <img src='img/water.gif' style='width:14px;height:auto;'> <span id='dwater'></span>,&nbsp;&nbsp; <img src='img/detals.gif' style='width:14px;height:auto;'> <span id='dcomp'></span></p>
<p>ПОТРЕБЛЕНИЕ: <img src='img/tilium.gif' style='width:14px;height:auto;'> <span id='rfuel'>0</span>,&nbsp;&nbsp; <img src='img/water.gif' style='width:14px;height:auto;'> <span id='rwater'></span>,&nbsp;&nbsp; <img src='img/detals.gif' style='width:14px;height:auto;'> <span id='rcomp'></span></p>
<p>ПРЫЖОК: <img src='img/tilium.gif' style='width:14px;height:auto;'> <span id='jump'></span></p>
<p>СПЕЦ ВОЗМОЖНОСТИ: <span id='spec'></span></p>
<p>ДЕМОНТАЖ: <img src='img/tilium.gif' style='width:14px;height:auto;'> <span id='nfuel'></span>,&nbsp;&nbsp; <img src='img/water.gif' style='width:14px;height:auto;'> <span id='nwater'></span>, &nbsp;&nbsp;<img src='img/detals.gif' style='width:14px;height:auto;'> <span id='ncomp'></span>, &nbsp;&nbsp;<span id='unic'></span></p>
</div>
</div>
<div id='tabl'>
<form>
<input type='hidden' name='ship' id='id_ship' value=''>
<input type='submit' class='but red' name='del' id='del' value='УНИЧТОЖИТЬ' onclick='fire(this);return false;'> 
<input type='submit' class='but' name='razob' id='razob' value='РАЗОБРАТЬ' onclick='fire(this);return false;'> 
<input type='submit' class='but' name='peres' id='peres' value='ПЕРЕСТРОИТЬ' onclick='fire(this);return false;'> 
<input type='submit' class='but' name='ch_fl' id='ch_fl' value='СМЕНИТЬ ФЛОТ' onclick='fire(this);return false;'> 
<input type='submit' class='but' name='new_fl' id='new_fl' value='ОТДЕЛИТЬСЯ В НОВЫЙ ФЛОТ' onclick='fire(this);return false;'> 
<input type='submit' class='but' name='ch_vlad' id='ch_vlad' value='СМЕНИТЬ ВЛАДЕЛЬЦА' onclick='fire(this);return false;'> 
<input type='submit' class='but red' name='brake' id='brake' value ='ЗАБАСТОВКА' onclick='doing(this);return false;'> 
<input type='submit' class='but red' name='arest' id='arest' value='АРЕСТОВАТЬ КОРАБЛЬ' onclick='fire(this);return false;'> 
</form>
</div>
</aside>
</div>
</div>
<footer></footer>
<script>
window.onload = function(){
  wship(",$fleet,",",$fleet,");
  var select = document.getElementById('rul');
  var options = select.getElementsByTagName('option');
  for (var i=0; i<options.length; i++)  {
    if (options[i].value==",$_COOKIE['user'],") {
    $.ajax({
         type: 'POST',
         url: 'modul/viewship.php',
         data: {my:",$my,",id_filt:options[i].value,fil:1,fl:",$fleet,"},
         success: function(html){
             $('#ship').html(html);	
         }
    });
	}
  }
}
var modal = document.getElementById('myModal');
window.onclick = function(event) {
    if (event.target == modal) {
        $('#myModal').fadeOut(250);
    }
}

function doing(ids){
    act=ids.id;
    idship=document.getElementById('id_ship').value;
    if (ids.value=='ЗАБАСТОВКА'){	
	    $.ajax({
        	 type: 'POST',
	         url: 'jobs/ships.php',
	         data: {fbreakship:1,act:act,ids:idship},
        	 success: function(json){
		     var obj=JSON.parse(json);
        	     wship(",$fleet,",obj.id_s);	
	         }
	    });
	    return false;
     }
     if (ids.value=='ОТМЕНИТЬ ЗАБАСТОВКУ'){
	    $.ajax({
        	 type: 'POST',
	         url: 'jobs/ships.php',
	         data: {repair:1,act:act,ids:idship},
        	 success: function(json){
		     var obj=JSON.parse(json);
        	     wship(",$fleet,",obj.id_s);	
	         }
	    });
	    return false;
     }
}

function fire(ids){
    act=ids.id;
    nam=ids.name;
    idship=document.getElementById('id_ship').value;
    $.ajax({
       	 type: 'POST',
         url: 'modul/viewship.php',
       	 data: {idship:idship,act:nam,fl:",$fleet,"},
         success: function(html){
       	     $('#info').html(html);	
         }
    });
    $('#myModal').fadeIn();	
}

function shipchange(selec,filt,fleet){
var selectedOption = selec.options[selec.selectedIndex];
    $.ajax({
         type: 'POST',
         url: 'modul/viewship.php',
         data: {my:",$my,",id_filt:selectedOption.value,fil:filt,fl:fleet},
         success: function(html){
             $('#ship').html(html);	
         }
    });
    selec.selectedIndex=0;
    return false;
}
function wship(fleet,id){
        $.ajax({
            type: 'POST',
            url: 'modul/viewship.php',
            data: {id:id,fleet:fleet},
            success: function(json) {
		var obj=JSON.parse(json);
		wiewship(obj);
	   }
       });
       return false;
}

function list(fleet,direct,id){
        $.ajax({
            type: 'GET',
            url: 'modul/viewship.php',
            data: {fleet:fleet,direct:direct,ids:id},
            success: function(json) {
		var obj=JSON.parse(json);
		wiewship(obj);
	   }
       });
       return false;
}

function wiewship(obj){
		document.getElementById('karusel').innerHTML='<a href=# style=\'display:block;\' title=\'Листать вперед\' onclick=\'list(",$fleet,",1,'+obj.id_s+');return false;\'><div id=\'rarrow\'></div></a><img src=\'img/ships/'+obj.imag+'\'><a href=# style=\'display:block;\' title=\'Листать назад\' onclick=\'list(",$fleet,",2,'+obj.id_s+');return false;\'><div id=\'larrow\'></div></a>';
		document.getElementById('fleet').innerHTML=obj.fleet;
		document.getElementById('nameship').innerHTML=obj.nameship;
		document.getElementById('ruler').innerHTML=obj.ruler;
		document.getElementById('sizz').innerHTML=obj.sizz;
		document.getElementById('type').innerHTML=obj.type;
		document.getElementById('human').innerHTML=obj.human;
		document.getElementById('dig').innerHTML=obj.obsl;
		document.getElementById('status').innerHTML=obj.status;
		document.getElementById('project').innerHTML='';
		document.getElementById('dfuel').innerHTML=obj.dfuel;
		document.getElementById('dwater').innerHTML=obj.dwater;
		document.getElementById('dcomp').innerHTML=obj.dcomp;
		document.getElementById('counter').innerHTML=obj.counter;
		document.getElementById('rfuel').innerHTML=obj.rfuel;
		document.getElementById('rwater').innerHTML=obj.rwater;
		document.getElementById('rcomp').innerHTML=obj.rcomp;
		document.getElementById('nfuel').innerHTML=obj.nfuel;
		document.getElementById('nwater').innerHTML=obj.nwater;
		document.getElementById('ncomp').innerHTML=obj.ncomp;
		document.getElementById('jump').innerHTML=obj.jfuel;
		document.getElementById('spec').innerHTML=obj.spec;
		document.getElementById('unic').innerHTML=obj.unic;
		document.getElementById('id_ship').value=obj.id_s;
		document.getElementById('brake').value=obj.butt_z;
		document.getElementById('project').innerHTML=obj.proj_c;
		document.getElementById('del').style.display='none';
		document.getElementById('razob').style.display='none';
		document.getElementById('peres').style.display='none';
		document.getElementById('ch_fl').style.display='none';
		document.getElementById('arest').style.display='none';
		document.getElementById('new_fl').style.display='none';
		document.getElementById('brake').style.display='none';
		document.getElementById('ch_vlad').style.display='none';

		if (Number.isInteger(",$access/1000,") && 0!=",$access," && (obj.id_f==",$fleet,")){
			document.getElementById('arest').style.display='inline-block';
			document.getElementById('del').style.display='inline-block';
		}
		if (Number.isInteger(",($access-1)/1000,") && (obj.id_f==",$fleet,")){
			document.getElementById('arest').style.display='inline-block';
		}
		if ((obj.id_u==",$my," && ",$my,"!=0) || (((Number.isInteger(",$access/1000,") && 0!=",$access,") || (Number.isInteger(",($access-1)/1000,"))) && (obj.id_f==",$fleet," && obj.id_f==obj.id_u))){
/*id='del' id='razob' id='peres' id='ch_vlad' id='ch_fl' id='new_fl' id='brake' id='arest'*/
		        if (obj.id_f==",$fleet,"){
			document.getElementById('razob').style.display='inline-block';
			document.getElementById('peres').style.display='inline-block';
			}
			document.getElementById('ch_vlad').style.display='inline-block';
			document.getElementById('ch_fl').style.display='inline-block';
			document.getElementById('new_fl').style.display='inline-block';
			document.getElementById('brake').style.display='inline-block';
		}
		if (obj.id_f!=",$fleet," && obj.edit==0){
			document.getElementById('del').style.display='none';
			document.getElementById('razob').style.display='none';
			document.getElementById('peres').style.display='none';
			document.getElementById('ch_fl').style.display='none';
			document.getElementById('arest').style.display='none';
			document.getElementById('new_fl').style.display='none';
			document.getElementById('brake').style.display='none';
			document.getElementById('ch_vlad').style.display='none';
		}
	return false;
}

</script>
</body>
</html>";
} else {
header('Location: users/index.php');
}
?>