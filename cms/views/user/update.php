<?php

use yii\helpers\Html;

$this->title = 'Update User: '.$model->username;
$this->params['breadcrumbs'][] = ['label' => 'Users Management', 'url' => ['user/index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="user-update">

    <?= $this->render('_formedit', [
        'model' => $model,
    ]) ?>

</div>
