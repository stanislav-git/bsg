<?php
if (isset($_POST['size'])){
if ($_POST['size']>=0) {
include_once('connect.php');
$cur_size=$_POST['size'];
$q_type=$pdo->prepare("SELECT purp,type from typeship where sizz=? order by type ASC");
$q_type->execute([$cur_size]);
echo "Назначение: <select id='purp' name='purp' onchange='purpchange(this);'>";
echo "<option value='0'>не выбрано</option>";
while ($purp=$q_type->fetch()){
	echo "<option value='",$purp['purp'],"'>",$purp['type'],"</option>";	
}
echo "</select>";
echo "<script>
function purpchange(select){
var selectedOption = select.options[select.selectedIndex];
    $.ajax({
         type: 'POST',
         url: 'modul/purpsh.php',
         data: {purp:selectedOption.value,size:",$cur_size,"},
         success: function(html){
             $('#type_size').html(html);	
         }
    });
    return false;
}
</script>";
 }
}
?>