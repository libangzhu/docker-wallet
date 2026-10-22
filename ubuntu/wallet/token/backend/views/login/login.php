<?php
use backend\assets\LoginAsset;

LoginAsset::register($this);
$picAsset = $this->getAssetManager()->getBundle(LoginAsset::className())->baseUrl;
$this->beginPage();
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
        <title>管理后台 | 登录</title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="width=device-width, initial-scale=1" name="viewport" />
        <meta content="" name="description" />
        <meta content="" name="author" />
        <link rel="shortcut icon" href="<?=$picAsset.'/pages/img/favicon.ico'?>" />
        <?php $this->head() ?>
        <script language="JavaScript">
            var BaseUrl = '<?=Yii::getAlias('@web')?>';
            var pics = [
                "<?=$picAsset.'/pages/media/bg/1.jpg'?>",
                "<?=$picAsset.'/pages/media/bg/2.jpg'?>",
                "<?=$picAsset.'/pages/media/bg/3.jpg'?>"
            ];
        </script>
    </head>
    <!-- END HEAD -->

    <body class=" login">
    <?php $this->beginBody() ?>
    <!-- BEGIN LOGO -->
    <div class="logo">
        <h2>管理后台</h2>
    </div>
    <!-- END LOGO -->
    <!-- BEGIN LOGIN -->
    <div class="content">
        <!-- BEGIN LOGIN FORM -->
        <form class="login-form" action="<?= \yii\helpers\Url::toRoute('login/login') ?>" method="post">
            <h3 class="form-title"></h3>
            <div class="alert alert-danger display-hide">
                <button class="close" data-close="alert"></button>
                <span> 请输入完整信息. </span>
            </div>
            <div class="form-group">
                <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
                <label class="control-label visible-ie8 visible-ie9">邮箱</label>
                <div class="input-icon">
                    <i class="fa fa-user"></i>
                    <input class="form-control placeholder-no-fix" type="text" autocomplete="on" placeholder="邮箱" name="info[username]" value=<?= is_string($name) ? "$name" : '\'\'' ?> />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label visible-ie8 visible-ie9">密码</label>
                <div class="input-icon">
                    <i class="fa fa-lock"></i>
                    <input class="form-control placeholder-no-fix" type="password" autocomplete="off" placeholder="密码" name="info[password]" /> </div>
            </div>

            <div class="form-actions">
                <label class="checkbox">
                    <input type="checkbox" name="info[rememberMe]" value="1" /> 记住密码 </label>
                <button id="login" type="submit" class="btn blue pull-right"> 登录 </button>
            </div>
        </form>

        <!-- END REGISTRATION FORM -->
    </div>
    <!-- END LOGIN -->
    <!-- BEGIN COPYRIGHT -->
    <div class="copyright"> 2017 &copy; Emerging Field. </div>

<?php
if (isset($error) && $error != '') {
	$this->registerJs("
        $(function () {
            layer.msg('{$error}', {anim: 5});
        });
    ", \yii\web\View::POS_END);
}?>

    <?php $this->endBody() ?>
    </body>

</html>
<?php $this->endPage() ?>