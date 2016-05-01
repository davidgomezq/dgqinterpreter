jQuery(document).ready(function($) {
    var login = $('#login-form');
    var logout = $('#logout-form');

    // Ajax send and response
    login.submit(function (ev) {
        $.ajax({
            type: 'POST',
            url: 'index.php?dgq_option=dgq_ajax&method=login',
            dataType: 'json',
            data: login.serialize(),
            beforeSend: function() {
                if ($(document).hasEventDefined('onBeforeLogin'))
                    $(document).trigger('onBeforeLogin');
                else
                    login.ajaxSubmitAnim();
            },
            success: function (data) {
                login.ajaxSubmitAnim();
                switch (data.state) {
                    case 'success':
                        if ($(document).hasEventDefined('onSuccessLogin'))
                            $(document).trigger('onSuccessLogin', [data]);
                        else
                            window.location.reload(true);
                        break;
                    default:
                        if ($(document).hasEventDefined('onFailedLogin'))
                            $(document).trigger('onFailedLogin', [data]);
                        else {
                            $('#login-alerts').empty();
                            $('#login-alerts').append("<p class='alert alert-danger alert-dismissible' role='alert'>" +
                                "<button type='button' class='close' data-dismiss='alert' aria-label='Close'>" +
                                "<span aria-hidden='true'>&times;</span>" +
                                "</button>" + Joomla.JText._('JLIB_LOGIN_AUTHENTICATE') + "</p>");
                        }
                        break;
                }
            }
        });

        ev.preventDefault();
    });

    logout.submit(function (ev) {
        $.ajax({
            type: 'POST',
            url: 'index.php?dgq_option=dgq_ajax&method=logout',
            dataType: 'json',
            data: logout.serialize(),
            beforeSend: function() {
                if ($(document).hasEventDefined('onBeforeLogout'))
                    $(document).trigger('onBeforeLogout');
                else
                    logout.ajaxSubmitAnim();
            },
            success: function (data) {
                if ($(document).hasEventDefined('onSuccessLogout'))
                    $(document).trigger('onSuccessLogout', [data]);
                else {
                    logout.ajaxSubmitAnim();
                    window.location.reload(true);
                }
            }
        });

        ev.preventDefault();
    });
});