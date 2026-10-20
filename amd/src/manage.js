// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Panel zarządzania slajdami: zmiana kolejności przeciąganiem (także z klawiatury).
 *
 * @module     block_slider/manage
 * @copyright  (C) 2026 Kamil Łuczak (https://limsko.pl)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'jquery',
    'core/ajax',
    'core/notification',
    'core/sortable_list',
    'core/str',
    'core/copy_to_clipboard'
], function($, Ajax, Notification, SortableList, Str) {
    'use strict';

    /**
     * Numery pozycji po zmianie kolejności.
     *
     * @param {HTMLElement} list lista slajdów
     */
    var renumber = function(list) {
        Array.prototype.forEach.call(list.querySelectorAll('[data-region="position"]'), function(position, i) {
            position.textContent = i + 1;
        });
    };

    /**
     * Zapis kolejności na serwerze.
     *
     * @param {number} blockid instancja bloku
     * @param {HTMLElement} list lista slajdów
     * @returns {Promise}
     */
    var saveOrder = function(blockid, list) {
        var ids = Array.prototype.map.call(list.querySelectorAll('[data-slideid]'), function(item) {
            return parseInt(item.dataset.slideid, 10);
        });
        return Ajax.call([{
            methodname: 'block_slider_reorder_slides',
            args: {blockid: blockid, slideids: ids}
        }])[0];
    };

    /**
     * Miniatury, których nie udało się wczytać, zastępuje ikona.
     *
     * @param {HTMLElement} root panel zarządzania
     */
    var watchThumbnails = function(root) {
        Array.prototype.forEach.call(root.querySelectorAll('[data-region="thumb"], [data-region="mobilethumb"]'), function(image) {
            var replace = function() {
                image.hidden = true;
                var placeholder = image.parentNode.querySelector('[data-region="noimage"]');
                if (placeholder && image.dataset.region === 'thumb') {
                    placeholder.hidden = false;
                }
            };
            if (image.complete && image.naturalWidth === 0 && image.currentSrc) {
                replace();
            }
            image.addEventListener('error', replace);
        });
    };

    return {
        /**
         * Inicjalizacja panelu.
         *
         * @param {number} blockid instancja bloku
         */
        init: function(blockid) {
            var root = document.querySelector('[data-region="block-slider-manage"][data-blockid="' + blockid + '"]');
            if (!root) {
                return;
            }
            watchThumbnails(root);
            var list = root.querySelector('[data-region="slides"]');
            if (!list || list.children.length < 2) {
                return;
            }

            var sortable = new SortableList(list, {moveHandlerSelector: '[data-drag-type="move"]'});
            // Nazwa slajdu w oknie przenoszenia z klawiatury: tytuł albo numer pozycji.
            sortable.getElementName = function(element) {
                var title = element.find('[data-region="title"]').first().text().trim();
                if (title) {
                    return $.Deferred().resolve(title).promise();
                }
                var position = element.find('[data-region="position"]').first().text().trim();
                return Str.get_string('slidenumber', 'block_slider', position);
            };

            $(list).on(SortableList.EVENTS.DROP, '> [data-slideid]', function(e, info) {
                if (!info.positionChanged) {
                    return;
                }
                renumber(list);
                saveOrder(blockid, list).catch(Notification.exception);
            });
        }
    };
});
