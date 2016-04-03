<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\helpers\BaseUrl;
use yii\bootstrap\ActiveForm;

$this->title = 'Вход';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <h1 style="margin-left: 30px"><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin([
        'id' => 'login-form',
        'options' => ['class' => 'form-horizontal'],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-4\">{input}</div>\n<div class=\"col-lg-6\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-2 control-label'],
        ],
    ]); ?>

        <?= $form->field($model, 'email')->label('Email')->textInput(['autofocus' => true]) ?>

        <?= $form->field($model, 'password')->label('Пароль')->passwordInput() ?>

        <?= $form->field($model, 'rememberMe')->label('Запомнить меня')->checkbox([
            'template' => "<div class=\"col-lg-5\">{input} <label class=\"col-lg-6 control-label\" for=\"loginform-rememberme\">Запомнить меня</label></div>\n<div class=\"col-lg-4\">{error}</div>",
        ]) ?>

        <div class="form-group">
            <div class="col-lg-offset-1 col-lg-11">
                <?= Html::submitButton('Вход', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
            </div>
            <div class="col-lg-offset-1 col-lg-11" style="margin-top: 10px">
                <a href="<?= BaseUrl::to(["site/signup"]) ?>">Зарегистрироваться</a>
            </div>
        </div>

    <?php ActiveForm::end(); ?>

</div>
