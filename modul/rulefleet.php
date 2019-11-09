<?php
//вставляется в код include
//незанятая мощность флота
$q_dig_sum_ships=$pdo->prepare("SELECT sum(typeship.dfuel) as sfuel, sum(typeship.dwater) as swater, sum(typeship.dcomp) as scomp
FROM ships JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who LEFT JOIN dig ON ships.id=dig.ship
WHERE dig.ship IS NULL AND  destination.locat=? AND destination.who=? and ships.repair=0");
$q_dig_sum_ships->execute(array($cur_pos,$pos));
$dig_power=$q_dig_sum_ships->fetch();

echo "<div id='fleets'>Добыча ресурсов - Флот: ",ask_name($pos),"</div>";
echo "<div id='dfuel'><div class='but'><span id='sfuel'>",$dig_power['sfuel'],"</span>/<span id='digfuel'></span></div><div class='titl'><hr><a href=# title='Отправить корабль на добычу ресурса' onclick='add_ship(1,1);'><img src='img/down.png'></a><b>ТИЛИУМ</b><a href=# title='Снять корабль с добычи ресурса' onclick='add_ship(1,0);'><img src='img/up.png'></a><a href=# title='Снять флот с добычи ресурса' onclick='add_ship(1,2);'><img src='img/d_up.png'></a><hr></div><div class='cont'><div id='rfuel' class='contt'></div></div></div>";
echo "<div id='dwater'><div class='but'><span id='swater'>",$dig_power['swater'],"</span>/<span id='digwater'></span></div><div class='titl'><hr><a href=# title='Отправить корабль на добычу ресурса' onclick='add_ship(2,1);'><img src='img/down.png'></a><b>ВОДА</b><a href=# title='Снять корабль с добычи ресурса' onclick='add_ship(2,0);'><img src='img/up.png'></a><a href=# title='Снять флот с добычи ресурса' onclick='add_ship(2,2);'><img src='img/d_up.png'></a><hr></div><div class='cont'><div id='rwater' class='contt'></div></div></div>";
echo "<div id='dcomp'><div class='but'><span id='scomp'>",$dig_power['scomp'],"</span>/<span id='digcomp'></span></div><div class='titl'><hr><a href=# title='Отправить корабль на добычу ресурса' onclick='add_ship(3,1);'><img src='img/down.png'></a><b>ЗАПЧАСТИ</b><a href=# title='Снять корабль с добычи ресурса' onclick='add_ship(3,0);'><img src='img/up.png'></a><a href=# title='Снять флот с добычи ресурса' onclick='add_ship(3,2);'><img src='img/d_up.png'></a><hr></div><div class='cont'><div id='rcomp' class='contt'></div></div></div>";

echo '<script type="text/javascript">
function add_ship(res,ad) {
 var request=$.ajax({
     type: "GET",
     url: "modul/dig.php",
     data:{fleet:',$pos,', locat:',$cur_pos,', resurs:res, act:ad},
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
    $("#sfuel").html(objd.res_fuel);
    $("#swater").html(objd.res_water);
    $("#scomp").html(objd.res_comp);
    $("#digfuel").html(objd.d_fuel);
    $("#digwater").html(objd.d_water);
    $("#digcomp").html(objd.d_comp);
    $.each(objd.name_ship_fuel, function(){
      $("#rfuel").append(this.name + "<br>");
    });
    $.each(objd.name_ship_water, function(){
      $("#rwater").append(this.name + "<br>");
    });
    $.each(objd.name_ship_comp, function(){
      $("#rcomp").append(this.name + "<br>");
    });
 });
    return false;
}
</script>';
?>