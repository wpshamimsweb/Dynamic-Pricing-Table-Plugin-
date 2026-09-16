/**
 * R3 Pricing Table — Admin JavaScript  v2.0
 * Handles: tabs, pricing row repeater, exosome row repeater,
 *          icon picker, color picker, media uploader,
 *          background type switcher, overlay slider, clipboard copy
 */
(function ($) {
    'use strict';

    // ── Tabs ────────────────────────────────────────────────────────────────────
    function initTabs() {
        var $nav    = $('.r3pt-tab-nav');
        var $panels = $('.r3pt-tab-panel');

        $nav.on('click', '.r3pt-tab-btn', function () {
            var tab = $(this).data('tab');

            $nav.find('.r3pt-tab-btn').removeClass('active');
            $(this).addClass('active');

            $panels.removeClass('active');
            $('#r3pt-tab-' + tab).addClass('active');
        });
    }

    // ── Generic Row Repeater ─────────────────────────────────────────────────────
    function initRowRepeater(bodyId, addBtnId, removeClass, fieldPrefix, placeholders) {
        var $body   = $(bodyId);
        var $addBtn = $(addBtnId);

        if ( ! $body.length ) return;

        function reindexRows() {
            $body.find('tr').each(function (i) {
                $(this).find('input').each(function () {
                    var name = $(this).attr('name');
                    if (name) {
                        $(this).attr('name', name.replace(/\[\d+\]/, '[' + i + ']'));
                    }
                });
            });
        }

        $body.on('click', '.' + removeClass, function () {
            $(this).closest('tr').remove();
            reindexRows();
        });

        $addBtn.on('click', function () {
            var idx = $body.find('tr').length;
            var p   = placeholders || {};
            var tpl =
                '<tr>' +
                '<td class="r3pt-col-handle r3pt-drag-handle" title="Drag to reorder">⠿</td>' +
                '<td><input type="text" name="' + fieldPrefix + '[' + idx + '][cell_count]" placeholder="' + (p.cell_count || 'e.g. 25M') + '" class="small-text"></td>' +
                '<td><input type="text" name="' + fieldPrefix + '[' + idx + '][price]" placeholder="' + (p.price || 'e.g. $4,999.00') + '" class="small-text"></td>' +
                '<td><input type="text" name="' + fieldPrefix + '[' + idx + '][exosomes]" value="' + (p.exosomes || 'NO') + '" class="small-text"></td>' +
                '<td><input type="text" name="' + fieldPrefix + '[' + idx + '][hotel]" value="' + (p.hotel || 'NO') + '" class="small-text"></td>' +
                '<td><button type="button" class="button ' + removeClass + '">✕</button></td>' +
                '</tr>';
            $body.append(tpl);
            $body.find('tr:last-child input:first').focus();
        });

        // Drag & drop sorting
        if ($.fn.sortable) {
            $body.sortable({
                handle: '.r3pt-drag-handle',
                axis: 'y',
                placeholder: 'r3pt-row-placeholder',
                update: function () {
                    reindexRows();
                }
            });
        }
    }

    function initPricingRowRepeater() {
        initRowRepeater(
            '#r3pt-rows-body',
            '#r3pt-add-row',
            'r3pt-remove-row',
            'pricing_rows',
            { cell_count: 'e.g. 275M', price: 'e.g. $21,000.00', exosomes: 'NO', hotel: 'NO' }
        );
    }

    function initExosomeRowRepeater() {
        initRowRepeater(
            '#r3pt-exosome-rows-body',
            '#r3pt-add-exosome-row',
            'r3pt-remove-exosome-row',
            'exosome_rows',
            { cell_count: 'e.g. 100 billion', price: 'e.g. $4,999.00', exosomes: 'no', hotel: 'no' }
        );
    }

    // ── Icon Select ─────────────────────────────────────────────────────────────
    function initIconSelect() {
        var icons = {
            cells:   '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36 36" width="28" height="28"><circle cx="18" cy="18" r="16" fill="none" stroke="#fff" stroke-width="2"/><circle cx="12" cy="14" r="4" fill="#fff"/><circle cx="24" cy="14" r="4" fill="#fff"/><circle cx="18" cy="23" r="4" fill="#fff"/></svg>',
            dollar:  '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36 36" width="28" height="28"><circle cx="18" cy="18" r="16" fill="none" stroke="#fff" stroke-width="2"/><text x="18" y="24" text-anchor="middle" font-family="Arial" font-weight="bold" font-size="18" fill="#fff">$</text></svg>',
            vial:    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36 36" width="28" height="28"><rect x="10" y="8" width="16" height="20" rx="4" fill="none" stroke="#fff" stroke-width="2"/><rect x="12" y="6" width="12" height="4" rx="1" fill="#fff"/><circle cx="18" cy="20" r="4" fill="#fff" opacity="0.5"/></svg>',
            hotel:   '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36 36" width="28" height="28"><rect x="6" y="12" width="24" height="18" fill="none" stroke="#fff" stroke-width="2"/><rect x="10" y="16" width="4" height="4" fill="#fff"/><rect x="22" y="16" width="4" height="4" fill="#fff"/><rect x="10" y="22" width="4" height="4" fill="#fff"/><rect x="22" y="22" width="4" height="4" fill="#fff"/><rect x="14" y="24" width="8" height="6" fill="#fff"/><polygon points="4,12 18,4 32,12" fill="none" stroke="#fff" stroke-width="2"/></svg>',
            syringe: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36 36" width="28" height="28"><line x1="6" y1="6" x2="30" y2="30" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/><rect x="10" y="8" width="16" height="8" rx="2" fill="none" stroke="#fff" stroke-width="2" transform="rotate(45 18 12)"/><line x1="14" y1="22" x2="22" y2="14" stroke="#fff" stroke-width="1.5"/><line x1="12" y1="24" x2="6" y2="30" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>',
            star:    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36 36" width="28" height="28"><polygon points="18,4 22,14 33,14 24,21 27,32 18,25 9,32 12,21 3,14 14,14" fill="#fff"/></svg>',
            heart:   '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36 36" width="28" height="28"><path d="M18 30 C18 30 4 21 4 12 C4 7.6 7.6 4 12 4 C14.6 4 17 5.4 18 7.4 C19 5.4 21.4 4 24 4 C28.4 4 32 7.6 32 12 C32 21 18 30 18 30Z" fill="#fff"/></svg>',
            dna:     '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36 36" width="28" height="28"><path d="M12 4 C12 4 24 10 24 18 C24 26 12 32 12 32" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/><path d="M24 4 C24 4 12 10 12 18 C12 26 24 32 24 32" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/><line x1="12" y1="10" x2="24" y2="14" stroke="#fff" stroke-width="1.5"/><line x1="12" y1="22" x2="24" y2="26" stroke="#fff" stroke-width="1.5"/><line x1="12" y1="16" x2="24" y2="20" stroke="#fff" stroke-width="1.5"/></svg>',
        };

        $(document).on('change', '.r3pt-icon-select', function () {
            var col      = $(this).data('col');
            var val      = $(this).val();
            var $preview = $('[data-col-preview="' + col + '"]');
            var $customArea = $('#r3pt-custom-svg-' + col);

            if (val === 'custom') {
                $customArea.slideDown(200);
                var customSvg = $customArea.find('textarea').val().trim();
                $preview.html(customSvg || icons['cells']);
            } else {
                $customArea.slideUp(200);
                $preview.html(icons[val] || icons['cells']);
            }
        });

        $(document).on('input', '.r3pt-custom-svg-input', function () {
            var col      = $(this).closest('.r3pt-custom-svg-field').attr('id').replace('r3pt-custom-svg-', '');
            var $preview = $('[data-col-preview="' + col + '"]');
            var svg      = $(this).val().trim();
            if (svg) {
                $preview.html(svg);
            }
        });
    }

    // ── Color Picker ─────────────────────────────────────────────────────────────
    function initColorPickers() {
        if ($.fn.wpColorPicker) {
            $('.r3pt-color-picker').wpColorPicker();
        }
    }

    // ── Border Radius Slider ─────────────────────────────────────────────────────
    function initRadiusSlider() {
        var $range  = $('#r3pt_border_radius_range');
        var $number = $('#r3pt_border_radius');

        $range.on('input', function () { $number.val($(this).val()); });
        $number.on('input', function () { $range.val($(this).val()); });
    }

    // ── Overlay Opacity Slider ───────────────────────────────────────────────────
    function initOverlaySlider() {
        var $range  = $('#r3pt_overlay_opacity_range');
        var $number = $('#r3pt_overlay_opacity');

        $range.on('input', function () { $number.val($(this).val()); });
        $number.on('input', function () { $range.val($(this).val()); });
    }

    // ── Background Type Switcher ─────────────────────────────────────────────────
    function initBackgroundTypeSwitcher() {
        function updateBgSections(val) {
            $('.r3pt-bg-color-section').toggle(val === 'color');
            $('.r3pt-bg-image-section').toggle(val === 'image');
            $('.r3pt-bg-video-section').toggle(val === 'video');
            $('.r3pt-bg-overlay-section').toggle(val !== 'color');

            // Update active state on radio cards
            $('.r3pt-radio-card').removeClass('active');
            $('input[name="bg_type"][value="' + val + '"]').closest('.r3pt-radio-card').addClass('active');
        }

        // Init on load
        var currentVal = $('input[name="bg_type"]:checked').val() || 'color';
        updateBgSections(currentVal);

        $(document).on('change', 'input[name="bg_type"]', function () {
            updateBgSections($(this).val());
        });
    }

    // ── Media Uploader (Logo) ─────────────────────────────────────────────────────
    function initMediaUploader() {
        var logoFrame;

        $(document).on('click', '.r3pt-upload-logo-btn', function (e) {
            e.preventDefault();
            if (logoFrame) { logoFrame.open(); return; }

            logoFrame = wp.media({
                title: 'Choose Logo Image',
                button: { text: 'Use This Image' },
                multiple: false,
                library: { type: 'image' }
            });

            logoFrame.on('select', function () {
                var attachment = logoFrame.state().get('selection').first().toJSON();
                $('#r3pt_logo_url').val(attachment.url);
                $('.r3pt-logo-preview').html('<img src="' + attachment.url + '" alt="Logo">');
                $('.r3pt-remove-logo-btn').show();
            });

            logoFrame.open();
        });

        $(document).on('click', '.r3pt-remove-logo-btn', function (e) {
            e.preventDefault();
            $('#r3pt_logo_url').val('');
            $('.r3pt-logo-preview').html('<div class="r3pt-logo-placeholder">No logo set — default R3 SVG will be used</div>');
            $(this).hide();
        });
    }

    // ── Media Uploader (Background Image) ────────────────────────────────────────
    function initBgImageUploader() {
        var bgFrame;

        $(document).on('click', '.r3pt-upload-bg-btn', function (e) {
            e.preventDefault();
            if (bgFrame) { bgFrame.open(); return; }

            bgFrame = wp.media({
                title: 'Choose Background Image',
                button: { text: 'Use This Image' },
                multiple: false,
                library: { type: 'image' }
            });

            bgFrame.on('select', function () {
                var attachment = bgFrame.state().get('selection').first().toJSON();
                $('#r3pt_bg_image_url').val(attachment.url);
                $('.r3pt-bg-image-preview').html('<img src="' + attachment.url + '" alt="Background" style="max-height:120px;border-radius:6px;">');
                $('.r3pt-remove-bg-btn').show();
            });

            bgFrame.open();
        });

        $(document).on('click', '.r3pt-remove-bg-btn', function (e) {
            e.preventDefault();
            $('#r3pt_bg_image_url').val('');
            $('.r3pt-bg-image-preview').html('<div class="r3pt-logo-placeholder">No background image set</div>');
            $(this).hide();
        });
    }

    // ── Clipboard Copy ────────────────────────────────────────────────────────────
    function initClipboard() {
        $(document).on('click', '.r3pt-copy-btn', function () {
            var text = $(this).data('clipboard');
            var $btn = $(this);

            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function () {
                    $btn.text('Copied!').addClass('copied');
                    setTimeout(function () { $btn.text('Copy').removeClass('copied'); }, 2000);
                });
            } else {
                var $temp = $('<input>');
                $('body').append($temp);
                $temp.val(text).select();
                document.execCommand('copy');
                $temp.remove();
                $btn.text('Copied!').addClass('copied');
                setTimeout(function () { $btn.text('Copy').removeClass('copied'); }, 2000);
            }
        });
    }

    // ── Init ──────────────────────────────────────────────────────────────────────
    $(document).ready(function () {
        initTabs();
        initPricingRowRepeater();
        initExosomeRowRepeater();
        initIconSelect();
        initColorPickers();
        initRadiusSlider();
        initOverlaySlider();
        initBackgroundTypeSwitcher();
        initMediaUploader();
        initBgImageUploader();
        initClipboard();
    });

})(jQuery);
