<?php defined('ALTUMCODE') || die() ?>

<div class="modal fade" id="regenerate_api_key_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="modal-title">
                        <i class="fas fa-fw fa-sm fa-sync text-primary mr-2"></i>
                        <?= l('account_api.regenerate_modal.header') ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" title="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <p class="text-muted"><?= l('account_api.regenerate_modal.subheader') ?></p>

                <form method="post" action="" role="form">
                    <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />
                    <input type="hidden" name="submit" value="1" />

                    <div class="mt-4">
                        <button type="submit" name="submit" class="btn btn-block btn-primary"><?= l('account_api.regenerate_modal.regenerate') ?></button>
                        <button type="button" class="btn btn-block btn-light" data-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
