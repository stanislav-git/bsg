<?php
function count_fuel($a1,$a2)
{
  $row1=7;
  if ($a1<37) {$row1=6;}
  if ($a1<31) {$row1=5;}
  if ($a1<25) {$row1=4;}
  if ($a1<19) {$row1=3;}
  if ($a1<13) {$row1=2;}
  if ($a1<7) {$row1=1;}
  $row2=7;
  if ($a2<37) {$row2=6;}
  if ($a2<31) {$row2=5;}
  if ($a2<25) {$row2=4;}
  if ($a2<19) {$row2=3;}
  if ($a2<13) {$row2=2;}
  if ($a2<7) {$row2=1;}
  $rcol=abs(($a1-(($row1-1)*6))-($a2-(($row2-1)*6)));
  $rrow=abs($row1-$row2);
  if ($rrow>$rcol) {
	$ret=$rrow;
  } else {
	$ret=$rcol;
  }
  return $ret;
}

function ask_name($a1,$pdo)
{
	$b1=round(($a1/100-floor($a1/100))*100);
	$q_nam_fleet = $pdo->prepare("SELECT name FROM destination WHERE `who` = ?");
	$q_nam_fleet->execute([$b1]);
	$nam_fleet=$q_nam_fleet->fetchColumn();
	$ret=$nam_fleet;
	if ($a1>100){
		if (strpos($nam_fleet, "айлон") !== false) {
		   $ret='Рейдер Сайлонов';
		} else {
		   $ret='Раптор '.$nam_fleet;
		}
	}
	return $ret;
}

?>