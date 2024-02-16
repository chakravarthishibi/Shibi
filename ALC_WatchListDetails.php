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

	//If session of app_root not set , then redirecting to login page
	if(!isset($_SESSION['app_root']))
	header('Location:login.php');
	define('ROOT_PATH', $_SESSION['app_root']);
	require_once ROOT_PATH.'/view/header.php';
	require_once ROOT_PATH.'/lib/common/session_inc.php';

	$get_roleid = $_SESSION['roleid_session']; 
		
	//checking sessions and updating lastaccessedtime , also including required files
	check_session();
	$_SESSION['lastaccessedtime'] = time();
	require_once ROOT_PATH.'/lib/utils/checkForVulnerability.php';
	require_once ROOT_PATH.'/lib/dao/ALCWatchListDetailsDAO.php';
	require_once ROOT_PATH.'/view/nocsrf.php';
	
	$apad = new AlcWatchListDetailsDAO;
	$assetDetails = $apad->getAssetDetails();
	$draobhsad = NoCSRF::generate('draobhsad');

	//Check For Security Issues , IF found redirect to 403
	if(checkForVulnerability::checkVulnerabilityURL_Ref($_SERVER['REQUEST_URI'],$_SERVER['HTTP_USER_AGENT'],$_SERVER['HTTP_REFERER']) == -1)
	{
		header('Location: '.URL_ROOT_PATH.'/view/403');
	}
	if(isset($_SESSION["responce_msg"])){
		echo "<script>alert('". $_SESSION["responce_msg"]."')</script>";
		unset($_SESSION['responce_msg']); 
	}
	

	if(ASSET_LIFE_CYCLE != 'enable') {
		header("Location:noaccess");
	}
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

<link rel="stylesheet" type="text/css" href="../asset/datatables/datatables.min.css"/>
<script type="text/javascript" src="../asset/datatables/datatables.min.js"></script>


<style>
	#container2 {
			padding-left: 10px;
			padding-top: 10px;
			/* background-color: #0b1520; */
			margin: 10px;
			color : #fff;
		}

	#addassets{
		border: 1px solid #4caf50;
		border-radius: 8px;
		transition: 0.3s ease;
		color:white;
		cursor: pointer;
		float: right;" 
	}

	#addassets:hover{
		background-color: unset !important;
		transform: scale(1.1);
	}
	#alc_value td {
		color: #fff;
		font-size: 13px;  
	}

	#alc_value tr:hover {
		background-color: unset;
			}

	#alc_value td:empty {
	display: none;
	height: 0;
	width: 0;
	padding: 0px;
	}
	#myDataTable th{
		/* cursor: pointer; */
		padding-right: 26px;
		position: relative;
		text-transform: Uppe;
		font-size: 13px !important;
	}

	/* #myDataTable td{

		font-size: 13px !important;
	} */

	#send:hover, #reset:hover, #search:hover{
		transform: scale(1.2);
		transition: transform .3s;
	}
	.error{
			color:#ff6666 !important;
			padding-top: 5px;
		}

	#myDataTable tbody th{
		font-size: 13px;
	}

