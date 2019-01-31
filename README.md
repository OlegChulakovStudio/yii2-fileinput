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
        'sortActionRoute' => ['/gallery/main/sort'],
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
The configuration parameter `sortActionRoute` determines the route to ajax-action for relocate item (change order) in DB level.
The Drag-n-drop icon will appear automatically if the `sortActionRoute` parameter is set.
### Single image uploading