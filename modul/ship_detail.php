<?php
echo "<span class='close'>&times;</span>";
if (isset($_POST['ship'])) {
	include_once('connect.php');
	$qship=$pdo->prepare("SELECT `id`, `name`, `image`, `descr`, `descparts`, `spec` FROM ships where id= ? LIMIT 1");
	$sh=(int)trim($_POST['ship']);	
//	echo $sh;
	$qship->execute([$sh]);
	$ship=$qship->fetch();
	echo "<h3>Описание корабля</h3>";
	echo "<form method='post' action='jobs/ships.php'><input type='hidden' name='ids' value='",$ship['id'],"'>";
	echo "<table><tr><td><b>",$ship['name'],"</b></td><td colspan=2 align='center'>изображение: ";
        echo "<select name='imag'>";
        $dir='../img/ships/';
        if (is_dir($dir)){
   	     if ($dh=opendir($dir)){
                   while (($file = readdir($dh)) !== false) {
			if (filetype($dir . $file)=='file'){
            			echo "<option value='",$file,"'";
            			if ($file==$ship['image']){ echo " selected";}
				echo ">",$file,"</option>";
			}
        	   }
		    closedir($dh);
	       }
        }
        echo "</select> ";	
        echo "<input type='submit' name='detail' value='Сохранить'></td></tr>";
	echo "<tr><td>Описание</td><td>Уникальные компоненты</td><td>Особенности</td><td></td></tr><tr><td><textarea cols=28 rows=4 name='desc'>",$ship['descr'],"</textarea></td>";
	echo "<td><textarea cols=28 rows=4 name='descparts'>",$ship['descparts'],"</textarea></td>";
	echo "<td><textarea cols=28 rows=4 name='spec'>",$ship['spec'],"</textarea></td>";
	echo "<td></td></tr></table></form>";
echo "<script>
    var span = document.getElementsByClassName('close')[0];
    span.onclick = function() {
      modal.style.display = 'none';
    }
</script>";
}
?>