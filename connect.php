<?php
$host = 'localhost';
$db   = 'bsg_nav';
$user = 'root';
$pass = '626123';
$charset = 'utf8';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$opt = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
];
$pdo = new PDO($dsn, $user, $pass, $opt);

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

?>