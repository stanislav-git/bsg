<?php
session_start();
if (isset($_SESSION['user_id'])){
  	include('connect.php');
//добавляем флот
	if (isset($_POST['save'])){
  		if (isset($_POST['id'])){
    			$id=(int)trim($_POST['id']);
  			if (trim($_POST['pass'])<>null and trim($_POST['pass'])<>''){
    				$passwod=md5(trim($_POST['pass']));
  			}
  			$name=trim($_POST['name']);
  			$loc=(int)trim($_POST['loc']);
			if ($id>100) {
				if (is_numeric($loc) and (0<=$loc) and ($loc<=30)) {
				} else {
					$loc=0;
				}
			} else {
  				if ($loc>0 and $loc<31 and is_numeric($loc)){

  				} else {
    					$loc=1;
  				}
			}
			if (isset($passwod)) {
				$updb1 = $pdo->prepare("UPDATE `destination` set `pass`=? where `who`=?");
				$updb1->execute(array($passwod,$id));
  			} 
			if (isset($_POST['sid'])) {
    				if ($_POST['sid']==$id) {
					$updb2 = $pdo->prepare("UPDATE `destination` set `tim` = 0, `sid`= '' where `who`=?");
					$updb2->execute([$id]);
				}
  			}
			$updb3=$pdo->prepare("UPDATE destination set name=?, locat=? WHERE who=?");
  			$updb3->execute(array($name,$loc,$id));
  			header('Location: admin.php?log');
		}
	}
}
?>
