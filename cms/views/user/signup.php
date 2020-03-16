<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Register User';
$this->params['breadcrumbs'][] = ['label' => 'Users Management', 'url' => ['user/index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="row">
	<div class="col-md-12">
		<div class="card">
			<div class="card-header">
				<div class="d-flex align-items-center">
          <h4 class="card-title">User Registration Form</h4>
				</div>
			</div>
			<div class="card-body">
        <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>
          <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>
          <?= $form->field($model, 'email') ?>
          <?= $form->field($model, 'password')->passwordInput() ?>

          <div class="form-group">
              <?= Html::submitButton('Submit', ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
          </div>

        <?php ActiveForm::end(); ?>
      </div>  
    </div>
  </div>
</div>  