</style>

	<div class="card">
		<div class="card-header">
			<button type="button" id="help_btn" class="btn btn-box-tool"  style="width: 80px;background-color: #337ab7;">Help</button>
			<?php if($get_roleid != 11 && $get_roleid != 13 && $get_roleid != 12){ ?>
				<button type="button" id="addassets" class="btn btn-box-tool lt_btn lt_btn_success" data-toggle="modal" data-target="#display_modal" style="width: 170px;background-color: green;margin-right: 20px"> Add Assets to Track</button>
			<?php } ?>
			<h3 class="mb-0">Watchlist Assets</h3>
		</div>
		<div class="card-body">
			<div id="errorAlert" class="alert hide-item text-center" role="alert">
			<?php echo ALC_WatchListDetails_HelpText; ?>
			<button type="button" class='close' id='close_alert'>&times;</button>
			</div>
			
			
			<div >
				<table id="myDataTable" class="table table-bordered table-striped table-hover">
					<thead>
						<tr>
						<th>Hostname</th>
						<th>Username</th>
						<th>IP Address</th>
						<th>Group</th>
						<th>Status</th>
						<th>Serial Number</th>
						<!-- <th>ALC All Asset ID</th> -->
						<?php if($get_roleid != 11 && $get_roleid != 13 && $get_roleid != 12){ ?>
						<th style="width:70px; text-align-last: center;">Action</th>
						<?php } ?>
						</tr>
					</thead>
		
					<tbody>
						<?php
						foreach($assetDetails as $arr1){ 
							$del = ""; ?>
							<tr>
								<?php 
									foreach($arr1 as $key => $col1){ ?>
										<?php
										if($key == 'nodeid'){ 
											if($get_roleid != 11 && $get_roleid != 13 && $get_roleid != 12){ 
												$del = '<td><div class="tab_delete icon-2 lt_btn lt_btn_danger lt_btn_round"  data-val="'.$col1.'" data-toggle="tooltip" data-title="Delete" style="margin-left: 30;"><i class="fa fa-trash" ></i></div></td>';
											}
										} else{ 
											if($key == 'status'){
												$class_n = "success";
												if($col1 == 'Inactive'){
													$class_n = "danger";
												}
												?>
											<td><div class="btn btn-sm btn-outline-<?php echo $class_n; ?>-disabled"><?php echo $col1; ?></div></td>
											<?php } else {?>
												<td><?php echo $col1; ?></td>
										<?php }
										}
									}
									echo $del;
									?>
										
								</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	
	
	




	<!-- Modal -->
	<div class="modal fade" id="display_modal">
    	<div class="modal-dialog">
    
			<!-- Modal content-->
			<div class="modal-content" style="width: 130%;">
			        
					<div class="modal-header" style="padding-bottom: 2px;">
						<button type="button" class="close" id="close"  data-dismiss="modal">&times;</button>
						<h4 class="modal-title" style='font-size: 18px !important;'>Add Assets to Track</h4>
					</div>

					<!-- Modal Body-->
					<form class="alc" method="POST" action="ALCWatchListDetailsDAO.php" id="Add_watchlistForm" style="margin-bottom: 0px;" >
					<div class="form-errors text-center"></div>
					    <div class="modal-body" style="padding: 10px;">

										<div class="col-md-6" Style="width: 40%;">
											<div class="right-inner-icon">
												<i class="icon-searchr"></i>
												<input type="text" name="search_name"  id="search_name"  placeholder="Search Hostname"  required /></n>
											</div>		
										</div>

										<div class="col-md-6" Style="width: 40%;">
											<div class="right-inner-icon">
												<i class="icon-searchr"></i>
												<input type="text" name="search_id" id="search_id"  placeholder="Search Serial Number"  required /></n>
											</div>		
										</div>

										<div>
											<input type="button" name="search" id="search" class="btn btn-primary-go"  value="Search" />
							            </div>

										<div class="modal_body_row" id="tabledata">
											<table id="DataTable" class="table table-bordered table-striped " style="display:none;margin-top: 20;">
												<thead>
													<tr style= "color: #6ca2ea;">
														<th style="width: 10;"><input type="checkbox" name="chkbox" id="selectall" data-toggle="tooltip" data-title="Select All"></th>
														<th id="host" >Hostname</th>
														<th id="username">Username</th>
														<th id="serial">Serial No</th>
														</tr>
												</thead>
											    <tbody id="alc_value"></tbody>
										    </table>	
							    		</div>
					
				        </div>

						<div class="modal-footer custom_modal_footer">
							<input type="button" id="send" class="btn btn-primary-go" value="Submit" onclick="myFunction(this.form)" style="margin:0px!important;float:right;display:none;"/>
							<button type="button" id="reset" class="btn btn-primary-go" style="float:left;">Reset</button>
						</div>

					</form>

			</div>
		</div>
	</div>
		
	<div class="hidden" id="hidden_div"></div>

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

	
	$(document).ready(function(){
		$("#myDataTable").DataTable({
				language: {searchPlaceholder: "Search"},
				lengthMenu: [50, 100, 500] //display row counts
			});
			//Hiding Loading overlay After page load
			// $.LoadingOverlay("hide", true);	 


			//Fetch the searched data from database using ajax call:
			$("#search").click(function(){

					var id = $("#search_id").val();
					var hostname = $("#search_name").val();
					var draobhsad ='<?php echo $draobhsad;?>';

					if($('#Add_watchlistForm').valid()){
					
					$.ajax({
						url:"../ajax/AlcWatchListAjax.php",
						type: "POST",
						async:"true",
						data: {id :id, hostname: hostname, operation:'watch_list_search_details', draobhsad : draobhsad},
						success: function(response) 
						{
						
							let trHTML = ''; 
							let spanHTML = ''; 
							//console.log(response);		
							let data_1 = JSON.parse(response);
							//console.log(data_1);
							if(data_1 != "")
							{
								$.each(data_1, function (i){
									trHTML += '<tr>';
									let hidden_ip = "";
									let hidden_ip_id = "";
									$.each(data_1[i], function (key, val) {
										
										if(key == 'nodeid'){
											hidden_ip_id = val;
											trHTML += '<td><input type="checkbox" name="chkbox" id="singlecheck" value="'+val+'" /><td>';
										}else{

										trHTML += '<td>'+val+'</td>';
										}

										hidden_ip += val+",";
									});

									spanHTML += '<span id="id_'+hidden_ip_id+'" data-val="'+hidden_ip+'"></span>';
									trHTML += '</tr>';
									
								});

									$('#alc_value').html(trHTML);
									$('#hidden_div').html(spanHTML);
									$('#DataTable').show();
									$('#send').show();
									$("#display_modal").modal('show');
						
							}else{
								bootbox.alert('Could not find the data for the given input.');	
							}
						}
					});

				}

		});

			//To reset the text filed:
						$('#reset').click( function() {
						$("input[type=text], textarea").val("");
						$('input').prop('checked', false);
						$('#alc_value').html("");
						$('#DataTable').hide();
						$('#send').hide();
						$('#hidden_div').html("");
						$('body .form-errors').text('');
							});
			//when modal is closed rest all.
						$('#display_modal').on('hidden.bs.modal', function () {
						$("input[type=text], textarea").val("");
						$('#alc_value').html("");
						$('#DataTable').hide();
						$('#send').hide();
						$(this).find('form').trigger('reset');
						$(".form-errors").hide();
						});
			 
	});


//Validator Method to allow only alphanumberic value
	jQuery.validator.addMethod("alphanumeric", function( value, element ) 
			{
				var regex = new RegExp("[^a-zA-Z0-9-.]");
				var key = value;
	
				if (regex.test(key)) 
				{
					return false;
				}
	
				return true;
	
			}, "Special characters and spaces are not allowed");

     

	//select all checkboxes.
	$('#selectall').change(function() {
		if($(this).prop('checked')){
			$('input').prop('checked', true);
		}else{$('input').prop('checked', false);}
	});

	$('body').on('click','#singlecheck',function(){
        //console.log($(this).find('.policy_id_checkbox').is(':checked'));
        if($(this).is(':checked') == false){
            $('body #selectall').prop('checked', false);
        }
    });

    //Form validation for input search fields.
	$('#Add_watchlistForm').validate({
				ignore: false,
				groups: {
					searchInputs: 'search_id search_name'
				},
				rules:{
					search_id: {
						required: '#search_name:blank',
						alphanumeric: true
					},
					search_name: {
						required: '#search_id:blank',
						alphanumeric: true
					},
				},
				messages: {
					search_id: {required: 'Please enter either Hostname or Serial Number'},
					search_name: {required: 'Please enter either Hostname or Serial Number'}
				},
				errorElement : 'div',
    			errorLabelContainer: '.form-errors'
			});

	
	//To update the selected info in modal to DB
    function myFunction(frm) 
	{
		
			var values = "";

			let insertable_values = '';

			for (var i = 0; i < frm.chkbox.length; i++)
			{

				if (frm.chkbox[i].checked)
				{
						values = frm.chkbox[i].value ;
						if(values != 'on'){
							idd = 'body #id_'+values;
							
							val2 = $(idd).attr('data-val');
							insertable_values += val2+"||";
						}
				}	
    		}
				event.preventDefault();
				//console.log(insertable_values);
				if(insertable_values == ""){
					bootbox.alert('Please select atleast one asset.');
				}else
				{
					$.ajax({
							url:"../ajax/AlcWatchListAjax.php",
							type: "POST",
							async:"true",
							data: {insert:insertable_values, operation:'watch_list_update_details'},
							success: function(response){
								bootbox.alert("Assets Added Successfully!", function()
								{
									window.location.reload();
								});		
							}

						});
				}
		}

		/**************** To Delete the row *****************/
		$('body').on('click','.tab_delete',function(){
			$(this).addClass('delete_me');

			bootbox.confirm("Are you sure you want to delete this?", function (result) {
                if (result) {

					let nodeid = $('.delete_me').attr('data-val');

					$.ajax({
						url : "../ajax/AlcWatchListAjax.php",
						type : "POST",
						data : {
							operation	: 'watch_list_delete_details',                         
							nodeid		: nodeid,                         
						},
						success:function(res){                  	
								bootbox.alert('Asset Deleted Successfully..!!',function(){
									$('.tab_delete').removeClass('delete_me');
									window.location.reload();
								});
							}
						});		
				 }
            });
		});

		//help_btn
		$('body').on('click', '#help_btn', function(){
			// bootbox.alert("Lorem ipsum dolor sit amet consectetur adipisicing elit. Aliquid eos obcaecati laborum nemo, itaque excepturi?",function() 
			// {
			// 	location.reload();
			// });
			$('#errorAlert').slideToggle("slow");
		})
		
		$('body').on('click', '#close_alert', function(){
			$('#errorAlert').slideUp("slow");
		})

</script>
<?php
	require_once ROOT_PATH.'/view/footer.php';
	ob_flush();
?>



