<style>
	#wrapper
	{
		width: 100%;
		margin-left: auto;
		margin-right: auto;
		position: relative;
		min-height: 100%;
	}

	#footer
	{
		clear:both;
		position: absolute;
		bottom: 0;
		text-align: center;
		width:100%;
		background-color:#404041;
		color:white;
		padding:10px 40px 10px 40px;
		z-index:9999;
	}

	#url_patch_list_modal
	{ 
		text-decoration: underline;
	}
	#url_patch_list_modal:hover 
	{
		color: #add8e6;
	}

	#container2 {
			padding-left: 10px;
			padding-top: 10px;
			/* background-color: #0b1520; */
			margin: 10px;
			color : #fff;
			width: 50%;
			float: right;
			height: 600px;
		}

	#myDataTable th{
		/* cursor: pointer; */
		padding-right: 26px;
		position: relative;
		text-transform: Uppe;
		width: 9% !important;/* Set your desired default column width here */
		text-align: left;
		padding: 5px;
    	color: #E9EAE0;
	}

	#myDataTable table {
		table-layout: fixed;
		width: 100%;
	}
  
	#myDataTable td {
		width: 9%;/* Set your desired default column width here */
		text-align: left;
		padding: 5px;
		/* border: 1px solid black; */
    	color: #000 !important;
	}

	.stepsform{
		float: left !important;
    	width: 45% !important;
		border-radius: unset !important;
		background-color: rgba(10, 20, 30, 0.5);
    	border-radius: 22px !important;
		padding-left: 20px !important;
	}

	.step1{
		display: flex;
		position: relative;
		width: 100%;
	}

	.user_details{
		display: flex;
		column-gap: 16px;
		position: absolute;
		padding-left: 20px;
	}

	h5{
		color: #00A9FF !important;
		text-align: center;
	}

#myDataTable thead{background: #6C6A61;}

#myDataTable tbody {background: #EDE4E3 !important;}

.toggle-password {
	position: absolute;
    top: 68%;
    transform: translateY(-50%);
    cursor: pointer;
    padding-right: 10px;
    left: 87%;
}

#myDataTable_paginate{
	width: auto !important;
}
</style>

<?php

ob_start();

	//If not session is started , then start the session
	if(!session_id())
	{
		if ($_SERVER['HTTPS']) 
		{  
			ini_set('session.cookie_secure',1);
		}    
		ini_set('session.cookie_httponly',1);
		session_start();
	}

    if(!isset($_SESSION['app_root']))
	header('Location:login.php');

	define('ROOT_PATH', $_SESSION['app_root']);
	require_once ROOT_PATH.'/view/header.php';
	require_once ROOT_PATH.'/lib/utils/checkForVulnerability.php';

	require_once ROOT_PATH.'/lib/dao/RemoteInstallationDAO.php';

	$RemoteDetails = new WindowsRemoteInstallationDAO();
	$flag = 'Y';
	$remote_details = $RemoteDetails->getRemoteDeatailsXLS($flag);

	$draobhsadcp = NoCSRF::generate('draobhsadcp');
	$draobhsad = NoCSRF::generate('draobhsad');

	if(checkForVulnerability::checkVulnerabilityURL_Ref($_SERVER['REQUEST_URI'],$_SERVER['HTTP_USER_AGENT'],$_SERVER['HTTP_REFERER']) == -1)
	{
		header('Location: '.URL_ROOT_PATH.'/view/403');
	}

?>

<!-- Datatable implementation css-->
<link rel="stylesheet" href="../asset/js/jqueryui/themes/base/jquery.ui.all.css">
<link rel="stylesheet" href="../asset/datatables/sorter/sorter.css" type="text/css" media="print, projection, screen" />

<!-- Stylesheet for dark theme -->
<link rel="stylesheet" type="text/css" href="../asset/css/dark-theme.css" />

<!-- To alert bootbox JS-->
<script src="../asset/js/bootbox.min.js"></script>

<!-- Loading Overlay JS-->
<script type="text/javascript" language="javascript" src="../asset/overlay/dist/loadingoverlay.min.js"></script>

