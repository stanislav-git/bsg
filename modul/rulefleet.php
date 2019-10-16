<?php
$q_dig_sum_ships=$pdo->prepare("SELECT sum(typeship.dfuel) as sfuel,sum(typeship.dwater) as swater, sum(typeship.dcomp) as scomp
FROM ships
JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who LEFT JOIN dig ON ships.id=dig.ship
WHERE dig.id IS NULL AND  destination.locat=? AND destination.who=?");
$q_dig_sum_ships->execute(array($cur_pos,$pos));
$dig_power=$q_dig_sum_ships->fetch();
echo "<div id='fleets'><h2>Добыча ресурсов - Флот: ",ask_name($pos,$pdo),"</h2>
<p>Тилиум: ",$dig_power['sfuel']," | Вода: ",$dig_power['swater']," | Запчасти: ",$dig_power['scomp'],"</p></div>";
echo "<div id='dfuel'><div class='but'><img src='img/down.png'> <img src='img/up.png'></div><div class='titl'>ТИЛИУМ</div><div class='cont'><div class='contt'>шахтер 1<br>шахтер 2</div></div></div>";
echo "<div id='dwater'><div class='but'><img src='img/down.png'> <img src='img/up.png'></div><div class='titl'>ВОДА</div><div class='cont'><div class='contt'>шахтер 1<br>шахтер 2<br>шахтер 1<br>шахтер 2</div></div></div>";
echo "<div id='dcomp'><div class='but'><img src='img/down.png'> <img src='img/up.png'></div><div class='titl'>МЕТАЛЛ</div><div class='cont'><div class='contt'></div></div></div>";
echo '<script type="text/javascript">
function add_ship() {
    $.ajax({
            type: "POST",
            url: "dig.php",
	    data:{fleet:',$pos,',locat:',$cur_pos,'},
            success: function(html){
                $("#fleetdig").html(html);
            }
    });
    return false;
}
</script>';
?>