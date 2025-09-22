<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<!-- _ci_view: <?php echo $_ci_view;?> -->
<title><?php echo $title;?></title>
<?php $this->load->view('admin/common/meta_tags'); ?>
<?php $this->load->view('admin/common/before_head_close'); ?>

<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
$( function() {
$( "#from,#until" ).datepicker({ dateFormat: 'dd-mm-yy' ,changeMonth: true,changeYear: true,yearRange: "<?php echo date('Y');?>:2050"});
$( "#tgl_period" ).datepicker({ dateFormat: 'dd-mm-yy' ,changeMonth: true,changeYear: true,yearRange: "1940:<?php echo date('Y')+5;?>"});
$( "#tgl_period2" ).datepicker({ dateFormat: 'dd-mm-yy' ,changeMonth: true,changeYear: true,yearRange: "1940:<?php echo date('Y')+5;?>"});

$( "#from,#until" ) .change(function () {    
	var from = $("#from").val();
	var to = $("#until").val();
	
	var parts = from.split("-");
	var st = new Date(parseInt(parts[2], 10),
					  parseInt(parts[1], 10) - 1,
					  parseInt(parts[0], 10));
	var parts2 = to.split("-");
	var et = new Date(parseInt(parts2[2], 10),
					  parseInt(parts2[1], 10) - 1,
					  parseInt(parts2[0], 10));				  
	
	//alert(st +' '+ et);
	if(from!='' && to!=''){
		if(st > et){
		   alert("Invalid Date Range");
		   $(this).val('');
		}
	}
});  

} );

function load_quick_period(id,tgl,tgl2,id_pay){
	$('#quick_period').modal('show');
	$('#errboxPeriod').text('')
	$('#errboxPeriod').hide()
	$("#id_period").val(id);
	$("#id_pay").val(id_pay);
	
	if(tgl!='0000-00-00' && tgl2!='0000-00-00'){
		var today = new Date(tgl);
		var dd = today.getDate();
		var mm = today.getMonth() + 1; 
		var yyyy = today.getFullYear();
		if (dd < 10) {
		  dd = '0' + dd;
		} 
		if (mm < 10) {
		  mm = '0' + mm;
		} 
		var today = dd + '-' + mm + '-' + yyyy;
		
		$("#tgl_period").val(today);
		
		
		var today2 = new Date(tgl2);
		var dd2 = today2.getDate();
		var mm2 = today2.getMonth() + 1; 
		var yyyy2 = today2.getFullYear();
		if (dd2 < 10) {
		  dd2 = '0' + dd2;
		} 
		if (mm2 < 10) {
		  mm2 = '0' + mm2;
		} 
		var today2 = dd2 + '-' + mm2 + '-' + yyyy2;
		
		$("#tgl_period2").val(today2);
	}
}

function load_quick_payment_detail(p1,p2,p3,p4,p5,p6,p7){
	$('#quick_detail_payment').modal('show');
	$("#p1").html(p1);
	$("#p2").html(p2);
	$("#p3").html(p3);
	$("#p4").html(p4);
	$("#p5").html(p5);
	$("#p6").html(p6);
	$("#p7").html(p7);
}

function savesetperiod(){
	var tgl_period   =  $('#tgl_period').val();
	var tgl_period2  =  $('#tgl_period2').val();
	var id_period  	 =  $('#id_period').val();
	var id_pay  	 =  $('#id_pay').val();
	$.ajax({
		type: "POST",
		url: '<?php echo site_url('admin/members/her_setperiod')?>',
		contentType: "application/json; charset=utf-8",
		dataType: "json",
		async: true,
		data: JSON.stringify({id:id_period,id_pay:id_pay,tgl_period:tgl_period,tgl_period2:tgl_period2}),
		success: function(response, status) {
			console.log(response);
			//response = JSON.parse(response);
			if (response.status) {
				$('#errboxPeriod').text(response.message);
				$('#errboxPeriod').show();
				location.reload();
			} else {
				$('#errboxPeriod').text('Error:' + response.message);
				$('#errboxPeriod').show();
			}
		},
		error: function(jqXHR, exception) {
			console.log(jqXHR);
			var error_msg = '';
			if (jqXHR.status === 0) {
				error_msg = 'Not connect.\n Verify Network.';
			} else if (jqXHR.status == 403) {
				error_msg = 'Not authorized. [403]';
			} else if (jqXHR.status == 404) {
				error_msg = 'Requested page/resource not found. [404]';
			} else if (jqXHR.status == 500) {
				error_msg = 'Internal Server Error [500].';
			} else if (exception === 'parsererror') {
				error_msg = 'Requested JSON parse failed.';
			} else if (exception === 'timeout') {
				error_msg = 'Time out error.';
			} else if (exception === 'abort') {
				error_msg = 'Ajax request aborted.';
			} else {
				error_msg = '<br/>\n' + jqXHR.responseText;
			}
			$('#errboxPeriod').html('<strong>Failed: </strong>' + error_msg);
			$('#errboxPeriod').show();
		}		
	});
	
	//$('#quick_period').modal('toggle');
	//window.location.href = "<?php echo base_url();?>admin/members";
}

