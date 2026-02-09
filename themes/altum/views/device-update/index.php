<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('devices') ?>"><?= l('devices.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li>
                    <a href="<?= url('device/' . $data->device->device_id) ?>"><?= l('device.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('device_update.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <div class="d-flex justify-content-between mb-4">
        <h1 class="h4 text-truncate mb-0"><i class="fas fa-fw fa-xs fa-address-book mr-1"></i> <?= l('device_update.header') ?></h1>

        <?= include_view(THEME_PATH . 'views/devices/device_dropdown_button.php', ['id' => $data->device->device_id, 'resource_name' => $data->device->name, 'is_connected' => $data->device->last_ping_datetime]) ?>
    </div>

    <div class="card">
        <div class="card-body">

            <form id="form" action="" method="post" role="form" enctype="multipart/form-data">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="form-group">
                    <label for="name"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('global.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control <?= \Altum\Alerts::has_field_errors('name') ? 'is-invalid' : null ?>" value="<?= $data->device->name ?>" />
                    <?= \Altum\Alerts::output_field_error('name') ?>
                </div>

                <button class="btn btn-sm btn-block btn-light my-3" type="button" data-toggle="collapse" data-target="#notifications_container" aria-expanded="false" aria-controls="notifications_container">
                    <i class="fas fa-fw fa-bell fa-sm mr-1"></i> <?= l('devices.notifications_container') ?>
                </button>

                <div class="collapse" id="notifications_container">
                    <div class="form-group">
                        <div class="d-flex flex-wrap flex-row justify-content-between">
                            <label><i class="fas fa-fw fa-sm fa-bell text-muted mr-1"></i> <?= l('devices.notifications') ?></label>
                            <a href="<?= url('notification-handler-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('notification_handlers.create') ?></a>
                        </div>
                        <div class="mb-2"><small class="text-muted"><?= l('devices.notifications_help') ?></small></div>

                        <div class="row">
                            <?php foreach($data->notification_handlers as $notification_handler): ?>
                                <div class="col-12 col-lg-6">
                                    <div class="custom-control custom-checkbox my-2">
                                        <input id="notifications_<?= $notification_handler->notification_handler_id ?>" name="notifications[]" value="<?= $notification_handler->notification_handler_id ?>" type="checkbox" class="custom-control-input" <?= in_array($notification_handler->notification_handler_id, $data->device->notifications ?? []) ? 'checked="checked"' : null ?>>
                                        <label class="custom-control-label" for="notifications_<?= $notification_handler->notification_handler_id ?>">
                                            <span class="mr-1"><?= $notification_handler->name ?></span>
                                            <small class="badge badge-light badge-pill"><?= l('notification_handlers.type_' . $notification_handler->type) ?></small>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="d-flex flex-wrap flex-row justify-content-between">
                            <label><i class="fas fa-fw fa-sm fa-bell text-muted mr-1"></i> <?= l('devices.sms_status_notifications') ?></label>
                            <a href="<?= url('notification-handler-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('notification_handlers.create') ?></a>
                        </div>
                        <div class="mb-2"><small class="text-muted"><?= l('devices.sms_status_notifications_help') ?></small></div>

                        <div class="row">
                            <?php foreach($data->notification_handlers as $notification_handler): ?>
                                <?php if($notification_handler->type != 'webhook') continue ?>

                                <div class="col-12 col-lg-6">
                                    <div class="custom-control custom-checkbox my-2">
                                        <input id="sms_status_notifications_<?= $notification_handler->notification_handler_id ?>" name="sms_status_notifications[]" value="<?= $notification_handler->notification_handler_id ?>" type="checkbox" class="custom-control-input" <?= in_array($notification_handler->notification_handler_id, $data->device->sms_status_notifications ?? []) ? 'checked="checked"' : null ?>>
                                        <label class="custom-control-label" for="sms_status_notifications_<?= $notification_handler->notification_handler_id ?>">
                                            <span class="mr-1"><?= $notification_handler->name ?></span>
                                            <small class="badge badge-light badge-pill"><?= l('notification_handlers.type_' . $notification_handler->type) ?></small>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach ?>
                        </div>
                    </div>
                </div>

                <button class="btn btn-sm btn-block btn-light my-3" type="button" data-toggle="collapse" data-target="#advanced_container" aria-expanded="false" aria-controls="advanced_container">
                    <i class="fas fa-fw fa-user-tie fa-sm mr-1"></i> <?= l('devices.advanced') ?>
                </button>

                <div class="collapse" id="advanced_container">
                    <div class="form-group">
                        <label for="sms_in_between_delay"><i class="fas fa-fw fa-sm fa-stopwatch text-muted mr-1"></i> <?= l('devices.sms_in_between_delay') ?></label>
                        <div class="input-group">
                        <input type="number" step="1" min="1" max="300" id="sms_in_between_delay" name="sms_in_between_delay" class="form-control <?= \Altum\Alerts::has_field_errors('sms_in_between_delay') ? 'is-invalid' : null ?>" value="<?= $data->device->settings->sms_in_between_delay ?>" />
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <?= l('global.date.seconds') ?>
                                </span>
                            </div>
                        </div>
                        <?= \Altum\Alerts::output_field_error('sms_in_between_delay') ?>
                        <small class="form-text text-muted"><?= l('devices.sms_in_between_delay_help') ?></small>
                    </div>
                </div>

                <button type="submit" name="submit" class="btn btn-block btn-primary"><?= l('global.update') ?></button>
            </form>

        </div>
    </div>
</div>


<?php ob_start() ?>
<script>
    'use strict';
    
let active_notification_handlers_per_resource_limit = <?= (int) $this->user->plan_settings->active_notification_handlers_per_resource_limit ?>;

    if(active_notification_handlers_per_resource_limit != -1) {
        let process_notification_handlers = () => {
            let notifications_selected = document.querySelectorAll('[name="notifications[]"]:checked').length;
            let sms_status_notifications_selected = document.querySelectorAll('[name="sms_status_notifications[]"]:checked').length;

            if(notifications_selected >= active_notification_handlers_per_resource_limit) {
                document.querySelectorAll('[name="notifications[]"]:not(:checked)').forEach(element => element.setAttribute('disabled', 'disabled'));
            } else {
                document.querySelectorAll('[name="notifications[]"]:not(:checked)').forEach(element => element.removeAttribute('disabled'));
            }

            if(sms_status_notifications_selected >= active_notification_handlers_per_resource_limit) {
                document.querySelectorAll('[name="sms_status_notifications[]"]:not(:checked)').forEach(element => element.setAttribute('disabled', 'disabled'));
            } else {
                document.querySelectorAll('[name="sms_status_notifications[]"]:not(:checked)').forEach(element => element.removeAttribute('disabled'));
            }
        }

        document.querySelectorAll('[name="notifications[]"]').forEach(element => element.addEventListener('change', process_notification_handlers));
        document.querySelectorAll('[name="sms_status_notifications[]"]').forEach(element => element.addEventListener('change', process_notification_handlers));

        process_notification_handlers();
    }
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
