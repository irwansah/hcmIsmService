<?php 
use yii\helpers\Html; 
use yii\helpers\Url;
use common\components\Helpers;

//check profile picture
$person_id = empty(Yii::$app->user->identity->employee) ? "0" : Yii::$app->user->identity->employee->person_id;
$url = Helpers::PICTURE_URL.$person_id;
$arrayUrl = @get_headers($url); 
if(strpos($arrayUrl[0], "200")) { 
    $srcImg = $url;
}  
else { 
	//default picture;
    $srcImg = Url::base()."/ism/assets/img/default-avatar.png";
}

?>
<!-- header page start --><div class="wrapper ">
    <div class="sidebar" data-color="rose" data-background-color="red" data-image="<?=Url::base()?>/ism/assets/img/3.jpg">

      <div class="logo">
        <a href="./" class="simple-text logo-mini nav-link"><i class="material-icons">cloud</i></a>
        <a href="./" class="simple-text logo-normal">CMS <br><p class="simple-text" style="font-size:8pt;margin-top:-20px;diplay:block;font-family:corbel">Internet Sosial Media</p></a>
      </div>
      <div class="sidebar-wrapper">
        <div class="user">
          <div class="photo">
            <img src="<?= $srcImg ?>" />
          </div>
          <div class="user-info">
            <a class="nav-link">
              <span>
            <?= empty(Yii::$app->user->identity->nik) ? Yii::$app->user->identity->username : Yii::$app->user->identity->nik.' - ' .Yii::$app->user->identity->username ?></span>
            </a>
          
          </div>
        </div>
        <ul class="nav">
          <li class="nav-item">
            <a class="nav-link" href="<?=Url::base()?>/index.php">
              <i class="material-icons">dashboard</i>
              <p> Dashboard </p>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?=Url::base()?>/index.php?r=user%2Findex">
              <i class="material-icons">person_add</i>
              <p> Management User </p>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?=Url::base()?>/index.php?r=posting%2Fposting">
              <i class="material-icons">view_list</i>
              <p> Management Posting</p>
            </a>
          </li>
      
             <li class="nav-item">
            <a class="nav-link" href="<?=Url::base()?>/index.php?r=group%2Findex">
                   <i class="material-icons">people_alt</i>
              <p> Management Group </p>
            </a>
          </li>
          <li class="nav-item ">
            <a class="nav-link" data-toggle="collapse" href="#pagesExamples">
              <i class="material-icons">description</i>
              <p> Report
                <b class="caret"></b>
              </p>
            </a>
            <div class="collapse" id="pagesExamples">
              <ul class="nav">
              <li class="nav-item ">
                  <a class="nav-link" href="<?=Url::base()?>/index.php?r=posting%2Findividu">
                    <span class="sidebar-mini"> RI </span>
                    <span class="sidebar-normal"> Report Individu </span>
                  </a>
                </li>
                <li class="nav-item ">
                  <a class="nav-link" href="<?=Url::base()?>/index.php?r=posting%2Findex">
                    <span class="sidebar-mini"> RV </span>
                    <span class="sidebar-normal"> Report View </span>
                  </a>
                </li>
                <li class="nav-item ">
                  <a class="nav-link" href="<?=Url::base()?>/index.php?r=posting%2Flike">
                    <span class="sidebar-mini"> RL </span>
                    <span class="sidebar-normal"> Report Like </span>
                  </a>
                </li>
                <li class="nav-item ">
                  <a class="nav-link" href="<?=Url::base()?>/index.php?r=posting%2Fcomment">
                    <span class="sidebar-mini"> RC </span>
                    <span class="sidebar-normal"> Report Comment </span>
                  </a>
                </li>
              </ul>
            </div>
          </li>
        </ul>
      </div>
    </div>
   <div class="main-panel">
      <!-- Navbar -->
      <nav class="navbar navbar-expand-lg navbar-transparent navbar-absolute fixed-top ">
        <div class="container-fluid">
          <div class="navbar-wrapper">
            <div class="navbar-minimize">
              <button id="minimizeSidebar" class="btn btn-just-icon btn-white btn-fab btn-round">
                <i class="material-icons text_align-center visible-on-sidebar-regular">more_vert</i>
                <i class="material-icons design_bullet-list-67 visible-on-sidebar-mini">view_list</i>
              </button>
            </div>
      
          </div>
          <button class="navbar-toggler" type="button" data-toggle="collapse" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
            <span class="sr-only">Toggle navigation</span>
            <span class="navbar-toggler-icon icon-bar"></span>
            <span class="navbar-toggler-icon icon-bar"></span>
            <span class="navbar-toggler-icon icon-bar"></span>
          </button>
          <div class="collapse navbar-collapse justify-content-end">
           
            <ul class="navbar-nav">
              
            
              <li class="nav-item dropdown">
                <a class="nav-link" href="#pablo" id="navbarDropdownProfile" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="material-icons">person</i>
                  <p class="d-lg-none d-md-block">
                    Account
                  </p>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownProfile">
                 
                 
                 <center> <?= Html::a(
                                    'Sign out',
                                    ['site/logout'],
                                    ['data-method' => 'post', 'class' => 'dropdown-item']
                                ) ?> </center>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </nav>