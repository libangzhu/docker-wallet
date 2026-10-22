<?php
/* @var $this \yii\web\View */

/* @var $content string */

use common\models\Menu;
use backend\assets\AppAsset;
use yii\helpers\Html;

AppAsset::register($this);

/* user */
if (!Yii::$app->user->isGuest) {
    $user = Yii::$app->user->identity->username;
} else {
    $user = '访客';
    $token = null;
}

/* route */
$route = $this->context->id . '/' . $this->context->action->id;
if ($route == 'public/error') {
    $this->title = Yii::$app->errorHandler->exception->statusCode;
} else {
    $this->title = Menu::find()->select('title')->where(['url' => $route])->scalar();
}

$picAsset = $this->getAssetManager()->getBundle(AppAsset::className())->baseUrl;
$logout = \yii\helpers\Url::toRoute('login/logout');

$this->beginPage()
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="<?= $picAsset . '/pages/img/favicon.ico' ?>"/>
    <?= Html::csrfMetaTags() ?>
    <title>管理后台<?= ' | ' . Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<div class="page-container-bg-solid page-boxed">
    <!-- BEGIN HEADER -->
    <div class="page-header">

        <!-- BEGIN HEADER TOP -->
        <div class="page-header-top">

            <div class="container" style="min-width: 300px">

                <a href="javascript:;" class="menu-toggler"></a>
                <a href="<?= $logout ?>" class="menu-logout">退出 </a>
                <span href="javascript:;" class="menu-user"><?= $user ?> </span>

            </div>

        </div>
        <!-- END HEADER TOP -->

        <!-- BEGIN HEADER MENU -->

        <div class="page-header-menu">

            <div class="menu-logo"><span>管理后台</span></div>
            <!-- BEGIN MEGA MENU -->
            <div class="hor-menu  ">
                <ul class="nav navbar-nav ">

                    <?= \common\widgets\Breadcrumbs::widget([
                        'homeLink' => false,
                        'links' => Menu::getBreadcrumbs(),
                        'options' => [//设置html属性
                            'class' => 'menu-dropdown classic-menu-dropdown '
                        ],
                        'active' => Menu::find()->select('id, pid, title')
                            ->where(['url' => $route])->asArray()->one()
                    ]) ?>

                </ul>
            </div>
            <!-- END MEGA MENU -->

            <div class="page-header-right">

                <a href="<?= \yii\helpers\Url::toRoute('login/logout') ?>" class="menu-logout">退出 </a>
                <span class="menu-user"><?= $user ?></span>

            </div>

        </div>
        <!-- END HEADER MENU -->
    </div>
    <!-- END HEADER -->

</div>

<div class="page-container">

    <div class="my-container">

        <div class="layui-row">
            <div class="col-md-12">
                <?= $content ?>
            </div>
        </div>

    </div>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
