<?php
session_start();
include_once('modul/connect.php');
include_once('modul/funct.php');
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
<title>УПРАВЛЕНИЕ ПРОЕКТАМИ</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script type="text/javascript" src="js/jquery.min.js"></script>
<link rel="stylesheet" href="css/base1.css">
<link rel="stylesheet" href="css/m_proj.css">
</head>
<body>
<div id="myModal" class="modal"><div class="modal-content" id="info"></div></div>
<header>УПРАВЛЕНИЕ ПРОЕКТАМИ</header>
<div class="container">
<div class="main-content">
<a href="users/index.php"><div id="logoff"><img src="img/power_red.png"></div></a>
<div class="content-wrap">
<figure id="karusel">';
echo $head;
if (isset($_SESSION['user_id'])){$fleet=$_SESSION['user_id'];$my=0;$dolj=0;}
if (isset($data_fleet['id_f'])){$fleet=$data_fleet['id_f'];$my=$_COOKIE['user'];$dolj=$data_fleet['dolj'];}
$q_proj=$pdo->prepare("SELECT distinct project.id as id,project.id_f as fleet, project.nazv as nazv, project.init as init, t1.name as init_n, 
project.rukov as rukov, t2.name as rukov_n, project.lobby as lobby, t3.name as lobby_n, project.type as `type`, project.flag AS `status`, project.vlast AS vlast, project.ship AS ship
FROM project 
left join users t3 on project.lobby=t3.id 
left join users t2 on project.rukov=t2.id 
left JOIN users t1 ON project.init=t1.id WHERE project.id_f=? ORDER BY project.flag DESC LIMIT 1");
$q_proj->execute([$fleet]);
$cur_proj=$q_proj->fetch();
echo "<a href=# style='display:block;' title='Листать вперед' onclick='list(",$fleet,",1,",$cur_proj['id'],",0);return false;'><div id='rarrow'></div></a>
<img src='img/proj/",$cur_proj['type'],".png'>
<a href=# style='display:block;' title='Листать назад' onclick='list(",$fleet,",2,",$cur_proj['id'],",0);return false;'><div id='larrow'></div></a>
</figure>";
if ($my<>0){
//	$q_vlad=$pdo->prepare("");
//	$q_vlad->execute(array($fleet,$my));
} else {
//	$q_vlad=$pdo->prepare("SELECT distinct ships.id as id,users.name as u_name,ships.user as u_id, ships.fleet as fleet
//FROM ships
//LEFT JOIN users ON ships.user=users.id
//WHERE ships.fleet=? GROUP BY users.id ORDER BY users.name");
//	$q_vlad->execute([$fleet]);
}
echo "<nav>
<form>
<select id='init' name='init' onchange='projchange(this,1,",$fleet,");'><option disabled selected>Инициатор</option>";
	$q_vlad=$pdo->prepare("SELECT distinct project.id as id,project.init as init, t1.name as init_name,project.nazv as nazv, project.id_f as fleet
FROM project
LEFT JOIN users t1 ON project.init=t1.id
WHERE project.id_f=? GROUP BY project.init ORDER BY t1.name");
	$q_vlad->execute([$fleet]);

while ($vlad=$q_vlad->fetch()){
	echo "<option value='",$vlad['init'],"'>",$vlad['init_name'],"</option>";
}
echo "</select>
<select id='rukov' name='rukov' onchange='projchange(this,2,",$fleet,");'><option disabled selected>Руководитель</option>";
$q_sizz=$pdo->prepare("SELECT distinct project.id as id, project.rukov as rukov, t1.name as rukov_name,project.nazv as nazv, project.id_f as fleet
FROM project
LEFT JOIN users t1 ON project.rukov=t1.id
WHERE project.id_f=? GROUP BY project.rukov ORDER BY t1.name");
$q_sizz->execute([$fleet]);
while ($sizz=$q_sizz->fetch()){
	echo "<option value='",$sizz['rukov'],"'>",$sizz['rukov_name'],"</option>";
}
echo "</select>
<select id='type' name='type' onchange='projchange(this,3,",$fleet,");'><option disabled selected>Тип</option>";
$q_purp=$pdo->prepare("SELECT distinct project.id as id, project.`type` as `type`,project.nazv as nazv, project.id_f as fleet
FROM project
WHERE project.id_f=? GROUP BY project.`type` ORDER BY project.`type`");
$q_purp->execute([$fleet]);
while ($purp=$q_purp->fetch()){
	if ($purp['type']==0) {echo "<option value='",$purp['type'],"'>Социальный</option>";}
	if ($purp['type']==1) {echo "<option value='",$purp['type'],"'>Инженерный</option>";}
	if ($purp['type']==2) {echo "<option value='",$purp['type'],"'>Биомедицинский</option>";}
	if ($purp['type']==3) {echo "<option value='",$purp['type'],"'>Научно-технический</option>";}
}
echo "</select>
<select id='status' name='status' onchange='projchange(this,4,",$fleet,");'><option disabled selected>Статус</option>";
$q_flag=$pdo->prepare("select distinct project.id as id, project.flag as `status`, project.nazv as nazv, project.id_f as fleet
FROM project
WHERE project.id_f=? GROUP BY project.flag ORDER BY project.flag");
$q_flag->execute([$fleet]);
while ($flag=$q_flag->fetch()){
	echo "<option value='",$flag['status'],"'>",status($flag['status']),"</option>";
}
echo "</select>
</form>
</nav>
<main><div class='wrap' id='proj'></div></main>
</div>
<aside>
<div id='detail'>
<div style='margin-bottom:30px'>
<p><span id='nazv' style='font-size:150%;'></span></p>
<p>ФЛОТ: <span id='fleet'></span></p>
<p>ИНИЦИАТОР: <span id='ini'></span></p>
<p>РУКОВОДИТЕЛЬ: <span id='ruk'></span></p>
<p>ПОДДЕРЖКА: <span id='lob'></span></p>
<p>СОГЛАСОВАНИЕ: <span id='vlas'></span></p>
</div>
<div style='margin-bottom:30px'>
<p>СТАТУС: <span id='stat'></span></p>
<p id='timer'>ВРЕМЯ ОКОНЧАНИЯ: <span id='time'></span></p>
<p>ТИП: <span id='typ'></span></p>
<p>КРАТКОЕ ОПИСАНИЕ: <span id='desc'></span></p>
<p>ОЖИДАЕМЫЙ РЕЗУЛЬТАТ: <span id='f_res'></span></p>
<p id='a_res'>ПОЛУЧЕННЫЙ РЕЗУЛЬТАТ: <span id='res'></span></p>
<p>КОРАБЛЬ: <span id='ship'></span></p>
<p id='a_cost'>СТОИМОСТЬ: <img src='img/tilium.gif' style='width:15px;height:auto;'> <span id='fuel'></span>,&nbsp;&nbsp; <img src='img/water.gif' style='width:15px;height:auto;'> <span id='water'></span>,&nbsp;&nbsp; <img src='img/detals.gif' style='width:15px;height:auto;'> <span id='comp'></span></p>
<p id='a_cost_u'>УНИК КОМПОНЕНТЫ: <span id='spec'></span></p>
</div>
</div>
<div id='tabl'>
<form>
<input type='hidden' name='project' id='project' value=''>
<input type='submit' class='but' name='new' id='new' value='НОВЫЙ ПРОЕКТ' onclick='fire(this);return false;'> 
<input type='submit' class='but' name='n_ruk' id='n_ruk' value='СТАТЬ РУКОВОДИТЕЛЕМ' onclick='fire(this);return false;'> 
<input type='submit' class='but oran' name='c_ruk' id='c_ruk' value='ЗАМЕНИТЬ РУКОВОДИТЕЛЯ' onclick='fire(this);return false;'> 
<input type='submit' class='but' name='n_lob' id='n_lob' value='ПОДДЕРЖАТЬ' onclick='fire(this);return false;'> 
<input type='submit' class='but oran' name='c_lob' id='c_lob' value='ВЫСТУПИТЬ В ПОДДЕРЖКУ' onclick='fire(this);return false;'> 
<input type='submit' class='but' name='ok' id='ok' value='УТВЕРДИТЬ' onclick='fire(this);return false;'>
<input type='submit' class='but red' name='no' id='no' value='ОТКАЗАТЬ' onclick='fire(this);return false;'>
<input type='submit' class='but oran' name='start' id='start' value='НАЧАТЬ ПРОЕКТ' onclick='fire(this);return false;'> 
<input type='submit' class='but oran' name='start1' id='start1' value='ВОЗОБНОВИТЬ ПРОЕКТ' onclick='fire(this);return false;'> 
<input type='submit' class='but red' name='stop' id='stop' value='ОСТАНОВИТЬ ПРОЕКТ' onclick='fire(this);return false;'> 
<input type='submit' class='but red' name='cancel' id='cancel' value='ЗАПРЕТИТЬ ПРОЕКТ' onclick='fire(this);return false;'> 
</form>
</div>
</aside>
</div>
</div>
<footer></footer>
<script>
window.onload = function(){";
if (isset($_GET['err'])){
	$err=urldecode($_GET['err']);
	echo "var err='<p>",$err,"</p>';";
	echo "$('#info').html(err);";
	echo "$('#myModal').fadeIn();";
}
echo "wproj(",$fleet,",";
if (isset($_GET['proj'])) {echo $_GET['proj'];} else {echo "0";}
echo ",0);
}
/*
var modal = document.getElementById('myModal');
window.onclick = function(event) {
    if (event.target == modal) {
        $('#myModal').fadeOut(250);
    }
}
*/
function fire(ids){
    act=ids.id;
    nam=ids.name;
    idship=document.getElementById('project').value;
    $.ajax({
       	 type: 'POST',
         url: 'modul/viewproj.php',
       	 data: {idship:idship,act:nam,fl:",$fleet,"},
         success: function(html){
       	     $('#info').html(html);	
         }
    });
    $('#myModal').fadeIn();	
}

function projchange(selec,filt,fleet){
var selectedOption = selec.options[selec.selectedIndex];
    $.ajax({
         type: 'POST',
         url: 'modul/viewproj.php',
         data: {id_filt:selectedOption.value,fil:filt,fl:fleet},
         success: function(html){
             $('#proj').html(html);	
         }
    });
    selec.selectedIndex=0;
    return false;
}

function wproj(fleet,id,sort){
        $.ajax({
            type: 'POST',
            url: 'modul/viewproj.php',
            data: {fleet:fleet,id:id},
            success: function(json) {
		var obj=JSON.parse(json);
		wiewproj(obj,sort);
	   }
       });
       return false;
}

function list(fleet,direct,id,sort){
        $.ajax({
            type: 'GET',
            url: 'modul/viewproj.php',
            data: {fleet:fleet,sort:sort,direct:direct,ids:id},
            success: function(json) {
		var obj=JSON.parse(json);
		wiewproj(obj,sort);
	   }
       });
       return false;
}

function wiewproj(obj,sort){
		document.getElementById('karusel').innerHTML='<a href=# style=\'display:block;\' title=\'Листать вперед\' onclick=\'list(",$fleet,",1,'+obj.id+','+sort+');return false;\'><div id=\'rarrow\'></div></a><img src=\'img/proj/'+obj.imag+'.png\'><a href=# style=\'display:block;\' title=\'Листать назад\' onclick=\'list(",$fleet,",2,'+obj.id+','+sort+');return false;\'><div id=\'larrow\'></div></a>';
		document.getElementById('fleet').innerHTML=obj.fleet;
		document.getElementById('project').value=obj.id;
		document.getElementById('nazv').innerHTML=obj.nazv;
		document.getElementById('ship').innerHTML=obj.nameship;
		document.getElementById('ini').innerHTML=obj.init_n;
		document.getElementById('ruk').innerHTML=obj.rukov_n;
		document.getElementById('lob').innerHTML=obj.lobby_n;
		document.getElementById('stat').innerHTML=obj.status;
		document.getElementById('typ').innerHTML=obj.type;
		document.getElementById('vlas').innerHTML=obj.vlast_n;
		document.getElementById('spec').innerHTML=obj.spec;
		document.getElementById('desc').innerHTML=obj.desc;
		document.getElementById('f_res').innerHTML=obj.f_res;
		document.getElementById('fuel').innerHTML=obj.fuel;
		document.getElementById('water').innerHTML=obj.water;
		document.getElementById('comp').innerHTML=obj.comp;
		document.getElementById('res').innerHTML=obj.res;

		document.getElementById('a_res').style.display='block';
		document.getElementById('a_cost').style.display='block';
		document.getElementById('a_cost_u').style.display='block';

		document.getElementById('n_ruk').style.display='none';
		document.getElementById('c_ruk').style.display='none';
		document.getElementById('n_lob').style.display='none';
		document.getElementById('c_lob').style.display='none';
		document.getElementById('ok').style.display='none';
		document.getElementById('no').style.display='none';
		document.getElementById('start').style.display='none';
		document.getElementById('start1').style.display='none';
		document.getElementById('stop').style.display='none';
		document.getElementById('cancel').style.display='none';
		var dolj=",$dolj,";
		if (obj.res==''||obj.res==null){		
			document.getElementById('a_res').style.display='none';
		}
		if (obj.fuel==0 && obj.water==0 && obj.comp==0){
			document.getElementById('a_cost').style.display='none';
		}
		if (obj.spec==''||obj.spec==null){
			document.getElementById('a_cost_u').style.display='none';
		}
		if (1004!=dolj){
			document.getElementById('new').style.display='inline-block';
		} else {
			document.getElementById('new').style.display='none';
		}
		if (obj.statu==0){
               		if (1004!=dolj){
				if (obj.rukov_n==''||obj.rukov_n==null) {
					document.getElementById('n_ruk').style.display='inline-block';
				} else {
					document.getElementById('c_ruk').style.display='inline-block';
				}
			}
			if ((obj.init!=",$my,") && 1004!=dolj){
				if (obj.lobby_n==''||obj.lobby_n==null)	{
					document.getElementById('n_lob').style.display='inline-block';
				} else {
					document.getElementById('c_lob').style.display='inline-block';
				}	
			}
			document.getElementById('a_res').style.display='none';
			document.getElementById('a_cost').style.display='none';
			document.getElementById('a_cost_u').style.display='none';
		}
		if (obj.statu==1){
               		if (1004!=dolj){
				if (obj.rukov_n==''||obj.rukov_n==null) {
					document.getElementById('n_ruk').style.display='inline-block';
				} else {
					document.getElementById('c_ruk').style.display='inline-block';
				}
			}
			if ((obj.init!=",$my,") && 1004!=dolj){
				if (obj.lobby_n==''||obj.lobby_n==null)	{
					document.getElementById('n_lob').style.display='inline-block';
				} else {
					document.getElementById('c_lob').style.display='inline-block';
				}	
			}
			document.getElementById('a_res').style.display='none';
		}
		if (obj.statu==2){
               		if (1004!=dolj){
				if (obj.rukov_n==''||obj.rukov_n==null) {
					document.getElementById('n_ruk').style.display='inline-block';
				} else {
					document.getElementById('c_ruk').style.display='inline-block';
				}
			}
			if ((obj.init!=",$my,") && 1004!=dolj){
				if (obj.lobby_n==''||obj.lobby_n==null)	{
					document.getElementById('n_lob').style.display='inline-block';
				} else {
					document.getElementById('c_lob').style.display='inline-block';
				}	
			}
			if (dolj==1004){
				document.getElementById('ok').style.display='inline-block';
				document.getElementById('no').style.display='inline-block';
				document.getElementById('cancel').style.display='inline-block';
			}
			document.getElementById('a_res').style.display='none';
		}
		if (obj.statu==3){
			if (dolj==1000||dolj==1001||dolj==2000||dolj==2001||dolj==3000||dolj==3001||dolj==4000||dolj==4001){
				document.getElementById('start').style.display='inline-block';
			}
			document.getElementById('a_res').style.display='none';
		}
		if (obj.statu==4){
			document.getElementById('a_res').style.display='none';
			document.getElementById('new').style.display='none';
			document.getElementById('timer').style.display='block';
			var timing=new Date(obj.timer*1000);
			document.getElementById('time').innerHTML=timing.toLocaleTimeString();
			if (dolj==1000||dolj==1001||dolj==2000||dolj==2001||dolj==3000||dolj==3001||dolj==4000||dolj==4001){
				document.getElementById('stop').style.display='inline-block';
			}
			if (dolj==1004||dolj==1000||dolj==1001||dolj==2000||dolj==2001||dolj==3000||dolj==3001||dolj==4000||dolj==4001){
				document.getElementById('cancel').style.display='inline-block';
			}

		} else {
			document.getElementById('timer').style.display='none';
		}
		if (obj.statu==5){
/*остановлен*/
			if (dolj==1000||dolj==1001||dolj==2000||dolj==2001||dolj==3000||dolj==3001||dolj==4000||dolj==4001){
				document.getElementById('start1').style.display='inline-block';
			}
			document.getElementById('new').style.display='none';
			document.getElementById('a_res').style.display='none';
               		if (1004!=dolj){
				if (obj.rukov_n==''||obj.rukov_n==null) {
					document.getElementById('n_ruk').style.display='inline-block';
				} else {
					document.getElementById('c_ruk').style.display='inline-block';
				}
			}
		}
		if (obj.statu==7){
/*запрещен*/
			document.getElementById('new').style.display='none';
			document.getElementById('a_res').style.display='none';
		}
		if (obj.statu==6){
/*окончен*/
			document.getElementById('a_res').style.display='block';
		}
		if (obj.statu==8){
/*отклонен*/
/*			if (dolj==1004){
					document.getElementById('ok').style.display='inline-block';
			} */
			document.getElementById('a_res').style.display='none';
			document.getElementById('new').style.display='none';
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