<?php 

use yii\helpers\Html;
?>

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
<div class="col-md-12">
  <div class="card">
    <div class="card-header card-header-danger card-header-icon">
        <div class="card-icon">
          <i class="material-icons">assignment</i>
        </div>
          <h4 class="card-title">List Comment By Posting</h4>
    </div>

    <div class="card-body" id="push-list-comment">
    
    </div>
              <!--  end card  -->
</div>
</div>
<?php $id=$_GET['id'];?>

<?php $param= Yii::$app->request->csrfParam; ?>
<?php $token=Yii::$app->request->csrfToken; ?>



</div>
<?php


$script = <<< JS
    $(document).ready(function(){
            let search=$('#search_tag').val();
            let ds=$('#date_start').val();
            let de=$('#date_end').val();
            $.ajax({
                url:'index.php?r=posting%2Flistcommentfetch',
                type: "GET",
                data:{"id":"$id","$param":"$token"},
                success:function(data){
                    $("#push-list-comment").html(data);
                    $("#scroll-div").focus();                    

                }
            });
         
    });
JS;

$this->registerJs($script);
?> 

