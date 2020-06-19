<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use frontend\assets\LandingAssets;

LandingAssets::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
	<meta charset="<?= Yii::$app->charset ?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?= Html::csrfMetaTags() ?>
	<title><?= Html::encode($this->title) ?> - Dengarkan Sekitarmu</title>
	<?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<!-- Start Header -->
<header id="mu-header" class="" role="banner">
	<div class="mu-header-overlay">
		<div class="container">
			<div class="mu-header-area">
				<!-- Start Logo -->
				<div class="mu-logo-area">
					<!-- text based logo -->
					<!-- image based logo -->
					
					 <a class="mu-logo" href="#"><img src="<?= Yii::getAlias("@web/landing/assets/images/logo.png")?>" alt="logo img"></a>
					
				</div>
				<!-- End Logo -->

				<!-- Start header featured area -->
				<div class="mu-header-featured-area">
					<div class="mu-header-featured-img">
						<img src="landing/assets/images/iphone.png" alt="iphone image">
					</div>

					<div class="mu-header-featured-content">
						<h1><span><?= Yii::$app->name ?></span></h1>
						<p>Ada apa sih di sekitar kita? yuk dengerin cerita-cerita menarik sekitar kita dan mulailah mengobrol!</p>

						<div class="mu-app-download-area">
							<h4>Download The App</h4>
							<a class="mu-apple-btn" href="#"><i class="fa fa-apple"></i><span>apple store (Coming Soon) </span></a>
							<a class="mu-google-btn" href=<?= Yii::$app->params['urlQrungu'] ?>><i class="fa fa-android"></i><span>google play</span></a>
							<!-- <a class="mu-windows-btn" href="#"><i class="fa fa-windows"></i><span>windows store</span></a> -->
						</div>

					</div>
				</div>
				<!-- End header featured area -->
			</div>
		</div>
	</div>
</header>
<!-- End Header -->

<!-- Start Menu -->

<button class="mu-menu-btn">
	<i class="fa fa-bars"></i>
</button>
<div class="mu-menu-full-overlay">
	<div class="mu-menu-full-overlay-inner">
		<a class="mu-menu-close-btn" href="#"><span class="mu-line"></span></a>
		<nav class="mu-menu" role="navigation">
			<ul>
				<li><a href="#mu-header">App</a></li>
				<li><a href="#mu-feature">About</a></li>
				<!--<li><a href="#mu-video">Promo Video</a></li>-->
				<li><a href="#mu-apps-screenshot">Apps Screenshot</a></li>
				<li><a href="#mu-download">Download</a></li>
				<!--<li><a href="#mu-faq">FAQ</a></li>-->
				<li><a href="#mu-contact">Get In Touch</a></li>
			</ul>
		</nav>
	</div>
</div>
<!-- End Menu -->

<main role="main">

	<!-- Start Feature -->
	<?= $this->render ('@app/views/landing/feature') ?>
	<!-- End Feature -->

	<!-- Start Video -->
	<?= $this->render ('@app/views/landing/video') ?>
	<!-- End Video -->

	<!-- Start Apps Screenshot -->
	<?= $this->render ('@app/views/landing/screenshot') ?>
	<!-- End Apps Screenshot -->

	<!-- Start Download -->
	<?= $this->render ('@app/views/landing/download') ?>
	<!-- End Download -->

	<!-- Start FAQ -->
	<!-- End FAQ -->


	<!-- Start Contact -->
	<?= $this->render ('@app/views/landing/contact') ?>
	<!-- End Contact -->

</main>

<!-- Start footer -->
<footer id="mu-footer" role="contentinfo">
	<div class="container">
		<div class="mu-footer-area">
			<p class="mu-copy-right">&copy; Copyright <a rel="nofollow" href="#">markups.io</a>. All right reserved.</p>
		</div>
	</div>

</footer>
<!-- End footer -->

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
