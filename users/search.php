<?php
include_once('../modul/connect.php');
include_once('../modul/funct.php');
/**
* поиск автокомплит
**/
if(!empty($_GET['term'])){
	$search = search_autocomplete($_GET['term']);
	exit( json_encode($search) );
}
?>