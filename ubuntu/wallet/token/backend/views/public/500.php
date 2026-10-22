<?php
use backend\assets\AppAsset;
$cssAsset = $this->getAssetManager()->getBundle(AppAsset::className())->baseUrl;
AppAsset::addCss($this, $cssAsset . '/pages/css/error.min.css');
AppAsset::register($this);
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->

<head>
    <meta charset="utf-8" />
    <title>管理后台 | 500 </title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <meta content="" name="description" />
    <meta content="" name="author" />
<!-- END HEAD -->

<body class=" page-500-full-page">
<div class="row">
    <div class="col-md-12 page-500">
        <div class=" number font-red"> 500 </div>
        <div class=" details">
            <h3>Oops! Something went wrong.</h3>

            <p>
                We are fixing it! Please come back in a while.
                <br/>
                <?= $message ?>
            </p>

            <p>
                <a href="<?= \yii\helpers\Url::toRoute('/') ?>" class="btn red btn-outline"> Return home </a>
                <br>
            </p>

        </div>
    </div>
</div>
</body>

</html>