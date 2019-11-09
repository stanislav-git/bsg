<?php
if (isset($_POST['size']) and isset($_POST['purp'])) {
	include_once('connect.php');
	$size=$_POST['size'];
	$purp=$_POST['purp'];
	$q_type_purp=$pdo->prepare("select * from typeship where sizz=? and purp=? LIMIT 1");
        $q_type_purp->execute(array($size,$purp));
        $tp=$q_type_purp->fetch();
	echo "<form action='jobs/typeship.php' method='post'><table class='typeship'><tr><td><b>Размер</b></td><td><b>Назначение</b></td><td>Прыжок</td></tr>";
	echo "<tr><td style='width:30%;'>",$tp['cargo'],"</td>";
	echo "<td style='width:30%;'><input type='hidden' name='idt' value='",$tp['id'],"'><input type='text' name='type' value='",$tp['type'],"'></td>";
	echo "<td style='width:30%;'><img src='img/tilium.gif'>:<input type='number' min='0' max='10000' name='jump' value='",$tp['jfuel'],"'></td><td rowspan=8><input type='submit' name='sav_type' value='Сохранить'></tr>";
	echo "<tr><td>расход тилиума</td><td>расход воды</td><td>расход запчастей</td></tr>";
	echo "<td><img src='img/tilium.gif'>:<input type='number' min='0' max='10000' name='rfuel' value='",$tp['rfuel'],"'></td>";
	echo "<td><img src='img/water.gif'>:<input type='number' min='0' max='10000' name='rwater' value='",$tp['rwater'],"'></td>";
	echo "<td><img src='img/detals.gif'>:<input type='number' min='0' max='10000' name='rcomp' value='",$tp['rcomp'],"'></td>";
	echo "<tr><td>добыча тилиума</td><td>добыча воды</td><td>добыча запчастей</td></tr>";
	echo "<tr><td><img src='img/tilium.gif'>:<input type='number' min='0' max='10000' name='dfuel' value='",$tp['dfuel'],"'></td>";
	echo "<td><img src='img/water.gif'>:<input type='number' min='0' max='10000' name='dwater' value='",$tp['dwater'],"'></td>";
	echo "<td><img src='img/detals.gif'>:<input type='number' min='0' max='10000' name='dcomp' value='",$tp['dcomp'],"'></td></tr>";
	echo "<tr><td colspan=3 align='center'><b>Возврат ресурсов при разборе корабля</b></td></tr>";
	echo "<td><img src='img/tilium.gif'>:<input type='number' min='0' max='10000' name='nfuel' value='",$tp['nfuel'],"'></td>";
	echo "<td><img src='img/water.gif'>:<input type='number' min='0' max='10000' name='nwater' value='",$tp['nwater'],"'></td>";
	echo "<td><img src='img/detals.gif'>:<input type='number' min='0' max='10000' name='ncomp' value='",$tp['ncomp'],"'></td></tr>";
	echo "</table></form>";
}	
?>