function load_quick_status(id){
	$('#quick_status').modal('show');
	$("#id_status").val(id);
}

function savesetstatus(){
	var status   	=  $('#status').val();
	var remarks   	=  $('#remarks').val();
	var id_status  	=  $('#id_status').val();
	
	var dataHTML 	= 'not valid';
	$.ajax({
		url: '<?php echo site_url('admin/members/setherstatus')?>',
		dataType: "html",
		type: "POST",
		async: true,//false
		data: {id:id_status,status:status,remarks:remarks},
		success: function(jsonObject,status) {
			console.log(jsonObject);
			if((jsonObject!='not valid')){
				dataHTML = jsonObject;
			}
			
			if(dataHTML=='not valid')
				alert('not valid');
			location.reload();
		}
	});
	
	//$('#quick_profile').modal('hide');
	$('#quick_status').modal('toggle');
	
	//window.location.href = "<?php echo base_url();?>admin/members";
}
</script>


</head>
<body class="skin-blue">
<?php $this->load->view('admin/common/after_body_open'); ?>
<?php $this->load->view('admin/common/header'); ?>
<div class="wrapper row-offcanvas row-offcanvas-left">
<?php $this->load->view('admin/common/left_side'); ?>
<!-- Right side column. Contains the navbar and content of the page -->
<aside class="right-side"> 
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1> Members Management 
      <!--<small>advanced tables</small>--> 
    </h1>
    <ol class="breadcrumb">
      <li><a href="<?php echo base_url('admin/dashboard');?>"><i class="fa fa-dashboard"></i> Home</a></li>
      <!--<li><a href="#">Examples</a></li>-->
      <li class="active">Manage Members</li>
    </ol>
  </section>
  
  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">HER Members / Perpanjangan Anggota PII</h3>
            <!--Pagination-->
            <div class="paginationWrap"> <?php echo ($result)?$links:'';?></div>
          </div>
          
          <!-- /.box-header -->
          <div class="box-body table-responsive" style="overflow:scroll;">
            <?php $this->load->view('admin/common/member_quick_search_bar_her_kta'); ?>
            <div class="clearfix text-right" style="padding:10px;"> 
            <?php
				//if(!isset($_GET['industry_ID'])):
			?>
            Total Records: <strong><?php echo $total_rows;?>
            
            <?php //endif;?>
            </strong> </div>
            <table id="example2" class="table table-bordered table-hover">
              <thead>
                <tr>
				  <th>Tanggal Pengajuan test</th>
				  <th>Wilayah | Cabang </th>
				  <th>BK / HKK</th>
				  <th>No KTA</th>
				  <th>Period</th>
				  
                  <th>Name/Email Address</th>
                  <!--<th>Gender</th>
                  <th align="center">ID</th>-->
                  <th>Date of Birth</th>
				  <th>SIP</th>
				  <th>Warga</th>
				  <th>Dokumen</th>
				  <th>Payment Status</th>
				  <th>Deskripsi</th>
				  <th>Total Transfer</th>
				   <?php
				  if($this->session->userdata('type')!="11"){
				  ?>
				  <th>Bukti Transfer</th>
                  <th>Action</th>
				   <?php
				  }
				  ?>
                </tr>
              </thead>
              <tbody>
                <?php 
				if($result):
					foreach($result as $row):
						$json_row = array();
						$total_posted_jobs = 0;

						$json_string1 = str_replace('"',"dquote",json_encode($row));
						$json_string2 = str_replace("'","squote",$json_string1);
						$json_string = str_replace("/","slash",$json_string2);
					?>
                <tr id="row_<?php echo $row->ID;?>">
                  <td valign="middle">
					
						<?php echo $row->tgl_pengajuan;?>
				  </td>
				  <td valign="middle">
					
						<?php echo str_pad($row->cab, 4, '0', STR_PAD_LEFT);?>
				  </td>
				  <td valign="middle">
				 
						<?php echo str_pad($row->bk, 3, '0', STR_PAD_LEFT);?>
				  </td>
				  <td valign="middle"><?php echo str_pad($row->no_kta, 6, '0', STR_PAD_LEFT);?><br />
                  </td>
				  
				  <td valign="middle">
				  
						<?php echo $row->from_date;?> - <?php echo $row->thru_date;?> 
                  </td>
				  
                  <td valign="middle">
					<a href="<?php echo base_url('admin/members/details/'.$row->ID);?>"><?php echo $row->firstname.' '.$row->lastname;?></a><br/>
				  	<?php echo $row->email;?>
				  </td>
				  <td valign="middle"><?php echo $row->dob;?></td>
                  <td valign="middle"><?php echo (($row->sip_lic_num!="")?$row->sip_lic_num.'<br />('.$row->sip_startyear.' sampai '.$row->sip_endyear.')':"");?> </td>
				  <td valign="middle"><?php echo ($row->warga_asing==0)?'WNI':'WNA';?></td>
				  <td valign="middle">
				  
				  <?php if($row->sertifikat_legal!=''){ ?>
				  <a target="_blank" href="<?php echo base_url().'assets/uploads/'.$row->sertifikat_legal;?>">- Sertifikat_legal</a>  <br />
				  <?php } ?>
				  <?php if($row->tanda_bukti!=''){ ?>
				  <a target="_blank" href="<?php echo base_url().'assets/uploads/'.$row->tanda_bukti;?>">- Tanda_bukti</a>  <br />
				  <?php } ?>
				  <?php if($row->surat_dukungan!=''){ ?>
				  <a target="_blank" href="<?php echo base_url().'assets/uploads/'.$row->surat_dukungan;?>">- Surat_dukungan</a>  <br />
				  <?php } ?>
				  <?php if($row->surat_pernyataan!=''){ ?>
				  <a target="_blank" href="<?php echo base_url().'assets/uploads/'.$row->surat_pernyataan;?>">- Surat_pernyataan</a>  <br />
				  <?php } ?>
				  <?php if($row->surat_ijin_domisili!=''){ ?>
				  <a target="_blank" href="<?php echo base_url().'assets/uploads/'.$row->surat_ijin_domisili;?>">- Surat_ijin_domisili</a>  <?php //echo $row->paydesc;?>
				  <?php } ?>
				  
				  </td>
				  
				  <td valign="middle">
				  <?php
				   $status = '';
				  		if($row->paystatus=='1'){
							$class_label = 'success';
							 $status = 'Valid';
						}
						elseif($row->paystatus=='0'){
							$class_label = 'warning';
							 $status = 'Please Confirm to Finance';
						}
						elseif($row->paystatus=='2'){
							$class_label = 'danger';
							 $status = 'Not Valid';
						}
						else
							$class_label = 'danger';
				  ?>
                  
                    <a onClick="update_status(<?php echo $row->ID;?>);" href="javascript:;" id="sts_<?php echo $row->ID;?>"> <span class="label label-<?php echo $class_label;?>"><?php echo $status;?></span> </a>
					
					<?php
				   $status = '';
					if($row->vnv_status=='1'){
						$class_label = 'success';
						 $status = 'Dokumen Valid';
					}
					elseif($row->vnv_status=='2'){
						$class_label = 'danger';
						 $status = 'Dokumen Not Valid';
					}
					elseif($row->id_pay!='' && $row->id_pay==$row->id_pay_cek){
						$class_label = 'success';
						 $status = 'Dokumen Valid';
					}
					
					if($row->vnv_status=='2' || $row->vnv_status=='1' || $row->id_pay!='')
					{
						?>
						<a href="javascript:;"> <span class="label label-<?php echo $class_label;?>"><?php echo $status;?></span> </a>
						<?php
						if($row->vnv_status=='2') echo '<br />'.$row->remark;
					}
				  ?>
					</td>
					 <td valign="middle"><?php echo $row->paydesc;?></td>
				  <td valign="middle"> 
				  <?php
				  if($this->session->userdata('type')!="11"){
				  ?>
				  	 <a href="javascript:;" class="btn btn-primary btn-xs" onClick="load_quick_payment_detail('<?php echo $row->payiuranpangkal;?>','<?php echo $row->payiurantahunan;?>','<?php echo $row->paysukarelaanggota;?>','<?php echo $row->paysukarelagedung;?>','<?php echo $row->paysukarelaperpus;?>','<?php echo $row->paysukarelaceps;?>','<?php echo $row->paysukarelatotal;?>');"><?php echo $row->paysukarelatotal;?></a> 
				  <?php
				  }
				  else{
					  echo $row->paysukarelatotal;
				  }
				  ?>
				  </td>
				  <?php if($this->session->userdata('type')!="11") { ?>
				  <td valign="middle">
				  <?php if(isset($row->payfile)) { ?>
				  	<?php echo $row->payname;?> <br /> 
				  	<?php echo $row->paydate;?><br />
				  <a target="_blank" href="<?php echo base_url().'assets/uploads/pay/'.$row->payfile;?>">download</a> <br /> <?php //echo $row->paydesc;?>
				  <?php } ?>
				  </td>  
					
					
                  <td valign="middle">
				  <?php if($this->session->userdata('type')=="0" || $this->session->userdata('type')=="2" || $this->session->userdata('type')=="14" || $this->session->userdata('type')=="9"){ ?>
				  <?php 
				  //print_r($row);
				  if($row->id_pay!="" && $row->id_pay!=$row->id_pay_cek){ //if($row->paystatus=="1" && $row->id_pay==""){ ?>
				  <a href="javascript:;" class="btn btn-primary btn-xs" onClick="load_quick_period('<?php echo $row->ID;?>','<?php echo $row->from_date;?>','<?php echo $row->thru_date;?>','<?php echo $row->payid;?>');">Set Period </a> | <a href="javascript:;"  class="btn btn-primary btn-xs" onClick="load_quick_status('<?php echo $row->payid;?>');">Reject</a>
				  <?php }
				  else if($row->id_pay==""){ //if($row->paystatus=="1" && $row->id_pay==""){ ?>
				  <a href="javascript:;" class="btn btn-primary btn-xs" onClick="load_quick_period('<?php echo $row->ID;?>','<?php echo $row->from_date;?>','<?php echo $row->thru_date;?>','<?php echo $row->payid;?>');">Set Period </a> | <a href="javascript:;"  class="btn btn-primary btn-xs" onClick="load_quick_status('<?php echo $row->payid;?>');">Reject</a>
				  <?php } else{ ?>  
				  <?php echo $row->plan_from_date.' - '.$row->plan_thru_date; ?>
				  <?php } } ?>  
                  </td>
				  
				  <?php
				  }
				  ?>
				  
                </tr>
                <?php endforeach; else:?>
                <tr>
                  <td colspan="10" align="center" class="text-red">No Record found!</td>
                </tr>
                <?php
					endif;
				?>
              </tbody>
              <tfoot>
              </tfoot>
            </table>
          </div>
          
          <!--Pagination-->
          <div class="paginationWrap"> <?php echo ($result)?$links:'';?> </div>
          
          <!-- /.box-body --> 
        </div>
        <!-- /.box --> 
        
        <!-- /.box --> 
      </div>
    </div>
  </section>
  <!-- /.content --> 
