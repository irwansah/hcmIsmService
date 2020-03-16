<?php 
use yii\widgets\DetailView;
?>

<div class="row">
<div class="col-md-6">
<div class="toolbar" >
  <?php 
  echo DetailView::widget([
    'model' => $data,
    'attributes' => [
        [
          'label' => 'ID Posting',
          'value' => function($data) {
              return $data[0]->id_posting;
          }
        ],
          [
          'label' => 'Owner ID',
          'value' => function($data) {
                  return $data[0]->owner_id;
          }
        ],
        [
          'label' => 'Owner Name',
          'value' => function($data) {
                  return $data[0]->owner_name;
          }
        ],
        [
          'label' => 'Owner EMail',
          'value' => function($data) {
                  return $data[0]->owner_email;
          }
        ],
    ],
  ]);
   
   ?>
   
</div>
</div>
<div class="col-md-6">
<div class="material-datatables"  style="height:300px;overflow-y:scroll;padding:10px;border:1px solid #dddddd">
    <table id="table-list-comment" class="table table-striped table-no-bordered table-hover"  style="width:100%">
      <thead>
        <tr>
          <th>Comment</th>
          <th>Comment At</th>
        </tr>
      </thead>
      <tbody>
                <?php foreach ($data as $row) : ?>
                <tr>
                  
                    <td><?php echo $row['comment']; ?></td>
                    <td><?php echo $row['created_at']; ?></td>
                  
                  
                  
                </tr>
                <?php endforeach; ?>
      </tbody>
    </table>
</div>
</div>
</div>
