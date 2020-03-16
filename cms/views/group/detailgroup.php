
<?php 

use yii\helpers\Html;

$this->title = 'Detail Groups';	
$this->params['breadcrumbs'][] = ['label' => 'Groups Management', 'url' => ['group/index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="row">
    <div class="col-md-12">
              <div class="card">
                <div class="card-header card-header-danger card-header-icon">
                  <div class="card-icon">
                    <i class="material-icons">assignment</i>
                  </div>
                <h4 class="card-title">Member Of Group <?=$data[0]['group_name'];?></h4>
                </div>
        
	
        
			<div class="card-body">
        <div class="table-responsive">
          <table id="tables-data" class="display table table-striped table-hover">
            <thead>
                <tr>
                  <th width="20%">NIK</th>
                  <th width="40%">NAME MEMBER</th>
                  <th width="15%">COUNT POSTING</th>
                  <th width="15%">JOIN AT</th> 
                
                </tr>
            </thead>
            <tbody>
              <?php foreach ($data as $row){ ?>
              <tr>
                  <td class="text-left"><?php echo $row['id_member']; ?></td>
                  <td><?php echo $row['member_name']; ?></td>
                  <?php if($row['count_posting'] == "")
                  { ?>
                  <td><?php echo "0";?></td> 
                  <?php }else
                    {?>
                  <td><?php echo $row['count_posting']; ?></td>
                      <?php
                        }
                      ?>    
                  <td><?php echo date("d-m-Y H:i",strtotime($row['created_at'])); ?></td>
      
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>  
      </div>

		</div>
	</div>
</div>

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