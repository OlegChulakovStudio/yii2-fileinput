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
     * Аттрибут, для которого определяются соответствующие параметры отображения в виджете FileInput
     * @var BaseFile|BaseFile[]
     */
    public $attribute;

    /**
     * Возвращает URL-адреса к файлам
     * @see http://plugins.krajee.com/file-input/plugin-options#initialPreview
     * @todo реализовать декорирование элементов через кложуру
     * @return array|string
     */
    public function getInitialPreview()
    {
        $files = $this->getFiles();
        if (is_array($files)) {
            $previews = [];
            foreach ($this->getFiles() as $file) {
                $previews[] = $file->getUrl();
            }
            return $previews;
        } else {
            return $files->getUrl();
        }
    }

    /**
     * Возвращает конфигурацию для отображенрия файлов
     * @see http://plugins.krajee.com/file-input/plugin-options#initialPreviewConfig
     * @return array
     */
    public function getInitialPreviewConfig()
    {
        $files = $this->getFiles();

        if (is_array($files)) {
            $config = [];
            foreach ($files as $file) {
                $config[] = $this->getConfig($file);
            }
            return $config;
        } else {
            return $this->getConfig($files);
        }
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
            'previewAsData' => true,
            'caption' => $file->ori_name,
            'key' => $file->id,
        ];
    }

    protected function getFiles()
    {
        return $this->owner->{$this->attribute};
    }
}