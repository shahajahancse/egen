<link rel="stylesheet" href="<?php echo base_url();?>skin/hrsale_assets/theme_assets/bower_components/bootstrap/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="<?php echo base_url();?>skin/hrsale_assets/css/hrsale/xin_hrsale_custom.css">
<script type="text/javascript" src="<?php echo base_url('skin/hrsale_assets/vendor/jquery/jquery-3.2.1.min.js');?>
"></script>

<body style="background:white">
    <?php  $this->load->view('admin/head_bangla'); ?>

    <style>
        .inptss {
            height: 26px !important;
            border: 1px solid #226ebd !important;
        }

        @media print {
           #hide_c {
            display: none;
           } 
           .btn {
            display: none;
           }
           #pageb {
            page-break-after: always;
           }
        }
    </style>

    <div class="row" style="margin-top: 6px; " id="hide_c">
        <div class="col-md-4"></div>
        <div class="col-md-4">
            <?php // <form action="<?php echo base_url('admin/reports/show_report/'); "> ?>
                <div class="col-md-5" style="padding: 0 2px !important">
                  <div class="form-group">
                    <input type="date" class="form-control inptss" value="<?=$first_date ?>" name="first_date" id="first_date">
                  </div>
                </div>
                <div class="col-md-5" style="padding: 0 !important">
                  <div class="form-group">
                    <input type="date" class="form-control inptss" value="<?=$second_date ?>" name="second_date" id="second_date">
                  </div>
                </div>
                <div class="col-md-2">
                  <button onclick="fetch_data()" class="btn btn-primary btn-sm">Search</button>
                </div>
            <!-- </form> -->
        </div>
        <div class="col-md-3"></div>
    </div>

    <div class="row" id="contentsss" style="margin-left: 15px !important; margin-right: 15px !important;">
        <div id="divc">
            <div style="float: right;">
                <!-- <button class="btn btn-sm btn-primary" id="btn_print" onclick="window.print()">Print</button>    -->
                <form style="float: right;"  action="<?php echo base_url('admin/reports/show_report/'); ?>" method="post">
                <input type="hidden" name="first_date" value="<?php echo $first_date; ?>">
                <input type="hidden" name="second_date" value="<?php echo $second_date; ?>">
                <input type="hidden" name="status" value="<?php echo $status; ?>">
                <input type="hidden" name="sql" value="<?php echo $sql; ?>">
                <input type="hidden" name="elc" value="<?php echo $elc=1; ?>">
                <input type="hidden" name="done" value="1">
                <button class="btn btn-sm btn-info" style="margin-right:15px" type="submit" id="excel">Excel</button>
                </form>
            </div>

            <h4 class="text-center"><span>Report of <?= $data_type ?> Pending List From <?php echo $first_date; ?> To <?php echo $second_date; ?></span></h4>
            <table class="table table-striped table-bordered">
                <thead style="font-size:12px;" >
                    <tr>
                        <th class="text-center">S.N</th>
                        <th class="text-center">Name</th>
                        <th class="text-center">Designation</th>
                        <th class="text-center">Department</th>
                        <th class="text-center">Joining Date</th>
                        <th class="text-center" style="background: #e9ed46;">Emp. Type</th>
                        <th class="text-center">End Date</th>
                        <th class="text-center">Duration</th>
                        <th class="text-center">Increment</th>
                        <?php if($session['role_id']==1){?>
                        <th class="text-center">Gross Salary</th>
                        <?php } ?>
                        <th class="text-center">Note File</th>
                        <th class="text-center">Job Duration</th>
                        <th class="text-center">Remark</th>
                    </tr>
                </thead>
                <tbody style="font-size:12px;" >
                    <?php  if (!empty($pending_list)) {
                        $i=1; foreach ($pending_list as $key => $value) {?>
                        <tr class="text-center">
                            <td><?= $i++?></td>
                            <td><?= $value->first_name.' '.$value->last_name?></td>
                            <td><?= $value->department_name?></td>
                            <td><?= $value->designation_name?></td>
                            <?php if ($value->status == 1) {
                                $emp_status = 'Regular';
                            } else if ($value->status == 4) {
                                $emp_status = 'Intern';
                            } else {
                                $emp_status = 'Probation';
                            } ?>
                            <td><?= $value->date_of_joining?></td>
                            <td style="background: #e9ed46;"><?= $emp_status ?></td>
                            <td><?= $value->next_incre_date?></td>
                            <td>
                            <?php
                            
                                if ($value->status == 1 && !empty($value->last_incre_date)) {
                                    $joiningDate = new DateTime($value->last_incre_date);
                                    $nextIncreDate = new DateTime($value->next_incre_date);
                                    $diff = $joiningDate->diff($nextIncreDate);
                                    
                                    $years = $diff->y;
                                    $months = $diff->m;
                                    $days = $diff->d;

                                } else if (!empty($value->next_incre_date)) {
                                $joiningDate = new DateTime($value->date_of_joining);
                                $nextIncreDate = new DateTime($value->next_incre_date);
                                
                                $diff = $joiningDate->diff($nextIncreDate);
                                
                                $years = $diff->y;
                                $months = $diff->m;
                                $days = $diff->d;
                                } else {
                                    $years = 0;
                                    $months = 0;
                                    $days = 0;
                                }

                            ?>
                            
                           <?=($years)?$years.' Years ':''?><?=($months)?$months.' Months ':''?><?=($days)?$days.' Days ':''?>
                         
                            </td>
                            <td><?= $value->new_salary - $value->old_salary?></td>
                            <?php if($session['role_id']==1){?>
                            <td><?= $value->basic_salary?></td>
                            <?php } ?>
                            <td>
                                <?php if($value->note_file == null){
                                    echo 'No File';
                                }else{?>
                                    <a href="<?=  base_url('uploads/profile/').$value->note_file?>" target="_blank">View</a>
                                    <a href="<?=  base_url('uploads/profile/').$value->note_file?>" download="<?=  base_url('uploads/profile/').$value->note_file?>">Download</a>
                                <?php  }?>
                            </td>

                            <?php 
                                $date1 = new DateTime($value->date_of_joining);
                                $date2 = new DateTime();
                                $interval = date_diff($date1, $date2);
                            ?>
                            <td><?= ($interval->y == 0 ? '':$interval->y.' years ').$interval->m.' months '.$interval->d.' days'?></td>
                            <td><?= $value->remark?></td>
                        </tr>
                    <?php } } ?>
                </tbody>
            </table>
            
            <div id="pageb"></div>
            <br>
            <h4 class="text-center"><span>Report of <?= $data_type ?> Complete List From <?php echo $first_date; ?> To <?php echo $second_date; ?></span></h4>
            <table class="table table-striped table-bordered">
                <thead style="font-size:12px;" >
                    <tr>
                        <th class="text-center">S.N</th>
                        <th class="text-center">Name</th>
                        <th class="text-center">Designation</th>
                        <th class="text-center">Department</th>
                        <th class="text-center">Joining Date</th>
                        <th class="text-center">Emp. Type</th>
                        <th class="text-center">End Date</th>
                        <th class="text-center">Duration</th>
                        <th class="text-center">Increment</th>
                        <?php if($session['role_id']==1){?>
                        <th class="text-center">Gross Salary</th>
                        <?php } ?>
                        <th class="text-center">Note File</th>
                        <th class="text-center">Job Duration</th>
                        <th class="text-center">Remark</th>
                    </tr>
                </thead>
                <tbody style="font-size:12px;" >
                    <?php if (!empty($done_list)) { 
                        $i=1; foreach ($done_list as $key => $value) {?>
                        <tr class="text-center">
                            <td><?= $i++?></td>
                            <td><?= $value->first_name.' '.$value->last_name?></td>
                            <td><?= $value->department_name?></td>
                            <td><?= $value->designation_name?></td>
                            <?php if ($value->status == 1) {
                                $emp_status = 'Regular';
                            } else if ($value->status == 4) {
                                $emp_status = 'Intern';
                            } else {
                                $emp_status = 'Probation';
                            } ?>
                            <td><?= $value->date_of_joining?></td>
                            <td><?= $emp_status ?></td>
                            <td><?= $value->next_incre_date?></td>
                            <td>
                            <?php
                            
                                if ($value->status == 1 && !empty($value->last_incre_date)) {
                                    $joiningDate = new DateTime($value->last_incre_date);
                                    $nextIncreDate = new DateTime($value->next_incre_date);
                                    $diff = $joiningDate->diff($nextIncreDate);
                                    
                                    $years = $diff->y;
                                    $months = $diff->m;
                                    $days = $diff->d;

                                } else if (!empty($value->next_incre_date)) {
                                $joiningDate = new DateTime($value->date_of_joining);
                                $nextIncreDate = new DateTime($value->next_incre_date);
                                
                                $diff = $joiningDate->diff($nextIncreDate);
                                
                                $years = $diff->y;
                                $months = $diff->m;
                                $days = $diff->d;
                                } else {
                                    $years = 0;
                                    $months = 0;
                                    $days = 0;
                                }

                            ?>
                            
                           <?=($years)?$years.' Years ':''?><?=($months)?$months.' Months ':''?><?=($days)?$days.' Days ':''?>
                         
                            </td>
                            <td><?= $value->new_salary - $value->old_salary?></td>
                            <?php if($session['role_id']==1){?>
                            <td><?= $value->basic_salary?></td>
                            <?php } ?>
                            <td>
                                <?php if($value->note_file == null){
                                    echo 'No File';
                                }else{?>
                                    <a href="<?=  base_url('uploads/profile/').$value->note_file?>" target="_blank">View</a>
                                    <a href="<?=  base_url('uploads/profile/').$value->note_file?>" download="<?=  base_url('uploads/profile/').$value->note_file?>">Download</a>
                                <?php  }?>
                            </td>
                            
                            <?php 
                                $date1 = new DateTime($value->date_of_joining);
                                $date2 = new DateTime();
                                $interval = date_diff($date1, $date2);
                            ?>
                            <td><?= ($interval->y == 0 ? '':$interval->y.' years ').$interval->m.' months '.$interval->d.' days'?></td>
                            <td><?= $value->remark?></td>
                        </tr>
                    <?php } } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>


<script>
  function fetch_data(){
    var ajaxRequest;  // The variable that makes Ajax possible!
    ajaxRequest = new XMLHttpRequest();
    first_date = document.getElementById('first_date').value;
    second_date = document.getElementById('second_date').value;

    if(first_date ==''){
      alert('Please select first date');
      return ;
    }
    if(second_date ==''){
      alert('Please select second date');
      return ;
    }

    var data = "first_date="+first_date+"&second_date="+second_date;
    url = "<?php echo base_url('admin/reports/fetch_data'); ?>";

    ajaxRequest.open("POST", url, true);
    ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded;charset=utf-8");
    ajaxRequest.send(data);
    ajaxRequest.onreadystatechange = function(){
      if(ajaxRequest.readyState == 4){
        // console.log(ajaxRequest);
        var resp = ajaxRequest.responseText;
        a = document.getElementById('contentsss');
        b = document.getElementById('divc');
        b.innerHTML = "";
        b.innerHTML = resp;

        // a = window.open('', '_blank', 'menubar=1,resizable=1,scrollbars=1,width=1400,height=800');
        // a.document.write(resp);
      }
    }
  }

</script>



