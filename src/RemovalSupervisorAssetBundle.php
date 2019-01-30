<?php
/**
 * Файл класса RemovalSupervisorAssetBundle.php
 *
 * @copyright Copyright (c) 2019, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\fileinput;

use yii\web\AssetBundle;

/**
 * @package chulakov\fileinput
 */
class RemovalSupervisorAssetBundle extends AssetBundle
{
    /**
     * @var array
     */
    public $js = [
        'js/file-removal-supervisor.js'
    ];

    /**
     * @var array
     */
    public $css = [];

    /**
     * @var array
     */
    public $depends = [
        'yii\web\JqueryAsset'
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = __DIR__ . '/assets';
    }
}