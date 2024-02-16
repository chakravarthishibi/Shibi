<?php ob_start(); ?>
<?php
	if (session_status() !== PHP_SESSION_ACTIVE)  
	{ 
		 if ($_SERVER['HTTPS']) {    ini_set('session.cookie_secure',1);  }    ini_set('session.cookie_httponly',1); session_start(); 
	}
	define('ROOT_PATH', $_SESSION['app_root']);
	require_once ROOT_PATH . '/conf/messages_inc.php';
	require_once ROOT_PATH . '/lib/dao/ConsoleActivationDAO.php';
	require_once ROOT_PATH . '/lib/models/ConsoleActivationVO.php';
	require_once ROOT_PATH . '/lib/dao/UtilityConfigDAO.php';
?>

</div>

<div class="span12" id= "footer" style="text-align:center;position: fixed">
<?php echo COPYRIGHT; ?>
	<!--&copy;&nbsp;Copyright 2008
	<!--script type="text/javascript">
		copyright = new Date();
		update = copyright.getFullYear();
		document.write(" - " + update);
	</script>&nbsp;Vigyanlabs Innovations Pvt. Ltd. All rights reserved.

</div-->
</div>

<?php 



$sx = "";
$pos = strrpos($_SERVER["REQUEST_URI"], "?");

if ($pos > 0)
{
	$sx = substr($_SERVER["REQUEST_URI"], 0, strrpos($_SERVER["REQUEST_URI"], "?"));
		
}
else {
	$sx = $_SERVER["REQUEST_URI"];
	
}
$sx1 = explode('/', $sx);
$res = '';
$count_sx = count($sx1);
for($i = 2; $i<=$count_sx ;++$i)
{
	$res .= '/'.$sx1[$i];
	$res_url = substr($res, 0, strrpos($res, "/"));
}

//$count = 0;
//echo $res_url;
// $utility = new UtilityConfigDAO();
// $count = $utility->getURLByModulesCheck($res_url);
// if($count <= 0)
// {
// 	header("Location:noaccess");
// }

?>

</body>
</html>

