<?php
/**
 * Файл виджета FileInput
 *
 * @copyright Copyright (c) 2019, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\fileinput\widgets;

use yii\helpers\Html;
use yii\web\View;
use chulakov\fileinput\assets\RemovalSupervisorAssetBundle;

/**
 * Класс виджета FileInput
 */
class FileInput extends \kartik\file\FileInput
{

    /**
     * Маршрут Ajax-действия, производящего изменение порядка следования карточек
     * @var mixed
     */
    public $sortActionRoute;

    /**
     * Постфикс, который будет подставлен к имени оригинального поля для формирования скрытого поля для удаления
     * @var string
     */
    public $removalInputPostfix = 'Deleted';

    /**
     * Наименование атрибута с ранее загруженными сущностями файлов
     * @var string
     */
    public $attachedFilesAttribute;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->initInitialPreview();
        $this->initInitialPreviewConfig();
        $this->attachOnChange();
        $this->attachPreRemove();
        $this->attachFileSorted();
    }

    /**
     * Если миниатюры отображения файлов не заданы через конфигурацию виджета,
     * то производится получить информацию о них у модели через методы поведения @see \chulakov\fileinput\behaviors\FileModelBehavior
     */
    protected function initInitialPreview()
    {
        if (!isset($this->pluginOptions['initialPreview'])) {
            if ($this->hasModel() && $this->model->hasMethod('getInitialPreview') && $this->attachedFilesAttribute) {
                $this->pluginOptions['initialPreview'] = $this->model->getInitialPreview($this->attachedFilesAttribute);
            }
        }
    }

    /**
     * Если конфигурация отображения файлов не задана через конфигурацию виджета,
     * то производится получить информацию о ней у модели через методы поведения @see \chulakov\fileinput\behaviors\FileModelBehavior
     */
    protected function initInitialPreviewConfig()
    {
        if (!isset($this->pluginOptions['initialPreviewConfig'])) {
            if ($this->hasModel() && $this->model->hasMethod('getInitialPreviewConfig') && $this->attachedFilesAttribute) {
                $this->pluginOptions['initialPreviewConfig'] = $this->model->getInitialPreviewConfig($this->attachedFilesAttribute);
            }
        }
    }

    /**
     * При каждом обновлении виджет происходит его полный ререндер.
     * Для этих целей после каждого обновления мы проходитмся по всем отмеченным на удаления файлам заново
     * и восстанавливаем скрытые поля
     */
    protected function attachOnChange()
    {
        if (!isset($this->pluginEvents['change']) && $this->isMultiple) {
            $this->pluginEvents['change'] = "function(){ {$this->jsSupervisorInstanceName}.markAll(); return false; }";
        }
    }

    /**
     * Перед удалением конкретного плашки-файла важно применить свой механизм, а не тот, который предлагает Krajee
     */
    protected function attachPreRemove()
    {
        if (!isset($this->pluginEvents['filepreremove'])) {
            $this->pluginEvents['filepreremove'] = "function(event, id, index) { {$this->jsSupervisorInstanceName}.touch(id); return false; }";
        }
    }

    /**
     * После изменения порядка элемента, фрмируем URL и посылаем ajax-запрос на физическое изменение порядка
     */
    protected function attachFileSorted()
    {
        if (!isset($this->pluginEvents['filesorted'])) {

            if ($this->sortActionRoute) {
                $url = \yii\helpers\Url::to($this->sortActionRoute);
                $this->pluginEvents['filesorted'] =
<<<JS
    function(event, params) {
        var data = {};
        
        var currentItem = params.stack[params.newIndex];
        var previousItem = params.stack[params.newIndex - 1];
        var nextItem = params.stack[params.newIndex + 1];
        
        data.currentKey = currentItem.key;
        
        if (typeof previousItem != "undefined") {
            data.previousKey = previousItem.key; 
        } else {
            data.previousKey = null;
        }
        
        if (typeof nextItem != "undefined") {
            data.nextKey = nextItem.key;
        } else {
            data.nextKey = null;
        }
        
        var \$input = $(this);
        var \$form = $(this).closest('form');
        
        $.ajax({
            url: '$url',
            type: 'get',
            data: data,
            success: function (data, textStatus, jqXHR) {
                
            },
            error: function ( jqXHR, textStatus, errorThrown ) {
                var errors = [];
                
                if (jqXHR.responseJSON) {
                    errors.push(jqXHR.responseJSON.message);
                } else {
                    errors.push(jqXHR.responseJSON.message);
                }
                
            }
        });
    }
JS;

                // Включаем отображение возможности перетаскивания
                $this->pluginOptions['fileActionSettings']['showDrag'] = true;
            } else {
                $this->pluginOptions['fileActionSettings']['showDrag'] = false;
            }

        } else {

            if (!isset($this->pluginOptions['fileActionSettings']['showDrag'])) {
                $this->pluginOptions['fileActionSettings']['showDrag'] = true;
            }

        }

    }

    /**
     * @inheritdoc
     */
    public function registerAssets()
    {
        parent::registerAssets();

        $view = $this->getView();

        RemovalSupervisorAssetBundle::register($view);

        $view->registerJs(
            "var {$this->jsSupervisorInstanceName} = new FileRemovalSupervisor('{$this->options['id']}', '{$this->getRemovalInputName()}')",
            View::POS_READY
        );
    }

    /**
     * Наименование формы, к которой принадлежит поле
     * @return string
     * @throws \yii\base\InvalidConfigException
     *
     */
    protected function getFormName()
    {
        if ($this->hasModel()) {
            return $this->model->formName();
        }

        return '';
    }

    /**
     * Возвращает наименование атрибута модели или имя поля
     * @return string
     */
    protected function getAttributeName()
    {
        if ($this->hasModel()) {
            $name = $this->attribute;
        } else {
            $name = $this->name;
        }
        
        return Html::getAttributeName($name);
    }

    /**
     * Проверяет включен ли мультивыбор
     * @return bool
     */
    protected function getIsMultiple()
    {
        if (isset($this->options['multiple'])  && $this->options['multiple']) {
            return true;
        }

        return false;
    }

    /**
     * Формирует полное наименование инпута
     *
     * @return string
     */
    protected function getRemovalInputName()
    {
        $attribute = $this->attributeName . $this->removalInputPostfix;

        if ($formName = $this->formName) {
            $inputName = "{$formName}[{$attribute}]";
        }

        return $inputName . ($this->isMultiple ? "[]" : "");
    }

    /**
     * Возвращает наименование JS-переменной для хранения контейнера супервизора
     * @return string
     */
    protected function getJsSupervisorInstanceName()
    {
        return "fsp{$this->id}";
    }
}
