<link rel="stylesheet" href="<?php echo base_url();?>skin/hrsale_assets/theme_assets/bower_components/bootstrap/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="<?php echo base_url();?>skin/hrsale_assets/css/hrsale/xin_hrsale_custom.css">
<body style="background:white">
<?php $this->load->view('admin/head_bangla'); ?>


<h4 class="text-center">Report of Employees Leave List</h4>
<div style="float: right;margin-top:30px">
    <!-- <button class="btn btn-sm btn-primary" id="btn_print" onclick="window.print()">Print</button>    -->
    <form style="float: right;"  action="<?php echo base_url('admin/reports/leave_application/'); ?>" method="post">
        <input type="hidden" name="first_date" value="<?php echo $first_date; ?>">
        <input type="hidden" name="second_date" value="<?php echo $second_date; ?>">
        <input type="hidden" name="sql" value="<?php echo $sql; ?>">
        <input type="hidden" name="exl" value="<?php echo $exl=1; ?>">
        <button class="btn btn-sm btn-info" style="margin-right:15px" type="submit" id="excel">Excel</button>
    </form>
</div>
<table class="table table-striped table-bordered table-responsive">
    <thead style="font-size:12px;" >
        <tr>
            <th class="text-center">S.N</th>
            <th class="text-center">Name</th>
            <th class="text-center">Department</th>
            <th class="text-center">Designation</th>
            <th class="text-center">Applied On</th>
            <th class="text-center">Leave From</th>
            <th class="text-center">Leave To</th>
            <th class="text-center">Reason</th>
            <th class="text-center">Status</th>
            <th class="text-center">Details</th>
        </tr>
    </thead>
    <tbody style="font-size:12px;" >
        <?php  foreach ($app_list as $key => $value) {?>
        <tr>
            <td><?= $key+1?></td>
            <td><?= $value->first_name.' '.$value->last_name?></td>
            <td><?= $value->department_name?></td>
            <td><?= $value->designation_name?></td>
            <td><?= $value->applied_on?></td>
            <td><?= $value->from_date?></td>
            <td><?= $value->to_date?></td>
            <td><?= $value->reason?></td>
            <td><?= $value->status == 1 ? 'Pending' :($value->status == 2 ? 'Approved' : ($value->status == 3 ? 'Reject': 'First Step Approved') )?></td>
            <td><a href="<?php echo base_url('admin/timesheet/leave_details/id/').$value->leave_id?>" >Details</a></td>
        </tr>
        <?php }?>
    </tbody>
</table>

</body>
