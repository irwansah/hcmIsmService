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
          <div class="btn-group " role="group" style="margin-left:-15px;">
            <button type="button" class="btn btn-primary m-1" onclick="location.href='index.php?r=posting%2Fposting&flags=list'">List Posting</button>
            <button type="button" class="btn m-1" onclick="location.href='index.php?r=posting%2Fposting&flags=group'">Group Created</button>
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
                                <select class="form-control" id='name-input' name='grup' style='width:250px;'>
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
                     <!-- table division -->

                     <table id="data-tables" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                         <thead>
                             <tr>
                                <th>No</th>
                                <th>Group</td>
                                <th>Activity</th>
                            </tr>
                         </thead>
                         <tbody>
                            <?php 
                            $ng=1;
                            foreach($models_groted as $res_groted):
                            ?>
                            
                            <tr>
                                <td valign="top"><?php echo $ng?></td>
                                <td valign="top">
                                <?php echo $grot=$res_groted['group_name']=="general" ? ' Tanpa Group' :$res_groted['group_name'] ?>
                                <button type="button" class="btn btn-sm btn-link btn-collapse2" data-toggle="collapse" data-target="#collapse2<?php echo $res_groted['person_id']?>" aria-expanded="true" aria-controls="collapse2<?php echo $ng?>"><i class="fa fa-plus-circle text-primary"></i></button>
                                                              <br>
                                                              <div class="collate collapse multi-collapse" id="collapse2<?php echo $res_groted['person_id']?>">
                                                                    <table class="table table-striped table-no-bordered table-hover dts" style="width:100%;min-width:800px">
                                                                      <thead>
                                                                        <tr>
                                                                            <th>Nik</th>
                                                                            <th>Nama</th>
                                                                            <th>Caption</th>
                                                                            <th>View</th>
                                                                            <th>Like</th>
                                                                            <th>Comment</th>
                                                                            <th>Created At</th>
                                                                            <th>Action</th>
                                                                        </tr>
                                                                      </thead>
                                                                      <tbody>
                                                                        <?php foreach ($models_posting as $res_posting):
                                                                            if($res_posting['group_name']==$res_groted['group_name']):
                                                                          ?>
                                                                          
                                                                            <tr>
                                                                              <td><?php echo $res_posting['owner_id'] ?></td>
                                                                              <td><?php echo $res_posting['owner_name'] ?></td>
                                                                              <td><?php echo $res_posting['caption'] ?></td>
                                                                              <td><?php echo $res_posting['views_count'] ?></td>
                                                                              <td><?php echo $res_posting['like_count'] ?></td>
                                                                              <td><?php echo $res_posting['comment_count'] ?></td>
                                                                              <td><?php echo $res_posting['created_at'] ?></td>
                                                                              <td></td>
                                                                            </tr>
                                                                        <?php 
                                                                          endif;
                                                                          endforeach;?>
                                                                      </tbody>
                                                                    </table>
                                                              </div>
                                </td>
                                <td valign="top"><?php echo $res_groted['count_group']?> Activity Post </td>
                            </tr>

                            <?php 
                            $ng++;
                             endforeach;?>
                         </tbody>
                      </table>
                     
                     </div>

		            </div>
              </div>
              
  </div>
</div>

<?php
$script = <<< JS
    $(document).ready(function(){
      $('#name-input').select2({
                       minimumInputLength: 2,
                       allowClear: true,
                       placeholder: 'Input Group Name',
                       ajax: {
                          dataType: 'json',
                          url: 'index.php?r=posting%2Fnamegroup',
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