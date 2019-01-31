<?php
/**
 * Файл виджета FileInput
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\fileinput;

use yii\web\View;

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
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->attachOnChange();
        $this->attachPreRemove();
        $this->attachFileSorted();
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
                $this->pluginEvents['filesorted'] = 'function(event, params) { $.post("' . $url . '",{ sort:params }); }';
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
