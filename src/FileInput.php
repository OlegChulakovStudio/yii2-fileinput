<?php
/**
 * Файл виджета  FileInput
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
     * Постфикс, который будет подставлен к имени оригинального поля для формирования скрытого поля для удаления
     * @var string
     */
    public $removalInputPostfix = 'Deleted';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $events = [];

        if ($this->isMultiple) {
            $events['change'] = "function(){ {$this->jsSupervisorInstanceName}.markAll(); return false; }";
        }

        $events['filepreremove'] = "function(event, id, index) { {$this->jsSupervisorInstanceName}.touch(id); return false; }";

        $this->pluginEvents = ArrayHelper::merge($events, $this->pluginEvents);

        parent::init();
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

        return rtrim($name, '[]');
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
