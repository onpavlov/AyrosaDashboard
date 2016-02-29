<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Asset bundle for the jquery ui css and js files
 */
class UiAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/jquery-ui.min.css',
    ];
    public $js = [
        'js/jquery-ui.min.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
