<?php // მედიის ასარჩევი ფანჯარა — ერთი ყველა გვერდზე; იხსნება [data-media-choose]-ით (media.js) ?>
<div class="modal fade" id="media-picker" tabindex="-1" aria-labelledby="media-picker-title" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="media-picker-title">მედიის არჩევა</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="დახურვა"></button>
            </div>
            <div class="modal-body">
                <?= $this->insert('admin/partials/upload-zone', ['zoneId' => 'picker-upload', 'folders' => app(App\Models\Media::class)->folders(), 'accept' => '']) ?>
                <div class="d-flex gap-2 my-3">
                    <input type="search" class="form-control form-control-sm" placeholder="ძებნა" data-picker-search aria-label="ძებნა">
                </div>
                <div class="media-grid" data-picker-grid></div>
                <p class="text-muted small mb-0 d-none" data-picker-empty>ფაილები ვერ მოიძებნა.</p>
            </div>
            <div class="modal-footer justify-content-between">
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary" data-picker-prev>‹</button>
                    <span class="btn btn-outline-secondary disabled" data-picker-page>1 / 1</span>
                    <button type="button" class="btn btn-outline-secondary" data-picker-next>›</button>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">დახურვა</button>
            </div>
        </div>
    </div>
</div>
