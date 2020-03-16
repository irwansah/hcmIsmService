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
                  <h4 class="card-title">Form Edit User</h4>
                </div>
                  <div class="card-body">
                  <div class="toolbar">
        <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>
      
         <br>
          <?= $form->field($model, 'nik')->textInput(['readonly'=> true]) ?>
          <br>
          <?= $form->field($model, 'username')->textInput(['readonly'=> true]) ?>
          <br>
          <?= $form->field($model, 'email') ?>
          <br>
          <?= $form->field($model, 'password')->passwordInput() ?>
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
                       minimumInputLength: 3,
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