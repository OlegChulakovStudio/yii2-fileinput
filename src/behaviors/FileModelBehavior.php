<?php
/**
 * Файл поведения FileModelBehavior
 *
 * @copyright Copyright (c) 2019, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\fileinput\behaviors;

use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\models\Image;
use yii\base\Behavior;
use yii\helpers\Html;

/**
 * Поведение для модели формы или AR, упрощающее конфигурацию параметров отображения загруженный файлов в виджете FileInput
 * @package chulakov\fileinput\behaviors
 * @see http://plugins.krajee.com/file-input/plugin-options
 */
class FileModelBehavior extends Behavior
{
    /**
     * Тип файла изображение
     */
    const TYPE_IMAGE = 'image';

    /**
     * Иной тип файла
     */
    const TYPE_OTHER = 'other';

    /**
     * Тип файлов для отображения
     * @var string
     * @see http://plugins.krajee.com/file-input/plugin-options#initialPreviewConfig
     */
    public $type;

    /**
     * Парсить контент препросмотра как отображаемые данные
     * @var bool
     * @see http://plugins.krajee.com/file-input/plugin-options#initialPreviewConfig
     */
    public $previewAsData = true;

    /**
     * Возвращает URL-адреса к файлам
     * @see http://plugins.krajee.com/file-input/plugin-options#initialPreview
     * @param string $attribute Аттрибут, для которого определяются соответствующие параметры отображения в виджете FileInput
     * @return array|string
     * @todo реализовать декорирование элементов через кложуру
     */
    public function getInitialPreview($attribute)
    {
        $previews = [];
        $files = $this->getAttachedFiles($attribute);

        if (is_array($files)) {

            foreach ($files as $file) {
                $previews[] = $file->getUrl();
            }

        } elseif ($files instanceof BaseFile) {

            $previews[] = $files->getUrl();

        }

        return $previews;
    }

    /**
     * Возвращает конфигурацию для отображенрия файлов
     * @see http://plugins.krajee.com/file-input/plugin-options#initialPreviewConfig
     * @param $attribute
     * @return array
     */
    public function getInitialPreviewConfig($attribute)
    {
        $config = [];
        $files = $this->getAttachedFiles($attribute);

        if (is_array($files)) {

            foreach ($files as $file) {
                $config[] = $this->getConfig($file);
            }

        } elseif ($files instanceof BaseFile) {

            $config[] = $this->getConfig($files);

        }

        return $config;
    }

    /**
     * Автоматически определяет тип файла.
     * Реализовано определение двух типов - изображение и иной файл
     * @see http://plugins.krajee.com/file-input/plugin-options#initialPreviewConfig
     * @param BaseFile $file
     * @return string
     */
    protected function getType(BaseFile $file)
    {
        if (!$this->type || !in_array($this->type, [
            self::TYPE_IMAGE,
            self::TYPE_OTHER
        ])) {
            return $file instanceof Image ? self::TYPE_IMAGE : self::TYPE_OTHER;
        }
        return $this->type;
    }

    /**
     * Возвращает конфигурацию для отображения файла
     *
     * @param BaseFile $file
     * @return array
     */
    protected function getConfig(BaseFile $file)
    {
        return [
            'type' => $this->getType($file),
            'filetype' => $file->mime,
            'size' => $file->size,
            'previewAsData' => $this->previewAsData,
            'caption' => $file->ori_name,
            'key' => $file->id,
        ];
    }

    /**
     * Возвращает сущность/сущности ранее загруженных файлов
     * @param string $attribute
     * @return BaseFile|BaseFile[]
     */
    protected function getAttachedFiles($attribute)
    {
        $attribute = Html::getAttributeName($attribute);
        return $this->owner->$attribute;
    }
}