<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\Posting */

$this->title = $model->id_posting;
$this->params['breadcrumbs'][] = ['label' => 'Postings', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="posting-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id_posting], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id_posting], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id_posting',
            'id_group',
            'group_name',
            'owner_id',
            'owner_name',
            'caption:ntext',
            'url_content:url',
            'thumnail_content',
            'text',
            'active',
            'views_count',
            'like_count',
            'comment_count',
            'type_posting',
            'created_at',
            'updated_at',
            'deleted_at',
        ],
    ]) ?>

</div>