</aside>
<div class="modal fade" id="quick_detail_payment">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Detail Transfer <span id="comp_name" style="font-weight:bold;"></span></h4>
      </div>
      <div class="modal-body"> 
        <!-- /.box-header --> 
        <!-- form start -->
        <div class="box-body">
          <table class="table" style="font-size:14px;font-weight:bold;">
			<tr><td>1.</td> <td>Iuran Pangkal</td> <td>:</td> <td>Rp. <span id="p1"></span></td> <tr/>
			<tr><td>2.</td> <td>Iuran Tahunan</td> <td>:</td> <td>Rp. <span id="p2"></span></td> <tr/>
			<tr><td>3.</td> <td>Sumbangan Sukarela</td> <td></td> <td></td> <tr/>
			<tr><td></td> <td>a.	Keanggotaan 	</td> <td>:  </td> <td>Rp. <span id="p3"></span></td> <tr/>
			<tr><td></td> <td>b.	Gedung 			</td> <td>:  </td> <td>Rp. <span id="p4"></span></td> <tr/>
			<tr><td></td> <td>c.	Perpustakaan 	</td> <td>:  </td> <td>Rp. <span id="p5"></span></td> <tr/>
			<tr><td></td> <td>d.	CEPS 			</td> <td>:  </td> <td>Rp. <span id="p6"></span></td> <tr/>	
			<tr><td>4. </td> <td>Total 1+2+3(a+b+c+d)</td> <td>:</td> <td>Rp. <span id="p7"></span></td> <tr/>
			 </table> 
        </div>
        <!-- /.box-body -->  
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
    <!-- /.modal-content --> 
  </div>
  <!-- /.modal-dialog --> 
