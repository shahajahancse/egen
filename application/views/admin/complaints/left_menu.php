  <style>
    /* Custom styles for pill-shaped badge */
    .badge-pill {
      border-radius: 10px; /* Adjust the value for pill shape */
    }
    .badge-danger {
      background-color: #e42520; /* Bootstrap's default danger color */
      color: white; /* Text color for visibility */
    }
  </style>

<?php
$session = $this->session->userdata('username');
$theme = $this->Xin_model->read_theme_info(1);
// set layout / fixed or static
if($theme[0]->right_side_icons=='true') {
	$icons_right = 'expanded menu-icon-right';
} else {
	$icons_right = '';
}
if($theme[0]->bordered_menu=='true') {
	$menu_bordered = 'menu-bordered';
} else {
	$menu_bordered = '';
}
$user_info = $this->Xin_model->read_user_info($session['user_id']);
if($user_info[0]->is_active!=1) {
	redirect('admin/');
}
$role_user = $this->Xin_model->read_user_role_info($user_info[0]->user_role_id);
if(!is_null($role_user)){
	$role_resources_ids = explode(',',$role_user[0]->role_resources);
} else {
	$role_resources_ids = explode(',',0);	
}
?>
<?php $system = $this->Xin_model->read_setting_info(1);?>
<?php $arr_mod = $this->Xin_model->select_module_class($this->router->fetch_class(),$this->router->fetch_method()); ?>
<?php 
if($theme[0]->sub_menu_icons != ''){
	$submenuicon = $theme[0]->sub_menu_icons;
} else {
	$submenuicon = 'fa-circle-o';
}
?>
<?php  if($user_info[0]->profile_picture!='' && $user_info[0]->profile_picture!='no file') {?>
<?php $cpimg = base_url().'uploads/profile/'.$user_info[0]->profile_picture;?>
<?php } else {?>
<?php  if($user_info[0]->gender=='Male') { ?>
<?php 	$de_file = base_url().'uploads/profile/default_male.jpg';?>
<?php } else { ?>
<?php 	$de_file = base_url().'uploads/profile/default_female.jpg';?>
<?php } ?>
<?php $cpimg = $de_file;?>
<?php  } ?>
<section class="sidebar">
  <!-- Sidebar user panel -->
  
  <div class="user-panel">
    <div class="image text-center"><img src="<?php echo $cpimg;?>" class="img-circle" alt="<?php echo $user_info[0]->first_name. ' '.$user_info[0]->last_name;?>"> </div>
    <div class="info">
      <p><?php echo $user_info[0]->first_name. ' '.$user_info[0]->last_name;?></p>
      <a href="<?php echo site_url('admin/profile');?>"><i class="fa fa-user"></i></a>
      <?php if(in_array('60',$role_resources_ids)) { ?>
      <a href="<?php echo site_url('admin/settings');?>"><i class="fa fa-gear"></i></a>
      <?php } ?>
      <a href="<?php echo site_url('admin/logout');?>"><i class="fa fa-power-off"></i></a> </div>
  </div>
  <?php
  $idocuments_expired = 0; $iimg_documents = 0;
  $icompany_license = 0; $iwarranty_assets = 0;
  if($user_info[0]->user_role_id==1){
	  $idocuments_expired = $this->Xin_model->count_get_documents_expired_all();
	  $iimg_documents = $this->Xin_model->count_get_img_documents_expired_all();
	  $icompany_license = $this->Xin_model->iicount_company_license_expired_all();
	  $iwarranty_assets = $this->Xin_model->count_warranty_assets_expired_all();
  } else {
	  $idocuments_expired = $this->Xin_model->count_get_user_documents_expired_all($session['user_id']);
	  $iimg_documents = $this->Xin_model->count_get_user_img_documents_expired_all($session['user_id']);
	  $icompany_license = $this->Xin_model->count_get_company_license_expired($session['user_id']);
	  if(in_array('265',$role_resources_ids)) {
			$iwarranty_assets = $this->Xin_model->count_company_warranty_assets_expired_all($user_info[0]->company_id);
		} else {
			$iwarranty_assets = $this->Xin_model->count_user_warranty_assets_expired_all($session['user_id']);
		}
  }
  $exp_count = $idocuments_expired + $iimg_documents + $icompany_license + $iwarranty_assets;
  ?>
  <!-- sidebar menu: : style can be found in sidebar.less -->
  <ul class="sidebar-menu" data-widget="tree"> 
    <li class="<?php if(!empty($arr_mod['active']))echo $arr_mod['active'];?>"> <a href="<?php echo site_url('admin/dashboard');?>"> <i class="fa fa-dashboard"></i> <span><?php echo $this->lang->line('dashboard_title');?></span> </a> </li>
    <?php if(in_array('13',$role_resources_ids) || in_array('88',$role_resources_ids) || in_array('92',$role_resources_ids) || in_array('22',$role_resources_ids) || in_array('23',$role_resources_ids) || in_array('393',$role_resources_ids) || in_array('400',$role_resources_ids) || $user_info[0]->user_role_id==1){?>
      <li class="<?php if(!empty($arr_mod['stff_open']))echo $arr_mod['stff_open'];?> treeview"> <a href="#"> <i class="fa fa-user"></i> <span><?php echo $this->lang->line('let_staff');?></span> <span class="pull-right-container"> <?php if($exp_count > 0):?><span class="label label-danger pull-right"><?php echo $exp_count;?></span><?php endif;?> <i class="fa fa-angle-left pull-right"></i> </span> </a>
        <ul class="treeview-menu">
          <?php if(in_array('13',$role_resources_ids)) { ?>
          <li class="<?php if(!empty($arr_mod['emp_active']))echo $arr_mod['emp_active'];?>"><a href="<?php echo site_url('admin/employees');?>"><i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('dashboard_employees');?></a></li>
          <?php } ?>
          <?php if(in_array('393',$role_resources_ids)) { ?>
          <li class="<?php if(!empty($arr_mod['custom_fields_active']))echo $arr_mod['custom_fields_active'];?>"><a href="<?php echo site_url('admin/custom_fields');?>"><i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_hrsale_custom_fields');?></a></li>
          <?php } ?>
          <?php if(in_array('88',$role_resources_ids)) { ?>
          <li class="<?php if(!empty($arr_mod['hremp_active']))echo $arr_mod['hremp_active'];?>"><a href="<?php echo site_url('admin/employees/hr');?>"><i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_employees_directory');?></a></li>
          <?php } ?>
          <?php if(in_array('92',$role_resources_ids)) { ?>
          <li class="<?php if(!empty($arr_mod['importemp_active']))echo $arr_mod['importemp_active'];?>"><a href="<?php echo site_url('admin/employees/import');?>"><i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_import_employees');?></a></li>
          <?php } ?>
          <?php if(in_array('23',$role_resources_ids)) { ?>
          <li class="<?php if(!empty($arr_mod['emp_ex_active']))echo $arr_mod['emp_ex_active'];?>"><a href="<?php echo site_url('admin/employee_exit');?>"><i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_employees_exit');?></a></li>
          <?php } ?>
          <?php if(in_array('400',$role_resources_ids)) { ?>
          <li class="<?php if(!empty($arr_mod['exp_doc_active']))echo $arr_mod['exp_doc_active'];?>"><a href="<?php echo site_url('admin/employees/expired_documents');?>"><i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_e_details_exp_documents');?> <?php if($exp_count > 0):?><span class="label label-danger pull-right"><?php echo $exp_count;?></span><?php endif;?></a></li>
          <?php } ?>
          <?php if(in_array('22',$role_resources_ids)) { ?>
          <li class="<?php if(!empty($arr_mod['emp_ll_active']))echo $arr_mod['emp_ll_active'];?>"><a href="<?php echo site_url('admin/employees_last_login');?>"><i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_employees_last_login');?></a></li>
          <?php } ?>
        </ul>
      </li>
    <?php } ?>

    <!-- Core HR -->
    <?php  if(in_array('12',$role_resources_ids) || in_array('14',$role_resources_ids) || in_array('15',$role_resources_ids) || in_array('16',$role_resources_ids) || in_array('17',$role_resources_ids) || in_array('18',$role_resources_ids) || in_array('19',$role_resources_ids) || in_array('20',$role_resources_ids) || in_array('21',$role_resources_ids)){?>
      <li class="<?php if(!empty($arr_mod['emp_open']))echo $arr_mod['emp_open'];?> treeview"> <a href="#"> <i class="fa fa-futbol-o"></i> <span><?php echo $this->lang->line('xin_hr');?></span> <span class="pull-right-container"> <i class="fa fa-angle-left pull-right"></i> </span> </a>
        <ul class="treeview-menu">
          <?php if($system[0]->module_awards=='true'){?>
          <?php if(in_array('14',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['awar_active']))echo $arr_mod['awar_active'];?>"> <a href="<?php echo site_url('admin/awards');?>" > <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_awards');?> </a> </li>
          <?php } ?>
          <?php } ?>
          <?php if(in_array('15',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['tra_active']))echo $arr_mod['tra_active'];?>"> <a href="<?php echo site_url('admin/transfers');?>" > <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_transfers');?> </a> </li>
          <?php } ?>
          <?php if(in_array('16',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['res_active']))echo $arr_mod['res_active'];?>"> <a href="<?php echo site_url('admin/resignation');?>" > <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_resignations');?> </a> </li>
          <?php } ?>
          <?php if($system[0]->module_travel=='true'){?>
          <?php if(in_array('17',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['trav_active']))echo $arr_mod['trav_active'];?>"> <a href="<?php echo site_url('admin/travel');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_travels');?> </a> </li>
          <?php } ?>
          <?php } ?>
          <?php if(in_array('18',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['pro_active']))echo $arr_mod['pro_active'];?>"> <a href="<?php echo site_url('admin/promotion');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_promotions');?> </a> </li>
          <?php } ?>
          <?php if(in_array('19',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['compl_active']))echo $arr_mod['compl_active'];?>"> <a href="<?php echo site_url('admin/complaints');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_complaints');?> </a> </li>
          <?php } ?>
          <?php if(in_array('20',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['warn_active']))echo $arr_mod['warn_active'];?>"> <a href="<?php echo site_url('admin/warning');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_warnings');?> </a> </li>
          <?php } ?>
          <?php if(in_array('21',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['term_active']))echo $arr_mod['term_active'];?>"> <a href="<?php echo site_url('admin/termination');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_terminations');?> </a> </li>
          <?php } ?>
        </ul>
      </li>
    <?php } ?>
    <!-- Core HR -->

    <!-- Organization -->
    <?php  if(in_array('2',$role_resources_ids) || in_array('3',$role_resources_ids) || in_array('5',$role_resources_ids) || in_array('6',$role_resources_ids) || in_array('4',$role_resources_ids) || in_array('11',$role_resources_ids) || in_array('9',$role_resources_ids) || in_array('96',$role_resources_ids)){?>
      <li class="<?php if(!empty($arr_mod['adm_open']))echo $arr_mod['adm_open'];?> treeview"> <a href="#"> <i class="fa fa-building"></i> <span><?php echo $this->lang->line('left_organization');?></span> <span class="pull-right-container"> <i class="fa fa-angle-left pull-right"></i> </span> </a>
        <ul class="treeview-menu">
          <?php if(in_array('5',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['com_active']))echo $arr_mod['com_active'];?>"><a href="<?php echo site_url('admin/company')?>"><i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_company');?></a></li>
          <li class="sidenav-link <?php if(!empty($arr_mod['official_documents_active']))echo $arr_mod['official_documents_active'];?>"><a href="<?php echo site_url('admin/company/official_documents')?>"><i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_hr_official_documents');?></a></li>
          <?php } ?>
          <?php if(in_array('6',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['loc_active']))echo $arr_mod['loc_active'];?>"><a href="<?php echo site_url('admin/location');?>"><i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_location');?></a></li>
          <?php } ?>
          <?php if(in_array('3',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['dep_active']))echo $arr_mod['dep_active'];?>"><a href="<?php echo site_url('admin/department');?>"><i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_department');?></a></li>
          <?php } ?>
          <?php if($system[0]->is_active_sub_departments=='yes'){?>
          <?php if(in_array('3',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['sub_departments_active']))echo $arr_mod['sub_departments_active'];?>"><a href="<?php echo site_url('admin/department/sub_departments');?>"><i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_hr_sub_departments');?></a></li>
          <?php } ?>
          <?php } ?>
          <?php if(in_array('4',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['des_active']))echo $arr_mod['des_active'];?>"><a href="<?php echo site_url('admin/designation');?>"><i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_designation');?></a></li>
          <?php } ?>
          <?php if(in_array('11',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['ann_active']))echo $arr_mod['ann_active'];?>"><a href="<?php echo site_url('admin/announcement');?>"><i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_announcements');?></a></li>
          <?php } ?>
          <?php if(in_array('9',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['pol_active']))echo $arr_mod['pol_active'];?>"><a href="<?php echo site_url('admin/policy');?>"><i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_policies');?></a></li>
          <?php } ?>
          <?php if($system[0]->module_orgchart=='true'){?>
          <?php if(in_array('96',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['org_chart_active']))echo $arr_mod['org_chart_active'];?>"><a href="<?php echo site_url('admin/organization/chart');?>"><i class="fa <?php echo $submenuicon;?>"></i><?php echo $this->lang->line('xin_org_chart_lnk');?></a></li>
          <?php } ?>
          <?php } ?>
        </ul>
      </li>
    <?php } ?>
    <!-- Organization -->

    <!-- Hr -->
    <?php  if(in_array('27',$role_resources_ids) || in_array('28',$role_resources_ids) || in_array('29',$role_resources_ids) || in_array('30',$role_resources_ids) || in_array('31',$role_resources_ids) || in_array('7',$role_resources_ids) || in_array('8',$role_resources_ids) || in_array('46',$role_resources_ids) || in_array('123',$role_resources_ids) || in_array('130',$role_resources_ids) || in_array('377',$role_resources_ids) || in_array('389',$role_resources_ids) || in_array('401',$role_resources_ids) || in_array('1001',$role_resources_ids)) {?>
      <li class="<?php if(!empty($arr_mod['attnd_open']))echo $arr_mod['attnd_open'];?> treeview"> <a href="#"> <i class="fa fa-users"></i> <span> HR <?php //echo $this->lang->line('left_timesheet');?></span> <span class="pull-right-container"> <i class="fa fa-angle-left pull-right"></i> </span> </a>
        <ul class="treeview-menu">

          <?php if(in_array('389',$role_resources_ids)) { ?>
            <li class="sidenav-link <?php if(!empty($arr_mod['emp_atten_active']))echo $arr_mod['emp_atten_active'];?>"> <a href="<?php echo site_url('admin/attendance/employee_attendance');?>"><i class="fa <?php echo $submenuicon;?>"></i>Attendance</a> </li>
          <?php } ?> 

          <?php if(in_array('123',$role_resources_ids)) { ?>
            <li class="sidenav-link <?php if(!empty($arr_mod['emp_move_active']))echo $arr_mod['emp_move_active'];?>"> <a href="<?php echo site_url("admin/attendance/employee_movement");?>"><i class="fa <?php echo $submenuicon;?>"></i>Movement</a></li>
          <?php } ?> 

          <?php if(in_array('124',$role_resources_ids)) { ?>
            <li class="sidenav-link <?php if(!empty($arr_mod['emp_leave']))echo $arr_mod['emp_leave'];?>"> <a href="<?php echo site_url("admin/leave/emp_leave");?>"><i class="fa <?php echo $submenuicon;?>"></i>Leave</a></li>
          <?php } ?> 

          <?php if(in_array('130',$role_resources_ids)) { ?>
            <li class="sidenav-link <?php if(!empty($arr_mod['emp_holyday']))echo $arr_mod['emp_holyday'];?>"> <a href="<?php echo site_url("admin/leave/emp_holyday");?>"><i class="fa <?php echo $submenuicon;?>"></i>Holiday</a></li>
          <?php } ?>  

          <?php if(in_array('1001',$role_resources_ids)) { ?>
            <li class="sidenav-link <?php if(!empty($arr_mod['attnf_active']))echo $arr_mod['attnf_active'];?>"> <a href="<?php echo site_url('admin/timesheet/attn_file_upload');?>"><i class="fa <?php echo $submenuicon;?>"></i> attn file upload</a> </li>
          <?php } ?>   

          <?php if(in_array('1002',$role_resources_ids)) { ?>
            <li class="sidenav-link <?php if(!empty($arr_mod['attnp_active']))echo $arr_mod['attnp_active'];?>"> <a href="<?php echo site_url('admin/attendance/');?>"> <i class="fa <?php echo $submenuicon;?>"></i> attendance process</a> </li>
          <?php } ?> 

          <?php if(in_array('1003',$role_resources_ids)) { ?>
            <li class="sidenav-link <?php if(!empty($arr_mod['move_active']))echo $arr_mod['move_active'];?>"> <a href="<?php echo site_url('admin/attendance/move_register');?>"> <i class="fa <?php echo $submenuicon;?>"></i> movement register</a> </li>
          <?php } ?> 

          <?php if(in_array('28',$role_resources_ids)) { ?>
            <li class="sidenav-link <?php if(!empty($arr_mod['attnd_active']))echo $arr_mod['attnd_active'];?>"> <a href="<?php echo site_url('admin/timesheet/attendance');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_attendance');?> </a> </li>
            <?php } ?>


          <?php if(in_array('10',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['timesheet_active']))echo $arr_mod['timesheet_active'];?>"> <a href="<?php echo site_url('admin/timesheet/');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_month_timesheet_title');?> </a> </li>
          <?php } ?>
          <?php if(in_array('261',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['timecalendar_active']))echo $arr_mod['timecalendar_active'];?>"> <a href="<?php echo site_url('admin/timesheet/timecalendar/');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_attendance_timecalendar');?> </a> </li>
          <?php } ?>
          <?php if(in_array('29',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['dtwise_attnd_active']))echo $arr_mod['dtwise_attnd_active'];?>"> <a href="<?php echo site_url('admin/timesheet/date_wise_attendance');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_date_wise_attendance');?> </a> </li>
          <?php } ?>
          <?php if(in_array('30',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['upd_attnd_active']))echo $arr_mod['upd_attnd_active'];?>"> <a href="<?php echo site_url('admin/timesheet/update_attendance');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_update_attendance');?> </a> </li>
          <?php } ?>
          <?php if(in_array('401',$role_resources_ids)) { ?>
          <li class="<?php if(!empty($arr_mod['overtime_request_act']))echo $arr_mod['overtime_request_act'];?>"><a href="<?php echo site_url('admin/overtime_request');?>"><i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_overtime_request');?></a></li>
          <?php } ?>
          <?php if(in_array('31',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['import_attnd_active']))echo $arr_mod['import_attnd_active'];?>"> <a href="<?php echo site_url('admin/timesheet/import');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_import_attendance');?> </a> </li>
          <?php } ?>
          <?php if(in_array('7',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['offsh_active']))echo $arr_mod['offsh_active'];?>"> <a href="<?php echo site_url('admin/timesheet/office_shift');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_office_shifts');?> </a> </li>
          <?php } ?>
          <?php if(in_array('8',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['hol_active']))echo $arr_mod['hol_active'];?>"> <a href="<?php echo site_url('admin/timesheet/holidays');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_manage_holidays');?> </a> </li>
          <?php } ?>
          <?php if(in_array('46',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['leave_active']))echo $arr_mod['leave_active'];?>"> <a href="<?php echo site_url('admin/timesheet/leave');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_manage_leaves');?> </a> </li>
          <?php } ?>
          <?php if(in_array('377',$role_resources_ids)) { ?>
          <li class="<?php if(!empty($arr_mod['eemp_active']))echo $arr_mod['eemp_active'];?>"><a href="<?php echo site_url('admin/employees/emp_list');?>"><i class="fa <?php echo $submenuicon;?>"></i> Employee List </a></li>
          <?php } ?>
        </ul>
      </li>
    <?php } ?>
    <!-- Hr -->

    <!-- Lunch -->
    <?php  if(in_array('1050',$role_resources_ids) || in_array('1051',$role_resources_ids) || in_array('1052',$role_resources_ids) || in_array('1053',$role_resources_ids) || in_array('1054',$role_resources_ids) || in_array('1055',$role_resources_ids) || in_array('1056',$role_resources_ids) || in_array('1057',$role_resources_ids) || in_array('1058',$role_resources_ids) || in_array('1059',$role_resources_ids)) {?>
      <li class="<?php if(!empty($arr_mod['lunch_open']))echo $arr_mod['lunch_open'];?> treeview"> <a href="#"> <i class="fa fa-cutlery"></i> Lunch <span class="pull-right-container">  <i class="fa fa-angle-left pull-right"></i> </span> </a>
        <ul class="treeview-menu">
 
          <?php if(in_array('1052',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['today_active']))echo $arr_mod['today_active'];?>"> <a href="<?php echo site_url('admin/lunch/today_lunch');?>"> <i class="fa <?php echo $submenuicon;?>"></i> Today Lunch  </a> </li>
          <?php } ?>  

          <?php if(in_array('1051',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['lunch_active']))echo $arr_mod['lunch_active'];?>"> <a href="<?php echo site_url('admin/lunch/index');?>"> <i class="fa <?php echo $submenuicon;?>"></i> 
        Lunch list </a> </li>
          <?php } ?>

          <?php if(in_array('1059',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['lemp_list_active']))echo $arr_mod['lemp_list_active'];?>"> <a href="<?php echo site_url('admin/lunch/employee_list');?>"> <i class="fa <?php echo $submenuicon;?>"></i> Collection Sheet </a> </li>
          <?php } ?> 

          <?php if(in_array('1053',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['emp_pay_active']))echo $arr_mod['emp_pay_active'];?>"> <a href="<?php echo site_url('admin/lunch/emp_pay_list');?>"> <i class="fa <?php echo $submenuicon;?>"></i> Employee Pay  </a> </li>
          <?php } ?> 
          
          <?php if(in_array('1054',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['vendor_active']))echo $arr_mod['vendor_active'];?>"> <a href="<?php echo site_url('admin/lunch/vendor_payment');?>"> <i class="fa <?php echo $submenuicon;?>"></i> Vendor Pay  </a> </li>
          <?php } ?> 


          <?php if(in_array('1060',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['vendor_list_active']))echo $arr_mod['vendor_list_active'];?>"> <a href="<?php echo site_url('admin/lunch/vendor_lunch_list');?>"> <i class="fa <?php echo $submenuicon;?>"></i> Vendor Lunch  </a> </li>
          <?php } ?> 

          <?php if(in_array('1055',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['lureport_active']))echo $arr_mod['lureport_active'];?>"> <a href="<?php echo site_url('admin/lunch/report');?>"> <i class="fa <?php echo $submenuicon;?>"></i> Report  </a> </li>
          <?php } ?>    
       

          <?php if(in_array('1056',$role_resources_ids) || in_array('1057',$role_resources_ids) || in_array('1058',$role_resources_ids)) { ?>
          <li class="<?php if(!empty($arr_mod['lusetting_open']))echo $arr_mod['lusetting_open'];?> treeview"> <a href="#"><i class="fa fa-circle-o"></i> Settings <span class="pull-right-container"> <i class="fa fa-angle-left pull-right"></i> </span> </a>
            <ul class="treeview-menu">
              <?php if(in_array('1057',$role_resources_ids)) { ?>
              <li class="sidenav-link <?php if(!empty($arr_mod['lpackage_active']))echo $arr_mod['lpackage_active'];?>"> <a href="<?php echo site_url('admin/lunch/lunch_package');?>"> <i class="fa <?php echo $submenuicon;?>"></i> Lunch Package </a> </li>
              <?php } ?>
              <?php if(in_array('1058',$role_resources_ids)) { ?>
              <li class="sidenav-link <?php if(!empty($arr_mod['lmenu_active']))echo $arr_mod['lmenu_active'];?>"> <a href="<?php echo site_url('admin/lunch/lunch_menu');?>"> <i class="fa <?php echo $submenuicon;?>"></i> Lunch Menu </a> </li>
              <?php } ?>
              <?php if(in_array('1059',$role_resources_ids)) { ?>
              <li class="sidenav-link <?php if(!empty($arr_mod['l_active']))echo $arr_mod['l_active'];?>"> <a href="<?php echo site_url('admin/lunch/lunch_active_list');?>"> <i class="fa <?php echo $submenuicon;?>"></i> Active Lunch </a> </li>
              <?php } ?>
            </ul>
          </li>
          <?php } ?>

        </ul>
      </li>
    <?php } ?>
    <!-- Lunch -->

    <!-- Payroll -->
    <?php //if($system[0]->module_payroll=='yes'){?>
    <?php  if(in_array('32',$role_resources_ids) || in_array('33',$role_resources_ids) || in_array('34',$role_resources_ids) || in_array('35',$role_resources_ids) || in_array('36',$role_resources_ids) || in_array('37',$role_resources_ids) || in_array('38',$role_resources_ids) || in_array('39',$role_resources_ids) || in_array('125',$role_resources_ids) || in_array('126',$role_resources_ids)|| in_array('127',$role_resources_ids) || in_array('390',$role_resources_ids)) {?>
      <li class="<?php if(!empty($arr_mod['payrl_open']))echo $arr_mod['payrl_open'];?> treeview"> <a href="#"> <i class="fa fa-usd"></i> <span><?php echo $this->lang->line('left_payroll');?></span> <span class="pull-right-container"> <i class="fa fa-angle-left pull-right"></i> </span> </a>
        <ul class="treeview-menu">

          <?php if(in_array('125',$role_resources_ids)) { ?>
            <li class="sidenav-link <?php if(!empty($arr_mod['selary_emp_active']))echo $arr_mod['selary_emp_active'];?>"> <a href="<?php echo site_url('admin/payroll/selary_emp');?>"><i class="fa <?php echo $submenuicon;?>"></i>Salary </a> </li>
          <?php } ?>  
          <?php if(in_array('126',$role_resources_ids)) { ?>
            <li class="sidenav-link <?php if(!empty($arr_mod['emp_bonus_active']))echo $arr_mod['emp_bonus_active'];?>"> <a href="<?php echo site_url('admin/payroll/employee_bonus');?>"><i class="fa <?php echo $submenuicon;?>"></i>Bonus </a> </li>
          <?php } ?>  

          <?php if(in_array('127',$role_resources_ids)) { ?>
            <li class="sidenav-link <?php if(!empty($arr_mod['emp_advanced_sal']))echo $arr_mod['emp_advanced_sal'];?>"> <a href="<?php echo site_url('admin/payroll/advanced_salary');?>"><i class="fa <?php echo $submenuicon;?>"></i>Advanced Salary </a> </li>
          <?php } ?> 
          
          <?php if(in_array('128',$role_resources_ids)) { ?>
            <li class="sidenav-link <?php if(!empty($arr_mod['emp_festival_bonus']))echo $arr_mod['emp_festival_bonus'];?>"> <a href="<?php echo site_url('admin/payroll/emp_festival_bonus');?>"><i class="fa <?php echo $submenuicon;?>"></i>Festival Bonus</a> </li>
          <?php } ?> 

          <?php if(in_array('390',$role_resources_ids)) { ?>
            <li class="sidenav-link <?php if(!empty($arr_mod['emp_lunch_active']))echo $arr_mod['emp_lunch_active'];?>"> <a href="<?php echo site_url('admin/lunch/lunch_emp_bill');?>"><i class="fa <?php echo $submenuicon;?>"></i>Lunch Bill</a> </li>
          <?php } ?>  

          <?php if(in_array('1021',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['salary_active']))echo $arr_mod['salary_active'];?>"> <a href="<?php echo site_url('admin/payroll/index');?>"> <i class="fa <?php echo $submenuicon;?>"></i> Generate Salary </a> </li>
          <?php } ?>        

          <?php if(in_array('1022',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['requisition_active']))echo $arr_mod['requisition_active'];?>"> <a href="<?php echo site_url('admin/requisition/index');?>"> <i class="fa <?php echo $submenuicon;?>"></i> Requisition Form </a> </li>
          <?php } ?>      

          <?php if(in_array('36',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['pay_generate_active']))echo $arr_mod['pay_generate_active'];?>"> <a href="<?php echo site_url('admin/payroll/generate_payslip');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_generate_payslip');?> </a> </li>
          <?php } ?>
          <?php if(in_array('37',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['pay_his_active']))echo $arr_mod['pay_his_active'];?>"> <a href="<?php echo site_url('admin/payroll/payment_history');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_payment_history');?> </a> </li>
          <?php } ?>
        </ul>
      </li>
    <?php } ?>
    <?php //} ?>
    <!-- Payroll -->

    <!-- Store -->
    <?php  if(in_array('1030',$role_resources_ids) || in_array('1031',$role_resources_ids) || in_array('1033',$role_resources_ids) || in_array('1070',$role_resources_ids) || in_array('1071',$role_resources_ids) || in_array('1072',$role_resources_ids) || in_array('1073',$role_resources_ids) || in_array('1074',$role_resources_ids) || in_array('1075',$role_resources_ids) || in_array('1076',$role_resources_ids) || in_array('1080',$role_resources_ids) || in_array('1081',$role_resources_ids) || in_array('1082',$role_resources_ids) || in_array('1083',$role_resources_ids) || in_array('1084',$role_resources_ids) || in_array('1085',$role_resources_ids) || in_array('1041',$role_resources_ids)) {?>
      <li class="<?php if(!empty($arr_mod['invtry_open']))echo $arr_mod['invtry_open'];?> treeview"> <a href="#"> <i class="fa fa-cart-arrow-down"></i> Store <span class="badge badge-pill " style="margin-top: 0px;margin-left: 56px; background:#0bbd22"><?php  $a = $this->db->select('COUNT(id) as id')->where_in('status', array(1, 2))->get('products_requisition_details')->row()->id ;  $b = $this->db->select('COUNT(id) as id')->where_in('status', array(1, 2))->get('products_purches_details')->row()->id ; $session = $this->session->userdata('username');  echo $session['role_id'] ==3 ? '' : ($session['role_id'] ==4 ? $a : ($a + $b))  ;?></span> <span class="pull-right-container">  <i class="fa fa-angle-left pull-right"></i> </span> </a>
        <ul class="treeview-menu">
          <?php if(in_array('1031',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['my_requi_active']))echo $arr_mod['my_requi_active'];?>"> <a href="<?php echo site_url('admin/inventory/index');?>"> <i class="fa < ?php echo $submenuicon;?>"></i> Requisition </a> </li>
          <?php } ?>  

          <!-- requisition part -->
        <?php if(in_array('1070',$role_resources_ids) || in_array('1070',$role_resources_ids) || in_array('1071',$role_resources_ids) || in_array('1072',$role_resources_ids) || in_array('1073',$role_resources_ids) || in_array('1074',$role_resources_ids) || in_array('1075',$role_resources_ids) || in_array('1076',$role_resources_ids)) { ?>
          <li class="<?php if(!empty($arr_mod['requi_active']))echo $arr_mod['requi_active'];?> treeview"> <a href="#"><i class="fa fa-user-plus"></i> Requisition
          <span class="badge badge-pill badge-info" style="margin-top: 0px;margin-left: 30px;"><?php echo $a = $this->db->select('COUNT(id) as id')->where_in('status', array(1, 2))->get('products_requisition_details')->row()->id ; ?></span> <span class="pull-right-container"> <i class="fa fa-angle-left pull-right"></i> </span> </a>
            <ul class="treeview-menu">
              <?php if(in_array('1072',$role_resources_ids)) { ?>
              <li class="sidenav-link <?php if(!empty($arr_mod['iqlist_open']))echo $arr_mod['iqlist_open'];?>"> <a href="<?php echo site_url('admin/inventory/index');?>"> <i class="fa fa-list-ul"></i> Requisition List </a> </li>
              <?php } ?>

              <?php if(in_array('1071',$role_resources_ids)) { ?>
              <li class="sidenav-link <?php if(!empty($arr_mod['iqcreate_open']))echo $arr_mod['iqcreate_open'];?>"> <a href="<?php echo site_url('admin/inventory/create');?>"> <i class="fa fa-plus-circle" style="color:green"></i> Create Requisition </a> </li>
              <?php } ?>

              <?php if(in_array('1033',$role_resources_ids)) { ?>
              <li class="sidenav-link <?php if(!empty($arr_mod['iqpending_open']))echo $arr_mod['iqpending_open'];?>"> <a href="<?php echo site_url('admin/inventory/pending_list');?>"> <i class="fa fa-retweet" style="color:red"></i> Pending List <span class="badge badge-pill badge-danger" style="margin-top: 0px;margin-left: 30px;"><?php echo $a = $this->db->select('COUNT(id) as id')->where('status', 1)->get('products_requisition_details')->row()->id ; ?></span></a> </li>
              <?php } ?>

              <?php if(in_array('1074',$role_resources_ids)) { ?>
              <li class="sidenav-link <?php if(!empty($arr_mod['iqapprove_open']))echo $arr_mod['iqapprove_open'];?>"> <a href="<?php echo site_url('admin/inventory/aproved_list');?>"> <i class="fa fa-check-circle" style="color:green"></i> Approved List <span class="badge badge-pill" style="margin-top: 0px;margin-left: 19px;background:green"><?php echo $a = $this->db->select('COUNT(id) as id')->where('status',2)->get('products_requisition_details')->row()->id ; ?></span></a> </li>
              <?php } ?>
              <?php if(in_array('1075',$role_resources_ids)) { ?>
              <li class="sidenav-link <?php if(!empty($arr_mod['iqdelivery_open']))echo $arr_mod['iqdelivery_open'];?>"> <a href="<?php echo site_url('admin/inventory/delivery_list');?>"> <i class="fa fa-truck" style="color:#1e8c98"></i> Delivered List</a> </li>
              <?php } ?>
              <?php if(in_array('1076',$role_resources_ids)) { ?>
              <li class="sidenav-link <?php if(!empty($arr_mod['reject_open']))echo $arr_mod['reject_open'];?>"> <a href="<?php echo site_url('admin/inventory/reject_list');?>"> <i class="fa fa-ban" style="color:red"></i> Rejected List</a> </li>
              <?php } ?>
            </ul>
          </li>
        <?php } ?>

          <!-- // purchase -->
          <?php if(in_array('1080',$role_resources_ids) || in_array('1081',$role_resources_ids) || in_array('1082',$role_resources_ids) || in_array('1083',$role_resources_ids) || in_array('1084',$role_resources_ids) || in_array('1085',$role_resources_ids)) { ?>
            
          <li class="<?php if(!empty($arr_mod['puiqu_active']))echo $arr_mod['puiqu_active'];?> treeview"> <a href="#"><i class="fa fa-shopping-bag" style="color:#1e8c98"></i> Purchase <span class="badge badge-pill badge-danger" style="margin-top: 0px;margin-left: 40px;"><?php $session = $this->session->userdata('username'); $a = $this->db->select('COUNT(id) as id')->where_in('status', array(1,2))->get('products_purches_details')->row()->id ; echo $session['role_id'] == 4 ? '' : $a;  ?></span><span class="pull-right-container"> <i class="fa fa-angle-left pull-right"></i> </span> </a>
            <ul class="treeview-menu">

              <?php if(in_array('1081',$role_resources_ids)) { ?>
              <li class="sidenav-link <?php if(!empty($arr_mod['puscreate_open']))echo $arr_mod['puscreate_open'];?>"> <a href="<?php echo site_url('admin/inventory/purchase');?>"> <i class="fa fa-list"></i> Purchase List </a> </li>
              <?php } ?>

              <?php if(in_array('1082',$role_resources_ids)) { ?>
              <li class="sidenav-link <?php if(!empty($arr_mod['puspending_open']))echo $arr_mod['puspending_open'];?>"> <a href="<?php echo site_url('admin/inventory/purchase_panding_list');?>"> <i class="fa fa-retweet" style="color:red"></i> Pending List <span class="badge badge-pill badge-danger" style="margin-top: 0px;margin-left: 30px;"><?php $c = $this->db->select('COUNT(id) as id')->where('status',1)->get('products_purches_details')->row()->id ; echo $session['role_id'] == 4 ? '' : $c;  ?></span></a> </li>
              <?php } ?>

              <?php if(in_array('1083',$role_resources_ids)) { ?>
              <li class="sidenav-link <?php if(!empty($arr_mod['pusaproved_open']))echo $arr_mod['pusaproved_open'];?>"> <a href="<?php echo site_url('admin/inventory/purchase_aproved_list');?>"> <i class="fa fa-check-circle" style="color:green"></i> Approved List <span class="badge badge-pill" style="margin-top: 0px;margin-left: 19px;background:green"><?php  $b = $this->db->select('COUNT(id) as id')->where('status', 2)->get('products_purches_details')->row()->id ; echo $session['role_id'] == 4 ? '' : $b;  ?></span></a> </li>
              <?php } ?>

              <?php if(in_array('1084',$role_resources_ids)) { ?>
              <li class="sidenav-link <?php if(!empty($arr_mod['pusreceived_open']))echo $arr_mod['pusreceived_open'];?>"> <a href="<?php echo site_url('admin/inventory/purchase_order_received_list');?>"> <i class="fa fa-handshake-o" style="color:green"></i> Order Received List</a> </li>
              <?php } ?>
              
              <?php if(in_array('1085',$role_resources_ids)) { ?>
              <li class="sidenav-link <?php if(!empty($arr_mod['pusreject_open']))echo $arr_mod['pusreject_open'];?>"> <a href="<?php echo site_url('admin/inventory/purchase_reject_list');?>"> <i class="fa fa-ban" style="color:red"></i> Rejected List</a> </li>
              <?php } ?>
            </ul>
          </li>
          <?php } ?> 

          <?php if(in_array('1042',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['product_open']))echo $arr_mod['product_open'];?>"> <a href="<?php echo site_url('admin/inventory/products');?>"> <i class="fa fa-product-hunt" style="color:green"></i> Product </a> </li>
          <?php } ?>


          <?php if(in_array('1048',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['move_open']))echo $arr_mod['move_open'];?>"> <a href="<?php echo site_url('admin/inventory/moves');?>"> <i class="fa fa-tablet" style="color:green"></i> Device Movements </a> </li>
          <?php } ?>

          <?php if(in_array('1047',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['low_product_list']))echo $arr_mod['low_product_list'];?>"> <a href="<?php echo site_url('admin/inventory/low_product_list');?>"> <i class="fa fa-sort-amount-asc" style="color:#e4d802"></i> Low Product List </a> </li>
          <?php } ?>

          <?php if(in_array('1033',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['inreport_active']))echo $arr_mod['inreport_active'];?>"> <a href="<?php echo site_url('admin/inventory/report');?>"> <i class="fa fa-line-chart" style="color:seagreen"></i> Report </a> </li>
          <?php } ?>    

          <?php if(in_array('1041',$role_resources_ids)) { ?>
          <li class="<?php if(!empty($arr_mod['insetting_open']))echo $arr_mod['insetting_open'];?> treeview"> <a href="#"><i class="fa fa-circle-o"></i> Settings <span class="pull-right-container"> <i class="fa fa-angle-left pull-right"></i> </span> </a>
            <ul class="treeview-menu">
              <?php if($user_info[0]->user_role_id != 3) { ?>
              <li class="sidenav-link <?php if(!empty($arr_mod['insetting_open']))echo $arr_mod['insetting_open'];?>"> <a href="<?php echo site_url('admin/inventory/daily_pkg');?>"> <i class="fa <?php echo $submenuicon;?>"></i> Requisition Package </a> </li>
              <?php } ?>

              <?php if(in_array('1046',$role_resources_ids)) { ?>
              <li class="sidenav-link <?php if(!empty($arr_mod['supplier_open']))echo $arr_mod['supplier_open'];?>"> <a href="<?php echo site_url('admin/inventory/supplier');?>"> <i class="fa <?php echo $submenuicon;?>"></i> Supplier </a> </li>
              <?php } ?>
              <?php if(in_array('1043',$role_resources_ids)) { ?>
              <li class="sidenav-link <?php if(!empty($arr_mod['unit_open']))echo $arr_mod['unit_open'];?>"> <a href="<?php echo site_url('admin/inventory/unit');?>"> <i class="fa <?php echo $submenuicon;?>"></i> Unit </a> </li>
              <?php } ?>
              <?php if(in_array('1044',$role_resources_ids)) { ?>
              <li class="sidenav-link <?php if(!empty($arr_mod['cat_open']))echo $arr_mod['cat_open'];?>"> <a href="<?php echo site_url('admin/inventory/category');?>"> <i class="fa <?php echo $submenuicon;?>"></i> Category </a> </li>
              <?php } ?>
              <?php if(in_array('1045',$role_resources_ids)) { ?>
              <li class="sidenav-link <?php if(!empty($arr_mod['subcat_open']))echo $arr_mod['subcat_open'];?>"> <a href="<?php echo site_url('admin/inventory/sub_category');?>"> <i class="fa <?php echo $submenuicon;?>"></i> Sub Category </a> </li>
              <?php } ?>
            </ul>
          </li>
          <?php } ?>

        </ul>
      </li>
    <?php } ?>
    <!-- Store -->

    <!-- Inventory / Accessories  -->
    <?php  if(in_array('1100',$role_resources_ids) || in_array('1101',$role_resources_ids) || in_array('1102',$role_resources_ids) || in_array('1103',$role_resources_ids) || in_array('1110',$role_resources_ids) || in_array('1111',$role_resources_ids) || in_array('1112',$role_resources_ids) || in_array('1113',$role_resources_ids)) {?>
      <li class="<?php if(!empty($arr_mod['access_open']))echo $arr_mod['access_open'];?> treeview"> <a href="#"> <i class="fa fa-houzz"></i> Inventory <span class="pull-right-container">  <i class="fa fa-angle-left pull-right"></i> </span> </a>
        <ul class="treeview-menu">
          <?php if(in_array('1101',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['item_active']))echo $arr_mod['item_active'];?>"> <a href="<?php echo site_url('admin/accessories/index');?>"> <i class="fa fa-list" style="color:seagreen"></i> Item List </a> </li>
          <?php } ?>    

          <?php if(in_array('1102',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['item_add_active']))echo $arr_mod['item_add_active'];?>"> <a href="<?php echo site_url('admin/accessories/item_add');?>"> <i class="fa fa-plus-circle" style="color:green"></i> Item Add </a> </li>
          <?php } ?>  

          <?php if(in_array('1103',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['acc_repo_active']))echo $arr_mod['acc_repo_active'];?>"> <a href="<?php echo site_url('admin/accessories/reports');?>"> <i class="fa fa-line-chart" style="color:seagreen"></i> Reports </a> </li>
          <?php } ?>  

       
          <?php if(in_array('1110',$role_resources_ids) || in_array('1111',$role_resources_ids) || in_array('1112',$role_resources_ids) || in_array('1113',$role_resources_ids)) { ?>
          <li class="<?php if(!empty($arr_mod['accsetting_open']))echo $arr_mod['accsetting_open'];?> treeview"> <a href="#"><i class="fa fa-cog"></i> Settings <span class="pull-right-container"> <i class="fa fa-angle-left pull-right"></i> </span> </a>
            <ul class="treeview-menu">
              <?php if(in_array('1111',$role_resources_ids)) { ?>
              <li class="sidenav-link <?php if(!empty($arr_mod['category_open']))echo $arr_mod['category_open'];?>"> <a href="<?php echo site_url('admin/accessories/category');?>"> <i class="fa fa-list-alt"></i> Category </a> </li>
              <?php } ?>

              <?php if(in_array('1112',$role_resources_ids)) { ?>
              <li class="sidenav-link <?php if(!empty($arr_mod['device_open']))echo $arr_mod['device_open'];?>"> <a href="<?php echo site_url('admin/accessories/device_model');?>"> <i class="fa fa-desktop"></i> Device Model </a> </li>
              <?php } ?>

              <?php if(in_array('1113',$role_resources_ids)) { ?>
              <li class="sidenav-link <?php if(!empty($arr_mod['number_open']))echo $arr_mod['number_open'];?>"> <a href="<?php echo site_url('admin/accessories/number_add');?>"> <i class="fa fa-tablet"></i> Add Phone Number</a> </li>
              <?php } ?>

              <?php if(in_array('1114',$role_resources_ids)) { ?>
              <li class="sidenav-link <?php if(!empty($arr_mod['desk_open']))echo $arr_mod['desk_open'];?>"> <a href="<?php echo site_url('admin/accessories/desk_add');?>"> <i class="fa fa-table"></i> Add Desk</a> </li>
              <?php } ?>
            </ul>
          </li>
          <?php } ?>

        </ul>
      </li>
    <?php } ?>
    <!-- Inventory / Accessories -->


    <?php //if($system[0]->module_projects_tasks=='true'){?>
    <?php  if(in_array('44',$role_resources_ids) || in_array('45',$role_resources_ids) || in_array('104',$role_resources_ids) || in_array('122',$role_resources_ids)) {?>
    <li class="<?php if(!empty($arr_mod['project_open']))echo $arr_mod['project_open'];?> treeview"> <a href="#"> <i class="fa fa-tasks"></i> <span><?php echo $this->lang->line('xin_project_manager_title');?></span> <span class="pull-right-container"> <i class="fa fa-angle-left pull-right"></i> </span> </a>
      <ul class="treeview-menu">
        <?php if(in_array('44',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['project_active']))echo $arr_mod['project_active'];?>"> <a href="<?php echo site_url('admin/project');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_projects');?> </a> </li>
        <?php } ?>
        <?php if(in_array('45',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['task_active']))echo $arr_mod['task_active'];?>"> <a href="<?php echo site_url('admin/timesheet/tasks');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_tasks');?> </a> </li>
        <?php } ?>  
        <?php if(in_array('44',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['timelogs_active']))echo $arr_mod['timelogs_active'];?>"> <a href="<?php echo site_url('admin/project/timelogs');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_project_timelogs');?> </a> </li>
        <?php } ?>  
        <?php if(in_array('44',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['project_cal_active']))echo $arr_mod['project_cal_active'];?>"> <a href="<?php echo site_url('admin/project/projects_calendar');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_hr_projects_calendar');?> </a> </li>
        <?php } ?> 
        <?php if(in_array('45',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['tasks_cal_active']))echo $arr_mod['tasks_cal_active'];?>"> <a href="<?php echo site_url('admin/project/tasks_calendar');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_tasks_calendar');?> </a> </li>
        <?php } ?> 
        <?php if(in_array('45',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['tasks_scrum_board_active']))echo $arr_mod['tasks_scrum_board_active'];?>"> <a href="<?php echo site_url('admin/project/tasks_scrum_board');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_tasks_sboard');?> </a> </li>
        <?php } ?>
        <?php if(in_array('44',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['projects_scrum_board_active']))echo $arr_mod['projects_scrum_board_active'];?>"> <a href="<?php echo site_url('admin/project/projects_scrum_board');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_projects_sboard');?> </a> </li>
        <?php } ?>
        <?php if(in_array('45',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['task_cat_active']))echo $arr_mod['task_cat_active'];?>"> <a href="<?php echo site_url('admin/project/task_categories');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_task_categories');?> </a> </li>
        <?php } ?>    
        <?php if(in_array('122',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['hr_taxes_inv_active']))echo $arr_mod['hr_taxes_inv_active'];?>"> <a href="<?php echo site_url('admin/invoices/taxes/');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_invoice_tax_type');?> </a> </li>
        <?php } ?>
      </ul>
    </li>
    <?php //} ?>
    <?php } ?>
    <?php  if(in_array('87',$role_resources_ids) || in_array('119',$role_resources_ids) || in_array('410',$role_resources_ids) || in_array('415',$role_resources_ids) || in_array('121',$role_resources_ids) || in_array('330',$role_resources_ids)) {?>
    <li class="<?php if(!empty($arr_mod['hr_quote_manager_open']))echo $arr_mod['hr_quote_manager_open'];?> treeview"> <a href="#"> <i class="fa fa-calendar-plus-o"></i> <span><?php echo $this->lang->line('xin_quote_manager');?></span> <span class="pull-right-container"> <i class="fa fa-angle-left pull-right"></i> </span> </a>
      <ul class="treeview-menu">
        <?php if(in_array('119',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['hr_clients_active']))echo $arr_mod['hr_clients_active'];?>"> <a href="<?php echo site_url('admin/clients');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_project_clients');?> </a> </li>
        <?php } ?>
        <?php if(in_array('410',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['hr_leads_active']))echo $arr_mod['hr_leads_active'];?>"> <a href="<?php echo site_url('admin/leads');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_leads');?> </a> </li>
        <?php } ?>
        <?php if(in_array('415',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['hr_all_quotes_active']))echo $arr_mod['hr_all_quotes_active'];?>"> <a href="<?php echo site_url('admin/quotes');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_estimates');?> </a> </li>
        <?php } ?>
        <?php if(in_array('121',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['hr_all_inv_active']))echo $arr_mod['hr_all_inv_active'];?>"> <a href="<?php echo site_url('admin/invoices/');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_invoices_title');?> </a> </li>
        <?php } ?>
       
        <?php if(in_array('330',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['hr_client_invoices_pay_active']))echo $arr_mod['hr_client_invoices_pay_active'];?>"> <a href="<?php echo site_url('admin/invoices/payments_history');?>"> <i class="fa <?php echo $submenuicon;?>"></i><?php echo $this->lang->line('xin_acc_invoice_payments');?> </a> </li>
        <?php } ?>
      </ul>
    </li>
    <?php } ?>
    <?php if($system[0]->module_training=='true'){?>
    <?php  if(in_array('53',$role_resources_ids) || in_array('54',$role_resources_ids) || in_array('55',$role_resources_ids) || in_array('56',$role_resources_ids)) {?>
    <li class="<?php if(!empty($arr_mod['training_open']))echo $arr_mod['training_open'];?> treeview"> <a href="#"> <i class="fa fa-graduation-cap"></i> <span><?php echo $this->lang->line('left_training');?></span> <span class="pull-right-container"> <i class="fa fa-angle-left pull-right"></i> </span> </a>
      <ul class="treeview-menu">
        <?php if(in_array('54',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['training_active']))echo $arr_mod['training_active'];?>"> <a href="<?php echo site_url('admin/training');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_training_list');?> </a> </li>
        <?php } ?>
        <?php if(in_array('55',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['tr_type_active']))echo $arr_mod['tr_type_active'];?>"> <a href="<?php echo site_url('admin/training_type');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_training_type');?> </a> </li>
        <?php } ?>
        <?php if(in_array('56',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['trainers_active']))echo $arr_mod['trainers_active'];?>"> <a href="<?php echo site_url('admin/trainers');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_trainers_list');?> </a> </li>
        <?php } ?>
      </ul>
    </li>
    <?php } ?>
    <?php } ?>
    <?php if($system[0]->module_performance=='yes'){?>
    <?php  if(in_array('40',$role_resources_ids) || in_array('41',$role_resources_ids) || in_array('42',$role_resources_ids) || in_array('107',$role_resources_ids) || in_array('108',$role_resources_ids) || in_array('372',$role_resources_ids) || in_array('373',$role_resources_ids)) {?>
    <li class="<?php if(!empty($arr_mod['performance_open']))echo $arr_mod['performance_open'];?> treeview"> <a href="#"> <i class="fa fa-cube"></i> <span><?php echo $this->lang->line('left_performance');?></span> <span class="pull-right-container"> <i class="fa fa-angle-left pull-right"></i> </span> </a>
      <ul class="treeview-menu">
        <?php if(in_array('41',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['per_indi_active']))echo $arr_mod['per_indi_active'];?>"> <a href="<?php echo site_url('admin/performance_indicator');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_performance_xindicator');?> </a> </li>
        <?php } ?>
        <?php if(in_array('42',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['per_app_active']))echo $arr_mod['per_app_active'];?>"> <a href="<?php echo site_url('admin/performance_appraisal');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_performance_xappraisal');?> </a> </li>
        <?php } ?>
        <?php if($system[0]->module_goal_tracking=='true'){?>
        <?php if(in_array('107',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['goal_tracking_active']))echo $arr_mod['goal_tracking_active'];?>"> <a href="<?php echo site_url('admin/goal_tracking');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_hr_goal_tracking');?> </a> </li>
        <?php } ?>
        <?php if(in_array('108',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['goal_tracking_type_active']))echo $arr_mod['goal_tracking_type_active'];?>"> <a href="<?php echo site_url('admin/goal_tracking/type');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_hr_goal_tracking_type_se');?> </a> </li>
        <?php } ?>
        <?php } ?>
      </ul>
    </li>
    <?php } ?>
    <?php } ?>
   
    <?php if($system[0]->module_accounting=='true'){?>
    <?php if(in_array('71',$role_resources_ids) || in_array('72',$role_resources_ids) || in_array('73',$role_resources_ids) || in_array('74',$role_resources_ids) || in_array('75',$role_resources_ids) || in_array('76',$role_resources_ids) || in_array('77',$role_resources_ids) || in_array('78',$role_resources_ids) || in_array('79',$role_resources_ids) || in_array('80',$role_resources_ids) || in_array('81',$role_resources_ids) || in_array('82',$role_resources_ids) || in_array('83',$role_resources_ids) || in_array('84',$role_resources_ids) || in_array('85',$role_resources_ids) || in_array('86',$role_resources_ids)){?>
    <li class="<?php if(!empty($arr_mod['hr_acc_open']))echo $arr_mod['hr_acc_open'];?> treeview"> <a href="#"> <i class="fa fa-money"></i> <span><?php echo $this->lang->line('xin_hr_finance');?></span> <span class="pull-right-container"> <i class="fa fa-angle-left pull-right"></i> </span> </a>
      <ul class="treeview-menu">
        <?php if(in_array('72',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['hr_bank_cash_active']))echo $arr_mod['hr_bank_cash_active'];?>"> <a href="<?php echo site_url('admin/accounting/bank_cash');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_acc_account_list');?> </a> </li>
        <?php } ?>
        <?php if(in_array('73',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['hr_account_balances_active']))echo $arr_mod['hr_account_balances_active'];?>"> <a href="<?php echo site_url('admin/accounting/account_balances');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_acc_account_balances');?> </a> </li>
        <?php } ?>
        <?php if(in_array('80',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['hr_payees_active']))echo $arr_mod['hr_payees_active'];?>"> <a href="<?php echo site_url('admin/accounting/payees');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_acc_payees');?> </a> </li>
        <?php } ?>
        <?php if(in_array('81',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['hr_payers_active']))echo $arr_mod['hr_payers_active'];?>"> <a href="<?php echo site_url('admin/accounting/payers');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_acc_payers');?> </a> </li>
        <?php } ?>
        <?php if(in_array('75',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['hr_deposit_active']))echo $arr_mod['hr_deposit_active'];?>"> <a href="<?php echo site_url('admin/accounting/deposit');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_hr_new_deposit');?> </a> </li>
        <?php } ?>
        <?php if(in_array('76',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['hr_account_expense_active']))echo $arr_mod['hr_account_expense_active'];?>"> <a href="<?php echo site_url('admin/accounting/expense');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_hr_new_expense');?> </a> </li>
        <?php } ?>
        <?php if(in_array('77',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['hr_account_transfer_active']))echo $arr_mod['hr_account_transfer_active'];?>"> <a href="<?php echo site_url('admin/accounting/transfer');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_acc_transfer');?> </a> </li>
        <?php } ?>
        <?php if(in_array('78',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['hr_account_transactions_active']))echo $arr_mod['hr_account_transactions_active'];?>"> <a href="<?php echo site_url('admin/accounting/transactions');?>" > <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_acc_view_transactions');?> </a> </li>
        <?php } ?>

        <?php if(in_array('82',$role_resources_ids) || in_array('83',$role_resources_ids) || in_array('84',$role_resources_ids) || in_array('85',$role_resources_ids) || in_array('86',$role_resources_ids)){?>
        <li class="treeview"> <a href="#"><i class="fa fa-circle-o"></i> <?php echo $this->lang->line('xin_acc_reports');?> <span class="pull-right-container"> <i class="fa fa-angle-left pull-right"></i> </span> </a>
          <ul class="treeview-menu" style="display: none;">
            <?php if(in_array('83',$role_resources_ids)) { ?>
            <li class="sidenav-link <?php if(!empty($arr_mod['hr_account_statement_active']))echo $arr_mod['hr_account_statement_active'];?>"> <a href="<?php echo site_url('admin/accounting/account_statement');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_acc_account_statement');?> </a> </li>
            <?php } ?>
            <?php if(in_array('84',$role_resources_ids)) { ?>
            <li class="sidenav-link <?php if(!empty($arr_mod['hr_expense_report_active']))echo $arr_mod['hr_expense_report_active'];?>"> <a href="<?php echo site_url('admin/accounting/expense_report');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_acc_expense_reports');?> </a> </li>
            <?php } ?>
            <?php if(in_array('85',$role_resources_ids)) { ?>
            <li class="sidenav-link <?php if(!empty($arr_mod['hr_income_report_active']))echo $arr_mod['hr_income_report_active'];?>"> <a href="<?php echo site_url('admin/accounting/income_report');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_acc_income_reports');?> </a> </li>
            <?php } ?>
            <?php if(in_array('86',$role_resources_ids)) { ?>
            <li class="sidenav-link <?php if(!empty($arr_mod['hr_transfer_report_active']))echo $arr_mod['hr_transfer_report_active'];?>"> <a href="<?php echo site_url('admin/accounting/transfer_report');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_acc_transfer_report');?> </a> </li>
            <?php } ?>
          </ul>
        </li>
        <?php } ?>

      </ul>
    </li>
    <?php } ?>
    <?php } ?>
    <?php if(in_array('95',$role_resources_ids)) { ?>
    <li class="<?php if(!empty($arr_mod['calendar_hr_active']))echo $arr_mod['calendar_hr_active'];?>"> <a href="<?php echo site_url('admin/calendar/hr');?>"> <i class="fa fa-calendar"></i> <span><?php echo $this->lang->line('xin_hr_calendar_title');?></span> </a> </li>
    <?php } ?>
    <?php if($system[0]->module_inquiry=='true'){?>
    <?php if(in_array('43',$role_resources_ids)) { ?>
    <li class="<?php if(!empty($arr_mod['ticket_active']))echo $arr_mod['ticket_active'];?>"> <a href="<?php echo site_url('admin/tickets');?>"> <i class="fa fa-ticket"></i> <span><?php echo $this->lang->line('left_tickets');?></span> </a> </li>
    <?php } ?>
    <?php } ?>
    <?php if($system[0]->module_recruitment=='true'){?>
    <?php  if(in_array('48',$role_resources_ids) || in_array('49',$role_resources_ids) || in_array('51',$role_resources_ids) || in_array('52',$role_resources_ids)) {?>
    <li class="<?php if(!empty($arr_mod['recruit_open']))echo $arr_mod['recruit_open'];?> treeview"> <a href="#"> <i class="fa fa-newspaper-o"></i> <span><?php echo $this->lang->line('left_recruitment');?></span> <span class="pull-right-container"> <i class="fa fa-angle-left pull-right"></i> </span> </a>
      <ul class="treeview-menu">
        <?php if(in_array('49',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['jb_post_active']))echo $arr_mod['jb_post_active'];?>"> <a href="<?php echo site_url('admin/job_post');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_job_posts');?> </a> </li>
        <?php } ?>
        <?php if(in_array('51',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['jb_cand_active']))echo $arr_mod['jb_cand_active'];?>"> <a href="<?php echo site_url('admin/job_candidates');?>"> <i class="fa <?php echo $submenuicon;?>"></i>
          <?php if(in_array('387',$role_resources_ids)) { ?>
          <?php echo $this->lang->line('left_job_candidates');?>
          <?php } else {?>
          <?php echo $this->lang->line('left_job_candidates');?>
          <?php } ?>
          </a> </li>
        <?php } ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['jb_emp_active']))echo $arr_mod['jb_emp_active'];?>"> <a href="<?php echo site_url('admin/job_post/employer');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_jobs_employer');?> </a> </li>
        <li class="sidenav-link <?php if(!empty($arr_mod['jb_pages_active']))echo $arr_mod['jb_pages_active'];?>"> <a href="<?php echo site_url('admin/job_post/pages');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_jobs_cms_pages');?> </a> </li>
      </ul>
    </li>
    <?php } ?>
    <?php } ?>
    <?php if($system[0]->module_files=='true'){?>
    <?php if(in_array('47',$role_resources_ids)) { ?>
    <li class="<?php if(!empty($arr_mod['file_active']))echo $arr_mod['file_active'];?>"> <a href="<?php echo site_url('admin/files');?>"> <i class="fa fa-file-text-o"></i> <span><?php echo $this->lang->line('xin_files_manager');?></span> </a> </li>
    <?php } ?>
    <?php } ?>
    
    <?php if($system[0]->module_assets=='true'){?>
    <?php  if(in_array('24',$role_resources_ids) || in_array('25',$role_resources_ids) || in_array('26',$role_resources_ids)) {?>
    <li class="<?php if(!empty($arr_mod['asst_open']))echo $arr_mod['asst_open'];?> treeview"> <a href="#"> <i class="fa fa-flask"></i> <span><?php echo $this->lang->line('xin_assets');?></span> <span class="pull-right-container"> <i class="fa fa-angle-left pull-right"></i> </span> </a>
      <ul class="treeview-menu">
        <?php if(in_array('25',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['asst_active']))echo $arr_mod['asst_active'];?>"> <a href="<?php echo site_url('admin/assets');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_assets');?> </a> </li>
        <?php } ?>
        <?php if(in_array('26',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['asst_cat_active']))echo $arr_mod['asst_cat_active'];?>"> <a href="<?php echo site_url('admin/assets/category');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_acc_category');?> </a> </li>
        <?php } ?>
      </ul>
    </li>
    <?php } ?>
    <?php } ?>

    <?php if($system[0]->module_events=='true'){?>
    <?php  if(in_array('97',$role_resources_ids) || in_array('98',$role_resources_ids) || in_array('99',$role_resources_ids) || in_array('127',$role_resources_ids) || in_array('131',$role_resources_ids)) {?>
      <li class="<?php if(!empty($arr_mod['hr_events_open']))echo $arr_mod['hr_events_open'];?> treeview"> <a href="#"> <i class="fa fa-calendar-plus-o"></i> <span><?php echo $this->lang->line('xin_hr_events_meetings');?></span> <span class="pull-right-container"> <i class="fa fa-angle-left pull-right"></i> </span> </a>
        <ul class="treeview-menu">

          <?php if(in_array('131',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['epm_event']))echo $arr_mod['epm_event'];?>"> <a href="<?php echo site_url('admin/events/epm_event');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_hr_events');?> </a> </li>
          <?php } ?>

          <?php if(in_array('98',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['hr_events_active']))echo $arr_mod['hr_events_active'];?>"> <a href="<?php echo site_url('admin/events');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_hr_events');?> </a> </li>
          <?php } ?>

          <?php if(in_array('127',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['notice_active']))echo $arr_mod['notice_active'];?>"> <a href="<?php echo site_url('admin/events/notice');?>"> <i class="fa <?php echo $submenuicon;?>"></i> Notice </a> </li>
          <?php } ?>

          <?php if(in_array('99',$role_resources_ids)) { ?>
          <li class="sidenav-link <?php if(!empty($arr_mod['hr_meetings_active']))echo $arr_mod['hr_meetings_active'];?>"> <a href="<?php echo site_url('admin/meetings');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_hr_meetings');?> </a> </li>
          <?php } ?>
        </ul>
      </li>
    <?php } ?>
    <?php } ?>

    <?php  if(in_array('110',$role_resources_ids) || in_array('111',$role_resources_ids) || in_array('112',$role_resources_ids) || in_array('113',$role_resources_ids) || in_array('114',$role_resources_ids) || in_array('115',$role_resources_ids)) {?>
    <li class="<?php if(!empty($arr_mod['reports_open']))echo $arr_mod['reports_open'];?> treeview"> <a href="#"> <i class="fa fa-bar-chart"></i> <span><?php echo $this->lang->line('xin_hr_report_title');?></span> <span class="pull-right-container"> <i class="fa fa-angle-left pull-right"></i> </span> </a>
      <ul class="treeview-menu">
        <?php if(in_array('111',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['reports_payslip_active']))echo $arr_mod['reports_payslip_active'];?>"> <a href="<?php echo site_url('admin/reports/payslip');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_hr_reports_payslip');?> </a> </li>
        <?php } ?>
        <?php if(in_array('112',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['reports_employee_attendance_active']))echo $arr_mod['reports_employee_attendance_active'];?>"> <a href="<?php echo site_url('admin/reports/employee_attendance');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_hr_reports_attendance_employee');?> </a> </li>
        <?php } ?>
        <?php if($system[0]->module_training=='true'){?>
        <?php if(in_array('113',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['reports_employee_training_active']))echo $arr_mod['reports_employee_training_active'];?>"> <a href="<?php echo site_url('admin/reports/employee_training');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_hr_reports_training');?> </a> </li>
        <?php } ?>
        <?php } ?>
        <?php if($system[0]->module_projects_tasks=='true'){?>
        <?php if(in_array('114',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['reports_projects_active']))echo $arr_mod['reports_projects_active'];?>"> <a href="<?php echo site_url('admin/reports/projects');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_hr_reports_projects');?> </a> </li>
        <?php } ?>
        <?php if(in_array('115',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['reports_tasks_active']))echo $arr_mod['reports_tasks_active'];?>"> <a href="<?php echo site_url('admin/reports/tasks');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_hr_reports_tasks');?> </a> </li>
        <?php } ?>
        <?php } ?>
        <?php if(in_array('116',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['reports_roles_active']))echo $arr_mod['reports_roles_active'];?>"> <a href="<?php echo site_url('admin/reports/roles');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_hr_report_user_roles_report');?> </a> </li>
        <?php } ?>
        <?php if(in_array('117',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['reports_employees_active']))echo $arr_mod['reports_employees_active'];?>"> <a href="<?php echo site_url('admin/reports/employees');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_hr_report_employees');?> </a> </li>
        <?php } ?>
        <?php if(in_array('409',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['reports_leave_active']))echo $arr_mod['reports_leave_active'];?>"> <a href="<?php echo site_url('admin/reports/employee_leave');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_hr_report_leave_report');?> </a> </li>
        <?php } ?>
      </ul>
    </li>
    <?php } ?>
    <?php  if(in_array('57',$role_resources_ids) || in_array('60',$role_resources_ids) || in_array('61',$role_resources_ids) || in_array('61',$role_resources_ids) || in_array('62',$role_resources_ids) || in_array('63',$role_resources_ids) || in_array('89',$role_resources_ids) || in_array('93',$role_resources_ids)) {?>
    <li class="<?php if(!empty($arr_mod['system_open']))echo $arr_mod['system_open'];?> treeview"> <a href="#"> <i class="fa fa-cog"></i> <span><?php echo $this->lang->line('xin_system');?></span> <span class="pull-right-container"> <i class="fa fa-angle-left pull-right"></i> </span> </a>
      <ul class="treeview-menu">

        <?php if($user_info[0]->user_role_id==1 || $user_info[0]->user_role_id==2) { ?>
          <li class="<?php if(!empty($arr_mod['roles_active']))echo $arr_mod['roles_active'];?>"><a href="<?php echo site_url('admin/attendance/moveplace');?>"><i class="fa <?php echo $submenuicon;?>"></i>Move Location</a></li>
        <?php } ?>

        <?php if($user_info[0]->user_role_id==1) { ?>
          <li class="<?php if(!empty($arr_mod['roles_active']))echo $arr_mod['roles_active'];?>"><a href="<?php echo site_url('admin/roles');?>"><i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_role_urole');?></a></li>
        <?php } ?>

        <?php if($system[0]->module_language=='true'){?>
        <?php if(in_array('89',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['languages_active']))echo $arr_mod['languages_active'];?>"> <a href="<?php echo site_url('admin/languages');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_multi_language');?> </a> </li>
        <?php } ?>
        <?php } ?>
        <?php if(in_array('60',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['settings_active']))echo $arr_mod['settings_active'];?>"> <a href="<?php echo site_url('admin/settings');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_settings');?> </a> </li>
        <?php } ?>
        <?php if(in_array('93',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['modules_active']))echo $arr_mod['modules_active'];?>"> <a href="<?php echo site_url('admin/settings/modules');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_setup_modules');?> </a> </li>
        <?php } ?>
        <?php if(in_array('94',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['theme_active']))echo $arr_mod['theme_active'];?>"> <a href="<?php echo site_url('admin/theme');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_theme_settings');?> </a> </li>
        <?php } ?>
        <?php if(in_array('118',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['payment_gateway_active']))echo $arr_mod['payment_gateway_active'];?>"> <a href="<?php echo site_url('admin/settings/payment_gateway');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('xin_acc_payment_gateway');?> </a> </li>
        <?php } ?>
        <?php if(in_array('61',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['constants_active']))echo $arr_mod['constants_active'];?>"> <a href="<?php echo site_url('admin/settings/constants');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_constants');?> </a> </li>
        <?php } ?>
        <?php if(in_array('62',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['db_active']))echo $arr_mod['db_active'];?>"> <a href="<?php echo site_url('admin/settings/database_backup');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_db_backup');?> </a> </li>
        <?php } ?>
        <?php if(in_array('63',$role_resources_ids)) { ?>
        <li class="sidenav-link <?php if(!empty($arr_mod['email_template_active']))echo $arr_mod['email_template_active'];?>"> <a href="<?php echo site_url('admin/settings/email_template');?>"> <i class="fa <?php echo $submenuicon;?>"></i> <?php echo $this->lang->line('left_email_templates');?> </a> </li>
        <?php } ?>
      </ul>
    </li>
    <?php } ?>
    <li> &nbsp; </li>
  </ul>
</section>
