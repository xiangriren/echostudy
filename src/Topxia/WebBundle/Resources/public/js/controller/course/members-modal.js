define(function(require, exports, module) {

    exports.run = function() {

        $("#course-student-list").on('click', '.follow-student-btn, .unfollow-student-btn', function() {
            var $this = $(this);

            $.post($this.data('url'), function(){
                $this.hide();
                if ($this.hasClass('follow-student-btn')) {
                    $this.parent().find('.unfollow-student-btn').show();
                } else {
                    $this.parent().find('.follow-student-btn').show();
                }
            });
        });

    }

});