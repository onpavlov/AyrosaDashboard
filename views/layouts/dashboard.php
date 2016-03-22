<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use app\assets\AppAsset;

AppAsset::register($this);
?>

<?php $this->beginPage();?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
    <?php
    NavBar::begin([
        'brandLabel' => '<img src="/images/logo.png" style="height: 100%">',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    if (!Yii::$app->user->isGuest) {
        $menuItems[] = '<li>'
            . Html::beginForm(['/site/logout'], 'post')
            . Html::submitButton(
                'Выйти (' . Yii::$app->user->identity->username . ')',
                ['class' => 'btn btn-link']
            )
            . Html::endForm()
            . '</li>';
        $menuItems[] = '<li style="margin: 0 5px"><img src="' . $this->context->avatar . '" alt="' . Yii::$app->user->identity->username . '" class="img-circle"></li>';
        $menuItems[] = '<li>   ' . Html::label(Yii::$app->user->identity->firstname . ' ' . Yii::$app->user->identity->lastname, null, ['class' => 'username']) . '</li>';
    }
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => (isset($menuItems) && !empty($menuItems)) ? $menuItems : array(),
    ]);
    NavBar::end();
    ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-3 col-md-2 sidebar">
                <?
                if (Yii::$app->user->can("seeTasks")) {
                    $leftMenuItems[] = ['label' => 'Мои задачи', 'url' => ['/task/mytasks']];
                }

                $leftMenuItems[] = ['label' => 'Все задачи', 'url' => ['/task/index']];

                if (Yii::$app->user->can("getTools")) {
                    $leftMenuItems[] = ['label' => 'Инструменты', 'url' => ['/tools/index']];
                }
                ?>
                <?=Nav::widget([
                    'options' => ['class' => 'nav nav-sidebar'],
                    'items' => $leftMenuItems
                ])?>
            </div>
            <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
                <?= $content ?>
            </div>
        </div>
</body>
<?php $this->endBody() ?>
</html>
<?php $this->endPage() ?>