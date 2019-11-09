<?php
if (isset($_POST)){
	include_once('connect.php');
	include_once('funct.php');
	$select_pos=$_POST['select_pos'];
	$current_pos=$_POST['current_pos'];
	$myid=$_POST['myid'];
	$shiphere=$pdo->prepare("SELECT destination.who as who1,destination.`name` as name1, COUNT(ships.id) as cfl
FROM destination
LEFT JOIN ships ON destination.who=ships.fleet
WHERE destination.who <> ? AND destination.locat = ?
GROUP BY destination.who");
	$shiphere->execute(array($myid,$current_pos));
	$ship_data = $shiphere->fetchAll(PDO::FETCH_ASSOC);
	$num_ship=count($ship_data);
	$cart = array("count" => $num_ship,
"current_pos" => $current_pos,
"select_pos" => $select_pos,
"fleets" =>array ());
	if ($num_ship>0){
  		$jc=$num_ship;
  		foreach ($ship_data as $sd) {
    			if ($sd['who1']<1000){
				$cart["fleets"][$jc]["type"]=2;	
    			} else {
				$cart["fleets"][$jc]["type"]=1;	
    			}
    			if ($sd['who1']<1000){
				$cart["fleets"][$jc]["fname"]=$sd['name1'];
				$cart["fleets"][$jc]["cship"]=$sd['cfl'];
    			} else {
				$cart["fleets"][$jc]["fname"]=ask_name($sd['who1']);
				$cart["fleets"][$jc]["cship"]=1;
    			}
    			$jc=$jc-1;
  		}
	}
echo json_encode($cart);
}
?>