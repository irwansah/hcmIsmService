<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Posting */

$this->title = 'Update Posting: ' . $model->id_posting;
$this->params['breadcrumbs'][] = ['label' => 'Postings', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id_posting, 'url' => ['view', 'id' => $model->id_posting]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="posting-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
