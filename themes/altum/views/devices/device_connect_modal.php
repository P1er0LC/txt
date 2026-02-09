<?php defined('ALTUMCODE') || die() ?>

<?php
//ALTUMCODE:DEMO if(DEMO) if(user()->user_id == 1) user()->api_key = 'hidden on demo';
?>

<div class="modal fade" id="device_connect_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="modal-title">
                        <i class="fas fa-fw fa-sm fa-plug text-dark mr-2"></i>
                        <?= l('device_connect_modal.header') ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" title="<?= l('global.close') ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <h3 class="h6 mt-5"><?= l('device_connect_modal.first.header') ?></h3>
                <p class="text-muted"><?= l('device_connect_modal.first.subheader') ?></p>

                <div class="mt-4">
                    <a href="<?= \Altum\Uploads::get_full_url('apk') . settings()->sms->apk ?>" target="_blank" class="btn btn-lg btn-block btn-primary" download="<?= get_slug(settings()->sms->app_name) . '.apk' ?>"><?= l('global.download') ?></a>
                </div>

                <h3 class="h6 mt-5"><?= l('device_connect_modal.second.header') ?></h3>
                <p class="text-muted mb-3"><?= l('device_connect_modal.second.subheader') ?></p>

                <div class="form-group">
                    <label for="device_connect_api_key"><?= l('device_connect_modal.api_key') ?></label>
                    <input id="device_connect_api_key" type="text" name="device_connect_api_key" class="form-control" value="<?= user()->api_key ?>" readonly="readonly" onclick="this.select();" />
                </div>

                <div class="form-group">
                    <label for="device_connect_site_url"><?= l('device_connect_modal.site_url') ?></label>
                    <input id="device_connect_site_url" type="text" name="device_connect_site_url" class="form-control" value="<?= SITE_URL ?>" readonly="readonly" onclick="this.select();" />
                </div>

                <div class="form-group">
                    <label for="device_connect_device_id"><?= l('device_connect_modal.device_id') ?></label>
                    <input id="device_connect_device_id" type="text" name="device_connect_device_id" class="form-control font-weight-bold text-center" style="font-size: 1.5rem; letter-spacing: 0.2rem;" value="" readonly="readonly" onclick="this.select();" />
                </div>

                <p class="text-muted mb-0"><?= l('device_connect_modal.second.finish') ?></p>

                <div class="mt-4">
                    <a href="<?= url('campaign-create') ?>" class="btn btn-lg btn-block btn-primary"><?= l('sms.send') ?></a>
                </div>
            </div>

        </div>
    </div>
</div>

<?php ob_start() ?>
<script src="<?= ASSETS_FULL_URL ?>js/libraries/clipboard.min.js?v=<?= PRODUCT_CODE ?>"></script>

<script>
    'use strict';

    /* On modal show */
    $('#device_connect_modal').on('show.bs.modal', event => {
        let device_code = $(event.relatedTarget).data('device-code');

        document.querySelector('#device_connect_device_id').value = device_code;
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
