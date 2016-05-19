<?php
/* @var $this yii\web\View */
?>
<h1 class="page-header"><?=$this->title?></h1>

<div class="container-fluid">
    <div class="row" style="margin-bottom: 20px">
        <div class="col-md-12">
            <div class="col-md-6 col-md-offset-3 bg-info">
                <p style="margin: 10px">Дата последнего обновления: <b><?=$date;?> (<?=$status;?>)</b></p>
            </div>
        </div>
        <div class="col-md-12" style="margin-top: 15px">
            <div class="progress" style="display: none">
                <div class="progress-bar progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em; width: 0%;">
                    0%
                </div>
            </div>
        </div>
        <div class="col-md-4" style="margin-top: 40px">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="item[]" value="users">
                    Обновить данные пользователей
                </label>
                <label>
                    <input type="checkbox" name="item[]" value="projects">
                    Обновить данные проектов и задач
                </label>
            </div>
            <button type="button" class="btn btn-primary btn-lg btn-block parse_data" style="margin-top: 40px">Обновить данные</button>
        </div>
        <div class="col-md-8">
            <div class="col-md-8 col-md-offset-2"><h3 style="text-align: center">Результат</h3></div>
            <div id="result-bc" class="col-md-10 col-md-offset-1"></div>
        </div>
    </div>
</div>