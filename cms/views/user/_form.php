<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;


?>


<div class="user-form">
      <div class="col-md-12">
              <div class="card">
                <div class="card-header card-header-danger card-header-icon">
                  <div class="card-icon">
                    <i class="material-icons">create</i>
                  </div>
                  <h4 class="card-title">Form User</h4>
                </div>
                  <div class="card-body">
                  <div class="toolbar">
        <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>
      
          <?php echo $form->field($model, 'nik')->dropdownList([
                    1 => 'item 1', 
                    2 => 'item 2'
          ],['prompt'=>'Select NIK','id'=>'nikselect2','style'=>'margin-top:20px','autofocus' => true]
                  
          );?>
          <br>
          <?= $form->field($model, 'username')->textInput() ?>
          <br>
          <?= $form->field($model, 'email')->textInput() ?>
          <br>
          <?= $form->field($model, 'password')->passwordInput(['maxlength' => 25]) ?>
          <br>
          <div class="form-group">
              <?= Html::submitButton('Submit', ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
          </div>

        <?php ActiveForm::end(); ?>
      </div>  
    </div>
  </div>
</div>  





<?php
$script = <<< JS
    $(document).ready(function(){
      $('#nikselect2').select2({
                       minimumInputLength: 2,
                       allowClear: true,
                       placeholder: 'Input Nik / Nama',
                       ajax: {
                          dataType: 'json',
                          url: 'index.php?r=user%2Femployeenik',
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
                  .on('select2:select', function (evt) {
                     var nikdata = $("#nikselect2 option:selected").text();
                      arr= nikdata.split('-');

                      var usern=arr[1].split(' ').join('');
                    
                      $('#signupform-username').val(usern.substr(0,10).toLocaleLowerCase());
                      $('#signupform-email').val(arr[2]);
                  });
         
    });
JS;

$this->registerJs($script);
?> 