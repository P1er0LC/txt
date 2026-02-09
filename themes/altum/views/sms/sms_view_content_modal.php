<?php defined('ALTUMCODE') || die() ?>

<div class="modal fade" id="sms_view_content_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="modal-title">
                        <i class="fas fa-fw fa-sm fa-comment text-dark mr-2"></i>
                        <?= l('sms.sms') ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" title="<?= l('global.close') ?>">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

               <p class="text-muted" id="sms_view_content"></p>
            </div>

        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
    'use strict';

    /* On modal show */
    $('#sms_view_content_modal').on('show.bs.modal', event => {
        let content = $(event.relatedTarget).data('content');

        document.querySelector('#sms_view_content').innerHTML = content.replace(/\n/g, '<br>');
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
