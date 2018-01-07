$(function() {

    if (typeof typo3bb_emoticons === 'undefined') {
        typo3bb_emoticons = {};
    }
    if (typeof typo3bb_tinymce_langKey === 'undefined') {
        typo3bb_tinymce_langKey = 'en_GB';
    }
    tinymce.init({
        language: typo3bb_tinymce_langKey,
        selector: '.typo3bb-rte-editor',
        setup: function (editor) {
            editor.on('change', function () {
                editor.save();
            });
        },
        //forced_root_block: false,
        content_css : '/typo3conf/ext/iglarp_template/Resources/Public/assets/css/main.min.css',
        auto_convert_smileys: true,
        relative_urls: false,
        plugins: 'link, autolink, image, table, smileys, hr, code',
        fontsize_formats: '8px 10px 12px 14px 18px 24px 30px 36px',
        toolbar1: 'undo redo | styleselect fontsizeselect | bold italic | link image | alignleft aligncenter alignright alignjustify | bullist numlist hr | outdent indent | blockquote | smileys | code',
        menu: {
            edit: {title: 'Edit', items: 'undo redo | cut copy paste | selectall'},
            insert: {title: 'Insert', items: 'link media | template hr'},
            view: {title: 'View', items: 'visualaid'},
            format: {title: 'Format', items: 'bold italic underline strikethrough superscript subscript | formats | removeformat'},
            table: {title: 'Table', items: 'inserttable tableprops deletetable | cell row column'},
            tools: {title: 'Tools', items: 'spellchecker code'}
        },
        image_class_list: [
            { title: 'responsive', value: 'img_responsive' }
        ],
        smileys: typo3bb_emoticons,

        valid_elements : "@[style|class|id|title],a[target|ping|media|href|hreflang|type|rel],blockquote[cite],big,br,code,dd,div,dl,dt,-em/i,footer,-h1,-h2,-h3,-h4,-h5,-h6,hr,img[alt=|src|ismap|usemap|width|height],li[value],mark,ol[reversed|start],-p,-pre,q[cite],small,-span,-strong/b,-sub,-sup,table,tbody,td[colspan|rowspan|headers],tfoot,th[colspan|rowspan|headers|scope],thead,tr,u,ul,",
        formats: {
            h1: {block: 'span', classes: 'h1'},
            h2: {block: 'span', classes: 'h2'},
            h3: {block: 'span', classes: 'h3'},
            h4: {block: 'span', classes: 'h4'},
            h5: {block: 'span', classes: 'h5'},
            h6: {block: 'span', classes: 'h6'},
            alignleft: {block: 'p', classes: 'text-left'},
            aligncenter: {block: 'p', classes: 'text-center'},
            alignright: {block: 'p', classes: 'text-right'},
            alignjustify: {block: 'p', classes: 'text-justify'},
            strikethrough: {inline: 's'},
        },
        style_formats: [
            {title: 'Headings', items: [
                {title: 'Heading 1', format: 'h1'},
                {title: 'Heading 2', format: 'h2'},
                {title: 'Heading 3', format: 'h3'},
                {title: 'Heading 4', format: 'h4'},
                {title: 'Heading 5', format: 'h5'},
                {title: 'Heading 6', format: 'h6'},
            ]},
            {title: 'Inline', items: [
                {title: 'Bold', icon: 'bold', format: 'bold'},
                {title: 'Italic', icon: 'italic', format: 'italic'},
                {title: 'Underline', icon: 'underline', inline: 'u'},
                {title: 'Strikethrough', icon: 'strikethrough', format: 'strikethrough'},
                {title: 'Superscript', icon: 'superscript', format: 'superscript'},
                {title: 'Subscript', icon: 'subscript', format: 'subscript'},
                {title: 'Code', icon: 'code', format: 'code'},
                {title: 'Marker',        inline: 'mark'},
                {title: 'Big',          inline: 'big'},
                {title: 'Small',         inline: 'small'},
            ]},
            {title: 'Alignment', items: [
                {title: 'Left', icon: 'alignleft', format: 'alignleft'},
                {title: 'Center', icon: 'aligncenter', format: 'aligncenter'},
                {title: 'Right', icon: 'alignright', format: 'alignright'},
                {title: 'Justify', icon: 'alignjustify', format: 'alignjustify'}
            ]},
            {title: 'Blocks', items: [
                {title: 'Paragraph', format: 'p'},
                {title: 'Div', format: 'div'},
                {title: 'Pre', format: 'pre'}
            ]},
            {title: 'Quotes', items: [
                {title: 'Insert Quote', block: 'blockquote', icon: 'blockquote', wrapper: true},
                {title: 'Reverse Blockquote', selector: 'blockquote', classes: 'blockquote-reverse'},
                {title: 'Centered Blockquote', selector: 'blockquote', classes: 'text-center'},
                {title: 'Blockquote Footer', block: 'footer'}
            ]},
            {title: 'Images', items: [
                {
                    title: 'Rounded Corners',
                    selector: 'img',
                    classes: 'img-rounded'
                },
                {
                    title: 'Circle',
                    selector: 'img',
                    classes: 'img-circle'
                },
                {
                    title: 'Thumbnail',
                    selector: 'img',
                    classes: 'img-thumbnail'
                }
            ]},
            { title: 'Colors', items: [
                {
                    title: 'Muted',
                    inline: 'span',
                    classes: 'text-muted'
                },
                {
                    title: 'Primary',
                    inline: 'span',
                    classes: 'text-primary'
                },
                {
                    title: 'Success',
                    inline: 'span',
                    classes: 'text-success'
                },
                {
                    title: 'Info',
                    inline: 'span',
                    classes: 'text-info'
                },
                {
                    title: 'Warning',
                    inline: 'span',
                    classes: 'text-warning'
                },
                {
                    title: 'Danger',
                    inline: 'span',
                    classes: 'text-danger'
                },
                {
                    title: 'Background Primary',
                    block: 'div',
                    classes: 'bg-primary',
                    wrapper: true
                },
                {
                    title: 'Background Success',
                    block: 'div',
                    classes: 'bg-success',
                    wrapper: true
                },
                {
                    title: 'Background Info',
                    block: 'div',
                    classes: 'bg-info',
                    wrapper: true
                },
                {
                    title: 'Background Warning',
                    block: 'div',
                    classes: 'bg-warning',
                    wrapper: true
                },
                {
                    title: 'Background Danger',
                    block: 'div',
                    classes: 'bg-danger',
                    wrapper: true
                }
            ]},
            { title: 'Links', items: [
                {
                    title: 'Default',
                    selector: 'a',
                    classes: 'btn btn-default'
                },
                {
                    title: 'Primary',
                    selector: 'a',
                    classes: 'btn btn-primary'
                },
                {
                    title: 'Success',
                    selector: 'a',
                    classes: 'btn btn-success'
                },
                {
                    title: 'Info',
                    selector: 'a',
                    classes: 'btn btn-info'
                },
                {
                    title: 'Warning',
                    selector: 'a',
                    classes: 'btn btn-warning'
                },
                {
                    title: 'Danger',
                    selector: 'a',
                    classes: 'btn btn-danger'
                },
                {
                    title: 'Link',
                    selector: 'a',
                    classes: 'btn btn-link'
                },
                {
                    title: 'Large',
                    selector: 'a,button,input',
                    classes: 'btn-lg'
                },
                {
                    title: 'Small',
                    selector: 'a,button,input',
                    classes: 'btn-sm'
                },
                {
                    title: 'Extra Small',
                    selector: 'a,button,input',
                    classes: 'btn-xs'
                },
                {
                    title: 'Block',
                    selector: 'a,button,input',
                    classes: 'btn-block'
                },
                {
                    title: 'Disabled',
                    selector: 'a,button,input',
                    attributes: {
                        disabled: 'disabled'
                    }
                }
            ]},
            { title: 'Labels', items: [
                {
                    title: 'Default',
                    inline: 'span',
                    classes: 'label label-default'
                },
                {
                    title: 'Primary',
                    inline: 'span',
                    classes: 'label label-primary'
                },
                {
                    title: 'Success',
                    inline: 'span',
                    classes: 'label label-success'
                },
                {
                    title: 'Info',
                    inline: 'span',
                    classes: 'label label-info'
                },
                {
                    title: 'Warning',
                    inline: 'span',
                    classes: 'label label-warning'
                },
                {
                    title: 'Danger',
                    inline: 'span',
                    classes: 'label label-danger'
                }
            ]},
            { title: 'Alerts', items: [
                {
                    title: 'Default',
                    block: 'div',
                    classes: 'alert alert-default',
                    wrapper: true
                },
                {
                    title: 'Primary',
                    block: 'div',
                    classes: 'alert alert-primary',
                    wrapper: true
                },
                {
                    title: 'Success',
                    block: 'div',
                    classes: 'alert alert-success',
                    wrapper: true
                },
                {
                    title: 'Info',
                    block: 'div',
                    classes: 'alert alert-info',
                    wrapper: true
                },
                {
                    title: 'Warning',
                    block: 'div',
                    classes: 'alert alert-warning',
                    wrapper: true
                },
                {
                    title: 'Danger',
                    block: 'div',
                    classes: 'alert alert-danger',
                    wrapper: true
                }
            ]},
            { title: 'Other', items: [
                {
                    title: 'Well',
                    block: 'div',
                    classes: 'well',
                    wrapper: true
                },
                {
                    title: 'Large Well',
                    block: 'div',
                    classes: 'well well-lg',
                    wrapper: true
                },
                {
                    title: 'Small Well',
                    block: 'div',
                    classes: 'well well-sm',
                    wrapper: true
                },
                {
                    title: 'Badge',
                    inline: 'span',
                    classes: 'badge'
                }
            ]}
        ]
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