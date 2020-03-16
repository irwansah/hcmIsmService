<?php

namespace cms\assets;

use yii\web\AssetBundle;

/**
 * Main cms application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
			'ism/assets/css/font.css',
      'ism/assets/css/tooltip.css',
      'ism/assets/css/buttons.dataTables.min.css',
      'ism/assets/fontawesome/css/font-awesome.min.css',
      'ism/assets/css/material-dashboard.minf066.css',
      'ism/assets/demo/demo.css',
      'themes/atlantis/assets/css/loader.css',
      'ism/select2.css',
      'ism/modal.css'
	];
	public $js = [
		
		  'ism/assets/demo/jquery.sharrre.js',
       //'ism/assets/js/core/jquery.min.js',
      'ism/assets/js/core/popper.min.js',
      'ism/assets/js/core/bootstrap-material-design.min.js',
      'ism/assets/js/plugins/perfect-scrollbar.jquery.min.js',
      'ism/assets/js/plugins/moment.min.js',
      'ism/assets/js/plugins/jquery.validate.min.js',
      'ism/assets/js/plugins/jquery.bootstrap-wizard.js',
      'ism/assets/js/plugins/jquery.dataTables.min.js', 
      'ism/buttons.github.io/buttons.js',
      'ism/assets/js/material-dashboard.minf066.js?v=2.1.0',
      'ism/last.js',
      'ism/last2.js',
      'ism/select2.min.js'

		];
    public $depends = [
       'yii\web\YiiAsset',
        //'yii\bootstrap\BootstrapAsset',
    ];
}
