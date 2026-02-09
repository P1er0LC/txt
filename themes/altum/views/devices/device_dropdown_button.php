<?php defined('ALTUMCODE') || die() ?>

<div class="dropdown">
    <button type="button" class="btn btn-link <?= $data->button_text_class ?? 'text-secondary' ?> dropdown-toggle dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport">
        <i class="fas fa-fw fa-ellipsis-v"></i>
    </button>

    <div class="dropdown-menu dropdown-menu-right">
        <a class="dropdown-item" href="<?= url('device/' . $data->id) ?>"><i class="fas fa-fw fa-sm fa-eye mr-2"></i> <?= l('global.view') ?></a>
        <a class="dropdown-item" href="<?= url('device-update/' . $data->id) ?>"><i class="fas fa-fw fa-sm fa-pencil-alt mr-2"></i> <?= l('global.edit') ?></a>

        <a
                href="#"
                data-toggle="modal"
                data-target="#device_connect_modal"
                data-device-code="<?= $data->device->device_code ?? $data->id ?? '' ?>"
                data-device-id="<?= $data->id ?>"
                data-resource-name="<?= $data->resource_name ?>"
                class="dropdown-item"
        ><i class="fas fa-fw fa-sm fa-plug mr-2"></i> <?= l('devices.connect') ?></a>

        <a href="<?= url('sms-create?device_id=' . $data->id) ?>" class="dropdown-item <?= $data->is_connected ? null : 'disabled' ?>"><i class="fas fa-fw fa-sm fa-comment mr-2"></i> <?= l('sms.send') ?></a>

        <a href="#" data-toggle="modal" data-target="#device_delete_modal" data-device-id="<?= $data->id ?>" data-resource-name="<?= $data->resource_name ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-trash-alt mr-2"></i> <?= l('global.delete') ?></a>
    </div>
</div>

<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
    'name' => 'device',
    'resource_id' => 'device_id',
    'has_dynamic_resource_name' => true,
    'path' => 'devices/delete'
]), 'modals', 'device_delete_modal'); ?>

<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/devices/device_connect_modal.php'), 'modals', 'device_connect_modal'); ?>
