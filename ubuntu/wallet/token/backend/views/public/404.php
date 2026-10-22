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
    <title>管理后台 | 404 </title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <meta content="" name="description" />
    <meta content="" name="author" />
<!-- END HEAD -->

<body class=" page-404-full-page">
<div class="row">
    <div class="col-md-12 page-404">
        <div class="number font-red"> 404 </div>
        <div class="details">
            <h3>Oops! You're lost.</h3>
            <p> We can not find the page you're looking for.</p>
            <!--<form action="#">
                <div class="input-group input-medium">
                    <input type="text" class="form-control" placeholder="keyword...">
                    <span class="input-group-btn">
                        <button type="submit" class="btn red">
                            <i class="fa fa-search"></i>
                        </button>
                    </span>
                </div>
            </form>-->
        </div>
    </div>
</div>
</body>

</html>