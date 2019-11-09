<?php
session_start();
if (isset($_SESSION['user_id'])) {
  include_once('../modul/connect.php');
//по кнопке сканирования
   if (isset($_POST['scan'])) {
      $pos=$_SESSION['user_id'];
	if ($pos<1000){	
                $fuelst=$pdo->prepare("UPDATE resurs SET `fuel`=`fuel`- 1 WHERE `id_f`= ?");
//писать ли в лог?
	} else {
                $fuelst=$pdo->prepare("UPDATE destination SET `fuel`=`fuel`- 1 WHERE `who`= ?");
	}
      $fuelst->execute([$pos]);
      $who=trim($_POST['scan']);
      $dest=trim($_POST['dest']);
      $stq = $pdo->prepare("SELECT count(id_ano) FROM scanning WHERE id_ano= ? and who= ?");
      $stm = $pdo->prepare("SELECT id FROM anom WHERE map=:id_map");
      $sti = $pdo->prepare("INSERT INTO scanning (`id_ano`, `who`,`level`) VALUES (?, ?, 1)");
      $stu = $pdo->prepare("UPDATE scanning SET `level`=1 WHERE  id_ano= ? AND who= ? and `level`<>2");
      $stm->bindValue(':id_map',$dest);
      $stm->execute();
      $anom = $stm->fetchAll();
      foreach ($anom as $id_ano) {
         $stq->execute(array($id_ano['id'],$who));
         $coun = $stq->fetchColumn();
         if ($coun==0) {
           //insert
           $sti->execute(array($id_ano['id'],$who));
         } else {
           $stu->execute(array($id_ano['id'],$who));
         }
      }
      header('Location: ../index.php');
   }

//вывод при попадании в сектор
   if (isset($_POST['scan1'])) {
      $who=trim($_POST['scan']);
      $dest=trim($_POST['dest']);
      $stq = $pdo->prepare("SELECT count(id_ano) FROM scanning WHERE id_ano= ? and who= ?");
      $stm = $pdo->prepare("SELECT id FROM anom WHERE map=:id_map");
      $stm->bindValue(':id_map',$dest);
      $stm->execute();
      $anom = $stm->fetchAll();
      foreach ($anom as $id_ano) {
         $stq->execute(array($id_ano['id'],$who));
         $coun = $stq->fetchColumn();
         if ($coun==0) {
           //insert
           $sti = $pdo->prepare("INSERT INTO scanning (`id_ano`, `who`) VALUES (?, ?)");
           $sti->execute(array($id_ano['id'],$who));
         }
      }
   }
} else {
     header('Location: ../testsess.php');
}
?>