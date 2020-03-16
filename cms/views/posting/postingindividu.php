<?php 

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Views';	
$this->params['breadcrumbs'][] = "Reports";
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="col-md-12">
              <div class="card">
                <div class="card-header card-header-danger card-header-icon">
                  <div class="card-icon">
                    <i class="material-icons">assignment</i>
                  </div>
              <h4 class="card-title">Posting Report by Individu</h4>
                </div>
                <div class="card-body">
                  <div class="toolbar">
                  </div>
                  <div class="material-datatables">
                    <table id="data-tables" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%"><thead>
              <tr>
                  <th class="text-center" width="8%">NIK</th>
                  <th class="text-center" width="15%">NAME</th>
                  <th class="text-center" width="15%">GROUP CREATED</th>
                  <th class="text-center">CAPTION</th>
                  <th class="text-center">DIVISION</th>
                  <th class="text-center">DEPARTMENT</th>
                  <th class="text-center" width="10%">VIEWS</th>
                  <th class="text-center" width="10%">LIKES</th>
                  <th class="text-center" width="10%">COMMENTS</th>
                  <th class="text-center" width="15%">CREATED AT</th>
                  <th class="text-center" width="5%">ACTION</th>
                </tr>
            </thead>
            <tbody>
              <?php foreach ($data as $row){ ?>
              <tr>
                  <td class="text-center"><?php echo $row['owner_id'] ?></td>
                  <td class="text-center"><?php echo $row['owner_name']; ?></td>
                  <td class="text-center"><?php echo $row['group_name']; ?></td>
                  <td class="text-center"><?php echo $row['caption']; ?></td>
                  <td class="text-center"><?php echo $row['division']; ?></td>
                 <td class="text-center"><?php echo $row['department']; ?></td>
                 <td class="text-center"><?php echo $row['views_count']; ?></td>
                  <td class="text-center"><?php if($row['like_count']>0){ echo Html::a($row['like_count'], ['listlike', 'id' => $row['id_posting']]); }else{ echo $row['like_count']; } ?>
                  <td class="text-center"><?php if($row['comment_count']>0){ echo Html::a($row['comment_count'], ['listcomment', 'id' => $row['id_posting']]); }else{ echo $row['comment_count'];} ?>
                  <td><?php echo date("d-m-Y H:i",strtotime($row['created_at'])); ?></td>
                  <td class="text-center">
                  <i class="material-icons"><?= Html::a('visibility', ['detail', 'id' => $row['id_posting']]) ?></i>
						    
                  </td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>  
      </div>

		</div>
	</div>
</div></div></div>


