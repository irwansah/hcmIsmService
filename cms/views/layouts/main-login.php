<?php
use cms\assets\LoginAsset;
use yii\helpers\Html;
use yii\helpers\Url;


/* @var $this \yii\web\View */
/* @var $content string */

LoginAsset::register($this);
$directoryAsset = Yii::$app->assetManager->getPublishedUrl('@web/themes/atlantis/assets');
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
	<head>
        <meta charset="<?= Yii::$app->charset ?>"/>
		<meta content='width=device-width, initial-scale=1.0, shrink-to-fit=no' name='viewport' />
        <?= Html::csrfMetaTags() ?>
        <title>Internal Social Media - <?php echo Html::encode($this->title);?></title>
		<link rel="shortcut icon" href="<?= Url::base() ?>/themes/atlantis/assets/img/telkomsel.png" rel="icon" type="image/png">
        <?php $this->head() ?>
	</head>
	<body class="login">
	<?php $this->beginBody() ?>
		<div class="wrapper wrapper-login wrapper-login-full p-0">
	
			<?= $content ?>
		</div>
	<?php $this->endBody() ?>
	</body>

</html>
<?php $this->endPage() ?>
