<?php
// dd($reports);
if(count($reports) == '' || count($reports) == null || empty($reports))
{
    echo "<h2 style='color:red;text-align:center'>No Record Found</h2>";
    exit;
}

?>

<!DOCTYPE html>
<html>
  <head>
    <title>User uses device list</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"/>
  </head>
  <body>
    <div class="container">
     <?php foreach($reports as $row){?>
     <div class="card-body" style="background-color: #343a4005;">     
      <h5>User Info</h5>
      <table class="table-sm">
          <tr>
            <td class="w-25">Name</td>
            <td>:</td>
            <td><?= $row->first_name." ".$row->last_name?></td>
          </tr>
          <tr>
            <td class="w-25">Department</td>
            <td>:</td>
            <td><?= $row->department_name?></td>
          </tr>
          <tr>
            <td class="w-25">Designation</td>
            <td>:</td>
            <td><?= $row->designation_name?></td>
          </tr>
      </table>
      <h5>Device Info</h5>
        <table class="table table-striped table-bordered text-center table-sm">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Device Name/Operator</th>
                    <th>Tag</th>
                    <th>Number</th>
                    <th>Image</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    $this->load->model("Accessories_model");
                    $datas = $this->Accessories_model->get_user_reports_info($row->user_id);
                    foreach($datas as $row){
                ?>
                <tr>
                    <td style="vertical-align: middle;" ><?= $row->cat_name?></td>
                    <td style="vertical-align: middle;" ><?= $row->model_name?></td>
                    <td style="vertical-align: middle;" ><?= $row->device_name_id==0? "" :"MHL ".$row->cat_short_name.'-'.$row->device_name_id?></td>
                    <td style="vertical-align: middle;" ><?= $row->number?></td>
                    <td ><img src="<?php echo empty($row->image)? base_url('uploads/no_image.png'):base_url('uploads/accessory_images/'.$row->image)?>" height="80" width="100"></td>
                </tr>
                <?php }?>
                <!-- Add more rows for additional users and device types -->
            </tbody>
        </table>
     </div>
    <?php }?>
    <div class="mt-2"></div>
    </div>
  </body>
</html>
