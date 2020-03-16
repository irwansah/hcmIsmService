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
              <h4 class="card-title">Posting Report by Total of Views</h4>
                </div>
                <div class="card-body">
                  <div class="toolbar">
                
  
  
              <div class="col-lg-7">
              <div class="form-inline">
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-calendar"></i></span>
                  </div>
                  <input type="date" class="form-control" name="start_date" id="start_date">
                </div>
                <div class="input-group mb-3">
                  <div class="input-group-prepend">
                  <span class="input-group-text" id="basic-addon1"><i class="fa fa-calendar"></i></span>
                  </div>
                  <input type="date" class="form-control" name="end_date" id="end_date">
                </div>  
                <div class="input-group mb-3">
                    <button id="btn-filter" class="ml-2 mt-3 d-4 btn btn-primary btn-border btn-round mr-2" type="button"><i class="fa fa-search"></i></button>
                </div>  
              </div>
                     
              </div>

                  </div>
                  <div class="material-datatables">
                    <table id="data-tables" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%"><thead>
                <tr>
                  <th class="text-center" width="10%">NIK</th>
                  <th class="text-center" width="27%">NAME</th>
                  <th class="text-center">CAPTION</th>
                  <th class="text-center" width="3%">VIEWS TOTAL</th>
                  <th class="text-center" width="20%">CREATED AT</th>

                </tr>
            </thead>
            <tbody>
              <?php foreach ($data as $row){ ?>
              <tr>
                  <td class="text-center"><?php echo $row['owner_id']; ?></td>
                  <td><?php echo $row['owner_name']; ?></td>
                  <td><?php echo $row->caption; ?></td>
                  <td class="text-center"><?php echo $row->views_count; ?></td> 
                  <td class="text-center"><?php echo $row->created_at; ?></td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>  
      </div>

		</div>
	</div>
</div>


