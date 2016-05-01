/**
 * DGQ Javascript functions
 */
var DGQ = {};

(function( DGQ, document ) {
    DGQ.selectors = {
        options: {},
        load: function( object ) {
            for ( var key in object ) {
                if (!object.hasOwnProperty(key)) continue;
                this.options[ key.toUpperCase() ] = object[ key ];
            }
            return this;
        }
    };
    DGQ.tools = {
        lang: url('?', $(location).attr('href')).lang,
        Itemid: url('?', $(location).attr('href')).Itemid,
        genURI: function (type) {},
    };
}( DGQ, document ));

/**
 * JQuery Functions
 */
(
    /**
     * AJAX FUNCTIONS
     */
    function ($) {
        /**
         * ajaxSubmitAnim: Animate the submit button in forms
         */
        $.fn.ajaxSubmitAnim = function () {
            var button = $(this).find(":submit");
            if (button.attr('dgq-ajax-animate') == 'on') {
                button.html(button.attr('origin-value'));
                button.attr('dgq-ajax-animate', 'off');
            } else {
                button.attr('origin-value', button.html());
                button.attr('dgq-ajax-animate', 'on');
                button.text('');
                button.append('<span class="' + button.attr('classload') + '"></span> ' + button.attr('valueload'));
            }
        }
        /**
         * ajaxSubmitAnim: Animate the submit button in forms
         */
        $.fn.ajaxFieldAnim = function (enable) {
            var id_attr = "#" + $(this).attr("id") + "1";
            if (enable) {
                $(this).closest('.form-group').removeClass('has-success has-error').addClass('has-ajax');
                $(id_attr).removeClass('glyphicon-ok glyphicon-remove').addClass('glyphicon-refresh glyphicon-refresh-animate');
            } else {
                $(this).closest('.form-group').removeClass('has-ajax').addClass('has-success');
                $(id_attr).removeClass('glyphicon-refresh glyphicon-refresh-animate glyphicon-remove').addClass('glyphicon-ok');
            }
        }
    }
)(jQuery);

(
    /**
     * VALIDATION
     */
    function ($) {
        /**
         * hasError:
         */
        $.fn.hasError = function (helpblock, msg) {
            var id_attr = "#" + $(this).attr("id") + "1";
            $(this).closest('.form-group').removeClass('has-success').addClass('has-error');
            $(id_attr).removeClass('glyphicon-ok').addClass('glyphicon-remove');
            if (helpblock) {
                var id_help = $(this).attr("id") + "-error";
                $("#" + id_help).remove();
                $(id_attr).after("<span id='" + id_help + "' class='help-block'>" + msg + "</span>");
            }
        }
        /**
         * hasSuccess:
         */
        $.fn.hasSuccess = function () {
            var id_attr = "#" + $(this).attr("id") + "1";
            $(this).closest('.form-group').removeClass('has-error').addClass('has-success');
            $(id_attr).removeClass('glyphicon-remove').addClass('glyphicon-ok');
            var id_help = $(this).attr("id") + "-error";
            $("#" + id_help).remove();
        }
    }
)(jQuery);

(
    function ($) {
        $.fn.attrs = function() {
            if(arguments.length === 0) {
                if(this.length === 0) {
                    return null;
                }

                var obj = {};
                $.each(this[0].attributes, function() {
                    if(this.specified) {
                        obj[this.name] = this.value;
                    }
                });
                return obj;
            }

            return old.apply(this, arguments);
        }
        $.fn.hasEventDefined = function (event) {
            var events = $._data($(this)[0], 'events');
            return events.hasOwnProperty(event);
        }
    }
)(jQuery);
