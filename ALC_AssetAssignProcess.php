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

	//If session of app_root not set , then redirecting to login page
	if(!isset($_SESSION['app_root']))
	header('Location:login.php');
	define('ROOT_PATH', $_SESSION['app_root']);
	require_once ROOT_PATH.'/view/header.php';
	require_once ROOT_PATH.'/lib/common/session_inc.php';

	
	//checking sessions and updating lastaccessedtime , also including required files
	check_session();
	$_SESSION['lastaccessedtime'] = time();
	require_once ROOT_PATH.'/lib/utils/checkForVulnerability.php';
	
	require_once ROOT_PATH.'/lib/dao/AlcAssetUserMappingDAO.php';
	require_once ROOT_PATH.'/lib/dao/ALCAssetDetailsDAO.php';

	$get_roleid = $_SESSION['roleid_session'];

	// ALC Related Heading Config File
	require_once ROOT_PATH.'/conf/ALC_conf.php';

	if(ASSET_LIFE_CYCLE != 'enable') {
		header("Location:noaccess");
	}

	$ALCAssetDetailsDAO = new ALCAssetDetailsDAO;


	$apad = new AlcAssetUserMappingDAO;
	$assetDetails = $apad->getAssetDetails();
	$locationNames = $apad->getLocationNames();
	$branchNames = $apad->getBranchNames();
	$departmentNames = $apad->getDepartment();
	$getAllAdUser = $apad->getAllAdUser();

	$userAssetMappingDetails = $apad->getUserAssetMappingDetails();
	$userAssetMappingDetails1 = array();

	foreach($userAssetMappingDetails as $details){
		$userAssetMappingDetails1[$details['alc_asset_id']] = $details;
	}

	//Check For Security Issues , IF found redirect to 403
	if(checkForVulnerability::checkVulnerabilityURL_Ref($_SERVER['REQUEST_URI'],$_SERVER['HTTP_USER_AGENT'],$_SERVER['HTTP_REFERER']) == -1)
	{
		header('Location: '.URL_ROOT_PATH.'/view/403');
	}
	if(isset($_SESSION["responce_msg"])){
		echo "<script>alert('". $_SESSION["responce_msg"]."')</script>";
		unset($_SESSION['responce_msg']); 
	}
	
	$draobhsadcp = NoCSRF::generate('draobhsadcp');
	$draobhsad = NoCSRF::generate('draobhsad');
?>


<!-- Datatable implementation css-->
<link rel="stylesheet" href="../asset/js/jqueryui/themes/base/jquery.ui.all.css">
<link rel="stylesheet" href="../asset/datatables/sorter/sorter.css" type="text/css" media="print, projection, screen" />




<!-- To alert bootbox JS-->
<script src="../asset/js/bootbox.min.js"></script>

<!-- Loading Overlay JS-->
<!-- <script type="text/javascript" language="javascript" src="../asset/overlay/dist/loadingoverlay.min.js"></script> -->

<!-- Jquery Validator JS-->
<script type="text/javascript" language="javascript" src="../asset/jqvalidator/jquery.validate.min.js"></script>
<!-- To validate Uploads in plugin-->
<script type="text/javascript" language="javascript" src="../asset/jqvalidator/additional-methods.min.js"></script>

<!-- Datatables JS -->

<link rel="stylesheet" type="text/css" href="../asset/datatables/datatables.min.css"/>
<script type="text/javascript" src="../asset/datatables/datatables.min.js"></script>

<!-- Select2 related  -->
<link href="../asset/select2/select2.min.css" rel="stylesheet" />
<script src="../asset/select2/select2.min.js"></script>

<style>
	.user_assign_btn{
		width : 110px;	
	}

	.label_field_data{
		color : #61bcd1 !important;
	}

	.label_field_data_1{
		color : #61d18e !important;
		word-break: break-all;
	}
	.not_valid{
		color : #e54737 !important;
	}

	hr {
		margin-top: 10px;
		margin-bottom: 10px;
		border-top: 1px solid #db7272;
	}
	.text-required{
		color : #ff1d1d !important;
	}
	/* table#myDataTable td, table#myDataTable th {
		font-size: 10px !important;
		max-width: 150px !important;
		padding: 8px 4px;
		min-width: 60px !important;
	} */

	.upload_tag_sticker_icon{
		font-size: 11px !important;
		padding: 4px !important;
		margin-left: 4px !important;
	}

	#myDataTable tr th{
		font-size: 13px !important;
	}

	#assign_asset_modal .select2-results__option, .select2-selection__rendered{
		font-size: 13px !important;
	}

	.system_details .col-sm-4, .user_details .col-sm-4{
		font-size: 13px;
	}

	
	@media only screen and (min-width: 1200px) and (max-width: 1400px){
		.modal{
			font-size : 10px;
		}

		.panel input{
			font-size: 12px;
		}

	}
	
