<?php ob_start(); ?>
<?php

ini_set('memory_limit', '2560M');
	set_time_limit(40000);

	if (session_status() !== PHP_SESSION_ACTIVE) 
	{
		session_start();
	}
    
	if( !isset($_SESSION['app_root']))
		header('Location:login');

	define('ROOT_PATH', $_SESSION['app_root']);
	
	require_once ROOT_PATH.'/lib/phpexcel/PHPExcel.php';
	require_once '../lib/phpexcel/PHPExcel/IOFactory.php';
	require_once ROOT_PATH.'/lib/utils/DBManager.php';
	require_once ROOT_PATH.'/lib/common/functions_inc.php';
	require_once ROOT_PATH.'/lib/common/session_inc.php';
	require_once ROOT_PATH.'/lib/utils/checkForVulnerability.php';
	require_once ROOT_PATH.'/conf/ipmplusec_config.php';

	require_once ROOT_PATH.'/lib/dao/RemoteInstallationDAO.php';
	check_session();

	// echo "hello";

	// This is the file path to be uploaded.
	$upload_flag = $_POST['file_upload_flag'];

	//check for upload flag
	if($upload_flag == 'yes'){
		// continue;
		if(checkForVulnerability::isVulnerableCSRF('draobhsad', 'POST', false, 60*10, true) == -1){
			header('Location: '.URL_ROOT_PATH.'/view/403');
			http_response_code(403);
			die('Forbidden');
			exit();
		}
	}

	//check if file details are set
	if (isset($_FILES["FileInput1"]) && $_FILES["FileInput1"]["error"] == UPLOAD_ERR_OK) {
		$UploadDirectory = '../uploads/';
		if ($_FILES["FileInput1"]["size"] > 5242880) {
				//echo "File size is too big!";
				//exit();
			}
		$File_Name = strtolower($_FILES['FileInput1']['name']);

		$pattern = "/^WindowsRemoteInstallation*/i"; 
                if(!preg_match($pattern,$File_Name)) 
                {
                    $msg='Please upload correct file';
					// alertMessage_redirect($msg,'AssetPreferences');
					alertMessage_redirect($msg,$_SERVER['HTTP_REFERER']);
					
                    exit();
				}
				
		$ext = pathinfo($File_Name, PATHINFO_EXTENSION);
		if($ext != 'csv')
		{
			$msg= 'Unsupported file!';
			alertMessage_redirect($msg,$_SERVER['HTTP_REFERER']);
			exit();
		}
		
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$type_of_file = finfo_file($finfo, $_FILES['FileInput1']['tmp_name']);

		if($type_of_file == 'text/plain')
		{
			
		}else
		{
			unlink($File_Name);
			$msg= 'Unsupported file!';
			alertMessage_redirect($msg,$_SERVER['HTTP_REFERER']);
			exit();
		}
		
						
		if (move_uploaded_file($_FILES['FileInput1']['tmp_name'], $UploadDirectory . $File_Name)) 
		{
			$inputFileName = '../uploads/'.$File_Name;
			$csv = array_map('str_getcsv', file($inputFileName));
			
			$username = '';
			$password = '';
			if(isset($_POST["username"]) && isset($_POST["password"])){
				$username = $_POST['username'];
				$password = $_POST['password'];
			}

			$query = "TRUNCATE TABLE remoteinstallation_windows RESTART IDENTITY;";
			$dbh = DBManager::getConnectionPer();
			$stmt = $dbh->prepare($query);
			$bool = $stmt->execute();
			
			foreach($csv as $data)
			{
				foreach($data as $key=>$value)
				{

						$query = "INSERT INTO remoteinstallation_windows(";
								$query_set = '';
					
								if(!empty($value))
								$query_set .= "ip_hostname,";
								if(!empty($username))
								$query_set .= "username,";
								if(!empty($password))
								$query_set .= "password,";
								// if(!empty($status))
								// $query_set .= "status,";
								$query .= substr($query_set, 0, -1).")";
					
								$query_set1 = 'VALUES(';
								if(!empty($value))
								$query_set1 .= "'$value',";
								if(!empty($username))
								$query_set1 .= "'$username',";
								if(!empty($password))
								$query_set1 .="'$password',";
								// if(!empty($status))
								// $query_set1 .= "'$status',";
								$query .= substr($query_set1, 0, -1).");";

								$dbh = DBManager::getConnectionPer();
								$stmt = $dbh->prepare($query);
								$bool = $stmt->execute();
					}
			}
			$dbh = null;
			unlink($File_Name);
			echo 'Uploaded Successfully!';	
			// sleep(10);

			// $command = "/usr/bin/sh /var/www/html/IPMPlusEEConsole/scripts/rhel/Windows_Agent_Remote_Installer_Exec.sh";
			// // $scriptOutput = exec($command, $output, $returnstatus);
			// system($command);

		} 
		else 
		{
			echo 'Upload failed!';
		}

	} 
	else 
	{
		die('Please select the file to be upload.');
	}


	$handle = fopen('/var/www/html/avinash.log','a+');
fwrite($handle, "\n".date('Y-m-d H:i:s')." ->email-> Hello Ajax" );
fwrite($handle, "\n".date('Y-m-d H:i:s')." ->email-> Hello Ajax".$_POST['operation'] );
$operation = getPostValue("operation");
$handle = fopen('/var/www/html/test5.log','a+');
		fwrite($handle, "\n".date('Y-m-d H:i:s')." ->operation-> ".$operation );
		fclose($handle);
fclose($handle);
	if (isset($_POST['operation'])){
		$handle = fopen('/var/www/html/test5.log','a+');
		fwrite($handle, "\n".date('Y-m-d H:i:s')." ->inside-> " );
		fclose($handle);

		var_dump($_POST['operation']);
		// echo "Ajax";
		// $operation =  $_POST['operation'];

		

		// if($operation == "runCron"){

		// 	echo "Ajax1";

		// 	$command = "/usr/bin/sh /var/www/html/IPMPlusEEConsole/scripts/rhel/Windows_Agent_Remote_Installer_Exec.sh";
		// 	// $scriptOutput = exec($command, $output, $returnstatus);
		// 	system($command);
		// }

	}
?>