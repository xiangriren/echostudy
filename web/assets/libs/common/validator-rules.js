define(function(require, exports, module) {

    var calculateByteLength = function(string) {
        var length = string.length;
        for ( var i = 0; i < string.length; i++) {
            if (string.charCodeAt(i) > 127)
                length++;
        }
        return length;
    }

    var isDate = function(x){
        return "undefined" == typeof x.getDate;
    }

    var rules = [
        [
            'integer',
            /[0-9]*/,
            '{{display}}必须是数字'
        ],
        [
            'chinese',
            /^([\u4E00-\uFA29]|[\uE7C7-\uE7F3])*$/i,
            '{{display}}必须是中文字'
        ],
        [
            'phone', 
            /^1\d{10}$/,
            '请输入合法的{{display}}'
        ],
        [
            'chinese_alphanumeric',
            /^([\u4E00-\uFA29]|[a-zA-Z0-9_])*$/i,
            '{{display}}必须是中文字、英文字母、数字及下划线组成'
        ],
        [
            'alphanumeric',
            /^[a-zA-Z0-9_]+$/i,
            '{{display}}必须是英文字母、数字及下划线组成'
        ],
        [
            'byte_minlength',
            function(options) {
                var element = options.element;
                var l = calculateByteLength(element.val());
                return l >= Number(options.min);
            },
            '{{display}}的长度必须大于等于{{min}}，一个中文字算2个字符'
        ],
        [
            'currency',
            /^(([1-9]{1}\d*)|([0]{1}))(\.(\d){1,2})?$/i,
            '请输入合法的{{display}},如:200, 221.99, 0.99, 0等'
        ],        
        [
            'byte_maxlength',
            function(options) {
                var element = options.element;
                var l = calculateByteLength(element.val());
                return l <= Number(options.max);
            },
            '{{display}}的长度必须小于等于{{max}}，一个中文字算2个字符'
        ],
        [
        'idcard',
        function(options){
        var idcard = options.element.val();
            var reg = /^\d{15}(\d{2}[0-9X])?$/i;
            if (!reg.test(idcard)) {
                return false;
            }
            if (idcard.length == 15) {
                var n = new Date();
                var y = n.getFullYear();
                if (parseInt("19" + idcard.substr(6, 2)) < 1900 || parseInt("19" + idcard.substr(6, 2)) > y) {
                    return false;
                }
                var birth = "19" + idcard.substr(6, 2) + "-" + idcard.substr(8, 2) + "-" + idcard.substr(10, 2);
                if (!isDate(birth)) {
                    return false;
                }
            }
            if (idcard.length == 18) {
                var n = new Date();
                var y = n.getFullYear();
                if (parseInt(idcard.substr(6, 4)) < 1900 || parseInt(idcard.substr(6, 4)) > y) {
                    return false;
                }
                var birth = idcard.substr(6, 4) + "-" + idcard.substr(10, 2) + "-" + idcard.substr(12, 2);
                if (!isDate(birth)) {
                    return false;
                }
                iW = new Array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2, 1);
                iSum = 0;
                for (i = 0; i < 17; i++) {
                    iC = idcard.charAt(i);
                    iVal = parseInt(iC);
                    iSum += iVal * iW[i];
                }
                iJYM = iSum % 11;
                if (iJYM == 0) sJYM = "1";
                else if (iJYM == 1) sJYM = "0";
                else if (iJYM == 2) sJYM = "x";
                else if (iJYM == 3) sJYM = "9";
                else if (iJYM == 4) sJYM = "8";
                else if (iJYM == 5) sJYM = "7";
                else if (iJYM == 6) sJYM = "6";
                else if (iJYM == 7) sJYM = "5";
                else if (iJYM == 8) sJYM = "4";
                else if (iJYM == 9) sJYM = "3";
                else if (iJYM == 10) sJYM = "2";
                var cCheck = idcard.charAt(17).toLowerCase();
                if (cCheck != sJYM) {
                    return false;
                }
            }
            return true;
        },
        '{{display}}格式不正确，为15位或18位'
        ],
        [
            'password',
            /^[\S]{4,20}$/i,
            '{{display}}只能由4-20个字符组成'
        ],
        [
            'qq',
            /^[1-9]\d{4,}$/,
            '{{display}}格式不正确'
        ],
        [
            'integer',
            /^[+-]?\d+$/,
            '{{display}}必须为整数'
        ],
        [
            'remote',
            function(options, commit) {
                var element = options.element,
                    url = options.url ? options.url : (element.data('url') ? element.data('url') : null);
                $.get(url, {value:element.val()}, function(response) {
                    commit(response.success, response.message);
                }, 'json');
            }
        ],
        [
            'email_remote',
            function(options, commit) {
                var element = options.element,
                    url = options.url ? options.url : (element.data('url') ? element.data('url') : null);
                    value = element.val().replace(/\./g, "!");
                $.get(url, {value:value}, function(response) {
                    commit(response.success, response.message);
                }, 'json');
            }
        ]

    ];

    exports.inject = function(Validator) {
        $.each(rules, function(index, rule){
            Validator.addRule.apply(Validator, rule);
        });

    }

});
