jQuery(document).ready(function($) {
    var frm = $('#register-form');
    var formMsg = $('#register-msg');
    formMsg.hide();
    var ajaxCheckFields = "index.php?dgq_option=dgq_ajax&method=checkRegFields&field=";

    // Ajax send and response
    frm.submit(function (ev) {
        if (!frm.validate().checkForm()) return false;
        $.ajax({
            type: 'POST',
            url: 'index.php?dgq_option=dgq_ajax&method=register',
            dataType: 'json',
            data: frm.serialize(),
            beforeSend: function() {
                if ($(document).hasEventDefined('onBeforeRegister'))
                    $(document).trigger('onBeforeRegister');
                else
                    frm.ajaxSubmitAnim();
            },
            success: function (data) {
                if ($(document).hasEventDefined('onSuccessRegister'))
                    $(document).trigger('onSuccessRegister');
                else {
                    frm.before('<p class="alert alert-success text-success" style="font-size: 14px;">' +
                        '' + Joomla.JText._('COM_USERS_REGISTRATION_SAVE_SUCCESS') + '</p>');
                    frm.hide();
                }
            }
        });

        ev.preventDefault();
    });

    /**
     * regex: Added rule to jquery validate.
     */
    $.validator.addMethod(
        'regex',
        function (value, element, regexp) {
            var re = new RegExp(regexp);
            return this.optional(element) || re.test(value);
        }
    );

    /**
     * inverse_regex: Added rule to jquery validate
     */
    $.validator.addMethod(
        'inverse_regex',
        function (value, element, regexp) {
            var re = new RegExp(regexp);
            return this.optional(element) || !re.test(value);
        }
    );

    /**
     * Adding functions to validator from jquery validate.
     */
    $.extend($.validator.prototype, {
        getFailedRule: function () {
            if (this.errorList.length == 0) return;
            var msg = this.errorList[0].message;
            var elementMsgs = this.settings.messages[$(this.errorList[0].element).attr("name")];
            for (var key in elementMsgs)
                if (elementMsgs[key] == msg)
                    return key;
        },
    });

    /**
     * Dynamic validation
     */
    frm.validate({
        rules: {
            register_name: {
                required: true,
                regex: /^([a-zA-ZÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ\']{1,30}\s*)+$/
            },
            register_username: {
                required: true,
                inverse_regex: /[\<|\>|\" | \\| /| \'|\%|\;|(|)|\&]/,
                remote: {
                    url: ajaxCheckFields+"username",
                    type: "post",
                    beforeSend: function () { $("input[name='register_username']").ajaxFieldAnim(true); },
                    dataFilter: function (data) {
                        $("input[name='register_username']").ajaxFieldAnim(false);
                        return (JSON.parse(data).state == 'success') ? true : false;
                    },
                }
            },
            register_email: {
                required: true,
                regex: /^(\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w*)*)/,
                remote: {
                    url: ajaxCheckFields+"email",
                    type: "post",
                    beforeSend: function () { $("input[name='email']").ajaxFieldAnim(true); },
                    dataFilter: function (data) {
                        $("input[name='email']").ajaxFieldAnim(false);
                        return (JSON.parse(data).state == 'success') ? true : false;
                    },
                }
            },
            register_password: {
                required: true,
                regex: /^\S[\S ]{1,98}\S$/
            },
            register_password2: {
                required: true,
                regex: /^\S[\S ]{1,98}\S$/,
                equalTo: "#register_password"
            },
        },
        highlight: function (element) {
            var classError = (frm.validate().getFailedRule() != 'remote') ? 'has-error' : 'has-ajax';
            var id_attr = "#" + $( element ).attr("id") + "1";
            $(element).closest('.form-group').removeClass('has-success').addClass(classError);
            $(id_attr).removeClass('glyphicon-ok').addClass('glyphicon-remove');
        },
        unhighlight: function (element) {
            if (!frm.validate().check(element)) return;
            var id_attr = "#" + $( element ).attr("id") + "1";
            $(element).closest('.form-group').removeClass('has-error').addClass('has-success');
            $(id_attr).removeClass('glyphicon-remove').addClass('glyphicon-ok');
        },
        onkeyup: function(element) {
            var button = frm.find(":submit");
            if (frm.validate().checkForm())
                button.removeAttr('disabled');
            else
                button.attr('disabled', 'disabled');
        },
        errorElement: 'span',
        errorClass: 'help-block',
        errorPlacement: function (error, element) {
            var id_help = $(error).attr("id")+"-error";
            $("#"+id_help).remove();
            error.insertAfter(element);
        },
        messages: {
            register_name: {
                required: Joomla.JText._('DGQ_AJAX_REGISTER_REQUIRED'),
                regex: Joomla.JText._('DGQ_AJAX_REGISTER_INVALID_NAME')
            },
            register_username: {
                required: Joomla.JText._('DGQ_AJAX_REGISTER_REQUIRED'),
                inverse_regex: Joomla.JText._('DGQ_AJAX_REGISTER_INVALID_USERNAME'),
                remote: Joomla.JText._('JLIB_DATABASE_ERROR_USERNAME_INUSE')
            },
            register_email: {
                required: Joomla.JText._('DGQ_AJAX_REGISTER_REQUIRED'),
                regex: Joomla.JText._('DGQ_AJAX_REGISTER_INVALID_EMAIL'),
                remote: Joomla.JText._('JLIB_DATABASE_ERROR_EMAIL_INUSE')
            },
            register_password: {
                required: Joomla.JText._('DGQ_AJAX_REGISTER_REQUIRED'),
                regex: Joomla.JText._('DGQ_AJAX_REGISTER_INVALID_PASSWORD')
            },
            register_password2: {
                required: Joomla.JText._('DGQ_AJAX_REGISTER_REQUIRED'),
                regex: Joomla.JText._('DGQ_AJAX_REGISTER_INVALID_PASSWORD'),
                equalTo: Joomla.JText._('DGQ_AJAX_REGISTER_CONFIRM_PASSWORD')
            }
        }
    });
});