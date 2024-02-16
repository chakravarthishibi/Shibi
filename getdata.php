<?php ob_start(); ?>
<?php
if (!session_id()) {
	if ($_SERVER['HTTPS']) {    ini_set('session.cookie_secure', 1);
	}    ini_set('session.cookie_httponly', 1);
	session_start();
}
ini_set("display_errors", 0);
$root_pathCheck = $_SESSION['app_root'];

$root_pathTT = "";
if ($root_pathCheck == "") {
	$root_path = dirname(__FILE__);
	//gets the current directory path.
	$root_path = substr($root_path, 0, -5);//remove the "/ajax" part from $root_path.
	//remove the "/ajax" part from $root_path.
	$root_pathT = str_replace("\\", "/", $root_path);
	$root_pathTT = str_replace("//", "/", $root_pathT);
	$root_pathTTT = trim($root_pathTT);

	$root_pathTLast = $root_pathTT[strlen($root_pathTT) - 1];
	if ($root_pathTLast == "/") {
		$root_pathTT = substr($root_pathTTT, 0, -1);
	}
} else {
	$root_path = $root_pathCheck;
	$root_pathT = str_replace("\\", "/", $root_path);
	$root_pathTT = str_replace("//", "/", $root_pathT);
	$root_pathTTT = trim($root_pathTT);

	$root_pathTLast = $root_pathTT[strlen($root_pathTT) - 1];
	if ($root_pathTLast == "/") {
		$root_pathTT = substr($root_pathTTT, 0, -1);
	}
}
define('ROOT_PATH', $root_pathTT);
//define('ROOT_PATH', $_SESSION['app_root']);
require_once ROOT_PATH.'/conf/ipmplusec_config.php';
require_once ROOT_PATH.'/lib/dao/AdvancedNodeInfoDAO.php';
require_once ROOT_PATH.'/lib/models/AdvancedNodeInfoVO.php';
//require_once ROOT_PATH.'/view/nocsrf.php';
require_once ROOT_PATH.'/lib/utils/checkForVulnerability.php';

$groupid = $_POST['groupid'];

try
	{
    // Run CSRF check, on POST data, in exception mode, for 10 minutes, in one-time mode.
		if(checkForVulnerability::isVulnerableCSRF('csrf_get_data', 'POST', false, 60*10, true) == -1){
			header('Location: '.URL_ROOT_PATH.'/view/403');
			http_response_code(403);
			die('Forbidden');
			exit();
		}

		if(checkForVulnerability::checkVulnerabilityURL_Ref($_SERVER['REQUEST_URI'],$_SERVER['HTTP_USER_AGENT'],$_SERVER['HTTP_REFERER']) == -1)
		{
			//header('Location: '.URL_ROOT_PATH.'/view/403');
			//header('Location: ../view/403.php');
			http_response_code(403);
			die('Forbidden');
			exit();
		}	

		if(isset($groupid)){
			$Anodesdao = new AdvancedNodeInfoDAO();
			$StatGrpNodes = array();
			$StatGrpNodes = $Anodesdao->getStatusOfNodesInGroup_new($groupid);
			$temp = json_encode($StatGrpNodes);
			echo $temp;
			exit;
		}else{
			echo "-1";
			exit;
		}
}

catch(Exception $e )
{
	//$result = $e->getMessage() . ' Form ignored.';
	$result = $e->getMessage();
	echo $result;
	header('Location: '.URL_ROOT_PATH.'/view/403');
	exit();
 }

?>
