// მედია: ფაილების ატვირთვა (drag & drop, პროგრესი) და მედიის ასარჩევი ფანჯარა ფორმის ველებისთვის
(function ($) {
    "use strict";

    var config = window.AdminConfig || {};
    var csrf = $('meta[name="csrf-token"]').attr('content');

    // ---- ატვირთვა ----

    /** ერთი ფაილი; აბრუნებს Promise-ს მედიის JSON-ით */
    function upload(file, folder, onProgress) {
        return new Promise(function (resolve, reject) {
            var data = new FormData();
            data.append('file', file);
            data.append('folder', folder || '');

            var xhr = new XMLHttpRequest();
            xhr.open('POST', config.mediaUpload);
            xhr.setRequestHeader('X-CSRF-TOKEN', csrf);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.upload.onprogress = function (e) {
                if (e.lengthComputable) { onProgress(Math.round(e.loaded / e.total * 100)); }
            };
            xhr.onload = function () {
                var json = null;
                try { json = JSON.parse(xhr.responseText); } catch (ignore) {}
                if (xhr.status === 201 && json && json.media) { resolve(json.media); }
                else { reject((json && json.error) || ('შეცდომა (' + xhr.status + ')')); }
            };
            xhr.onerror = function () { reject('კავშირის შეცდომა'); };
            xhr.send(data);
        });
    }

    /** ზონაში ჩაგდებული/არჩეული ფაილები რიგრიგობით; ბოლოს onDone(ატვირთულები) */
    function uploadAll($zone, files, onDone) {
        var $list = $zone.find('[data-upload-list]');
        var folder = $zone.find('[data-upload-folder]').val();
        var uploaded = [];
        var queue = Array.prototype.slice.call(files);

        function next() {
            if (queue.length === 0) { onDone(uploaded); return; }
            var file = queue.shift();
            var $item = $('<li class="upload-item"><span class="upload-name"></span> <span class="upload-status text-muted">0%</span></li>');
            $item.find('.upload-name').text(file.name);
            $list.append($item);
            upload(file, folder, function (percent) {
                $item.find('.upload-status').text(percent + '%');
            }).then(function (media) {
                uploaded.push(media);
                $item.find('.upload-status').removeClass('text-muted').addClass('text-success').text('✓');
            }).catch(function (error) {
                $item.find('.upload-status').removeClass('text-muted').addClass('text-danger').text(error);
            }).then(next);
        }
        next();
    }

    function bindZone($zone, onDone) {
        var $input = $zone.find('[data-upload-input]');
        $input.on('change', function () {
            if (this.files.length) { uploadAll($zone, this.files, onDone); }
            this.value = '';
        });
        $zone.on('dragover dragenter', function (e) {
            e.preventDefault();
            $zone.addClass('is-dragover');
        }).on('dragleave dragend drop', function (e) {
            e.preventDefault();
            $zone.removeClass('is-dragover');
        }).on('drop', function (e) {
            var files = e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files;
            if (files && files.length) { uploadAll($zone, files, onDone); }
        });
    }

    // ბიბლიოთეკის გვერდი: ატვირთვის შემდეგ სიის განახლება (თუ რამე აიტვირთა)
    $('#library-upload').each(function () {
        bindZone($(this), function (uploaded) {
            if (uploaded.length) { setTimeout(function () { window.location.reload(); }, 600); }
        });
    });

    // ---- ასარჩევი ფანჯარა ----

    var $modal = $('#media-picker');
    if (!$modal.length) { return; }

    var modal = new bootstrap.Modal($modal[0]);
    var state = {page: 1, pages: 1, q: '', type: 'image', $target: null};
    var $grid = $modal.find('[data-picker-grid]');
    var searchTimer = null;

    function card(media) {
        var $card = $('<button type="button" class="media-card"></button>');
        var $thumb = $('<span class="media-card-thumb"></span>');
        if (media.image) { $thumb.append($('<img loading="lazy" alt="">').attr('src', media.thumb)); }
        else { $thumb.append('<i class="fa fa-file-pdf fa-3x text-danger"></i>'); }
        $card.append($thumb)
            .append($('<span class="media-card-name"></span>').text(media.name))
            .append($('<span class="media-card-meta"></span>').text((media.width ? media.width + '×' + media.height + ' · ' : '') + media.size));
        $card.on('click', function () { select(media); });
        return $card;
    }

    function load() {
        var params = $.param({page: state.page, q: state.q, type: state.type});
        $.getJSON(config.mediaPicker + '?' + params).done(function (json) {
            state.pages = json.pages;
            $grid.empty().append(json.items.map(card));
            $modal.find('[data-picker-empty]').toggleClass('d-none', json.items.length > 0);
            $modal.find('[data-picker-page]').text(json.page + ' / ' + json.pages);
            $modal.find('[data-picker-prev]').prop('disabled', json.page <= 1);
            $modal.find('[data-picker-next]').prop('disabled', json.page >= json.pages);
        });
    }

    function render($input, media) {
        var $preview = $input.find('[data-media-preview]').empty();
        if (!media) {
            $input.find('[data-media-value]').val('');
            $preview.append('<i class="fa fa-image fa-2x text-muted"></i>');
            $input.find('[data-media-name]').html('<span class="text-muted">არ არის არჩეული</span>');
            $input.find('[data-media-clear]').addClass('d-none');
            return;
        }
        $input.find('[data-media-value]').val(media.id);
        if (media.image) { $preview.append($('<img alt="">').attr('src', media.thumb)); }
        else { $preview.append('<i class="fa fa-file-pdf fa-2x text-danger"></i>'); }
        $input.find('[data-media-name]').text(media.name);
        $input.find('[data-media-clear]').removeClass('d-none');
    }

    function select(media) {
        if (state.$target) { render(state.$target, media); }
        modal.hide();
    }

    $(document).on('click', '[data-media-choose]', function () {
        state.$target = $(this).closest('[data-media-input]');
        state.type = state.$target.data('type') || '';
        state.page = 1;
        load();
        modal.show();
    });
    $(document).on('click', '[data-media-clear]', function () {
        render($(this).closest('[data-media-input]'), null);
    });

    $modal.find('[data-picker-search]').on('input', function () {
        var value = this.value;
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { state.q = value; state.page = 1; load(); }, 300);
    });
    $modal.find('[data-picker-prev]').on('click', function () { if (state.page > 1) { state.page--; load(); } });
    $modal.find('[data-picker-next]').on('click', function () { if (state.page < state.pages) { state.page++; load(); } });

    // ფანჯარაში ატვირთვა: ერთი ფაილი → მაშინვე არჩეულია; რამდენიმე → სია განახლდება
    bindZone($modal.find('#picker-upload'), function (uploaded) {
        if (uploaded.length === 1) { select(uploaded[0]); }
        else if (uploaded.length > 1) { state.page = 1; load(); }
    });
})(jQuery);
