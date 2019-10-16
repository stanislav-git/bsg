<?php
include_once('connect.php');

$fleet=$_POST['fleet'];
$locat=$_POST['locat'];
$qresurs=$pdo->prepare("select anom.id,anom.resurs,anom.quality,scanning.who from anom JOIN scanning ON anom.id=scanning.id_ano WHERE anom.map=? AND scanning.who=? AND scanning.`level`>0");
$qresurs->execute(array($locat,$fleet));
$qships=$pdo->prepare("SELECT ships.id,typeship.dfuel,typeship.dwater, typeship.dcomp
FROM ships JOIN typeship ON ships.`type`=typeship.id
JOIN destination ON ships.fleet=destination.who
WHERE destination.locat=? AND destination.who=? ORDER BY typeship.dfuel desc");
$qships->execute(array($locat,$fleet));
echo "<span class='close'>&times;</span>";
while ($row = $qships->fetch()) {

}
echo '<script>
var span = document.getElementsByClassName("close")[0];
span.onclick = function() {
    modal.style.display = "none";
}
</script>';
?>