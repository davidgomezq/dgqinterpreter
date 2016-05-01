
(
    /**
     * PAGINATION CONTROLLER
     */
    function ($) {
        $.fn.loopAjaxController = {
            objs: {},
            init: function (data) {
                this.objs[data.id] = {
                    // Content data
                    rel: data.rel,
                    id: data.id,
                    selector: $('#'+data.id),
                    content: null,
                    // Pagination data
                    pagContent: null,
                    pagSelector: 'dgq[field=pagination][for='+data.id+']',
                    pagAmount: data.pagAmount,
                    pagData: null,
                    pagTotal: data.pagTotal,
                }
                this.objs[data.id]['pattern'] = this.objs[data.id].selector.find('dgq-limit').html();
                this.ajax(1, data.id);
            },
            /**
             * AJAX PETITIONS
             */
            ajax: function (page, id) {
                var _this = this;
                var _obj = this.objs[id];
                jQuery.ajaxQueue({
                    type: 'POST',
                    url: 'index.php?dgq_option=dgq_ajax&method=getContentLoop',
                    dataType: 'json',
                    data: {
                        rel: _obj.rel,
                        id: id,
                        page: page,
                        amount: _obj.pagAmount,
                        lang: url('?', $(location).attr('href')).lang
                    },
                    success: function (data) {
                        switch (data.state) {
                            case 'success':
                                _this.parseData(data.extra.content, data.extra.id);
                                break;
                            default:
                                _this.parseData([], data.extra.id);
                                break;
                        }
                    }
                });
            },
            runPagination: function (id) {
                var _obj = this.objs[id];
                var data = _obj.pagData;
                var _this = this;
                _obj.pagSelector.bootpag({
                    total: _obj.pagTotal,
                    page: 1,
                    maxVisible: (!data.hasOwnProperty('max-visible')) ? 5 : (Number(data['max-visible']) > 1) ? Number(data['max-visible']) : 5,
                    leaps: true,
                    firstLastUse: (data.hasOwnProperty('first') || data.hasOwnProperty('last')) ? true : false,
                    first: (data.hasOwnProperty('first')) ? data['first'] : '←',
                    firstClass: (!data.hasOwnProperty('first-class')) ? 'first' : (data['first-class'].length > 0) ? data['first-class'] : 'first',
                    last: (data.hasOwnProperty('last')) ? data['last'] : '→',
                    lastClass: (!data.hasOwnProperty('last-class')) ? 'last' : (data['last-class'].length > 0) ? data['last-class'] : 'last',
                    wrapClass: (!data.hasOwnProperty('wrap-class')) ? 'pagination' : (data['wrap-class'].length > 0) ? data['wrap-class'] : 'pagination',
                    activeClass: (!data.hasOwnProperty('active-class')) ? 'active' : (data['active-class'].length > 0) ? data['active-class'] : 'active',
                    disabledClass: (!data.hasOwnProperty('disable-class')) ? 'disable' : (data['disable-class'].length > 0) ? data['disable-class'] : 'disable',
                    nextClass: (!data.hasOwnProperty('next-class')) ? 'next' : (data['next-class'].length > 0) ? data['next-class'] : 'next',
                    prevClass: (!data.hasOwnProperty('prev-class')) ? 'prev' : (data['next-class'].length > 0) ? data['prev-class'] : 'prev',
                    next: (data.hasOwnProperty('next')) ? data['next'] : '&raquo;',
                    prev: (data.hasOwnProperty('prev')) ? data['prev'] : '&laquo;',
                }).on("page", function(event, num) {
                    _this.ajax(num, id);
                });
            },
            /**
             * PARSER FUNCTIONS
             */
            parseData: function (content, id) {
                var _obj = this.objs[id];
                _obj.content = content;
                this.prepareLimits(id);
                var _this = this;
                _obj.selector.find('dgq-limit[element-id]').each(function (index) {
                    var toParse = _obj.content[$(this).attr('element-id')];
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
            /**
             * PREPARED FUNCTIONS
             */
            prepareLimits: function (id) {
                var _obj = this.objs[id];
                var limit = _obj.selector.find('div[data-pag='+id+'-content]');
                if (!limit.length) {
                    limit = _obj.selector.find('dgq-limit');
                    if (!limit.length) return;
                }

                // Generate code of limits
                var htmlCode = '';
                for (var i = 0; i < _obj.content.length; i++)
                    htmlCode += '<dgq-limit element-id="' + i + '">' + _obj.pattern + '</dgq-limit>';
                if (htmlCode == '') htmlCode = '<p class="no-content">'+Joomla.JText._('DGQ_NOT_CONTENT')+'</p>';
                limit.replaceWith('<div data-pag="'+id+'-content">'+htmlCode+'</div>');
                _obj.pagContent = _obj.selector.find('div[data-pag=content]');
                this.preparePagination(id);
            },
            preparePagination: function (id) {
                var _obj = this.objs[id];
                var pagSelector = $(_obj.pagSelector);
                if (_obj.pagData != null) return;
                _obj.pagData = pagSelector.attrs();
                pagSelector.replaceWith('<div data-pag="'+_obj.id+'-pagination"></div>');
                _obj.pagSelector = $("div[data-pag="+id+"-pagination]");
                this.runPagination(id);
            },
        }
    }
)(jQuery);
