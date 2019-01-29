<?php
/**
 * Файл виджета  FileInputWidget
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\fileinput;

use yii\web\View;
use yii\helpers\ArrayHelper;

/**
 * Класс виджета FileInput
 */
class FileInput extends \kartik\file\FileInput
{
    /**
     * Обязательное
     */
    public $required = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->options = ArrayHelper::merge([
            'multiple' => false,
            'accept' => 'image/*'
        ], $this->options);

        if (!$this->options['multiple']) {
            $this->pluginOptions['overwriteInitial'] = true;
            if ($this->required) {
                $this->pluginOptions['fileActionSettings']['showRemove'] = false;
            }
        }

        $this->pluginOptions = ArrayHelper::merge([
            'initialPreviewAsData' => true,
            'overwriteInitial' => false,
            'showUpload' => false,
            'showClose' => false,
            'showRemove' => false,
            'maxFileSize' => 5120,
            'fileActionSettings' => [
                'showDrag' => false,
                'showRemove' => true,
            ],
        ], $this->pluginOptions);

        $events = [];
        if ($this->options['multiple']) {
            $events['change'] = 'function(){ syncFiles(); }';
        }
        $events['filepreremove'] = 'function(event, id, index) { return deleteFile($("#" + id).find(".kv-file-remove")); }';
        $this->pluginEvents = ArrayHelper::merge($events, $this->pluginEvents);

        parent::init();
    }

    /**
     * Регистрация скриптов удаления файла
     *
     * @throws \yii\base\InvalidConfigException
     */
    protected function registerDeleteAsset()
    {
        $formName = $this->model->formName();
        $attributeDeleted = trim($this->attribute,'[]') . 'Deleted';
        $formPostfix = $this->options['multiple'] ? '[]' : '';

        $script = <<< JS
var deletedFilesCache = [];
var inputName = "{$formName}[{$attributeDeleted}]{$formPostfix}";

$(document).on('click', '.kv-file-remove', function () {
    deleteFile($(this));
});

function syncFiles() {
    $('#' + deletedFilesCache.join(',#')).each(function(index, value) {
        deleteFile($(value).find('.kv-file-remove'));
    });
}
function deleteFile(elem) {
    var input = '<input class="kv-deleted-item" type="hidden" name="' + inputName + '" value="' + elem.data('key') + '">';
    var frame = elem.closest('.file-preview-frame');

    frame.append(input);
    markPreviewDeleted(elem, frame);
    reindexDelete();

    return false;
}

function markPreviewDeleted(elem, frame) {
    if (deletedFilesCache.indexOf(frame.attr('id')) === -1) {
        deletedFilesCache.push(frame.attr('id'));
    }
    frame.css('opacity', 0.5);
    elem.attr('disabled', true);
}

function reindexDelete() {
    $(".kv-deleted-item").each(function(index) {
        var input = $(this);
        var newName = input.attr('name').replace(/\[([0-9]*)\]/, '[' + index + ']');
        input.attr('name', newName);
    });
}
JS;
        $this->getView()->registerJs($script,View::POS_END);
    }

    /**
     * @inheritdoc
     */
    public function registerAssets()
    {
        parent::registerAssets();
        $this->registerDeleteAsset();
    }
}
