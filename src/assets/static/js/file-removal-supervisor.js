var FileRemovalSupervisor = (function($) {


    /**
     * Конструктор инстанса удаляемой сущности файла
     * @param string frameId уникальный идешник фрейм-плашки
     * @param FileRemovalSupervisor supervisor объект супервизор.
     * @constructor
     */
    var RemovalItem = function (frameId, supervisor)
    {
        this.$item = $('#' + frameId);

        // Идентификатор фрейма файла в виджете
        this.id = frameId;

        // Идентификатор удаляемого файла
        this.fileId = $('#' + frameId + ' .kv-file-remove').data('key');

        // Объект-супервизор
        this.supervisor = supervisor;
    }

    /**
     * Помечает элемент на удаление или снимает отметку.
     *
     * Если элемент не был помечен на удаление,
     * то для него создается скрытое поле,
     * элемент затемняется и помещается в хранилище объекта-супервизора.
     *
     * Если элемент был ранее помечен на удаление,
     * то его скрытое поле удаляется и он извлекается из хранилища объекта-супервизора.
     * Его непрозрачноть становится 1.
     */
    RemovalItem.prototype.touch = function () {

        var idx = this.supervisor.index(this.id);

        if (idx === -1) {
            this.supervisor.add(this.id);
            this.mark();
        } else {
            this.supervisor.delete(idx);
            this.unmark();
        }
    }

    RemovalItem.prototype.mark = function () {
        this.createInput();
        this.$item.css('opacity', 0.5);
    }

    RemovalItem.prototype.unmark = function () {
        this.dropInput();
        this.$item.css('opacity', 1);
    }

    /**
     * Создает скрытое поле для передачи идентификатора файла на удаление
     */
    RemovalItem.prototype.createInput = function () {
        $('<input data-frame_id="' + this.id + '" class="kv-deleted-item" type="hidden" name="' + this.supervisor.removalInputName + '" value="' + this.fileId + '">')
            .appendTo(this.$item);
    }

    /**
     * Удаляет скрытое поле для передачи идентификатора файла на удаление
     */
    RemovalItem.prototype.dropInput = function () {
        $("input[data-frame_id='" + this.id + "']").remove();
    }

    /**
     * Конструктор класса-супервизора
     *
     * Сколько полей проинициализировано, столько супервизоров будет создано.
     * Один супервизор содержит информацию о своих итемах (файлах)
     *
     * @param string inputId уникальный идентификатор поля-оригинала
     * @param string removalInputName полное наменование поля ввода для удаления
     * @constructor
     */
    var FileRemovalSupervisor = function (inputId, removalInputName) {

        this.$parent = $("#" + inputId).closest('.file-input');

        this.itemsStorage = [];
        this.removalInputName = removalInputName;

        var supervisor = this;

        this.$parent.on('click', '.kv-file-remove', function (event) {

            var item = new RemovalItem(
                $(this).closest('.file-preview-frame').attr('id'),
                supervisor
            );

            item.touch();

        });
    }

    /**
     * Возвращает индекс значения в хранилище
     * @param string value
     * @returns number
     */
    FileRemovalSupervisor.prototype.index = function (value) {
        return this.itemsStorage.indexOf(value);
    }

    /**
     * Помещает значение в хранилище
     * @param string value
     */
    FileRemovalSupervisor.prototype.add = function (value) {
        this.itemsStorage.push(value);
    }

    /**
     * Удаляест значение из хранилища по индексу
     * @param integer index
     */
    FileRemovalSupervisor.prototype.delete = function (index) {
        this.itemsStorage.splice(index, 1);
    }

    /**
     * Перепомечает ранее отмеченные фреймы с файлами на удаление
     */
    FileRemovalSupervisor.prototype.markAll = function () {
        for (var i = 0; i < this.itemsStorage.length; i++) {
            var item = new RemovalItem(
                this.itemsStorage[i],
                this
            );

            item.mark();
        }
    }

    /**
     * Обновляет состояние конкретного фрейма на помечен на удаление/снят с удаления
     * @param string fraimeId
     */
    FileRemovalSupervisor.prototype.touch = function (fraimeId) {

        var item = new RemovalItem(
            fraimeId,
            this
        );

        item.touch();
    }


    return FileRemovalSupervisor;

})(jQuery);