<?php 

use yii\helpers\Html;

$this->title = 'Groups Management';	
$this->params['breadcrumbs'][] = $this->title;

?>

		<div class="col-md-12">
              <div class="card">
                <div class="card-header card-header-danger card-header-icon">
                  <div class="card-icon">
                    <i class="material-icons">assignment</i>
                  </div>
                  <h4 class="card-title">Data Group</h4>
                </div>
        
      <!-- body -->
			<div class="card-body">
         <div class="material-datatables">
                    <table id="data-tables" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
            <thead>
                <tr>
                  <th class="text-left" width="15%">ID GROUP</th>
                  <th class="text-left" width="20%">GROUP NAME</th>
                  <th class="text-left" width="25%">NAME</th>
                  <th class="text-left" width="15%">MEMBER</th>
                  <th class="text-left" width="15%">CREATED AT</th>
                  <th class="text-left" width="15%">ACTION</th>
                </tr>
            </thead>
            <tbody>
              <?php foreach ($data as $row){ ?>
              <tr>
                  <td class="text-left"><?php echo $row['id_group']; ?></td>
                  <td><?php echo $row['group_name']; ?></td>
                  <td><?php echo $row['owner_name']; ?></td>
                  <?php if($row['member_scope'] == 1)
                  { ?>
                   <td><?php echo 'All Employee'; ?></td> 
                  <?php } else { ?>
                  <td><?php echo $row['count_member'];?></td> 
                  <?php } ?>

                  <td><?php echo date("d-m-Y H:i",strtotime($row['created_at'])); ?></td>
                  <?php if($row['member_scope']== 1)
                  {?>
                    <td><?php echo 'All Employee'; ?></td> 
                 <?php }
                  elseif($row['count_member'] == 0 && $row['count_member'] == 0)
                  {?>
                    <div class="form-button-action">
                    <td><?php echo "don't have a member yet"; ?></td> <?php } 
                  else
                  {?>
                  <td>
                    <div class="form-button-action">
											<?= Html::a('<i class="fa fa-eye text-danger"></i>', 
                          ['detail', 'id' => $row['id_group']],
                          ['class' => 'btn btn-link btn-danger', 'title' => 'View Detail']) 
                      ?>
                  </div></td><?php } ?>
                  </tr>
                  <?php } ?>
                </tbody></table></div></div></div></div>

<!-- JS SCRIPT -->
<?php
$script = <<< JS
    $(document).ready(function(){
			$("#tables-data").DataTable({
        "order": [[ 3, "desc" ]]  
			});
		});
JS;

$this->registerJs($script);
?>  