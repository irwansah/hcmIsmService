<?php
use cms\assets\AppAsset;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $content string */


//check apakah action login jika ya masuk ke login
if (Yii::$app->controller->action->id == 'login') { 
    echo $this->render(
        'main-login',
        ['content' => $content]
    );
} else {
	AppAsset::register($this);
    $directoryAsset = Yii::$app->assetManager->getPublishedUrl('@web/ism');
?>

<?php $this->beginPage() ?>

<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">


<!-- Mirrored from demos.creative-tim.com/material-dashboard-pro/examples/dashboard.html by HTTrack Website Copier/3.x [XR&CO'2014], Fri, 22 Nov 2019 04:04:57 GMT -->
<!-- Added by HTTrack --><meta http-equiv="content-type" content="text/html;charset=utf-8" /><!-- /Added by HTTrack -->
<head>
  <meta charset="<?= Yii::$app->charset ?>">
  <link rel="apple-touch-icon" sizes="76x76" href="<?=Url::base()?>/ism/assets/img/telkom.png">
  <link rel="icon" type="image/png" href="<?=Url::base()?>/ism/assets/img/telkom.png">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <title>
   Internal Social Media
  </title>
  
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

   
<body>
    <?php $this->beginBody() ?>
		
			<!-- header start -->
			<?= $this->render(
				'header.php',
				['directoryAsset' => $directoryAsset]
			)
			?>
			<!-- header menu end -->
			
			<!-- main page start -->		
			
				<!-- content start -->
				<?= $this->render(
					'content.php',
					['content' => $content, 'directoryAsset' => $directoryAsset]
				) ?>
				<!-- content start -->
			</div>
			<!-- main page end -->
		</div>
    <?php $this->endBody() ?>
    </body>
	   
                </div>
              </div>
              <div class="fixed-plugin">
                <div class="dropdown show-dropdown">
                  <a href="#" data-toggle="dropdown">
                    <i class="fa fa-cog fa-2x"> </i>
                  </a>
                  <ul class="dropdown-menu">
            
                    <li class="header-title">Sidebar Background</li>
                    <li class="adjustments-line">
                      <a href="javascript:void(0)" class="switch-trigger background-color">
                        <div class="ml-auto mr-auto">
                          <span class="badge filter badge-black active" data-background-color="black"></span>
                          <span class="badge filter badge-white" data-background-color="white"></span>
                          <span class="badge filter badge-red" data-background-color="red"></span>
                        </div>
                        <div class="clearfix"></div>
                      </a>
                    </li>

                    <li class="adjustments-line">
                      <a href="javascript:void(0)" class="switch-trigger">
                        <p>Sidebar Images</p>
                        <label class="switch-mini ml-auto">
                          <div class="togglebutton switch-sidebar-image">
                            <label>
                              <input type="checkbox" checked="">
                              <span class="toggle"></span>
                            </label>
                          </div>
                        </label>
                        <div class="clearfix"></div>
                      </a>
                    </li>
                    <li class="header-title">Images</li>
            
                     <li>
                      <a class="img-holder switch-trigger" href="javascript:void(0)">
                        <img src="<?=Url::base()?>/ism/assets/img/1.jpg" alt="">
                      </a>
                    </li>
                    <li>
                      <a class="img-holder switch-trigger" href="javascript:void(0)">
                        <img src="<?=Url::base()?>/ism/assets/img/2.jpg" alt="">
                      </a>
                    </li>
                      <li>
                      <a class="img-holder switch-trigger" href="javascript:void(0)">
                        <img src="<?=Url::base()?>/ism/assets/img/3.jpg" alt="">
                      </a>
                    </li>
                   
                  </ul>
                </div>
              </div>
               


    
</html>
<?php $this->endPage() ?>
<?php } ?>