<!-- Jquery Validator JS-->
<script type="text/javascript" language="javascript" src="../asset/jqvalidator/jquery.validate.min.js"></script>
<!-- To validate Uploads in plugin-->
<script type="text/javascript" language="javascript" src="../asset/jqvalidator/additional-methods.min.js"></script>

<!-- Datatables JS -->
<link rel="stylesheet" type="text/css" href="../asset/css/custom.css"/>
<link rel="stylesheet" type="text/css" href="../asset/datatables/datatables.min.css"/>
<script type="text/javascript" src="../asset/datatables/datatables.min.js"></script>

<!-- Loading Overlay JS-->
<script type="text/javascript" language="javascript" src="../asset/overlay/dist/loadingoverlay.min.js"></script>

<style>
	#myDataTable th, #myDataTable td{ font-size: 14px !important;}
</style>

<div id="container1">

		<div id="first_row" style="margin:0px;">
			<div class="box-header" style="padding:5px">
				<h3 class="box-title" style="margin-bottom:2px !important;padding:0.5%;color: white;">Windows Remote Installation</h3>
				<!-- <button type="button" id="ad_details" class="btn btn-primary-green" data-toggle="modal" data-target="#upload_modal" style="font-size: 15px;width: 290px;margin-right: 20px;float:right;">Download/ Upload Remote Details</button> -->
			</div>
		</div>
</div>

<div id="container2" class="stepsform" style="margin-top: 20px !important;margin-left:15px !important;">

		<div style="margin-top: 16px;">
			<h5 style="margin-bottom: 60px !important;font-size: 19px !important;">Welcome to IPM+ Agent Remote Installation Wizard</h5></n>

			<div class="step1">
				<p><span style="color: #EDE4E3;">1. Download host details template.</span><br></n></p>

				<a /*href="javascript:setRemoteInstalltionDetails()"*/ href="../view/Remoteinstallation_CSV.php" style="cursor:pointer;float: right;position: absolute;margin-left: 65%;">
					<input type="button" class="btn btn-primary-green" style="padding:5px;color:#00a65a;margin-right:30px;font-size:17px;min-width:200px;text-align:center;position:absolute;" value="Download Template"></input>
				</a>
			</div>

		</div></br>

		<div class="step2">

			<p><span style="color: #EDE4E3;">2. Fill the IP or Hostname details one by one in the template and save the file.</span><br></p></br>

			<p><span style="color: #EDE4E3;">3. Enter the endpoint admin credentials for remote installation.</span><br></p></br>

<form  style="margin-bottom: 30px;"method="post" name="UplaodWindows" id="UplaodWindows" enctype="multipart/form-data">
    <div class="user_details">
        <div class="form-group">
            <label for="username" style="color:#00A9FF;">Username:</label><br>
            <input type="text" name="username" id="username" placeholder="Enter Username" autocomplete="username"/>
        </div>

        <div class="form-group">
		<div class="input-container" style="position:relative;">
            <label for="password" style="color:#00A9FF;">Password:</label><br>
            <input type="password" name="password" id="password"placeholder="Enter Password" autocomplete="new-password"/>
			<span class="toggle-password" id="togglePassword" title="Show Password">👁️</span>
		</div>
        </div>
    </div>
	<!-- <div id ="error" style="color: #ff6666;" hidden>Please Enter Username & Password.</div> -->
	
    <div class="step1" style="margin-top: 100px;column-gap: 115px;display:inline-block;">
		<div><p><span style="color: #EDE4E3;">4. Upload the host details file saved in Step 2.</span><br></p></br></div>

        <!-- <label for="FileInput1" class="custom-file-upload">
            <i class="fas fa-file-upload" style="color:#3470C0;margin-left:5px;font-size:20px;display:inline;cursor:pointer"></i>
            <span style="display:inline;color:#3470C0;font-size:20px;cursor:pointer">&nbsp;Upload Template</span>
        </label> -->

		<div style="display:flex;width:70%">
			<input type="hidden" name="draobhsad" id="draobhsad" value="<?php echo $draobhsad; ?>" />
			<input type="hidden" name="file_upload_flag" id="file_upload_flag" value="yes" />
			<input type="file"  style="display: block;position: relative;width: 65%;" name="FileInput1" id="FileInput1" accept=".csv" /></br></br>
			<input type="button" class="btn btn-primary-green" style="display:inline;color:#3470C0;font-size:17px;cursor:pointer;margin-left: 65%;float: right;position: absolute;min-width: 200px" value="Upload Template" onclick="validateAndExecute()"></input>
		</div>
    </div>
