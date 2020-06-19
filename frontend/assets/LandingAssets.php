<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class LandingAssets extends AssetBundle
{
	public $basePath = '@webroot';
	public $baseUrl = '@web';
	public $css = [
	    "https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css",
		//"https://fonts.googleapis.com/css?family=Open+Sans:300,400,400i,600,700,800",
		"https://fonts.googleapis.com/css?family=Nunito",
	    "landing/assets/css/bootstrap.min.css",
		"landing/assets/css/slick.css",
	    "landing/assets/css/theme-color/orange-theme.css",
		"landing/style.css"
	];
	public $js = [
	    "https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js",
	    "landing/assets/js/bootstrap.min.js",
	    "landing/assets/js/slick.min.js",
	    "landing/assets/js/app.js",
		"landing/assets/js/custom.js",
	];

	public $depends = [];
}
