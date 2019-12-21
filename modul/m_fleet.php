<?php
include_once('connect.php');
include_once('funct.php');
$fleet=$_POST['fleet'];
if ($_POST['stat']==0){
	$q_loc=$pdo->prepare("select locat from destination where who=?");
	$q_loc->execute([$fleet]);
	$cur_pos=$q_loc->fetchColumn();

//незанятая мощность флота
	echo "<span class='close'>&times;</span>";
	echo "<div id='fleets'>Добыча ресурсов - Флот: ",ask_name($fleet),"</div>";
	echo "<div id='dfuel'><div class='but'><span id='sfuel'></span>/<span id='digfuel'></span></div><div class='titl'><hr><a href=# title='Отправить корабль на добычу ресурса' onclick='add_ship(1,1,0);'><img src='img/down.png'  style='height: 20px;width: auto;'></a> <b>ТИЛИУМ</b> <a href=# title='Снять флот с добычи ресурса' onclick='add_ship(1,2,0);'><img src='img/d_up.png' style='height: 20px;width: auto;'></a><hr></div><div class='cont'><div id='rfuel' class='contt'></div></div></div>";
	echo "<div id='dwater'><div class='but'><span id='swater'></span>/<span id='digwater'></span></div><div class='titl'><hr><a href=# title='Отправить корабль на добычу ресурса' onclick='add_ship(2,1,0);'><img src='img/down.png' style='height: 20px;width: auto;'></a> <b>ВОДА</b> <a href=# title='Снять флот с добычи ресурса' onclick='add_ship(2,2,0);'><img src='img/d_up.png' style='height: 20px;width: auto;'></a><hr></div><div class='cont'><div id='rwater' class='contt'></div></div></div>";
	echo "<div id='dcomp'><div class='but'><span id='scomp'></span>/<span id='digcomp'></span></div><div class='titl'><hr><a href=# title='Отправить корабль на добычу ресурса' onclick='add_ship(3,1,0);'><img src='img/down.png' style='height: 20px;width: auto;'></a> <b>ЗАПЧАСТИ</b> <a href=# title='Снять флот с добычи ресурса' onclick='add_ship(3,2,0);'><img src='img/d_up.png' style='height: 20px;width: auto;'></a><hr></div><div class='cont'><div id='rcomp' class='contt'></div></div></div>";

	echo '<script type="text/javascript">
$(document).ready(function(){
add_ship(0,3);
});
var span = document.getElementsByClassName("close")[0];
span.onclick = function() {
      modal.style.display = "none";
	d_ship(1);
}
function add_ship(res,ad,ship) {
 var request=$.ajax({
     type: "GET",
     url: "modul/dig.php",
     data:{fleet:',$fleet,', locat:',$cur_pos,', resurs:res, act:ad,ship:ship},
     dataType:"html"
 });
 request.done(function(msg){
    $("#rfuel").html("");
    $("#rwater").html("");
    $("#rcomp").html("");
    $("#digfuel").html("");
    $("#digwater").html("");
    $("#digcomp").html("");
    var objd=JSON.parse(msg);
    if (objd.widt.fuel==0) {document.getElementById("dfuel").style.display="none";} else {document.getElementById("dfuel").style.display="block";}
    if (objd.widt.water==0) {document.getElementById("dwater").style.display="none";} else {document.getElementById("dwater").style.display="block";}
    if (objd.widt.comp==0) {document.getElementById("dcomp").style.display="none";} else {document.getElementById("dcomp").style.display="block";}
    document.getElementById("dfuel").style.width = "calc("+objd.widt.fuel+"% - 10px)";
    document.getElementById("dwater").style.width = "calc("+objd.widt.water+"% - 10px)";
    document.getElementById("dcomp").style.width = "calc("+objd.widt.comp+"% - 10px)";
    if (objd.res_fuel==null) {objd.res_fuel=0;}
    if (objd.res_water==null) {objd.res_water=0;}
    if (objd.res_comp==null) {objd.res_comp=0;}
    $("#sfuel").html(objd.res_fuel);
    $("#swater").html(objd.res_water);
    $("#scomp").html(objd.res_comp);
    $("#digfuel").html(objd.d_fuel);
    $("#digwater").html(objd.d_water);
    $("#digcomp").html(objd.d_comp);
    var rf="<table style=\"width:100%;\">";
    $.each(objd.name_ship_fuel, function(){
      rf=rf+"<tr><td>"+this.digg+"</td><td><a href=# title=\'Снять корабль с добычи ресурса\' onclick=\'add_ship(1,0,"+this.ids +");\'><img src=\'img/up.png\' style=\'height:20px;width:auto;display:block;\'></a></td><td style=\"text-align:left;\">"+this.name + "</td></tr>";
    });
    rf=rf+"</table>";	
    $("#rfuel").html(rf); 
    rw="<table style=\"width:100%;\">"; 
    $.each(objd.name_ship_water, function(){
      rw=rw+"<tr><td>"+this.digg+"</td><td><a href=# title=\'Снять корабль с добычи ресурса\' onclick=\'add_ship(2,0,"+this.ids +");\'><img src=\'img/up.png\' style=\'height:20px;width:auto;display:block;\'></a></td><td style=\"text-align:left;\">"+this.name + "</td></tr>";
    });
    rw=rw+"</table style=\"width:100%;\">";
    $("#rwater").html(rw); 
    rc="<table>"; 
    $.each(objd.name_ship_comp, function(){
	rc=rc+"<tr><td>"+this.digg+"</td><td><a href=# title=\'Снять корабль с добычи ресурса\' onclick=\'add_ship(3,0,"+this.ids +");\'><img src=\'img/up.png\' style=\'height:20px;width:auto;display:block;\'></a></td><td style=\"text-align:left;\">"+this.name + "</td></tr>";
    });
    rc=rc+"</table>";
    $("#rcomp").html(rc); 
 });
    return false;
}
</script>';
}
if ($_POST['stat']==1){
	echo "<span class='close'>&times;</span>";
	echo "<div id='fleets'>Состояние резерва - Флот: ",ask_name($fleet),"</div>";
//	$qw=$pdo->prepare("select min(timer) as m_tim, max(timer) as b_tim from hist_resurs where id_f=?");
//	$qhf=$pdo->prepare("select min(fuel) as m_fuel, max(fuel) as b_fuel from hist_resurs where id_f=?");
//	$qhw=$pdo->prepare("select min(water) as m_water, max(water) as b_water from hist_resurs where id_f=?");
//	$qhc=$pdo->prepare("select min(comp) as m_comp, max(comp) as b_comp from hist_resurs where id_f=?");
	echo "<div style='overflow-y: auto;position: absolute;top: 60px;left: 0px;bottom: 0px;right: 0px;'>";
	$q_data=$pdo->prepare("select timer,fuel,water,comp,descr from hist_resurs where id_f=? and timer>(unix_timestamp(now())-7200) ORDER BY timer DESC");
	$q_data->execute([$fleet]);
	echo "<table border=0>";
	while($data=$q_data->fetch()){
		echo "<tr><td style='text-align:right;'>",date('d/m/\2\5\4\1 H:i:s',$data['timer']),"</td><td>",$data['fuel'],"</td><td>",$data['water'],"</td><td>",$data['comp'],"</td></tr>";
		echo "<tr><td></td><td colspan=3 style='text-align:left;'>",$data['descr'],"</td></tr>";
	}
	echo "</table></div>";
	echo '<script type="text/javascript">
var span = document.getElementsByClassName("close")[0];
span.onclick = function() {
      modal.style.display = "none";
      d_ship(1);
}
</script>';
}
if ($_POST['stat']==2){
	echo "<span class='close'>&times;</span>";
	echo "<div id='fleets'>События во флоте ",ask_name($fleet),"</div>";
       	$qnews=$pdo->prepare("SELECT * from news where fleet=0 or fleet=? order by timnews DESC");
	$qnews->execute([$fleet]);
//	echo "<div><img src='img/power_red.png' alt='' / style='width:100%;'></div>";
	echo "<div id='cnews' style='overflow-y: auto;position: absolute;top: 60px;left: 0px;bottom: 0px;right: 0px;'>";
	while ($news = $qnews->fetch()) {
		$ndate=''.date('d/m/\2\5\4\1 H:i',$news['timnews']);
		echo "<div><span class='head'>",$ndate,"</span><p>",$news['news'];
		echo "</p><span class='autor'>",$news['autor'],"</span></div>";
	}
	echo "</div>";

	echo '<script type="text/javascript">
var span = document.getElementsByClassName("close")[0];
span.onclick = function() {
      modal.style.display = "none";
      d_ship(1);
}
</script>';
}
?>