<?php
if (isset($_POST)){
	include_once('connect.php');
//and timnews+7200>unix_timestamp(NOW()) 
	$q_news=$pdo->prepare("SELECT * FROM news where (fleet=? or fleet=0) and timnews>(unix_timestamp(now())-10800) order by timnews DESC");
	$q_news->execute([$_POST['fleet']]);
	$answ=array();
	$i=0;
	while ($nnews=$q_news->fetch()){
		$answ[$i]['timnews']=date('d/m/\2\5\4\1 H:i',$nnews['timnews']);
		$answ[$i]['autor']=$nnews['autor'];
		$answ[$i]['text']=$nnews['news'];
//		$json_news[$i]="<div><span class='head'>".$nnews['timnews']."</span><p>".$nnews['news']."</p><span class='autor'>".$nnews['autor']."</span></div>";
		$i++;
	}
	echo json_encode($answ);
}
?>