</div>

<div class="modal fade" id="quick_period">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Set Period <span id="j_comp_name" style="font-weight:bold;"></span></h4>
      </div>
      <div class="modal-body"> 
        <!-- /.box-header --> 
        <!-- form start -->
        <div id="errboxPeriod" style="display:none;" class="alert alert-warning" role="alert"></div>
        <div class="box-body" id="j_box">
          <table width="95%" border="0">
			<tr>
              <td id="">
				<script type="text/javascript" src="<?php echo base_url();?>assets/js/jquery-1.4.2.min.js"></script>
				<script type="text/javascript" src="<?php echo base_url();?>assets/js/jquery.maskedinput-1.2.2-co.min.js"></script>
				<strong><span class="form-group">From date:</span></strong>
			  </td>
			  <td>
				<input type="text" name="tgl_period" id="tgl_period" value="<?php //echo set_value('period',(isset($row->period)?date('d-m-Y',strtotime($row->period)):$row->period));?>" class="form-control datepicker" placeholder="Start Date" required="required" /> 			
			  </td>
            </tr>
			<tr>
				<td><strong><span class="form-group">To date:</span></strong></td>
				<td><input type="text" name="tgl_period2" id="tgl_period2" value="<?php //echo set_value('period',(isset($row->period)?date('d-m-Y',strtotime($row->period)):$row->period));?>" class="form-control datepicker" placeholder="End Date" required="required" /></td>
				<script type="text/javascript">
				$(function($){
					$("#tgl_period").mask("99-99-9999"), {placeholder: 'dd-mm-yyyy'};
					$("#tgl_period2").mask("99-99-9999"), {placeholder: 'dd-mm-yyyy'};
				});
				</script>	
			</tr>
			
            <tr>
			  <td></td>
              <td id=""><button type="button" class="btn btn-default" onclick="savesetperiod()">Save</button><input type="hidden" name="id_period" id="id_period" value=""  /><input type="hidden" name="id_pay" id="id_pay" value=""  /></td>
            </tr>
          </table>
        </div>
        <!-- /.box-body --> 
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
    
    <!-- /.modal-content --> 
  </div>
  <!-- /.modal-dialog --> 
