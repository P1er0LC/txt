<?php defined('ALTUMCODE') || die() ?>

<div class="dropdown">
    <button type="button" class="btn btn-link <?= $data->button_text_class ?? 'text-secondary' ?> dropdown-toggle dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport">
        <i class="fas fa-fw fa-ellipsis-v"></i>
    </button>

    <div class="dropdown-menu dropdown-menu-right">
        <a class="dropdown-item" href="<?= url('contact-view/' . $data->id) ?>"><i class="fas fa-fw fa-sm fa-eye mr-2"></i> <?= l('global.view') ?></a>
        <a class="dropdown-item" href="<?= url('contact-update/' . $data->id) ?>"><i class="fas fa-fw fa-sm fa-pencil-alt mr-2"></i> <?= l('global.edit') ?></a>
        <a class="dropdown-item <?= $data->has_opted_out ? 'disabled' : null ?>" href="<?= url('sms-create?contact_id=' . $data->id) ?>"><i class="fas fa-fw fa-sm fa-comment mr-2"></i> <?= l('sms.send') ?></a>
        <a href="#" data-toggle="modal" data-target="#contact_delete_modal" data-contact-id="<?= $data->id ?>" data-resource-name="<?= $data->resource_name ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-trash-alt mr-2"></i> <?= l('global.delete') ?></a>
    </div>
</div>

<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/partials/universal_delete_modal_form.php', [
    'name' => 'contact',
    'resource_id' => 'contact_id',
    'has_dynamic_resource_name' => true,
    'path' => 'contacts/delete'
]), 'modals', 'contact_delete_modal'); ?>

