<?php
echo "<span class='close'>&times;</span>";
if (isset($_POST['ship'])) {
	include_once('connect.php');
	$qship=$pdo->prepare("SELECT ships.id as ids, ships.fleet as fleet, user, `name`, repair, `image`, `descparts`, `spec`, typeship.type, typeship.cargo FROM ships join typeship on ships.type=typeship.id where ships.id= ? LIMIT 1");
	$sh=(int)trim($_POST['ship']);
//	echo $sh;
	$qship->execute([$sh]);
	$ship=$qship->fetch();
	$q_num_sh=$pdo->prepare("select count(id) as numb from ships where fleet=? group by fleet");
	$q_num_sh->execute([$ship['fleet']]);
	$num_sh=$q_num_sh->fetchColumn();	
	echo "<form method='post' action='jobs/ships.php'><input type='hidden' name='ids' value='",$ship['ids'],"'>";
	echo "<h3>",$ship['name'],"</h3><h4>",$ship['type']," ",$ship['cargo'];
	echo "</h4><table style='width:100%;'><tr>";	

	$q_user=$pdo->query("SELECT id, name, live from users order by name ASC");
	$q_user->execute();
	echo "<td nowrap>Владелец:<select name='user'><option value='0'>нет хозяина</option>";
	while ($user=$q_user->fetch()){
		echo "<option value='",$user['id'],"'";
		if ($user['id']==$ship['user']){echo " selected";}
		echo ">",$user['name'];
		if ($user['live']==0) {echo " (мертв)";}
		echo "</option>";
	}
	echo "</select></td>";

        echo "<td nowrap>Картинка:<select name='imag'><option value=''>нет изображения</option>";
	$filelist=array();
        $dir='../img/ships/';
        if (is_dir($dir)){
   	     if ($dh=opendir($dir)){
                   while (($file = readdir($dh)) !== false) {
			if (filetype($dir . $file)=='file'){
            			$filelist[]=$file;
			}
        	   }
		    closedir($dh);
	       }
        }
        sort($filelist);
	foreach ($filelist as $file) {
		echo "<option value='",$file,"'";
		if ($file==$ship['image']){ echo " selected";}
		echo ">",$file,"</option>";
	}
        echo "</select></td>";
	echo "</tr><tr><td>Уникальные компоненты</td><td>Особенности</td><td></td></tr><tr>";
	echo "<td><textarea rows=5 style='width:90%;' name='descparts'>",$ship['descparts'],"</textarea></td>";
	echo "<td><textarea rows=5 style='width:100%;' name='spec'>",$ship['spec'],"</textarea></td>";
	echo "<td></td></tr></table><p style='width:100%;line-height:40px;'>";
	echo "<input type='submit' name='fleet_d' value='В отдельный флот'";
	if ($ship['fleet']==0 or $num_sh==1) {echo " disabled='disabled' style='display:none;'";}
	echo "> ";
	echo "<input type='submit' name='flag' value='Назначить флагманом'";
	if ($ship['fleet']==0 or $ship['fleet']==$sh) {echo " disabled='disabled' style='display:none;'";}
	echo "> ";
	echo "<input type='submit' name='parseship' value='Разобрать'";
	if ($ship['fleet']==0 or $num_sh==1) {echo " disabled='disabled' style='display:none;'";}
	echo "> ";
	echo "<input type='submit' name='breakship' value='Сломать'";
	if ($ship['fleet']==0 or $ship['repair']<>0) {echo " disabled='disabled' style='display:none;'";}
	echo "> ";
	echo "<input type='submit' name='fbreakship' value='Забастовка'";
	if ($ship['fleet']==0 or $ship['repair']<>0) {echo " disabled='disabled' style='display:none;'";}
	echo "> ";
	echo "<input type='submit' name='repair' value='Починить'";
	if ($ship['fleet']==0 or $ship['repair']==0) {echo " disabled='disabled' style='display:none;'";}
	echo "> ";
	echo "<input type='submit' name='detail' value='Сохранить'></p></form>";
echo "<script>
    var span = document.getElementsByClassName('close')[0];
    span.onclick = function() {
      modal.style.display = 'none';
    }
</script>";
}
?>