$(function() {
    var ckeditorDefaultSettings = {};
    try {
        ckeditorDefaultSettings = LumIT.Typo3bb.Constants.ckeditorSettings;
    } catch (e) {}
    var ckeditorSettings = $.extend(true, {}, ckeditorDefaultSettings, {
        language: 'de',
        uiColor: '#FDF8EC',
        extraPlugins: 'smiley,autolink',
        removePlugins: 'clipboard',
        contentsCss: '/typo3conf/ext/iglarp_template/Resources/Public/assets/css/main.min.css',
        toolbarGroups: [
            { name: 'styles', groups: [ 'styles' ] },
            { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
            { name: 'links', groups: [ 'links' ] },
            { name: 'colors', groups: [ 'colors' ] },
            '/',
            { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
            { name: 'insert', groups: [ 'insert' ] },
            { name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
            { name: 'editing', groups: [ 'find', 'selection', 'editing' ] },
            { name: 'tools', groups: [ 'tools' ] },
            { name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
            { name: 'forms', groups: [ 'forms' ] },
            { name: 'others', groups: [ 'others' ] },
            { name: 'about', groups: [ 'about' ] }
        ],
        removeButtons: 'spellchecker,Save,NewPage,Preview,Print,Templates,Find,Replace,SelectAll,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,Subscript,Superscript,Outdent,Indent,CreateDiv,JustifyLeft,JustifyCenter,JustifyRight,JustifyBlock,BidiLtr,BidiRtl,Language,Anchor,Image,Flash,PageBreak,Iframe,Font,ShowBlocks,About',
        allowedContent: {
            '*': {attributes: ['id', 'title']},
            a: {attributes: ['target', 'ping', 'media', '!href', 'hreflang', 'type', 'rel']},
            'blockquote q': {attributes: ['cite']},
            img: {attributes: ['alt', '!src', 'ismap', 'usemap', 'width', 'height']},
            ol: {attributes: ['reversed', 'start']},
            li: {attributes: ['value']},
            ul: true,
            'dl dt dd': true,
            'table thead tbody tfoot tr': true,
            'th td': {attributes: ['colspan', 'rowspan', 'headers']},
            footer: true,
            hr: true,
            'div p pre code': true,
            'h1 h2 h3 h4 h5 h6': true,
            //inline tags
            'br big small em i strong b u s mark sub sup': true,
            'span': true
        }
    });
    $('.typo3bb-rte-editor').each(function() {
        CKEDITOR.replace(this.id, ckeditorSettings);
    });

    LumIT.MessageReceiver.init();
});

$(function() {
    var $pollForm = $('#poll-form');
    var $pollToggle = $('[data-target="#poll-form"]');
    $pollForm.on('show.bs.collapse', function() {
        $pollToggle.addClass('active');
    });
    $pollForm.on('hide.bs.collapse', function() {
        $pollForm.find('input[type=text], input[type=number]').val('');
        $pollForm.find('input[type=checkbox]').prop('checked', false);
        $pollToggle.removeClass('active');
    });
});
(function (window, $, undefined) {
    if($ === undefined) {
        alert('Error: jQuery not loaded');
        return
    }

    var LumIT = window.LumIT || {},
        MessageReceiver = LumIT.MessageReceiver || (LumIT.MessageReceiver = {});

    $.extend(MessageReceiver, {
        Selectors : {
            receiversSelect: '#new-message-receivers'
        },
        Options: {
            dataAjaxUrl: 'data-ajax-action',
            dataPreviousReceivers: 'data-previous-receivers'
        },

        init: function() {
            var self = this,
                $receiversInput = $(self.Selectors.receiversSelect);
            if ( !$receiversInput.length ) {
                return;
            }
            var previousReceivers = JSON.parse($receiversInput.attr(self.Options.dataPreviousReceivers));
            $receiversInput.replaceWith(
                $('<select ' +
                    'name="' + $receiversInput.attr('name') + '[]" ' +
                    'id="' + $receiversInput.attr('id') + '" ' +
                    'class="' + $receiversInput.attr('class') + '" ' +
                    'value="' + $receiversInput.attr('value') + '" ' +
                    'data-ajax-action="' + $receiversInput.attr('data-ajax-action') + '" ' +
                    'multiple="multiple"></select>'));
            $receiversSelect = $(self.Selectors.receiversSelect);
            $.each(previousReceivers, function(index, value) {
                $receiversSelect.append("<option selected value='" + value.id + "'>" + value.text + "</option>")
            });

            $receiversSelect.select2({
                // data: previousReceivers,
                ajax: {
                    url: $receiversSelect.attr(self.Options.dataAjaxUrl),
                    method: 'POST',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        var nameAttribute = $receiversSelect.attr('name'),
                            searchKey = nameAttribute.substr(0, nameAttribute.indexOf('[')),
                            data = {};
                        searchKey += '[search]';
                        data[searchKey] = params.term;

                        return data;
                    },
                    processResults: function (data, params) {
                        return data;
                    },
                    cache: true
                },
                escapeMarkup: function (markup) { return markup; },
                minimumInputLength: 1,
            });
        }
    });

    window.LumIT = LumIT;
}) (this, jQuery);