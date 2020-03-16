<?php
use yii\helpers\Html;
use yii\helpers\Url;
use common\components\Helpers;
?>
<div class="row" style="margin-top:-50px;">
  <div class="col-lg-12">
     <div class="card">
      <div class="card-header mb-2">
            <h4 class="card-title">Type Data</h4>
      </div>
      <div class="card-body" style="margin-top:-50px;margin-bottom:-15px;">
      <div class="tab-flow m-2" >
          <div class="btn-group" role="group" style="margin-left:-15px;">
            <button type="button" class="btn m-1" onclick="location.href='index.php?r=posting%2Fposting&flags=list'">List Posting</button>
            <button type="button" class="btn btn-primary m-1" onclick="location.href='index.php?r=posting%2Fposting&flags=group'">Group Created</button>
            <button type="button" class="btn btn-primary m-1" onclick="location.href='index.php?r=posting%2Fposting&flags=hierarchy'">Hierarchy</button>
          </div>
      </div>
      </div>
     </div>
  </div>

</div>




<div class="row" style="margin-top:-20px;">
  <div class="col-lg-12">
              <div class="card">
                <div class="card-header card-header-danger card-header-icon">
                  <div class="card-icon">
                    <i class="material-icons">assignment</i>
                  </div>
                  <h4 class="card-title">Data Posting</h4>
                </div>
                <div class="card-body">
                    <div class="filter-tags">
                          <div class="form-inline" style="margin-left:-10px">    
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-calendar"></i></span>
                                </div>
                                <input type="date" class="form-control" name="start_date" id="start_date_tag">
                            </div>  
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-calendar"></i></span>
                                </div>
                                <input type="date" class="form-control" name="end_date" id="end_date_tag">
                            </div> 
                             
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-tags"></i></span>
                                </div>
                                <input type="text" class="form-control" name="tags" id="tags-input" placeholder="input tags ex: #ntags">
                               
                            </div> 
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-building"></i></span>
                                </div>
                                <select class="form-control" id='department-input' name='department' style='width:250px;'>
                                 <option disabled selected>Input Department Name</option>
                                  <option value='1'>1</option>
                                  <option value='2'>2</option>
                                  </select>
                                 
                                  
                            </div> 
                            <div class="input-group mb-3">
                                <button id="btn-filter-tag" class="ml-3 mt-2 btn btn-primary btn-sm btn-round float-right" type="button"><i class="fa fa-search"></i></button>
                            </div>  
                          </div>
                    </div>
                  <div class="material-datatables">
                    <table id="data-tables" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
            <thead>
                <tr>
                <th class="text-center" width="8%">ID</th>
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
              <td class="text-center"><?php echo $row['id_posting'] ?></td>
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
                  <td>
                      <i class="material-icons"><?= Html::a('visibility', ['detail', 'id' => $row['id_posting']]) ?></i>
						      </div>
                  </td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>  
      </div>

		</div>
	</div>
</div>
</div>

<?php
$script = <<< JS
    $(document).ready(function(){
      $('#department-input').select2({
                       minimumInputLength: 2,
                       allowClear: true,
                       placeholder: 'Input Department Name',
                       ajax: {
                          dataType: 'json',
                          url: 'index.php?r=posting%2Femployeedepartment',
                          delay: 800,
                          data: function(params) {
                            return {
                              search: params.term
                            }
                          },
                          processResults: function (data, page) {
                          return {
                            results: data
                          };
                        },
                      }
                  })       
    });
JS;

$this->registerJs($script);
?> 