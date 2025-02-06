<div id="{{ $id }}" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog {{ $sizeClass ?? '' }}" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Sluiten"></button>
            </div>
            <div class="modal-body">
                {{ $body }}
            </div>
            <div class="modal-footer">
                {{ $footer ?? '' }}
            </div>
        </div>
    </div>
</div>
