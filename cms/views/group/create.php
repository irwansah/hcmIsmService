<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Group */

$this->title = 'Create Group';
$this->params['breadcrumbs'][] = ['label' => 'Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>


    <?= $this->render('_form', [
        'model' => $model,
        'pegawai' => $pegawai
    ]) ?>

</div>