</style>
<div class= "dark">

	<div class="card">
		<div class="card-header">
			<div class="upload_xls_custom_div">
				<!-- <a href="ALC_ProcuredAssetCustCol" class="btn btn-new-1 mt-5 mr-5">
					<span>Procured Asset Custom Column</span>			
				</a>
				<div class="btn btn-new-1 mt-5 mr-5" data-toggle="modal" data-target="#upload_modal">
					<span>Download/Upload Custom Column Details</span>			
				</div>		 -->
				<button type="button" id="help_btn" class="btn btn-box-tool mt-5 mr-5" style="font-size: 15px;width: 80px;background-color: #337ab7;">Help</button>
			</div>
			<h3><?php echo ALC_AssetAssignProcess; ?></h3>
		</div>
		<div class="card-body">
			<div id="errorAlert" class="alert hide-item text-center" role="alert"> <?php echo ALC_AssetAssignProcess_HelpText; ?> <button type="button" class='close' id='close_alert'>&times;</button></div>
		
			<div id="container2">
				<table id="myDataTable" class="table table-bordered table-striped table-hover">
					<thead>
						<tr>
						<th class="text-left" style="width:50px">SL No</th>
						<th class="text-left">Hostname</th>
						<th class="text-left">Asset Serial Number</th>
						<th class="text-left">Username (UserId)</th>
						<th class="text-left">Location</th>
						<th class="text-left">Ageing</th>
						<th class="text-center" style="width:70px">Asset Status</th>
						<th class="text-center" style="width:70px">User Assign Status</th>
						<th class="text-center" style="width:70px">User Assign Ageing</th>
						<th class="text-center" style="width:70px">Action</th>
						</tr>
					</thead>
					<tbody>
						<?php  $j=1;
						//echo print_r($heading_names,true); 
						
						foreach($assetDetails as $arr1) {  
						?> 	
							<tr>
								<td class="text-left" style="width:70px"> <?php echo $j++; ?> </td>
								<td class="text-left" style="width:200px"><?php echo $arr1['hostname']; ?></td>
								<td class="text-left" style="width:200px"><?php echo $arr1['serial_number']; ?></td>
								<td class="text-left" style="width:200px"><?php echo $arr1['username']; ?></td>
								<td class="text-left" style="width:200px"><?php echo $arr1['location']; ?></td>
								<?php 
									$acceptance_flag = $arr1['acceptance_flag'] == '' ? 0 : $arr1['acceptance_flag'];
									$user_assign_status1 = ['NOT YET ASSIGNED','ACCEPTED','REJECTED','PENDING','ASSIGNED'];
									$btn_class_arr = ['mdf2','success','danger','warning','info'];
									$user_assign_status = $user_assign_status1[$acceptance_flag];
									$btn_class_name = $btn_class_arr[$acceptance_flag];
									// $user_assign_status = $user_assign_status == '' ? 'NOT YET ASSIGNED' : $user_assign_status;
								?>
								<td class="text-left" style="width:100px"><?php echo $arr1['installation_date']; ?></td>
								<td class="text-center pt-5 pb-5" style="width:200px;">
									<div class="btn btn-outline-info-disabled" style="font-size: 11px;"><?php echo str_replace("_"," ",$arr1['asset_status']); ?></div>
								</td>
								<td class="text-center pt-5 pb-5" style="width:200px;">
									<div class="btn btn-outline-<?php echo $btn_class_name ?>-disabled" style="font-size: 11px;"><?php echo $user_assign_status ?></div>
								</td>
								<td class="text-left" style="width:100px"><?php echo $arr1['asset_assigned_date']; ?></td>
								<td class="text-center pt-5 pb-5" style="width:100px">	
								<?php if($acceptance_flag == 0 || $acceptance_flag == 2){  
										
										if($get_roleid != 11 && $get_roleid != 13 && $get_roleid != 12){
									?>
									<div class="tab_edit icon-2 assign_asset_btn lt_btn lt_btn_primary lt_btn_round" data-val="<?php echo $arr1['id']; ?>" data-toggle="tooltip" data-title="Edit"><i class="fa fa-pencil"></i></div>
								<?php 	} 
									  } else { ?>
									<div class="tab_view icon-2 view_asset_btn lt_btn lt_btn_mdf2 lt_btn_round" data-val="<?php echo $arr1['id']; ?>" data-toggle="tooltip" data-title="View Details"><i class="fa fa-eye"></i></div>
								<?php } ?>
								</td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	
	
	



	<div class="modal fade" id="assign_asset_modal">
		<div class="modal-dialog modal-lg-90">
			<div class="modal-content">
				<!-- Modal Header -->
				<div class="modal-header">				
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title mb-5">Asset Assign to User</h4>
				</div>
				<form action="" id='assign_asset_form' method="post">
					<!-- Modal body -->
					<!-- style="font-size:12px !important" -->
					<div class="modal-body custom_modal_body form_modal"  style="font-size:11px !important">
						<input type="hidden" class="data_nodeid" id="form_nodeid" name="form_nodeid" value="">
						<input type="hidden" class="data_hostname" id="form_hostname" name="form_hostname" value="">
						<input type="hidden" class="data_serial_number" id="form_serial_number" name="form_serial_number" value="">
						<input type="hidden" class="data_id" id="alc_asset_id" name="alc_asset_id" value="">
						<div class="row system_details" style="font-size:11px !important">
							<div class="col-md-4 mt-5 row">
								<div class="col-sm-4 pl-0">
									<label>Hostname </label>
								</div>
								<div class="col-sm-1 pl-0">:</div>
								<div class="col-sm-7 pl-0"> 
								<label class="label_field_data data_hostname"></label>	
								</div>
							</div>

							<div class="col-md-4 mt-5 row">
								<div class="col-sm-4 pl-0">
									<label>Asset Type </label>
								</div>
								<div class="col-sm-1 pl-0">:</div>
								<div class="col-sm-7 pl-0"> 
								<label class="label_field_data data_asset_type"></label>	
								</div>
							</div>
							
							<div class="col-md-4 mt-5 row">
								<div class="col-sm-4 pl-0">
									<label>Domain </label>
								</div>
								<div class="col-sm-1 pl-0">:</div>
								<div class="col-sm-7 pl-0"> 
								<label class="label_field_data data_domain"></label>	
								</div>
							</div>

							<div class="col-md-4 mt-5 row">
								<div class="col-sm-4 pl-0">
									<label>Serial Number </label>
								</div>
								<div class="col-sm-1 pl-0">:</div>
								<div class="col-sm-7 pl-0"> 
								<label class="label_field_data data_serial_number"></label>	
								</div>
							</div>

							<div class="col-md-4 mt-5 row">
								<div class="col-sm-4 pl-0">
									<label>Asset Code </label>
								</div>
								<div class="col-sm-1 pl-0">:</div>
								<div class="col-sm-7 pl-0"> 
								<label class="label_field_data data_asset_code"></label>	
								</div>
							</div>

							<!-- <div class="col-md-31 mt-5 hidden">
								<div class="col-sm-4 pl-0">
									<label>Location Type </label>
								</div>
								<div class="col-sm-1 pl-0">:</div>
								<div class="col-sm-7 pl-0"> 
								<label class="label_field_data data_location_type"></label>	
								</div>
							</div> -->
							
							<div class="col-md-4 mt-5 row">
								<div class="col-sm-4 pl-0">
									<label>CPU Make </label>
								</div>
								<div class="col-sm-1 pl-0">:</div>
								<div class="col-sm-7 pl-0"> 
								<label class="label_field_data data_cpu_make"></label>	
								</div>
							</div>

							
							<div class="col-md-4 mt-5 row">
								<div class="col-sm-4 pl-0">
									<label>Asset Age </label>
								</div>
								<div class="col-sm-1 pl-0">:</div>
								<div class="col-sm-7 pl-0"> 
								<label class="label_field_data data_asset_age"></label>	
								</div>
							</div>
							<div class="col-md-4 mt-5 row">
								<div class="col-sm-4 pl-0">
									<label>MAC Address </label>
								</div>
								<div class="col-sm-1 pl-0">:</div>
								<div class="col-sm-7 pl-0"> 
								<label class="label_field_data data_mac_address"></label>	
								</div>
							</div>
							<div class="col-md-4 mt-5 row">
								<div class="col-sm-4 pl-0">
									<label>CPU Speed </label>
								</div>
								<div class="col-sm-1 pl-0">:</div>
								<div class="col-sm-7 pl-0"> 
								<label class="label_field_data data_cpu_speed"></label>	
								</div>
							</div>

							<div class="col-md-4 mt-5 row">
								<div class="col-sm-4 pl-0">
									<label>Make </label>
								</div>
								<div class="col-sm-1 pl-0">:</div>
								<div class="col-sm-7 pl-0"> 
								<label class="label_field_data data_asset_make"></label>	
								</div>
							</div>
							<div class="col-md-4 mt-5 row">
								<div class="col-sm-4 pl-0">
									<label>Build Version </label>
								</div>
								<div class="col-sm-1 pl-0">:</div>
								<div class="col-sm-7 pl-0"> 
								<label class="label_field_data data_build_version"></label>	
								</div>
							</div>

							<div class="col-md-4 mt-5 row">
								<div class="col-sm-4 pl-0">
									<label>RAM </label>
								</div>
								<div class="col-sm-1 pl-0">:</div>
								<div class="col-sm-7 pl-0"> 
								<label class="label_field_data data_ram"></label>	
								</div>
							</div>
							<div class="col-md-4 mt-5 row">
								<div class="col-sm-4 pl-0">
									<label>Model </label>
								</div>
								<div class="col-sm-1 pl-0">:</div>
								<div class="col-sm-7 pl-0"> 
								<label class="label_field_data data_model"></label>	
								</div>
							</div>

							<!-- <div class="col-md-31 mt-5 hidden">
								<div class="col-sm-4 pl-0">
									<label>DNS Entry </label>
								</div>
								<div class="col-sm-1 pl-0">:</div>
								<div class="col-sm-7 pl-0"> 
								<label class="label_field_data data_dns_entry"></label>	
								</div>
							</div> -->
							<div class="col-md-4 mt-5 row">
								<div class="col-sm-4 pl-0">
									<label>Hard Disk </label>
								</div>
								<div class="col-sm-1 pl-0">:</div>
								<div class="col-sm-7 pl-0"> 
								<label class="label_field_data data_hard_disk"></label>	
								</div>
							</div>
							<!-- <div class="col-md-31 mt-5 hidden">
								<div class="col-sm-4 pl-0">
									<label>Hard disk Type</label>
								</div>
								 <div class="col-sm-1 pl-0">:</div>
								 <div class="col-sm-7 pl-0"> 
								<label class="label_field_data data_hard_disk_tpe"></label>
								</div>
							</div> -->
							
							<div class="col-md-4 mt-5 row">
								<div class="col-sm-4 pl-0">
									<label>Machine IP Address </label>
								</div>
								<div class="col-sm-1 pl-0">:</div>
								<div class="col-sm-7 pl-0"> 
								<label class="label_field_data data_ip_address"></label>	
								</div>
							</div>
							
							<div class="col-md-4 mt-5 row">
								<div class="col-sm-4 pl-0">
									<label>OS </label>
								</div>
								<div class="col-sm-1 pl-0">:</div>
								<div class="col-sm-7 pl-0"> 
								<label class="label_field_data data_os"></label>	
								</div>
							</div>
							<div class="col-md-4 mt-5 row">
								<div class="col-sm-4 pl-0">
									<label>Bit Type </label>
								</div>
								<div class="col-sm-1 pl-0">:</div>
								<div class="col-sm-7 pl-0"> 
								<label class="label_field_data data_bit_type"></label>	
								</div>
							</div>
						</div>
						<div class="row form_row">
							<hr>
							
							<div class="col-md-3">
								<div class="form-group">
									<label style='font-size: 15px !important;' for="status">User : <span class="text-required">*</span></label>
									<select class="form-control user_dropdown select2" style="width:100%" name="user_id" id="form_user" required>
										<option value="">SELECT</option>
										<?php foreach($getAllAdUser as $user){ ?>
											<option value="<?php echo $user['user_id']; ?>" data-user-name="<?php echo $user['emp_name']; ?>"><?php echo $user['username']; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class='col-sm-3 status_div'>
								<div class="form-group">
									<label style='font-size: 15px !important;' for='asset_remark_status'>Asset Remark: <span class="text-required">*</span></label>
									<select class="form-control select2" style="width:100%"  id="asset_remark_status" name="asset_remark_status" name='pro_name' required>
										<option value="">SELECT</option>
										<?php 
										//$Remarkarray = ['ALLOCATED FOR NEW BRANCH_WITHOUT BOX PACK','ASSET DELIVERED IN PROPER CONDITION','ASSET LOST-STOLEN','ASSET NOT FOUND','ASSET PHYSICALLY NOT AVAILABLE','BURN ASSET','DISPOSED ASSET','LIVE IN ICICI NETWORK DOMAIN','LIVE IN VO NETWORK','LIVE STANDALONE ASSET','PC IN STORE-DEPLOYABLE WITH BOX PACK','PC IN STORE-DEPLOYABLE WITH OUT BOX PACK','PC IN STORE-NON DEPLOYABLE WITHOUT BOX PACK','PHYSICAL DAMAGE ASSET','STANDBY ASSET ALLOTED TO USER','SURRENDER TO SCRAP VENDOR','USE-IN-ICICI NETWORK','USER ABSCONDED WITH ASSET','USER CUSTODY','WORK FROM HOME PROJECT'];
										
										// foreach(ALC_ASSET_REMARK_ARRAY as $remark){?>
										<!-- <option value="<?php //echo str_replace(' ', '_',$remark); ?>"><?php //echo $remark; ?></option> -->
										<?php 
										// }
										?>
									</select>
								</div>
							</div>

							
							<div class="col-md-3">
								<div class="form-group">
									<label style='font-size: 15px !important;' for="status">Location : <span class="text-required">*</span></label>
									<select class="form-control location_dropdown select2" style="width:100%" name="location_id" id="form_location" required>
										<option value="">SELECT</option>
										<?php foreach($locationNames as $loc){ ?>
											<option value="<?php echo $loc['location_id']; ?>" data-val-am-name = "<?php echo $loc['am_name']; ?>" ><?php echo $loc['location_name']; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>

							<div class="col-md-3">
								<div class="form-group">
								<label style='font-size: 15px !important;' for='edit_alc_areaM_name'>Area Manager: </label>
									<select class="form-control am_name_dropdown select2" style="width:100%" readonly disabled id="edit_alc_areaM_name" name="edit_alc_areaM_name" name='pro_name[]'>
										<option value="">SELECT</option>
										<?php $areaManagerName = $ALCAssetDetailsDAO->getLocationDetails();
												$am_list = array();
											foreach($areaManagerName as $areaManager){ 
												if(!in_array($areaManager['am_name'],$am_list)){
													array_push($am_list,$areaManager['am_name']);?>
												<option values="<?php echo $areaManager['id']; ?>"><?php echo $areaManager['am_name']; ?></option>
										<?php }
											} ?>
									</select>
								</div>
							</div>

							<div class="col-md-3 hidden">
								<div class="form-group">
								<input type="hidden" class="data_branch_id" id="branch_id" name="branch_id" value="0">

									<label for="status">Branch : <span class="text-required">*</span></label>
									<!-- <select class="form-control branch_dropdown select2" style="width:100%" name="branch_id" id="form_branch" required>
										<option value="">SELECT</option>
									</select> -->
								</div>
							</div>
							<!-- <div class="col-md-3">
								<div class="form-group">
									<label for="status">Department : <span class="text-required">*</span></label>
									<select class="form-control department_dropdown select2" style="width:100%" name="department_id" id="form_department" required>
										<option value="">SELECT</option>
									</select>
								</div>
							</div> -->

							<!-- <div class="col-md-4 hidden">
								<div class="form-group">
									<label for="">Comments :</label>
									<textarea class="form-control" id="comments" name="reason" placeholder="Comments" rows="3"></textarea>
								</div>
							</div> -->
						</div>
						<div class="row form_row user_details">
							
							<div class="col-md-3" style="font-size:11px !important"> 
								
								<div class="row">
									<div class="col-md-4"><hr></div>
									<div class="col-md-4">
										<label style="display: block; text-align: center; margin-top: 4px; font-size:13px;" for="asset_tag_sticker"> USER DETAILS </label>
									</div>
									<div class="col-md-4"><hr></div>
								</div>
								<div class="col-md-12 mt-5">
									<div class="col-sm-4 pl-0">
										<label>User Name </label>
									</div>
									<div class="col-sm-1 pl-0">:</div>
									<div class="col-sm-7 pl-0"> 
									<label class="label_field_data_1 user_data_emp_name"></label>	
									</div>
								</div>
								
								<div class="col-md-12 mt-5">
									<div class="col-sm-4 pl-0">
										<label>User Email </label>
									</div>
									<div class="col-sm-1 pl-0">:</div>
									<div class="col-sm-7 pl-0"> 
									<label class="label_field_data_1 user_data_emp_email"></label>	
									</div>
								</div>
								<div class="col-md-12 mt-5">
									<div class="col-sm-4 pl-0">
										<label>Location </label>
									</div>
									<div class="col-sm-1 pl-0">:</div>
									<div class="col-sm-7 pl-0"> 
									<label class="label_field_data_1 user_data_location"></label>	
									</div>
								</div> 
								<!-- <div class="col-md-12 mt-5">
									<div class="col-sm-4 pl-0">
										<label>Branch </label>
									</div>
									<div class="col-sm-1 pl-0">:</div>
									<div class="col-sm-7 pl-0"> 
									<label class="label_field_data_1 user_data_branch"></label>	
									</div>
								</div> -->
								<div class="col-md-12 mt-5">
									<div class="col-sm-4 pl-0">
										<label>Department </label>
									</div>
									<div class="col-sm-1 pl-0">:</div>
									<div class="col-sm-7 pl-0"> 
									<label class="label_field_data_1 user_data_department"></label>	
									</div>
								</div>
								<!-- <div class="col-md-12 mt-5">
									<div class="col-sm-4 pl-0">
										<label>State </label>
									</div>
									<div class="col-sm-1 pl-0">:</div>
									<div class="col-sm-7 pl-0"> 
									<label class="label_field_data_1 user_data_state"></label>	
									</div>
								</div>
								<div class="col-md-12 mt-5">
									<div class="col-sm-4 pl-0">
										<label>City </label>
									</div>
									<div class="col-sm-1 pl-0">:</div>
									<div class="col-sm-7 pl-0"> 
									<label class="label_field_data_1 user_data_city"></label>	
									</div>
								</div> -->

								<div class="col-md-12 mt-5">
									<div class="col-sm-4 pl-0">
										<label>Band </label>
									</div>
									<div class="col-sm-1 pl-0">:</div>
									<div class="col-sm-7 pl-0"> 
									<label class="label_field_data_1 user_data_band"></label>	
									</div>
								</div>
								
								<div class="col-md-12 mt-5">
									<div class="col-sm-4 pl-0">
										<label>Employee Type </label>
									</div>
									<div class="col-sm-1 pl-0">:</div>
									<div class="col-sm-7 pl-0"> 
									<label class="label_field_data_1 user_data_employee_type"></label>	
									</div>
								</div>
								
								
							</div>
							<div class="col-md-6" style="border-left: 2px solid #db7272; border-right: 2px solid #db7272; ">
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label style='font-size: 15px !important;' for="status">Workstation ID :</label>
										<input type="text" class="form-control" placeholder="Workstation ID" id="work_station_id" name="work_station_id"  minlength='2' maxlength='10' autocomplete="off" pattern="[a-zA-Z0-9]+" required title="Please enter only letters and/or numbers">
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label style='font-size: 15px !important;' for="status">Asset Physical Status : <span class="text-required">*</span></label>
										<select class="form-control" name="asset_physical_status" id="asset_physical_status" required>
											<option value="">SELECT</option>
											<option value="WORKING_FINE">WORKING FINE</option>
											<option value="NO_PHYSICAL_DAMAGE">NO PHYSICAL DAMAGE</option>
											<option value="DAMAGE_OBSERVED">DAMAGE OBSERVED</option>
										</select>
									</div>
								</div>
								<div class="col-md-6 hidden" id="damage_remark_main_div">
									<div class="form-group">
										<label for="status">Damage Remark : <span class="text-required">*</span></label>
										<input type="text" class="form-control" name="damage_remark" id="damage_remark" placeholder="Damage Remark">
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label style="display:block; margin-bottom: 14px; font-size: 15px;" for="asset_tag_sticker">Asset Tag Sticker :</label>
										<input type="radio" name="asset_tag_sticker" id="asset_tag_sticker_yes" value="YES" checked>
										<label style="margin-right:7px" for="asset_tag_sticker_yes">YES </label>
										<input type="radio" name="asset_tag_sticker" id="asset_tag_sticker_no" value="NO">
										<label for="asset_tag_sticker_no">NO</label>
										
										<input type="text" name="upload_tag_sticker_uploaded" id="upload_tag_sticker_uploaded" value='' class="hidden" />
										<input type="file" name="upload_tag_sticker" id="upload_tag_sticker" class="hidden" />
										<span class='ml-20 btn btn-outline-mdf2 upload_tag_sticker_icon'><i class="fa fa-cloud-upload" aria-hidden="true"></i> UPLOAD TAG STICKER</span>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label style="display:block; margin-bottom: 14px; font-size: 15px;" for="asset_tag_sticker">Service Tag :</label>
										<input type="radio" name="service_tag" id="service_tag_yes" value="WARRANTY" checked>
										<label style="margin-right:7px" for="service_tag_yes">WARRANTY </label></br>
										<input type="radio" name="service_tag" id="service_tag_no" value="OUT_OF_WARRANTY">
										<label for="service_tag_no">OUT OF WARRANTY</label>
									</div>
								</div>
								<div class="col-md-6 hidden">
									<div class="form-group">
										<label style="display:block; margin-bottom: 10px;" for="asset_tag_sticker">User Type :</label>
										<input type="radio" name="user_type" id="employee" value="EMPLOYEE" checked>
										<label style="margin-right:20px" for="employee">EMPLOYEE </label>
										<input type="radio" name="user_type" id="consultant" value="CONSULTANT">
										<label for="consultant">CONSULTANT</label>
									</div>
								</div>
								<div class="col-md-12">
									<div class="form-group">
										<label style='font-size: 15px !important;'for="status_remark">Status Remark :</label>
										<textarea class="form-control" name="status_remark" id="status_remark" cols="50" rows="2"></textarea>
									</div>
								</div>
							</div>
							</div>
							<div class="col-md-3">
								<label style="display: block; text-align: center; margin-top: 4px; font-size: 13px;" for="asset_tag_sticker"> LIST OF ACCESSORIES PROVIDED </label>
								<hr>
								<div class="row">
									<div class="col-md-12">
										<div class="form-group">
											<input type="checkbox" name="bag" id="bag">
											<label style="margin-right:20px" for="bag">Bag </label>
											<br>
											<input type="checkbox" name="charger" id="charger">
											<label style="margin-right:20px" for="charger">Charger / Adapter </label>
											<br>
											<div class="form-group">
												<label style='font-size: 15px !important;' for="status">Internet Dongle :</label>
												<select class="form-control" name="internet_dongle" id="internet_dongle" >
													<option value="">SELECT</option>
													<option value="AIRTEL">AIRTEL</option>
													<option value="JIO">JIO</option>
													<option value="OTHER">OTHER</option>
												</select>
											</div>
											<div class="form-group hidden">
												<label for="sim_number" class="sim_number_label" style='font-size:15px;'>SIM Number :</label>
												<input type="text" class="form-control" name="sim_number" id="sim_number" placeholder="SIM Number" autocomplete="off" maxlength="10" minlength="10">
											</div>
										</div>
									</div>
								</div>

							</div>


						</div>
					</div>
					<div class="modal-footer custom_modal_footer">
						<button type="button" class="btn btn-outline-danger" data-dismiss="modal">Cancel</button>
						<button type="submit" id="form_submit" class="btn btn-outline-success">Submit</button>
					</div>	
				</form>	
			</div>
		</div>
	</div>


	

	<div class="modal fade" id="view_asset_modal">
		<div class="modal-dialog modal-lg-90">
			<div class="modal-content">
				<!-- Modal Header -->
				<div class="modal-header">				
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title mb-5">Assigned Asset Details</h4>
				</div>
				
					<div class="modal-body custom_modal_body form_modal" >
						<div class="row dynamic_data" style = 'font-size:11px !important'>
							
						</div>
						
					</div>
					<div class="modal-footer custom_modal_footer">
						<button type="button" class="btn btn-outline-danger" data-dismiss="modal">Cancel</button>
					</div>	
				
			</div>
		</div>
	</div>

	<!-- <div class="add_data" id="add_data" data-toggle="tooltip" data-placement="left" title="Add Custom Column"><i class="fa fa-plus"></i></div> -->
<!-- Custom JS-->
<script type="text/javascript">

	// loadingOverlay();

	// function loadingOverlay(){
	// 	//Showing Loding Overlay Untill Page Load
	// 	$.LoadingOverlay("show",
	// 		{
	// 			background  : "rgba(10, 20, 30,0.5)",
	// 			imageColor : "rgb(52, 112, 192)",
	// 			text : "Loading....",
	// 			textColor : "rgb(52, 112, 192)",
	// 			size : "50px"
	// 		});
	// }


	$('#assign_asset_modal').on('hidden.bs.modal', function () {
		$('#assign_asset_form')[0].reset();
    
		// Reset Select2 inputs
		$('.select2').val(null).trigger('change');
	})

	$(document).ready(function(){

		$(".upload_tag_sticker_icon").on('click',function(){
			$("#upload_tag_sticker").click();
		});

		$("#upload_tag_sticker").change(function () {
			
			var file_size = this.files[0].size;
			var max_size = 500 * 1024;
			
			$("body .modal_footer_msg").remove();
			var fileExtension = ['jpeg', 'jpg', 'png'];
			if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
				$('#form_submit').addClass('hidden');
				$(".modal-footer").prepend('<span class="modal_footer_msg" style="max-width: 92%;">Allowed Formats : '+ fileExtension.join(", ") +'</span>');
			}
			else if(file_size > max_size) {
				$('#form_submit').addClass('hidden');
				$(".modal-footer").prepend('<span class="modal_footer_msg" style="max-width: 90%;">File size must be less than 500Kb</span>');
			}
			
			else{
				var fileInput = $('#upload_tag_sticker')[0];
				var formData = new FormData();
				var filename = $('.data_asset_code').text();
				if(filename == ''){
					$('#form_submit').addClass('hidden');
					$(".modal-footer").prepend('<span class="modal_footer_msg" style="max-width: 92%;">Asset Code should not be Empty</span>');
					return;
				}
				formData.append('file', fileInput.files[0]);
				formData.append('operation', 'upload_tag_sticker_file');
				formData.append('filename', filename);

				// loadingOverlay();
				$('.preloader-container').show();
				$.ajax({
					url : "../ajax/ALC_ajaxData.php",
					type : "POST", 
					data: formData,
					contentType: false,
					processData: false,
					success:function(res){ 
						// console.log('res : ' + res); 
						$('#form_submit').removeClass('hidden');
						$("body .modal_footer_msg").remove();
						$("#upload_tag_sticker_uploaded").val(res);
						// $.LoadingOverlay("hide", true);
						$('.preloader-container').hide();
					},
					error : function(jqXHR, textStatus, errorThrown) {
						bootbox.alert(errorThrown, function() {
							// $.LoadingOverlay("hide", true);
							$('.preloader-container').hide();
						});
						return false;
					}
				});

				
			}
		});

		$('#myDataTable').DataTable( {
			"language": {
				"search": "Filter records:",
				"searchPlaceholder" : "Search"
			},
			"lengthMenu": [50, 100, 500],
  	 		"pageLength": 50
		});
		
		// $.LoadingOverlay("hide", true);	 
		$('.preloader-container').hide();


		$("body").on('click','.assign_asset_btn',function(){
			// loadingOverlay();
            $('.preloader-container').show();
			let id = $(this).attr('data-val');
			$.ajax({
					url : "../ajax/ALC_ajaxData.php",
					type : "POST",
					data : {
						operation	: 'getAssetDetailsByID',                         
						id			: id,                              
					},
					success:function(res){  
						let res_arr = JSON.parse(res);
						// console.log(res_arr);
						$.each(res_arr, function(key, value) {
							if(key == 'hostname1'){
								//value = 'LGI2019NOT029238';
								let length_1 = value.length;
								let first_str_1 = value.substring(0, 3);
								//let second_str_1 = value.substring(3, 7);
								if (length_1 == 16 && (/^[a-zA-Z]*$/.test(first_str_1) == true)) {
									$('.data_'+key).text(value);
								} else{
									$('.data_'+key).addClass('not_valid');
									$('.data_'+key).text(value);
									$('#form_submit').addClass('hidden');
									$('.form_row').addClass('hidden');
									// $("#assign_asset_form input").prop("disabled", true);
									// $("#assign_asset_form select").prop("disabled", true);
									$(".modal-footer").prepend('<span class="modal_footer_msg">Hostname has not met Valid Criteria. Please do the changes in Physical machine.</span>')
								}
							}
							else{
								$('.data_'+key).text(value);
							}


							if(key == 'id' || key == 'nodeid' || key == 'hostname' || key == 'serial_number' || key == 'id'){
								$('.data_'+key).val(value);
								//$('.data_'+key).text('');
							}
							
							 
						});

						// $.LoadingOverlay("hide", true);
						$('.preloader-container').hide();

						$(".label_field_data_1").html('');
						$('.user_dropdown').val('');
						$('.user_dropdown').trigger('change');
						$('.am_name_dropdown').val('');
						$('.am_name_dropdown').trigger('change');
						$('#work_station_id').val('');
						$('#asset_physical_status').val('');
						$('#asset_tag_sticker_yes').trigger('click');
						$('#service_tag_yes').trigger('click');
						$('#status_remark').val('');
						$('#bag').prop('checked', false);
						$('#charger').prop('checked', false);
						$('#internet_dongle').val('');
						$('#sim_number').val('');
						$('#damage_remark').val('');
						
						$('#sim_number').parent().addClass('hidden');
						$('#assign_asset_modal').modal('toggle');
						
					},
					error : function(jqXHR, textStatus, errorThrown) {
						bootbox.alert(errorThrown, function() {
							// $.LoadingOverlay("hide", true);
							$('.preloader-container').hide();
						});
						return false;
					}
				});


				$.ajax({
					url: '../ajax/getAlcAllDetails.php',
					type: 'POST',
					dataType : 'json',
					data: {
						operation: 'getRemarksWithStatus',
						statusVal : 'LIVE'
					},
					success:function(res){
						console.log(res);
						let remarks = res.asset_remarks.split(',');
						let options = "";
						options += "<option value=''>--- Select Remark --- </option>";
						$.each(remarks, function(key,val){
							options += "<option value='"+val.toUpperCase().replaceAll(' ', '_')+"'>"+val.toUpperCase()+"</option>";
						});

						$('#asset_remark_status').select2("destroy");
						$('#asset_remark_status').html('');
						$('#asset_remark_status').append(options);
						$('#asset_remark_status').select2();
						// $('#asset_remark_status').val($('#edit_alc_assetStatus').attr('data-val-oldRemark')).trigger('change');
					},
					error : function(jqXHR, textStatus, errorThrown) {
                        alert(errorThrown, function() {
                        });
						// $.LoadingOverlay("hide", true);
						$('.preloader-container').hide();
                        return false;

                    }
				});
			
		});


		$("body").on('click','.view_asset_btn',function(){
			// loadingOverlay();
            $('.preloader-container').show();
			$(".dynamic_data").html('');
			let id = $(this).attr('data-val');
			$.ajax({
					url : "../ajax/ALC_ajaxData.php",
					type : "POST",
					data : {
						operation	: 'getAssetDetailsByIDtoView',                         
						id			: id,                              
					},
					success:function(res){  
						let res_arr = JSON.parse(res);
						// console.log(res_arr);
						
						$.each(res_arr, function(key, value) {
							//$('.data_'+key).text(value); 

							let final_key = capitalize(key);

							$(".dynamic_data").append(
								'<div class="col-md-4 mt-5 row">'+
								'<div class="col-sm-4 pl-0">'+
								'<label>'+final_key+' </label>'+
								'</div>'+
								'<div class="col-sm-1 pl-0">:</div>'+
								'<div class="col-sm-7 pl-0"> '+
								'<label class="label_field_data">'+value+' </label>'+
								'</div>'+
								'</div>'
							);
						});

						// $.LoadingOverlay("hide", true);	
						$('.preloader-container').hide();

						$('#view_asset_modal').modal('toggle');
					},
					error : function(jqXHR, textStatus, errorThrown) {
						bootbox.alert(errorThrown, function() {
							// $.LoadingOverlay("hide", true);
							$('.preloader-container').hide();
						});
						return false;
					}
				});
			
		});

		function capitalize(str) {
			keywords = ['ram','ip','sim','cpu','mac','os','id'];
			strVal = '';
			str = str.split('_');
			for (var chr = 0; chr < str.length; chr++) {
				if(jQuery.inArray(str[chr], keywords) !== -1){
					strVal +=str[chr].toUpperCase() + " ";
				}else{
					strVal += str[chr].substring(0, 1).toUpperCase() + str[chr].substring(1, str[chr].length) + ' ';
				}
			}
			return strVal;
		}

		$('#asset_physical_status').on('change',function(){
			let val = $(this).find(":selected").val();
			if(val == 'DAMAGE_OBSERVED'){
				$('#damage_remark_main_div').removeClass('hidden');
				$('#damage_remark').attr('required','required');
			}else{
				$('#damage_remark_main_div').addClass('hidden');
				$('#damage_remark').val('');
				$('#damage_remark').removeAttr('required');
			}
		});
		

		$('#internet_dongle').on('change',function(){
			let val = $(this).find(":selected").val();
			$(".sim_number_label .text-required").remove();
			if(val != ''){
				$('#sim_number').parent().removeClass('hidden');
				$('#sim_number').attr('required','required');
				$(".sim_number_label").append(' <span class="text-required">*</span>');
			}else{
				$('#sim_number').parent().addClass('hidden');
				$('#sim_number').removeAttr('required');
			}
		});


		$("input[name=asset_tag_sticker]").on('click',function(){
			//console.log($('input[name="asset_tag_sticker"]:checked').val());
			//console.log($(this).val());
			$("body .modal_footer_msg").remove();
			let val = $(this).val();
			if(val == 'NO'){
				$('#form_submit').addClass('hidden');
				$(".modal-footer").prepend('<span class="modal_footer_msg">Asset cannot be assigned to any user without ASSET TAG STICKER.</span>');
			}else{
				$('#form_submit').removeClass('hidden');
				$("body .modal_footer_msg").remove();
			}

		});


		$('.datepicker').datepicker();

		$('.select2:not([disabled])').select2({ });

		$(".div_tooltip").tooltip();
		

		$('.am_name_dropdown11').change(function(){
				var am_text = $(this).find(":selected").text();
				let selected_1 = $(this).attr('data-selected');
				if(am_text != ''){
					
					// loadingOverlay();
					$('.preloader-container').show();
					$.ajax({
						url : "../ajax/getAlcAllDetails.php",
						type : "POST",
						dataType : 'json',
						data : {
							operation	: 'getlocation_by_areaM',                         
							areaManager_text : am_text
						},		
						success:function(res){
							// $.LoadingOverlay("hide", true);
							$('.preloader-container').hide();
							let options = '';
							$.each(res, function(key, value) {
								options += '<option value="'+value.id+'">'+value.location+'</option>';
							});
							cl_name = 'location_dropdown';
							setSelect2(cl_name,options);
						},
						error : function(jqXHR, textStatus, errorThrown) {
							bootbox.alert(errorThrown, function() {
								// $.LoadingOverlay("hide", true);
								$('.preloader-container').hide();
							});
							return false;
						}
					});
				}
				else {
					let options = '';
					cl_name = 'location_dropdown';
					setSelect2(cl_name,options);
				}
			})

		$('.location_dropdown').on('change',function(){
			
			let location_id = $(this).find(":selected").val();
			let am_name = $(this).find(":selected").attr('data-val-am-name');
			 
			if(location_id != ''){
				// loadingOverlay();
				$('.preloader-container').show();

				$(".am_name_dropdown").val(am_name).trigger('change');
				// console.log(am_name);
				// $(".am_name_dropdown").trigger('change');
				// $(".am_name_dropdown").select2('destroy');

				// $.LoadingOverlay("hide", true);
				$('.preloader-container').hide();
				return;

				$.ajax({
					url : "../ajax/ALC_ajaxData.php",
					type : "POST",
					data : {
						operation	: 'get_branch_by_location',                         
						location_id	: location_id,                         
					},
					success:function(res){  
						let res_arr = JSON.parse(res);
							// console.log(res_arr);
						// $.LoadingOverlay("hide", true);
						$('.preloader-container').hide();
						let options = '';
					
						$.each(res_arr, function( index, value ) {
							options += '<option value="'+value.branch_id+'" data-val-dep-ids="'+value.department_ids+'">'+value.branch_name+'</option>';
						});
						cl_name = 'branch_dropdown';
						setSelect2(cl_name,options);
					},
					error : function(jqXHR, textStatus, errorThrown) {
						bootbox.alert(errorThrown, function() {
							// $.LoadingOverlay("hide", true);
							$('.preloader-container').hide();
						});
						return false;
					}
				});
			} else{
				let options = '';
				cl_name = 'branch_dropdown';
				setSelect2(cl_name,options);
			}
		});
		
		let department_arr = [];
		<?php foreach($departmentNames as $department) { ?>
			department_arr[<?php echo $department['department_id']; ?>] = "<?php echo $department['department_name']; ?>";
		<?php } ?>
		
		$('.branch_dropdown1111').on('change',function(){
			
			// loadingOverlay();
            $('.preloader-container').show();
			let branch_id = $(this).find(":selected").val();
			// let dep_id = $(this).find(":selected").attr('data-val-dep-ids');
			// if(dep_id != undefined){
			// 	dep_id = dep_id.split(',');
			// }
			
			if(branch_id != '' && branch_id != undefined){
				let options = '';
				let cl_name = 'department_dropdown';
				$.each(dep_id, function( index, value) {
					options += '<option value="'+value+'" id="dept__'+value+'">'+department_arr[value]+'</option>';
				});
				// setSelect2(cl_name,options);
				// setTimeout(function(){
				// 	$.LoadingOverlay("hide", true);
				// }, 300);
			} else{
				// let options = '';
				// let cl_name = 'department_dropdown';
				// setSelect2(cl_name,options);
				// setTimeout(function(){
				// 	$.LoadingOverlay("hide", true);
				// }, 300);
			}
		});

		// $('.department_dropdown').trigger('change');

		$('.user_dropdown').on('change',function(){
			let user_id = $(this).find(":selected").val();
			
			if(user_id != ''){
				// loadingOverlay();
				$('.preloader-container').show();
				$.ajax({
					url : "../ajax/ALC_ajaxData.php",
					type : "POST",
					data : {
						operation	: 'get_user_details_by_user_id',                         
						user_id	: user_id,                         
					},
					success:function(res){ 
						let res_arr = JSON.parse(res);
						// console.log(res_arr);
						$.each(res_arr, function(key, value) {
							$('.user_data_'+key).text(value);
						});

						// $.LoadingOverlay("hide", true);	
						$('.preloader-container').hide();
					},
					error : function(jqXHR, textStatus, errorThrown) {
						bootbox.alert(errorThrown, function() {
							// $.LoadingOverlay("hide", true);
							$('.preloader-container').hide();
						});
						return false;
					}
				});
			} 
		});

		$('#assign_asset_form').on('submit',function(){
			$("body .modal_footer_msg").remove();
			event.preventDefault();
			if($('#upload_tag_sticker_uploaded').val() == ''){
				$('#form_submit').addClass('hidden');
				$(".modal-footer").prepend('<span class="modal_footer_msg">Please Upload ASSET TAG STICKER.</span>');
				return;
			}
			let formValues = $('#assign_asset_form').serializeArray();

			// formValues.name = 'user_name';
			
			formValues.push({name : 'username', value : $("#form_user").find(":selected").text()});
			formValues.push({name : 'user_name', value : $("#form_user").find(":selected").attr('data-user-name')});
			formValues.push({name : 'location_name', value : $("#form_location").find(":selected").text()});
			formValues.push({name : 'branch_name', value : $("#form_branch").find(":selected").text()});
			formValues.push({name : 'department_name', value : $("#form_department").find(":selected").text()});

		
			let data_ip = [];
			$.each(formValues, function() {
				data_ip[this.name] = this.value;
				//console.log(this.name + ' : ' + this.value);
			});
				// console.log(data_ip);
			
			// return;
		
			// loadingOverlay();
            $('.preloader-container').show();
			//if(data_ip){
				$.ajax({
					url : "../ajax/ALC_ajaxData.php",
					type : "POST",
					data : {
						operation			: 'insert_user_asset_assign_process',                         
						data_ip				: formValues,                 
						draobhsad			: "<?php echo $draobhsad ?>",                         
					},
					success:function(res){                  
						if(res.trim() == 1){
							// $.LoadingOverlay("hide", true);	
							$('.preloader-container').hide();
							bootbox.alert('User Asset Mapped Successfully..!!',function(){
								// loadingOverlay();
								$('.preloader-container').show();
								window.location.reload();
							});
						} 
						else{
							// $.LoadingOverlay("hide", true);	
							$('.preloader-container').hide();
							bootbox.alert('Something went wrong. Try again later..!!',function(){
								// loadingOverlay();
								$('.preloader-container').show();
								window.location.reload();
							});
						}
					},
					error : function(jqXHR, textStatus, errorThrown) {
						bootbox.alert(errorThrown, function() {
							// $.LoadingOverlay("hide", true);
							$('.preloader-container').hide();
						});
						return false;
					}
				});
			// } 
			// else{
			// 	bootbox.alert('Something went wrong. Try again later..!!');
			// }

		
		});


		function setSelect2(cl_name,options=''){
			let selected_2 = $("."+cl_name).attr('data-selected');
			
			if($("."+cl_name+'[disabled]').html() != undefined){
				$("."+cl_name).html('');
				$("."+cl_name).append('<option value="">SELECT</option>');
				if(options !=''){
					$("."+cl_name).append(options);
				}
				$("."+cl_name).val(selected_2);
			} else{
				$("."+cl_name).html('');
				$("."+cl_name).append('<option value="">SELECT</option>');
				if(options !=''){
					$("."+cl_name).append(options);
				}
				$("."+cl_name).select2('destroy'); 
				$("."+cl_name).select2();
			}
			//console.log('cl_name : '+ cl_name);
			//console.log('selected_2 : '+ selected_2);
			$("."+cl_name).trigger('change');
		}

		
		//help_btn
		$('body').on('click', '#help_btn', function(){
			$('#errorAlert').slideToggle("slow");
		})
		
		$('body').on('click', '#close_alert', function(){
			$('#errorAlert').slideUp("slow");
		})

		$("#sim_number").on('keypress',function(event){	
			$(".sim_number_error").remove();
			var charCode = event.which;
			if (charCode > 31 && (charCode < 48 || charCode > 57)){
				$(this).parent().append('<span class="sim_number_error" style="position:absolute; color:#ff4444">Only Numbers Allowed..</span>');
				event.preventDefault();
			} 
		});

		$("#sim_number").on('paste', function(e) {
			var sim_no = e.originalEvent.clipboardData.getData('text');
			$(".sim_number_error").remove();
			if(!$.isNumeric(sim_no)){
				e.preventDefault();
				$(this).parent().append('<span class="sim_number_error" style="position:absolute; color:#ff4444">Alphanumeric Not Allowed..</span>');
			}
			else if(sim_no.length > 10){
				e.preventDefault();
				$(this).parent().append('<span class="sim_number_error" style="position:absolute; color:#ff4444">Sim Number Length should be 10 Digits..</span>');
			}
		});

		
	});     
	


</script>
<?php
	require_once ROOT_PATH.'/view/footer.php';
	ob_flush();
?>
