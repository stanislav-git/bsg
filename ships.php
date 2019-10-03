<?php
session_start();
if (isset($_SESSION['user_id'])){
	include('connect.php');
	if (isset($_POST['add_ship'])) {
		$ship=$_POST['ship'];
		$class=$_POST['class'];
		$power=$_POST['power'];
		$add_s=$pdo->prepare("INSERT INTO ships (name,type,fuel,fleet) VALUES (?,?,?,'0')");
	        $add_s->execute(array($ship,$class,$power));
		header('Location: admin.php?ships');
	}
//Редактируем корабли
//Array ( [ids] => 6 [flname] => Новый мир [class] => Огромный [fleet] => 0 [power] => 5 [savship] => Сохранить )
        if (isset($_POST['savship'])){
		if (!isset($_POST['giper'])) {$giper=1;} else {$giper=0;}
        	$ship=$_POST['flname'];
        	$ids=$_POST['ids'];
        	$class=$_POST['class'];
        	$power=$_POST['power'];
        	$fleet=$_POST['fleet'];
		$ed_s=$pdo->prepare("UPDATE ships SET name= ?,type= ?, fuel= ?, fleet= ?,repair=? WHERE id= ?");
	        $ed_s->execute(array($ship,$class,$power,$fleet,$giper,$ids));
		header('Location: admin.php?ships');
        }
}
print_r($_POST);
?>