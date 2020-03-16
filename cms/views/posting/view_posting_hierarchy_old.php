<?php

use yii\helpers\Html;
use yii\helpers\Url;
use common\components\Helpers;



?>
<div class="row">
  <div class="col-12-lg">
      <div class="tab-flow m-2">
          <div class="btn-group" role="group" aria-label="Basic example">
            <button type="button" class="btn m-1" onclick="location.href='index.php?r=posting%2Fposting&flags=hierarchy'">Hirarki</button>
            <button type="button" class="btn btn-primary m-1" onclick="location.href='index.php?r=posting%2Fposting&flags=group'">Edited Group </button>
            <button type="button" class="btn btn-primary m-1" onclick="location.href='index.php?r=posting%2Fposting&flags=list'">List Posting</button>
          </div>
      </div>
  </div>
</div>
<div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header">
            <h4 class="card-title">Filter Tags </h4>
      </div>
      <div class="card-body">
      <div class="filter-tags" style="margin-top:-30px;">
                          <div class="form-inline">
                      
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-tags"></i></span>
                                </div>
                                <input type="text" class="form-control" name="tags" id="tags-input" placeholder="input tags ex: #ntags">
                            </div>  
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
                                <button id="btn-filter-tag" class="ml-2 mt-3 d-4 btn btn-primary btn-border btn-round mr-2" type="button"><i class="fa fa-search"></i></button>
                            </div>  
                          </div>
                        </div>
      </div>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-lg-12">
              <div class="card">
                <div class="card-header card-header-danger card-header-icon">
                  <div class="card-icon">
                    <i class="material-icons">assignment</i>
                  </div>
                  <h4 class="card-title">Data Posting</h4>
                </div>


                <div class="card-body">
                     <div class="material-datatables">
                     <!-- table division -->
                     <table id="data-tables" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                         <thead>
                             <tr>
                                <th>No</th>
                                <th>Division</td>
                                <th>Activity</th>
                            </tr>
                         </thead>
                         <tbody>
                            <?php 
                            $nd=1;
                            foreach($models_division as $res_division):?>
                            <tr>
                              
                                <td valign="top"><?php echo $nd?></td>
                                <td valign="top">
                                  <?php echo $div=$res_division['division']=="" ? ' Tidak Punya Divisi' :$res_division['division'] ?>
                                  <button type="button" class="btn btn-sm btn-link btn-collapse" data-toggle="collapse" data-target="#collapse<?php echo $nd?>" aria-expanded="true" aria-controls="collapse<?php echo $nd?>"><i class="fa fa-plus-circle text-primary"></i></button>
                                  <br>
                                  <div class="collate collapse multi-collapse" id="collapse<?php echo $nd?>">
                                  <table class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                      <thead>
                                          <tr>
                                              <th>No</th>
                                              <th>Department</td>
                                          </tr>
                                      </thead>
                                      <tbody>
                                          <?php 
                                          $nv=1;
                                          foreach($models_department as $res_department):
                                            if($res_department['department']==$res_division['department']):
                                              
                                          ?>
                                          
                                          <tr>
                                            
                                              <td valign="top"><?php echo $nv?></td>
                                              <td valign="top"><?php echo $div=$res_department['department']=="" ? ' Tidak Punya Departmen' :$res_department['department'] ?>
                                              <button type="button" class="btn btn-sm btn-link btn-collapse2" data-toggle="collapse" data-target="#collapse2<?php echo $res_department['person_id']?>" aria-expanded="true" aria-controls="collapse2<?php echo $nv?>"><i class="fa fa-plus-circle text-primary"></i></button>
                                              <br>
                                              <div class="collate collapse multi-collapse" id="collapse2<?php echo $res_department['person_id']?>">
                                                    <table class="table table-striped table-no-bordered table-hover" width="100%">
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
                                                            if($res_posting['owner_id']==$res_department['owner_id']):
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
                                              
                                          </tr>
                                          
                                          <?php 
                                          $nv++;
                                          endif;
                                         
                                        endforeach;?>
                                      </tbody>
                                    </table>
                                  </div>
                                </td>
                                <td valign="top"><?php echo $res_division['count_division']?> </td>
                            </tr>

                            
                            <?php 
                            $nd++;
                          endforeach;?>
                         </tbody>
                      </table>
                     </div>

		            </div>
              </div>
              
  </div>
</div>