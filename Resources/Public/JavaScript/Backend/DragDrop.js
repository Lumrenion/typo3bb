/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Module: TYPO3/CMS/Typo3bb/Backend/DragDrop
 * this JS code does the drag+drop logic for the Layout module (Web => Page)
 * based on jQuery UI
 */
define(['jquery', 'jquery-ui/sortable', 'TYPO3/CMS/Typo3bb/Backend/NestedSortable'], function ($) {
    'use strict';

    /**
     *
     * @type {{contentIdentifier: string, dragIdentifier: string, dropZoneAvailableIdentifier: string, dropPossibleClass: string, sortableItemsIdentifier: string, columnIdentifier: string, columnHolderIdentifier: string, addContentIdentifier: string, langClassPrefix: string}}
     * @exports TYPO3/CMS/Backend/LayoutModule/DragDrop
     */
    var DragDrop = {
        sortableItemsIdentifier: '.t3js-nested-sortable',

        contentIdentifier: '.t3js-page-ce',
        dragIdentifier: '.t3js-page-ce-draghandle',
        dropZoneAvailableIdentifier: '.t3js-page-ce-dropzone-available',
        dropPossibleClass: 't3-page-ce-dropzone-possible',
        columnIdentifier: '.t3js-page-column',
        columnHolderIdentifier: '.t3js-page-columns',
        addContentIdentifier: '.t3js-page-new-ce',
        langClassPrefix: '.t3js-sortable'
    };

    /**
     * initializes Drag+Drop for all content elements on the page
     */
    DragDrop.initialize = function() {
        $(document).ready(function(){

            $('.sortable').nestedSortable({
                handle: 'div',
                items: DragDrop.sortableItemsIdentifier,
                toleranceElement: '> div',
                protectRoot: true,
                isTree: true,
                forcePlaceholderSize: true,
                helper:	'clone',
                expandOnHover: 700,
                startCollapsed: true,
                placeholder: 'placeholder',
                revert: 250,
                tolerance: 'pointer',
                update: function(e, ui) {
                    DragDrop.onSortUpdate(ui.item.parent(), ui);
                }
            });
            $('.disclose').on('click', function() {
                $(this).closest('li').toggleClass('mjs-nestedSortable-collapsed').toggleClass('mjs-nestedSortable-expanded');
            });

        });
    };

    /**
     * Called when the new position of the element gets stored
     *
     * @param {Object} $container
     * @param {Object} ui
     */
    DragDrop.onSortUpdate = function($container, ui) {
        var $selectedItem = $(ui.item),
            contentElementUid = parseInt($selectedItem.data('uid')),
            contentElementType = $selectedItem.data('recordType'),
            parameters = {},
            $parent = $container.parent();

        // send an AJAX requst via the AjaxDataHandler
        if (contentElementUid > 0 && contentElementType !== 'tx_typo3bb_domain_model_forumcategory') {
            // forum categories cannot be nested. This sets the immediate parent of a board
            parameters['data'] = {};
            parameters['data'][contentElementType] = {};
            parameters['data'][contentElementType][contentElementUid] = {};
            var containerElementType = $container.parent().data('recordType');
            if (containerElementType === 'tx_typo3bb_domain_model_forumcategory') {
                parameters['data'][contentElementType][contentElementUid]['forum_category'] = $parent.data('uid');
                parameters['data'][contentElementType][contentElementUid]['parent_board'] = 0;
            } else {
                parameters['data'][contentElementType][contentElementUid]['parent_board'] = $parent.data('uid');
                parameters['data'][contentElementType][contentElementUid]['forum_category'] = 0;
            }
        }

        var targetContentElementUid = $selectedItem.prev().data('uid');
        // the item was moved to the top of the colPos, so the page ID is used here
        if (typeof targetContentElementUid === 'undefined') {
            // the actual page is needed
            targetContentElementUid = parseInt($parent.data('pageUid'));
        } else {
            // the negative value of the content element after where it should be moved
            targetContentElementUid = parseInt(targetContentElementUid) * -1;
        }

        parameters['cmd'] = {};
        parameters['cmd'][contentElementType] = {};
        parameters['cmd'][contentElementType][contentElementUid] = {move: targetContentElementUid};
        console.log(parameters);
        // fire the request, and show a message if it has failed
        require(['TYPO3/CMS/Backend/AjaxDataHandler'], function(DataHandler) {
            DataHandler.process(parameters).done(function(result) {
                if (result.hasErrors) {
                    $container.sortable('cancel');
                }
            });
        });
    };

    $(DragDrop.initialize);

    return DragDrop;
});
