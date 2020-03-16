<?php

namespace cms\assets;

use yii\web\AssetBundle;

/**
 * Main cms application asset bundle.
 */
class LoginAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
		'themes/atlantis/assets/css/bootstrap.min.css',
		'themes/atlantis/assets/css/atlantis.css'
	];
	public $js = [
		'themes/atlantis/assets/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js',
		'themes/atlantis/assets/js/core/popper.min.js',
		'themes/atlantis/assets/js/core/bootstrap.min.js',
		'themes/atlantis/assets/js/atlantis.min.js',
		'themes/atlantis/assets/js/plugin/webfont/webfont.min.js'
	];
    public $depends = [
        'yii\web\YiiAsset',
        //'yii\bootstrap\BootstrapAsset',
    ];
}
