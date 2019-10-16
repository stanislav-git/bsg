<?php
session_start();
if (isset($_SESSION['user_id'])){
  	include('../modul/connect.php');
//добавляем флот
	if (isset($_POST['save'])){
  		if (isset($_POST['id'])){
    			$id=(int)trim($_POST['id']);
  			if (trim($_POST['pass'])<>null and trim($_POST['pass'])<>''){
    				$passwod=md5(trim($_POST['pass']));
  			}
  			$name=trim($_POST['name']);
			if ($id>100) {
				$loc=0;
			} else {
	  			$loc=(int)trim($_POST['loc']);
  				if ($loc>0 and $loc<31 and is_numeric($loc)){

  				} else {
    					$loc=1;
  				}
			}
			if (isset($passwod)) {
				$updb1 = $pdo->prepare("UPDATE `destination` set `pass`=? where `who`=?");
				$updb1->execute(array($passwod,$id));
				if ($id>100){
				        $idn=$id+100;
					$updb1->execute(array($passwod,$idn));
				        $idn=$idn+100;
					$updb1->execute(array($passwod,$idn));
				}
  			} 
			if (isset($_POST['sid'])) {
    				if ($_POST['sid']==$id) {
					$updb2 = $pdo->prepare("UPDATE `destination` set `tim` = 0, `sid`= '' where `who`=?");
					$updb2->execute([$id]);
				}
  			}
			if ($id<100) {
			   $updb3=$pdo->prepare("UPDATE destination set name=?, locat=? WHERE who=?");
  			   $updb3->execute(array($name,$loc,$id));
			} else {
			   $updb3=$pdo->prepare("UPDATE destination set name=? WHERE who=?");
  			   $updb3->execute(array($name,$id));
  			   $id=$id+100;
  			   $updb3->execute(array($name,$id));
			   $id=$id+100;
  			   $updb3->execute(array($name,$id));
			}
  			header('Location: ../admin.php?log');
		}
	}
}
?>
