<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Group */

$this->title = 'Update Group: ' . $model->id_group;
$this->params['breadcrumbs'][] = ['label' => 'Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id_group, 'url' => ['view', 'id' => $model->id_group]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="group-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
