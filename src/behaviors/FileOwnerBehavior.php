<?php
/**
 * Файл класса FileOwnerBehavior
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\fileinput\behaviors;

use yii\base\Model;
use yii\base\Behavior;
use yii\db\ActiveRecord;

/**
 * Поведение получения идентификатора владельца файла.
 *
 * Кроме возможности получения просто основного ключа, можно указать от куда получить этот самый ключ.
 * Для указания способа получения достаточно при добавлении поведения прокинуть свойство $property,
 * которое можен быть как строкой (указывая на аттрибут модели) или замыканием (функция получения/генерации идентификатора).
 *
 * Если свойство $property не задано, будет попытка получить getPrimaryKey() из модели.
 *
 * @property ActiveRecord|Model $fileOwner Только для записи!
 */
class FileOwnerBehavior extends Behavior
{
    /**
     * @var string|\Closure Способ получения идентификатора из данных владельца модели
     */
    public $property;
    /**
     * @var int Значение ключа по умолчанию
     */
    public $defaultValue = 0;

    /**
     * @var ActiveRecord|Model
     */
    protected $fileOwner;

    /**
     * Установка владельца файла для полуения из него первичного ключа для связки
     *
     * @param ActiveRecord|Model $owner
     */
    public function setFileOwner($owner)
    {
        $this->fileOwner = $owner;
    }

    /**
     * Полученние первичного ключа из модели владельца файла
     *
     * @return int|mixed
     */
    public function getPrimaryKey()
    {
        // Если не указано значение для поиска свойства
        if (empty($this->property)) {
            if ($this->fileOwner && $this->fileOwner->hasMethod('getPrimaryKey')) {
                return $this->fileOwner->getPrimaryKey();
            }
        }
        // Если свойство является замыканием
        if ($this->property instanceof \Closure) {
            return call_user_func($this->property, $this->owner, $this->fileOwner);
        }
        // Поиск поля в атрибутах ActiveRecord
        if ($this->fileOwner instanceof ActiveRecord
        &&  $this->fileOwner->hasAttribute($this->property)) {
            return $this->fileOwner->getAttribute($this->property);
        }
        // Поиск поля в классе
        if (property_exists($this->fileOwner, $this->property)) {
            return $this->fileOwner->{$this->property};
        }
        // Значение по умолчанию
        return $this->defaultValue;
    }
}
