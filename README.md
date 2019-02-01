# yii2-fileinput
[Krajee File Input widget](http://demos.krajee.com/widget-details/fileinput) Wrapper which implemets removal logic without ajax.
Marking items for delete without ajax and provides files ids for removal to server throw special hidden input.
## Usage
### Multiple images uploading
Configuration of widget for multiple images uploading
```php
   <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data']
    ]); ?>

    ...

    <?= $form->field($model, 'images[]')->widget(FileInput::className(), [
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
### Reordering (sorting) files in widget
The configuration parameter `sortActionRoute` determines the route to ajax-action for relocate item (change order) in DB level.
The Drag-n-drop icon will appear automatically if the `sortActionRoute` parameter is set.

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
After installing the [sem-soft / yii-sortable](https://github.com/sem-soft/yii2-sortable) package, you need to configure the action `swap` in the appropriate controller.
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
You also need to configure the behavior that will set the value of the sorted field before insert.
An example action configuration is shown below.
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
More information about configuration of [sem-soft / yii-sortable](https://github.com/sem-soft/yii2-sortable).
### Single image uploading
Comming soon...