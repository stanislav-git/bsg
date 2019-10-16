<?php
session_start();
if (isset($_SESSION['user_id'])){
	include_once('../modul/connect.php');
	if (isset($_POST['addfleet'])){
  		if (isset($_POST['ids']) and $_POST['ids']<100){
			$id_flad=(int)trim($_POST['ids']);
//  		        $ask_sh=$pdo->prepare("SELECT name FROM ships WHERE id=?");
//	                $ask_sh->execute([$id_flad]);
//			$myship = $ask_sh->fetchAll(PDO::FETCH_ASSOC);
				$flname=trim($_POST['flname']);
    				$sti = $pdo->prepare("INSERT INTO destination (who, name, locat, timer, tim_pre, jumping, map_dest, image, radimage) VALUES (?, ?, 1, 1980, 300, 0, 0, 'COLONIAL_FLEET', 'radar-col.gif')");
    				$sti->execute(array($id_flad,$flname));
		//Добавляем рапторы
	    			$flrap = $pdo->prepare("INSERT INTO destination (who, name, locat, timer, tim_pre, jumping, map_dest, image, radimage) VALUES (?, ?, 0, 300, 180, 0, 0, 'RAPTOR', 'radar-col.gif')");
    				$name_r='пилот '.$flname;
    				$id_r=$id_flad+100;
	    			$flrap->execute(array($id_r,$name_r));
   				$name_r='пилот '.$flname;
    				$id_r=$id_flad+200;
    				$flrap->execute(array($id_r,$name_r));
	    			$name_r='пилот '.$flname;
    				$id_r=$id_flad+300;
    				$flrap->execute(array($id_r,$name_r));
		//Устанавливаем кораблю номер флота
    				$upship=$pdo->prepare("UPDATE `ships` SET `fleet`=? WHERE  `id`=?");
	    			$upship->execute(array($id_flad,$id_flad));
    				header('Location: ../admin.php?fleet');
    		} else {
    			header('Location: ../admin.php?ships=1');
		}	
	}
	if (isset($_POST['delship'])){
		$ship=(int)trim($_POST['ids']);
		$del_sh=$pdo->prepare("DELETE FROM ships WHERE id=? LIMIT 1");
		$del_sh->execute([$ship]);
		header('Location: ../admin.php?ships');
	}
	if (isset($_POST['detail'])){
		$ship=trim($_POST['ids']);
		$desc=trim($_POST['descr']);
		$descparts=trim($_POST['descparts']);
		$spec=trim($_POST['spec']);
		$image=trim($_POST['imag']);
		$fimage='../img/ships/'.$image;
		if (!file_exists($fimage)) {
			$image='';
		}
		$ed_s=$pdo->prepare("UPDATE ships SET `image`=?, `descr`= ?,`descparts`= ?, `spec`= ? WHERE `id`= ?");
	        $ed_s->execute(array($image,$desc,$descparts,$spec,$ship));
		header('Location: ../admin.php?ships');
	}
	if (isset($_POST['add_ship'])) {
		$ship=trim($_POST['ship']);
		if (mb_strlen($ship,'UTF-8')>3){
			$class=$_POST['class'];
			$human=$_POST['human'];
			$add_s=$pdo->prepare("INSERT INTO ships (name,type,human,fleet) VALUES (?,?,?,'0')");
		        $add_s->execute(array($ship,$class,$human));
			header('Location: ../admin.php?ships');
		} else {
			header('Location: ../admin.php?ships=2');

		}
	}
//Редактируем корабли
        if (isset($_POST['savship'])){
		if (strlen(trim($_POST['flname']))>4) {
			if (!isset($_POST['giper'])) {$giper=1;} else {$giper=0;}
//			print_r($_POST);
        		$ship=$_POST['flname'];
	        	$ids=$_POST['ids'];
        		$class=$_POST['class'];
        		$human=$_POST['human'];
	        	$fleet=$_POST['fleet'];
			$ed_s=$pdo->prepare("UPDATE ships SET name= ?,type= ?, human= ?, fleet= ?,repair=? WHERE id= ?");
		        $ed_s->execute(array($ship,$class,$human,$fleet,$giper,$ids));
		} 
		header('Location: ../admin.php?ships');
        }
} else {
	header('../Location: testsess.php');
}
?>