<?php
echo "<span class='close'>&times;</span>";
if (isset($_POST['tim'])) {
	include_once('connect.php');
	if (isset($_POST['nw'])){
		$curnews=array('autor'=>"",'news'=>"",'fleet'=>0,'timnews'=>time());
	} else {
		$q_curnews=$pdo->prepare("select * from news where fleet=? and timnews=?");
		$q_curnews->execute(array($_POST['fleet'],$_POST['tim']));
		$curnews=$q_curnews->fetch();
	}
	echo "<form method='post' action='jobs/news.php'><p>Время: <input type='hidden' name='mark' value='",$curnews['timnews'],"'><input type='text' pattern='[0-9]{2}.[0-9]{2}.[0-9]{4} [0-9]{2}:[0-9]{2}:[0-9]{2}' name='tim' value='",date("d.m.Y H:i:s",$curnews['timnews']),"'>";
	echo " Автор: <input type='text' name='autor' value='",$curnews['autor'],"'>";
	echo " Флот: <select name='fleet'>";
	echo "<option value='0'";
	if ($_POST['fleet']==0) {echo " selected";}
	echo ">Общий</option>";
	$q_fl=$pdo->query("select who,name from destination where who < 1000 order by who ASC");
	$q_fl->execute();
	while ($fl=$q_fl->fetch()){
		echo "<option value='",$fl['who'],"'";
		if ($fl['who']==$_POST['fleet']){echo " selected";}
		echo ">",$fl['name'],"</option>";
	}
	echo "<option value='999'";
	if ($_POST['fleet']==999) {echo " selected";}
	echo ">Клуб</option></select></p>";
	echo "<p><textarea name='news' rows=4 style='width:100%;'>",$curnews['news'],"</textarea></p>";
	if (isset($_POST['nw'])){
		echo "<p><input type='submit' name='add' value='ОПУБЛИКОВАТЬ'></p></form>";
	} else {
		echo "<p><input type='submit' name='save' value='СОХРАНИТЬ'> <input type='submit' name='del' value='УДАЛИТЬ'></p></form>";
	}
echo "<script>
    var span = document.getElementsByClassName('close')[0];
    span.onclick = function() {
      modal.style.display = 'none';
    }
</script>";
}
?>