</form>


			<p><span style="color: #EDE4E3;">5. Installation will start now. Track the installation progress.</span><br></p><br>
			<div class="progress" id="progress" style="width:100%;display:none;">
				<div class="progress-bar progress-bar-striped active" id="progressbar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%;">Uploading...Please Wait</div>
			</div>
			<p><span style="color: #EDE4E3;">6. Installation Complete.</span><br></p><br>

			<!-- <div style="position: relative; margin-left: 70%;margin-bottom: 25px"> 
				<button type="button" class="btn btn-primary-submit" id="" style="margin-left: 10%;width: 80%;">Install Software</button>
			</div> -->
				 
		</div>

</div>

<div id="container2" style="margin-top: 20px !important;background-color: rgba(10, 20, 30, 0.5);;border-radius: 22px;padding: 25px;margin-right:15px !important;">
	<h5 style="margin-bottom: 60px !important;font-size: 19px !important;">Remote Installation Status Details</h5></n>
	<table id="myDataTable" class="table table-bordered table-hover">
		<thead style="font-size: 14px !important;">
			<tr>
				<th>IP/Hostname</th>
				<th>Username</th>
				<!-- <th>Password</th> -->
				<th>Status</th>

			</tr>
		</thead>

		<tbody>
			<?php
			foreach ($remote_details as $row) {
				$values = explode(',', $row);
				echo '<tr>';
				echo '<td>' . $values[0] . '</td>';
				echo '<td>' . $values[1] . '</td>';
				// echo '<td>' . $values[2] . '</td>';
				if($values[2] == 'Y'){
					echo '<td>Not Installed</td>';
				}else if($values[2] == 'N'){
					echo '<td>Installed</td>';
				}
				
				echo '</tr>';
			}
			?>
		</tbody>
	</table>
</div>

<!-- Modal Upload-->
<div class="modal fade" id="upload_modal">
	<div class="modal-dialog">
		<div class="modal-content">

		<!-- Modal Header -->
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal">&times;</button>
			<h4 class="modal-title">Download/Upload Excel</h4>
		</div>

		<!-- Modal body
		<div class="modal-body">
			<div class="upload_download_div">
				
				<a href="javascript:setRemoteInstalltionDetails()" style="cursor:pointer">
					<button type="button" class="btn btn-box-tool" style="padding:0px;color:#00a65a;margin-right:30px;font-size:20px;min-width:200px;text-align:left"><i class="fa fa-file-excel-o"></i> Download Details</button>
				</a>
				
				<form action="import_windows_remote_installation" method="post" name="UplaodWindows" id="UplaodWindows" enctype="multipart/form-data" style="display:inline">
					<label for="FileInput1" class="custom-file-upload">
						<i class="fas fa-file-upload" style="color:#3470C0;margin-left:5px;font-size:20px;display:inline;cursor:pointer"></i><span style="display:inline;color:#3470C0;font-size:20px;cursor:pointer">&nbsp;Upload Details</span>
					</label>
					<input type="hidden" name="draobhsad" id="draobhsad" value="<?php echo $draobhsad; ?>"/>
					<input type="hidden" name="file_upload_flag" id="file_upload_flag" value="yes"/>
					<input style="margin-right:20px;display:none" type="file" name="FileInput1" id="FileInput1" onchange="WindowsRemoteDetailsFun();" accept=".xls,.xlsx"/>
				</form>
				
			</div>
		</div> -->
	
		</div>
	</div>
</div>

