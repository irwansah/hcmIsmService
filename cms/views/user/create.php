
<?php

use yii\helpers\Html;

$this->title = 'Create User';
$this->params['breadcrumbs'][] = ['label' => 'Users Management', 'url' => ['user/index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="user-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

