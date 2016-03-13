<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
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
    <style>
        body {
            background-color: #eee;
        }
    </style>
    <body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-3 main">
                <?= $content ?>
            </div>
        </div>
    </div>
    </body>
    <?php $this->endBody() ?>
    </html>
<?php $this->endPage() ?>