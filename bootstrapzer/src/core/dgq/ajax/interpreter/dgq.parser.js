

(
    function ($) {
        $.fn.parsePattern = {
            init: function (type, data) {
                switch (type) {
                    case 'loopajax': this.typeLoopAjax(data); break;
                }
            },
            /**
             * TYPES
             */
            typeLoopAjax: function (data) {
                var content = data.content;
                $(this).find('dgq-limit[element-id]').each(function (index) {
                    var toParse = content[$(this).attr('element-id')];
                    $(this).find('dgq[field][for=' + id + ']').each(function (i) {
                        var field = $(this).attr('field');
                        switch (true) {
                            case (field.indexOf('button') > -1):
                                $(this).replaceWith(_this.parseButtons($(this), toParse));
                                break;
                            default:
                                $(this).replaceWith(toParse[$(this).attr('field')]);
                                break;
                        }
                    });
                    $(this).replaceWith($(this).html());
                });
            },
            /**
             * PARSERS
             */
            parseFields: function () {

            },
            parseButtons: function (element, toParse) {
                var field = element.attr('field');
                var matches = field.match(/button\[(\d+)\]/);
                var num = matches[1];
                var params = JSON.parse(toParse['attribs']);

                var name = params['dgq_button'+num+'_name'];
                var css = params['dgq_button'+num+'_css'];
                var link = params['dgq_button'+num+'_link'];

                link = (link.length == 0) ?
                'index.php?option=com_content&view=article&id='+toParse.id+'&Itemid='+DGQ.tools.Itemid+'&lang='+DGQ.tools.lang :
                    link;

                return '<a href="'+link+'" class="'+css+'">'+name+'</a>';

            },
        }
    }
)(jQuery);
