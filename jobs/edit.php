<?php
session_start();
if (isset($_SESSION['user_id'])){
  include('../modul/connect.php');
//добавляем аномалию
if (isset($_POST['i0'])){
      $maps=trim($_POST['m0']);
      $resurs=$_POST['resurs'];
      $quality=$_POST['quality'];
      if ($resurs==0){$quality=0;}
      $anomaly=trim($_POST['t0']);
      $scanned=trim($_POST['s0']);
      $sti = $pdo->prepare("INSERT INTO anom (`anomaly`, `scanned`,`map`,`resurs`,`quality`) VALUES (?, ?, ?, ?, ?)");
      $sti->execute(array($anomaly,$scanned,$maps,$resurs,$quality));
      header('Location: ../admin.php?maps='.$maps);
}
//удавляем аномалию
if (isset($_POST['del'])){
  $maps=trim($_POST['m0']);
  $id_ano=trim($_POST['id_ano']);
  $sti = $pdo->prepare("DELETE FROM anom WHERE id= ?");
  $sti->execute([$id_ano]);
  $rr=$sti->rowCount();
  if ($rr==1){
      header('Location: ../admin.php?maps='.$maps);
  }
}
//редактируем аномалию
if (isset($_POST['save'])){
  $maps=trim($_POST['m0']);
  $id_ano=trim($_POST['id_ano']);
  $anomaly=trim($_POST['text']);
  $scanned=trim($_POST['scan']);
  $resurs=$_POST['resurs'];
  $quality=$_POST['quality'];
  if ($resurs==0){$quality=0;}
  $updb = $pdo->prepare("UPDATE `anom` set `anomaly` = :anomaly, `scanned` = :scanned, `resurs`=:resurs,`quality`=:quality where `id`=:id_ano");
  $updb->bindParam(':anomaly', $anomaly);
  $updb->bindParam(':scanned', $scanned);
  $updb->bindParam(':id_ano', $id_ano);
  $updb->bindParam(':resurs', $resurs);
  $updb->bindParam(':quality', $quality);
  $updb->execute();
  header('Location: ../admin.php?maps='.$maps);
}
} else {
  header('Location: ../testsess.php');
}
?>