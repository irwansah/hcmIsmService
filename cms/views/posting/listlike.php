<?php 

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\mpdf\Pdf;
?>

<div class="col-md-12">
              <div class="card">
                <div class="card-header card-header-danger card-header-icon">
                  <div class="card-icon">
                    <i class="material-icons">assignment</i>
                  </div>
              <h4 class="card-title">List Likers By Posting</h4>
                </div>
                <div class="card-body">
                  <div class="toolbar">
                             <table id="bootstrap-data-table" class="table table-striped table-bordered">
        <?= DetailView::widget([
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
          ],
        ]) ?>
                  </div>
                  <div class="material-datatables">
                    <table id="data-tables" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%"><thead>
                         <tr>
                         
             
                 <th>NIK </th>
                 <th>Likers Name</th>
                 <th>Liked At</th>
              
                        </tr>
                      </thead>
        <tbody>
            <?php foreach ($data as $row) : ?>
            <tr>
              
                <td><?php echo $row['owner_id']; ?></td>
                <td><?php echo $row['owner_name']; ?></td>
                <td><?php echo $row['created_at']; ?></td>
               
               
              
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