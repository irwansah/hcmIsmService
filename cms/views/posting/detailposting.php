<?php 

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = 'Detail Posts';	
$this->params['breadcrumbs'][] = ['label' => 'Posts Management', 'url' => ['posting/posting']];
$this->params['breadcrumbs'][] = $this->title;

//check content 
$visibleContent = !empty($data->postingDetail) ? (($data->postingDetail->file_type == 'image/jpeg') ? true : false) : false;
?>

<div class="col-md-12">
              <div class="card">
                <div class="card-header card-header-danger card-header-icon">
                  <div class="card-icon">
                    <i class="material-icons">assignment</i>
                  </div>
                  <h4 class="card-title">Detail Posting</h4>
                </div>
                <div class="card-body">
                  <div class="toolbar">
                    <!--        Here you can write extra buttons/actions for the toolbar              -->
                  </div>
                  <div class="material-datatables">
                          

                   <table id="bootstrap-data-table" class="table table-striped table-bordered">
        <?= DetailView::widget([
          'model' => $data,
          'attributes' => [
              [
                'label' => 'ID',
                'value' => function($data) {
                    return $data->id_posting;
                }
              ],
              [
                'label' => 'NIK',
                'value' => function($data) {
                    return $data->employee['nik'];
                }
              ],
              [
                'label' => 'Name',
                'value' => function($data) {
                    return $data->employee['nama'];
                }
              ],
              [
                'label' => 'Caption',
                'value' => function($data) {
                    return $data->caption;
                }
              ],
              [
                'label' => 'Views',
                'value' => function($data) {
                    return $data->views_count;
                }
              ],
              [
                'label' => 'Likes',
                'value' => function($data) {
                    return $data->like_count;
                }
              ],
              [
                'label' => 'Comments',
                'value' => function($data) {
                    return $data->comment_count;
                }
              ],
              [
                'format'=>'raw',
                'label' => 'Content',
                'value' => function($data) {
                  return '<img src="data:'.$data->postingDetail->file_type.';base64,'.$data->postingDetail->file_content.'" width = "400px" height = "auto" style="margin-top: 10px; margin-bottom: 10px;"/>';;
                },
                'visible' => $visibleContent
              ],
              [
                'label' => 'Created At',
                'value' => function($data) {
                    return date("d-m-Y H:i",strtotime($data->created_at));
                }
              ],
              [
                'label' => 'Updated At',
                'value' => function($data) {
                  return date("d-m-Y H:i",strtotime($data->updated_at));
                }
              ],
          ],
        ]) ?>
      </div>

		</div>
	</div>
</div>