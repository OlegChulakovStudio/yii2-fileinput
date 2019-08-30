# yii2-fileinput
Обертка над виджетом [Krajee File Input widget](http://demos.krajee.com/widget-details/fileinput).
Реализует логику удаления файлов через скрытое поле, без AJAX.
Добавляет упрощенную конфигурацию виджета FileInput, используя специальное поведение.
Реализует удобную AJAX-сортировку карточек файлов виджета.
## Использование
### Загрузка файлов
```php
   <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data']
    ]); ?>

    ...

    <?= $form->field($model, 'images[]')->widget(\chulakov\fileinput\widgets\FileInput::className(), [
        'options' => [
            'multiple' => true
        ],
        'pluginOptions' => [
            'initialPreview' => $model->getImagesInitial(),
            'initialPreviewConfig' => $model->getImagesInitialConfig(),
            'overwriteInitial' => false,
            'showUpload' => false,
            'showClose' => false,
            'showRemove' => false,
            'fileActionSettings' => [
                'showRemove' => true,
            ],
        ],
        ...
    ]); ?>

    ...

    <div class="form-group">
        <?= Html::submitButton(Save, ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
```
### Изменени порядка следования файлов (сортировка) в фиджете
Для того, чтобы сортировка работатал, при конфигурации виджета необходимо задать параметр `sortActionRoute`.
Этот параметр содержит маршрут к AJAX-действию, которое производит непосредственное изменение порядка следования файлов.
Иконка перетаскивания картточек появится автоматически, если этот параметр задан.

Механизм изменения порядка следования полностью совместим с механизмом работы пакета [sem-soft/yii-sortable](https://github.com/sem-soft/yii2-sortable). Для сортировки файлов можно использовать этот пакет.
Пример конфигурации совместной работы виджета и backend-сортировки представлен ниже.
```php
   <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data']
    ]); ?>

    ...

    <?= $form->field($model, 'images[]')->widget(FileInput::className(), [
        'options' => [
            'multiple' => true
        ],
        // !!!
        'sortActionRoute' => ['swap'],
        // ---
        'pluginOptions' => [
            'initialPreview' => $model->getImagesInitial(),
            'initialPreviewConfig' => $model->getImagesInitialConfig(),
            'overwriteInitial' => false,
            'showUpload' => false,
            'showClose' => false,
            'showRemove' => false,
            'fileActionSettings' => [
                'showRemove' => true,
            ],
        ],
        ...
    ]); ?>

    ...

    <div class="form-group">
        <?= Html::submitButton(Save, ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
```
После установки пакета [sem-soft / yii-sortable](https://github.com/sem-soft/yii2-sortable), необходимо сконфигурировать `swap`-действие в соответствующем контроллере.
```php
<?php
...
use sem\sortable\actions\DragDropMoveAction;
use Yii;
...
class SliderController extends Controller
{
    ...
    public function actions()
    {
        return [
            ...
            'swap' => [
                'class' => DragDropMoveAction::class,
                'modelClass' => Image::class,
            ],
            ...
        ];
    }
}
```
Также необходимо подключить поведение, которое будет задавать очередное значение поля `sort` при вставке новой записи.
```php
<?php
...
use Yii;
use sem\sortable\behaviors\SortAttributeBehavior;
...
class Image extends ActiveRecord
{
    ...
    public function rules()
    {
        return [
            ...
            [['sort'], 'number'],
            ...
        ];
    }
    ...
    public function behaviors()
    {
        return [
            ...
            SortAttributeBehavior::class,
            ...
        ];
    }
}
```
Более детальную информацию можно получить в описании пакета [sem-soft / yii-sortable](https://github.com/sem-soft/yii2-sortable).
### Получение информации предварительного просмотра при помощи поведения
Можно скоратить время на конфигурацию виджета `FileInput` для подготовки миниатюр загруженных ранее файлов.
При условии что управление файлами осуществляется с помощью компонента [yii2-filestorage](https://bitbucket.org/OlegChulakovStudio/yii2-filestorage).

Поведение `FileModelBehavior` добавляет модели формы два метода `getInitialPreview` and `getInitialPreviewConfig`.
Оба метода получают параметр `attribute`, указывающий имя атрибута формы, содержащего загруженные ранее сущности файлов для построения информации предпросмотра в виджете.

Эти методы виджет будет вызывать автоматически при своей инициализации, если выполняются условия:
- Форма построена на основе виджета ActiveForm
- При конфигурации виджета не заданы ключи `initialPreview`, `initialPreviewConfig` параметра `pluginOptions`
- При конфигурации виджета задан параметр `attachedFilesAttribute`, который указывает на имя атрибута формы содержащего загруженные ранее сущности файлов
- К модели формы подключено поведение `\chulakov\fileinput\behaviors\FileModelBehavior`

Пример конфигурации виджет `FileInput`
```php
    <?= $form->field($model, 'images[]')->widget(\chulakov\fileinput\widgets\FileInput::className(), [
        'options' => [
            'multiple' => true
        ],
        'sortActionRoute' => ['swap'],
        // !!!!
        'attachedFilesAttribute' => 'imagesAttached',
        // ----
        'pluginOptions' => [
            'overwriteInitial' => false,
            'showUpload' => false,
            'showClose' => false,
            'showRemove' => false,
            'fileActionSettings' => [
                'showRemove' => true,
            ],
        ]
    ]); ?>
```
Поведение `FileModelBehavior` подключается в модели формы.
Дополнительная конфигурация этого поведения не требуется.
```php
...
class GalleryForm extends \yii\base\Model
{
    ...

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            ...
            [
                'class' => \chulakov\fileinput\behaviors\FileModelBehavior::class,
            ]
        ];
    }
    ...
}
```

Дополнительно:
-------------
- Поведение получения идентификатора владельца файла [FileOwnerBehavior](docs/behavior-file-owner.md)
