<?php
/* @var $this yii\web\View */
?>
<?$this->registerJsFile("js/scripts.js", array("depends" => "app\assets\UiAsset", "position" => $this::POS_HEAD))?>
<h1 class="page-header"><?=$this->title?></h1>
<? $permission = (Yii::$app->user->can("updatePriority")); ?>

<div class="preloader" style="display: none">
    <img src="/images/preloader.gif" alt="">
</div>
<div class="container-fluid">
    <div class="row" style="margin-bottom: 20px">
        <div class="col-md-3">
            <select class="form-control implementer">
                <option value="0">Ответственный</option>
                <?$selected=(isset($filterValue["user"])) ? $filterValue["user"] : ""?>
                <? foreach ($filter["users"] as $user):?>
                    <option value="<?=$user["id"]?>" <?=($selected == $user["id"]) ? "selected" : ""?>>
                        <?=(!empty($user["firstname"]) || !empty($user["lastname"])) ? $user["firstname"] . " " . $user["lastname"] : $user["username"]?>
                    </option>
                <?endforeach;?>
            </select>
        </div>
        <div class="col-md-4">
            <select class="form-control project">
                <option value="0">Все проекты</option>
                <?$selected=(isset($filterValue["project_id"])) ? $filterValue["project_id"] : ""?>
                <? foreach ($filter["projects"] as $project):?>
                    <option value="<?=$project["id"]?>" <?=($selected == $project["id"]) ? "selected" : ""?>>
                        <?=$project["project_name"]?>
                    </option>
                <?endforeach;?>
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <? foreach ($tasks as $priority => $task):?>
                <?
                switch($priority){
                    case "high":
                        $subTitle = "Высокий приоритет";
                        $class = "high-priority bg-danger";
                        break;
                    case "middle":
                        $subTitle = "Средний приоритет";
                        $class = "middle-priority bg-warning";
                        break;
                    case "low":
                        $subTitle = "Отложенные";
                        $class = "low-priority bg-success";
                        break;
                }
                ?>
                <h2><?=$subTitle?></h2>
                <ul id="sortable1" class="dropable list-unstyled <?=$class?>">
                    <?if (empty($task)):?>
                        <p class="message ui-state-disabled" style="padding: 0 20px">Задачи отсутствуют</p>
                    <?endif;?>
                    <?foreach($task as $item):?>
                    <li>
                        <p class="task" data-id="<?=$item["id"]?>" data-priority="<?=$priority?>" data-sort="<?=$item["sort"]?>">
                            <span class="<?=($permission) ? "glyphicon glyphicon-option-vertical" : ""?>" aria-hidden="true"></span>
                            <span class="title">
                                <?=$item["name"]?>
                                <a href="<?=$item["task_url"]?>" target="_blank"><span class="glyphicon glyphicon-link" aria-hidden="true"></span></a>
                            </span>
                            <span class="left">
                                <span class="user-info">
                                    <span class="text-primary"><?=($item["user"]) ? $item["user"] : "Не назначена"?></span>
                                    <span class="text-primary"><?=$item["date"]?></span>
                                    <span class="text-primary"><a href="<?=$item["project_url"]?>" target="_blank"><?=$item["project"]?></a></span>
                                </span>
                            </span>
                        </p>
                    </li>
                    <?endforeach;?>
                </ul>
            <?endforeach;?>

        </div>
    </div>
</div>

<? if (!$permission):?>
    <script type="text/javascript">
        $( "ul.dropable" ).sortable({
            disabled: true
        })
    </script>
<?endif;?>