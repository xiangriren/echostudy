define(function(require, exports, module) {

    var EditorFactory = require('common/kindeditor-factory');
    var Validator = require('bootstrap.validator');
    var VideoChooser = require('../widget/media-chooser/video-chooser');
    var AudioChooser = require('../widget/media-chooser/audio-chooser');
    var Notify = require('common/bootstrap-notify');

    function createValidator ($form) {

        Validator.addRule('mediaValueEmpty', function(options) {
            var value = options.element.val();
            return value != '""';
        }, '请选择或上传{{display}}文件');

        Validator.addRule('timeLength', function(options) {
            return /^\d+:\d+$/.test(options.element.val())
        }, '时长格式不正确');

        validator = new Validator({
            element: $form,
            autoSubmit: false
        });

        validator.on('formValidated', function(error, msg, $form) {
            if (error) {
                return;
            }

            var $panel = $('.lesson-manage-panel');
            $.post($form.attr('action'), $form.serialize(), function(html) {

                var id = '#' + $(html).attr('id'),
                    $item = $(id);
                if ($item.length) {
                    $item.replaceWith(html);
                    Notify.success('课时已保存');
                } else {
                    $panel.find('.empty').remove();
                    $("#course-item-list").append(html);
                    Notify.success('添加课时成功');
                }
                $(id).find('.btn-link').tooltip();
                $form.parents('.modal').modal('hide');
            });

        });

        return validator;
    };

    function switchValidator(validator, type) {
        validator.removeItem('#lesson-title-field');
        validator.removeItem('#lesson-content-field');
        validator.removeItem('#lesson-media-field');
        validator.removeItem('#lesson-length-field');

        validator.addItem({
            element: '#lesson-title-field',
            required: true
        });

        switch (type) {
            case 'video':
            case 'audio':
                validator.addItem({
                    element: '#lesson-media-field',
                    required: true,
                    rule: 'mediaValueEmpty',
                    display: type == 'video' ? '视频' : '音频'
                });

                validator.addItem({
                    element: '#lesson-length-field',
                    required: true,
                    rule: 'timeLength'
                });

                break;
            case 'text':
                validator.addItem({
                    element: '#lesson-content-field',
                    required: true
                });
                break;
        }

    }

    exports.run = function() {
        var $form = $("#course-lesson-form");

        var choosedMedia = $form.find('[name="media"]').val();
        choosedMedia = choosedMedia ? $.parseJSON(choosedMedia) : {};
        
        var videoChooser = new VideoChooser({
            element: '#video-chooser',
            choosed: choosedMedia,
        });

        var audioChooser = new AudioChooser({
            element: '#audio-chooser',
            choosed: choosedMedia,
        });

        videoChooser.on('change', function(item) {
            var value = item ? JSON.stringify(item) : '';
            $form.find('[name="media"]').val(value);
            if (item.status == 'waiting') {
                
            }
        });

        audioChooser.on('change', function(item) {
            var value = item ? JSON.stringify(item) : '';
            $form.find('[name="media"]').val(value);
        });

        var mediaFileInfoFetchingCallback = function () {
            var $group = $("#lesson-length-form-group").show();
            var $help = $group.find('.help-block');
            $help.data('help', $help.text());
            $help.text('正在读取时长，请稍等...');
        };

        var mediaFileInfoFetchedCallback = function (info) {
            var $group = $("#lesson-length-form-group").show();
            var $help = $group.find('.help-block');
            if ($help.data('help')) {
                $help.text($help.data('help'));
            }

            if (info.duration) {
                $("#lesson-length-field").val(info.duration);
            }
        }

        videoChooser.on('fileinfo.fetching', mediaFileInfoFetchingCallback);
        videoChooser.on('fileinfo.fetched', mediaFileInfoFetchedCallback);
        
        audioChooser.on('fileinfo.fetching', mediaFileInfoFetchingCallback);
        audioChooser.on('fileinfo.fetched', mediaFileInfoFetchedCallback);

        var validator = createValidator($form);

        $form.on('change', '[name=type]', function(e) {
            var type = $(this).val();

            $form.removeClass('lesson-form-video').removeClass("lesson-form-audio").removeClass("lesson-form-text")
            $form.addClass("lesson-form-" + type);

            if (type == 'video') {
                videoChooser.show();
                audioChooser.hide();

            } else if (type == 'audio') {
                audioChooser.show();
                videoChooser.hide();
            }

            switchValidator(validator, type);
        });

        $form.find('[name="type"]:checked').trigger('change');

        var editor = EditorFactory.create('#lesson-content-field', 'standard', {extraFileUploadParams:{group:'course'}, height: '300px'});
        
        validator.on('formValidate', function(elemetn, event) {
            editor.sync();
        });

    };
});