<?php
session_start();
if (isset($_SESSION['user_id'])){
  	include('../modul/connect.php');
//редактирование логин/пароль
	if (isset($_POST['save'])){
  		if (isset($_POST['id'])){
    			$id=(int)trim($_POST['id']);
  			if (trim($_POST['pass'])<>null and trim($_POST['pass'])<>''){
    				$passwod=md5(trim($_POST['pass']));
  			}
  			$name=trim($_POST['name']);
			$q_exists=$pdo->prepare("select who as idn, name from destination where name=? and who<>? union select id as idn, name from ships where name=? and id<>?");
			$q_exists->execute(array($name,$id,$name,$id));
			$nums=$q_exists->rowcount();
//надо проверить имя флота на совпадения с имеющимися и кораблями
			if ($id>1000) {
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
				if ($id>1000){
				        $idn=$id+1000;
					$updb1->execute(array($passwod,$idn));
				        $idn=$idn+1000;
					$updb1->execute(array($passwod,$idn));
				}
  			} 
			if (isset($_POST['sid'])) {
    				if ((int)$_POST['sid']==$id) {
					$updb2 = $pdo->prepare("UPDATE `destination` set `tim` = 0, `sid`= '' where `who`=?");
					$updb2->execute([$id]);
				}
  			}
			if ($nums==0 or ($id>1000 and $nums==2)) {
			if ($id<1000) {
			   $updb3=$pdo->prepare("UPDATE destination set name=?, locat=? WHERE who=?");
  			   $updb3->execute(array($name,$loc,$id));
			} else {
			   $updb3=$pdo->prepare("UPDATE destination set name=? WHERE who=?");
  			   $updb3->execute(array($name,$id));
  			   $id=$id+1000;
  			   $updb3->execute(array($name,$id));
			   $id=$id+1000;
  			   $updb3->execute(array($name,$id));
			}
			} else {
				$_SESSION['err']='Данное имя используется';
			}
  			header('Location: ../admin.php?log');
		}
	}
}
?>
