<?php
//вставляется в код include
//незанятая мощность флота

echo "<div id='fleets'>Добыча ресурсов - Флот: ",ask_name($pos),"</div>";
echo "<div id='dfuel'><div class='but'><span id='sfuel'></span>/<span id='digfuel'></span></div><div class='titl'><hr><a href=# title='Отправить корабль на добычу ресурса' onclick='add_ship(1,1,0);'><img src='img/down.png'></a><b>ТИЛИУМ</b><a href=# title='Снять корабль с добычи ресурса' onclick='add_ship(1,0,0);'><img src='img/up.png'></a><a href=# title='Снять флот с добычи ресурса' onclick='add_ship(1,2,0);'><img src='img/d_up.png'></a><hr></div><div class='cont'><div id='rfuel' class='contt'></div></div></div>";
echo "<div id='dwater'><div class='but'><span id='swater'></span>/<span id='digwater'></span></div><div class='titl'><hr><a href=# title='Отправить корабль на добычу ресурса' onclick='add_ship(2,1,0);'><img src='img/down.png'></a><b>ВОДА</b><a href=# title='Снять корабль с добычи ресурса' onclick='add_ship(2,0,0);'><img src='img/up.png'></a><a href=# title='Снять флот с добычи ресурса' onclick='add_ship(2,2,0);'><img src='img/d_up.png'></a><hr></div><div class='cont'><div id='rwater' class='contt'></div></div></div>";
echo "<div id='dcomp'><div class='but'><span id='scomp'></span>/<span id='digcomp'></span></div><div class='titl'><hr><a href=# title='Отправить корабль на добычу ресурса' onclick='add_ship(3,1,0);'><img src='img/down.png'></a><b>ЗАПЧАСТИ</b><a href=# title='Снять корабль с добычи ресурса' onclick='add_ship(3,0,0);'><img src='img/up.png'></a><a href=# title='Снять флот с добычи ресурса' onclick='add_ship(3,2,0);'><img src='img/d_up.png'></a><hr></div><div class='cont'><div id='rcomp' class='contt'></div></div></div>";

echo '<script type="text/javascript">
$(document).ready(function(){
add_ship(0,3);
});
function add_ship(res,ad,ship) {
 var request=$.ajax({
     type: "GET",
     url: "modul/dig.php",
     data:{fleet:',$pos,', locat:',$cur_pos,', resurs:res, act:ad,ship:ship},
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
      rf=rf+"<tr><td style=\"text-align:right;width:45%;\">"+this.digg+"</td><td style=\"width:5%;\"><a href=# title=\'Снять корабль с добычи ресурса\' onclick=\'add_ship(1,0,"+this.ids +");\'><img src=\'img/up.png\' style=\'height:20px;width:auto;display:block;\'></a></td><td style=\"text-align:left;width:45%;\">"+this.name + "</td></tr>";
    });
    rf=rf+"</table>";	
    $("#rfuel").html(rf); 
    rw="<table style=\"width:100%;\">"; 
    $.each(objd.name_ship_water, function(){
      rw=rw+"<tr><td style=\"text-align:right;width:45%;\">"+this.digg+"</td><td style=\"width:5%;\"><a href=# title=\'Снять корабль с добычи ресурса\' onclick=\'add_ship(2,0,"+this.ids +");\'><img src=\'img/up.png\' style=\'height:20px;width:auto;display:block;\'></a></td><td style=\"text-align:left;width:45%;\">"+this.name + "</td></tr>";
    });
    rw=rw+"</table style=\"width:100%;\">";
    $("#rwater").html(rw); 
    rc="<table>"; 
    $.each(objd.name_ship_comp, function(){
	rc=rc+"<tr><td style=\"text-align:right;width:45%;\">"+this.digg+"</td><td style=\"width:5%;\"><a href=# title=\'Снять корабль с добычи ресурса\' onclick=\'add_ship(3,0,"+this.ids +");\'><img src=\'img/up.png\' style=\'height:20px;width:auto;display:block;\'></a></td><td style=\"text-align:left;width:45%;\">"+this.name + "</td></tr>";
    });
    rc=rc+"</table>";
    $("#rcomp").html(rc); 
 });
    return false;
}
</script>';
?>