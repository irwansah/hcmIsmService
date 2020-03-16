<?php 

use yii\helpers\Html;

$this->title = 'Users Management';	
$this->params['breadcrumbs'][] = $this->title;

//validasi notification
if(Yii::$app->session->hasFlash('success-notif')){
  $icon_notif = "fas fa-check-circle";
  $flag_notif = "Success";
  $content_notif = Yii::$app->session->getFlash('success-notif');
  $visible_notif = 1;
}elseif(Yii::$app->session->hasFlash('error-notif')){
  $icon_notif = "fas fa-times-circle";
  $flag_notif = "Error";
  $content_notif = Yii::$app->session->getFlash('error-notif');
  $visible_notif = 1;
}
else{
  $icon_notif = "";
  $flag_notif = "";
  $content_notif = "";
  $visible_notif = 0;
}
if(Yii::$app->session->hasFlash('success-notif'))
{
?>
<div id="row">
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <strong><?= $content_notif?></strong> 
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div></div> <?php
}
else {
?> 

<?php } ?>

<!-- hidden input for notification -->
<input type="hidden" id="icon_notif" value="<?= $icon_notif ?>" />
<input type="hidden" id="flag_notif" value="<?= $flag_notif ?>" />
<input type="hidden" id="content_notif" value="<?= $content_notif ?>" />
<input type="hidden" id="visible_notif" value="<?= $visible_notif ?>" />


<div class="col-md-12">
              <div class="card">
                <div class="card-header card-header-danger card-header-icon">
                  <div class="card-icon">
                    <i class="material-icons">assignment</i>
                  </div>
                  <h4 class="card-title">Data User</h4>
                </div>



                <div class="card-body">
                  <div class="toolbar"><br>
                    <p>
        <?= Html::a('Create User', ['create'], ['class' => 'btn btn-primary btn-round']) ?>
    </p>
                    <!--        Here you can write extra buttons/actions for the toolbar              -->
                  </div>
                  <div class="material-datatables">
                    <table id="tables-data" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                      <thead>
                         <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row) : ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['username']; ?></td>
                <td><?php echo $row['email']; ?></td>
               
                <td>
                  
                     <i class="material-icons"><?= Html::a('input', ['update', 'id' => $row['id']]) ?>
                      <?= Html::a('delete', ['delete', 'id' => $row['id']], [
           
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    
                </td>
            </tr>
        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
                <!-- end content-->
              </div>
              <!--  end card  -->
            </div>

<!-- JS SCRIPT -->
<?php
$script = <<< JS
    $(document).ready(function(){      
      
			$("#tables-data").DataTable({
        "order": [[ 2, "asc" ]],
			});
		});
JS;

$this->registerJs($script);
?>  