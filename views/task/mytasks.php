<?php
/* @var $this yii\web\View */
?>
<h1 class="page-header"><?=$this->title?></h1>
<? $permission = (Yii::$app->user->can("seeTasks")); ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <? foreach ($tasks as $priority => $task):?>
                <?
                switch($priority){
                    case "high":
                        $subTitle = "Высокий приоритет";
                        $class = "high";
                        break;
                    case "middle":
                        $subTitle = "Средний приоритет";
                        $class = "middle";
                        break;
                    case "low":
                        $subTitle = "Отложенные";
                        $class = "low";
                        break;
                }
                ?>
                <h2><?=$subTitle?></h2>
                <ul class="zebra <?=$class?>">
                    <?if (empty($task)):?>
                        <p class="message ui-state-disabled" style="padding: 0 20px">Задачи отсутствуют</p>
                    <?endif;?>
                    <?foreach($task as $item):?>
                    <li>
                        <span class="title">
                        <?=$item["name"]?>
                            <a href="<?=$item["task_url"]?>" target="_blank">
                                <span class="glyphicon glyphicon-link" aria-hidden="true"></span>
                            </a>
                        </span>
                        <span class="right">
                            <span><a href="<?=$item["project_url"]?>" target="_blank"><?=$item["project"]?></a></span>
                            <span class="date"><?=$item["date"]?></span>
                        </span>
                    </li>
                    <?endforeach;?>
                </ul>
            <?endforeach;?>

        </div>
    </div>
</div>