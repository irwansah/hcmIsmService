<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;

$this->title = 'Sign In';

$fieldOptions = [
    'options' => ['class' => 'form-control'],
    'inputTemplate' => '{input}'
];
?>

<div class="login-aside w-50 d-flex flex-column align-items-center justify-content-center text-center bg-danger-gradient">
	<img src="<?= Url::base() ?>/themes/atlantis/assets/img/logo_telkomsel_white.png" alt="" width="60%">
</div>
<div class="login-aside w-50 d-flex align-items-center justify-content-center bg-white">
	<div class="container container-login container-transparent animated fadeIn">
		<h3 class="text-center">Sign In</h3>
		<?php $form = ActiveForm::begin(['id' => 'login-form', 'enableClientValidation' => false]); ?>
			<div class="form-group">
				<label for="username" class="placeholder"><b>Username</b></label>
				<?= $form
				->field($model, 'username', $fieldOptions)
				->label(false)
				->textInput(['placeholder' => $model->getAttributeLabel('username')]) ?>
			</div>
			<div class="form-group">
				<label for="password" class="placeholder"><b>Password</b></label>
				<div class="position-relative">
					<?= $form
					->field($model, 'password', $fieldOptions)
					->label(false)
					->passwordInput(['placeholder' => $model->getAttributeLabel('password')]) ?>
					<div class="show-password">
						<i class="icon-eye"></i>
					</div>
				</div>
			</div>
			<div class="form-group form-action-d-flex mb-3">
				<?= Html::submitButton('Sign in', ['class' => 'btn btn-danger col-md-12 float-right mt-3 mt-sm-0 fw-bold', 'name' => 'login-button']) ?>
			</div>
		<?php ActiveForm::end(); ?>	
	</div>
</div>