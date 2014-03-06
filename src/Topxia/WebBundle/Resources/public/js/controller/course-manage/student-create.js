define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var $modal = $('#student-create-form').parents('.modal');
        var $table = $('#course-student-list');

        var validator = new Validator({
            element: '#student-create-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                
                $.post($form.attr('action'), $form.serialize(), function(html) {
                    $table.find('tr.empty').remove();
                    $(html).prependTo($table.find('tbody'));
                    $modal.modal('hide');
                    Notify.success('添加学员操作成功!');
                }).error(function(){
                    Notify.danger('添加学员操作失败!');
                });

            }
        });

        validator.addItem({
            element: '#student-nickname',
            required: true,
            rule: 'chinese_alphanumeric remote'
        });

        validator.addItem({
            element: '#student-remark',
            rule: 'maxlength{max:80}'
        });

        validator.addItem({
            element: '#buy-price',
            rule: 'currency'
        });

    };

});