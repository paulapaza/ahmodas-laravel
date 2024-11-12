<div class="modal fade" id="{{ $id ?? 'modal' }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog {{ $size ?? '' }}">
        <div class="modal-content">
            <form id="form">
                <div class="modal-header bg-xprimary py-3 text-white">
                    <h5 class="modal-title">{{ $modaltitle ?? ''}}</h5>

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                </div>
                <div class="modal-body">
                    {{ $slot }}

                </div>
                <div class="content-errors d-none alert alert-danger mx-3">
                    <div class="message_errors col-12 mx-auto mb-2"></div>
                    <div class=" col-12 mx-auto">
                        <ul class="validate_errors">

                        </ul>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn bg-xsuccess  btn-store text-xaccent text-bold" disabled>Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
