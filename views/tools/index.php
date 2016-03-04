<?php
/* @var $this yii\web\View */
?>
<h1 class="page-header"><?=$this->title?></h1>

<div class="container-fluid">
    <div class="row" style="margin-bottom: 20px">
        <div class="col-md-4" style="margin-top: 40px">
            <button type="button" class="btn btn-primary btn-lg btn-block parse_users">Обновить пользователей</button>
            <button type="button" class="btn btn-primary btn-lg btn-block parse_tasks">Обновить задачи</button>
        </div>
        <div class="col-md-8">
            <div class="col-md-8 col-md-offset-2"><h3 style="text-align: center">Результат</h3></div>
            <div id="result-bc" class="col-md-10 col-md-offset-1"></div>
        </div>
    </div>
</div>