</div>

<div class="modal fade" id="quick_status">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Set Status (Reject)<span id="j_comp_name_" style="font-weight:bold;"></span></h4>
      </div>
      <div class="modal-body"> 
        <!-- /.box-header --> 
        <!-- form start -->
        <div id="errbox" style="display:none;" class="alert alert-warning" role="alert"></div>
        <div class="box-body" id="j_box_">
          <table width="95%" border="0">
           
			<tr>
              <td><strong><span class="form-group">Status</span></strong></td>
              <td id="">
				<select class="form-control" name="status" id="status" required="">
				<option value="2">Not Valid
				</option></select>
			  </td>
            </tr>
			
			
			<tr>
              <td><strong><span class="form-group">Note</span></strong></td>
              <td>
				<textarea name="remarks" id="remarks" rows="4" cols="60"></textarea>
			  </td>
            </tr>
			
            <tr>
              <td><strong><span class="form-group"></span></strong></td>
              <td id=""><button type="button" class="btn btn-default" onclick="savesetstatus()" data-dismiss="modal">Save</button><input type="hidden" name="id_status" id="id_status" value=""  /></td>
            </tr>
          </table>
        </div>
        <!-- /.box-body --> 
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
    
    <!-- /.modal-content --> 
  </div>
  <!-- /.modal-dialog --> 
</div>
  
    <!-- /.modal-content --> 
  </div>
  <!-- /.modal-dialog --> 
</div>
<!-- /.right-side -->


<?php $this->load->view('admin/common/footer'); ?>