<script>
	$(document).ready(function(){
		$('#myDataTable').DataTable( {
				"language": {
					"search": "Filter records:",
					"searchPlaceholder" : "Search IP/Hostname",
				},
					// "lengthMenu": [10, 12],
					"lengthChange": false,
  	 				"pageLength": 10
			});
$('#myDataTable').removeClass('dataTable');

	});

	const passwordField = document.getElementById('password');
	const togglePassword = document.getElementById('togglePassword');

	togglePassword.addEventListener('click', function () {
	if (passwordField.type === 'password') {
		passwordField.type = 'text'; // Change to text to show the password
	} else {
		passwordField.type = 'password'; // Change back to password to mask it
	}
	});
	
	function setRemoteInstalltionDetails(type){
		var draobhsadpei   = '<?php echo $draobhsadpei; ?>';

		form = document.createElement('form');
		form.setAttribute('method', 'POST');
		form.setAttribute('action', 'xls_downloadremote_installation_windows');

		myvar = document.createElement('input');
		myvar.setAttribute('name', 'report_val');
		myvar.setAttribute('type', 'hidden');
		myvar.setAttribute('value', 'RemoteInstallation');
		form.appendChild(myvar);

		myvar = document.createElement('input');
		myvar.setAttribute('name', 'draobhsadpei');
		myvar.setAttribute('type', 'hidden');
		myvar.setAttribute('value', draobhsadpei);
		form.appendChild(myvar);

		document.body.appendChild(form);
		form.submit();   
	}


	// function WindowsRemoteDetailsFun(){
	// 			// $('.preloader-container').show();
	// 			document.getElementById("UplaodWindows").submit();
	// 		}

	// function _(el){
	// 	return document.getElementById(el);
	// }

	$('#UplaodWindows').bootstrapValidator({
      fields: {
        username: {
          validators: {
            notEmpty: {
              message: 'The username is required'
            }
          }
        },
        password: {
          validators: {
            notEmpty: {
              message: 'The password is required'
            }
          }
        }
      }
    });

	$('#UplaodWindows').validate({
		errorElement: 'div',
			errorPlacement: function(error, element) {
				error.addClass('text-danger');
				error.insertAfter(element.parent());
			},
		rules: {
        FileInput1: {
          required: true,
		  accept: "text/csv"
        }
      },
      messages: {
        FileInput1: {
          required: "Please select a file.",
		  accept: "Please choose a valid csv file."
        }
      }
    });

	function validateAndExecute() {
    $('#UplaodWindows').bootstrapValidator('validate');

    if ($('#UplaodWindows').data('bootstrapValidator').isValid()) {
	  if($('#UplaodWindows').valid()){
		Upload_file();
	  }
      
    }
  }

  $('#FileInput1').on('change', function() {
      $('#fileInput-error').html(''); 
    });

function Upload_file(){
		
		var form = document.getElementById('UplaodWindows'); 
		var formData = new FormData(form);
		var username = formData.get('username');
		var file = formData.get('FileInput1');

		formData.append('FileInput1',file);

		var ajax = new XMLHttpRequest();
		ajax.upload.addEventListener("progress",progressHandler,false);
		ajax.addEventListener("load",completeHandler,false);
		ajax.open("POST","import_windows_remote_installation.php");
		ajax.send(formData);

		function progressHandler(e){
			var progressBar = document.getElementById('progressbar');
			document.getElementById('progress').style.display = "block";
			var percent = (event.loaded / event.total) * 100;
			setTimeout(function() {
				progressBar.style.width = Math.round(percent) + '%';
			}, 50);

		}

		function triggerCron(){
			console.log("Hellow");
			// var value = "runCron";
			$.ajax({
				url:"../ajax/BackupConfigAjax.php",
				type: "post",
				data: {
					operation: "runCron"
				},
				success: function(response) {
					alert(response.operation);
				}
			});
		}

		function completeHandler(e){
			document.getElementById('progressbar').style.width = 0;
			document.getElementById('progress').style.display = "none";
			bootbox.alert(e.target.responseText, function()
			{
				window.location.reload();
				triggerCron();

			});

		}
	}
</script>