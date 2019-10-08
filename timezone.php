<?php
if (!isset($_POST['tz'])) {
  echo "<form id='form2' method='post' action='timezone.php'><input id='tz' name='tz' value=''><input id='times' name='times' value=''><input type='button' value='Запрос' onClick='timez()'></form>";
  print "<script type='text/javascript'>                                                                                                                                        
function timez() {
var dates=new Date();
alert(dates);
document.getElementById('times').value=Date.parse(dates)/1000;
document.getElementById('tz').value=dates.getTimezoneOffset();
document.getElementById(\"form2\").submit();
}
</script>";
} else {                      
  echo $_POST['times'],"<br>";
  echo date_default_timezone_get(),"<br>";
  echo time(),"<br>";
  echo $_POST['tz']*60,"<br>";
  echo $_POST['times']-time();
}
?>