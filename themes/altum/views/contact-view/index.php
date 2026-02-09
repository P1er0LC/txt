<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('contacts') ?>"><?= l('contacts.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('contact_view.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <div class="card d-flex flex-row mb-4">
        <div class="pl-3 d-flex flex-column justify-content-center">
            <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                <i class="fas fa-fw fa-sm fa-user-check"></i>
            </div>
        </div>

        <div class="card-body text-truncate d-flex justify-content-between align-items-center">
            <div class="text-truncate">
                <h1 class="h4 text-truncate mb-0"><?= sprintf(l('contact_view.header'), $data->contact->has_opted_out ? '<s>' . $data->contact->phone_number . '</s>' : $data->contact->phone_number) ?></h1>

                <p class="small text-muted mb-0">
                    <?php if($data->contact->country_code): ?>
                        <img src="<?= ASSETS_FULL_URL . 'images/countries/' . mb_strtolower($data->contact->country_code) . '.svg' ?>" class="icon-favicon-small mr-1" data-toggle="tooltip" title="<?= get_country_from_country_code($data->contact->country_code) ?>" />
                    <?php else: ?>
                        <span class="mr-1" data-toggle="tooltip" title="<?= l('global.unknown') ?>">
                            <i class="fas fa-fw fa-xs fa-flag"></i>
                        </span>
                    <?php endif ?>

                    <?= $data->contact->name ?: l('contacts.unknown_name') ?>
                </p>
            </div>

            <?= include_view(THEME_PATH . 'views/contacts/contact_dropdown_button.php', ['id' => $data->contact->contact_id, 'resource_name' => $data->contact->phone_number, 'has_opted_out' => $data->contact->has_opted_out]) ?>
        </div>
    </div>

    <div class="my-4">
        <div class="row">
            <div class="col-12 col-sm-6 col-xl-3 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" data-html="true" title="<?= l('contacts.last_sent_datetime') . ($data->contact->last_sent_datetime ? '<br />' . \Altum\Date::get($data->contact->last_sent_datetime, 2) . '<br /><small>' . \Altum\Date::get($data->contact->last_sent_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($data->contact->last_sent_datetime) . ')</small>' : '<br />' . l('global.na')) ?>">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                            <i class="fas fa-fw fa-sm fa-arrow-up"></i>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= $data->contact->last_sent_datetime ? \Altum\Date::get_timeago($data->contact->last_sent_datetime) : l('global.na') ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" data-html="true" title="<?= l('contacts.last_received_datetime') . ($data->contact->last_received_datetime ? '<br />' . \Altum\Date::get($data->contact->last_received_datetime, 2) . '<br /><small>' . \Altum\Date::get($data->contact->last_received_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($data->contact->last_received_datetime) . ')</small>' : '<br />' . l('global.na')) ?>">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                            <i class="fas fa-fw fa-sm fa-arrow-down"></i>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= $data->contact->last_received_datetime ? \Altum\Date::get_timeago($data->contact->last_received_datetime) : l('global.na') ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($data->contact->datetime, 2) . '<br /><small>' . \Altum\Date::get($data->contact->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($data->contact->datetime) . ')</small>') ?>">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                            <i class="fas fa-fw fa-sm fa-clock"></i>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= $data->contact->datetime ? \Altum\Date::get_timeago($data->contact->datetime) : l('global.na') ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($data->contact->last_datetime ? '<br />' . \Altum\Date::get($data->contact->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($data->contact->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($data->contact->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                            <i class="fas fa-fw fa-sm fa-clock-rotate-left"></i>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= $data->contact->last_datetime ? \Altum\Date::get_timeago($data->contact->last_datetime) : l('global.na') ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-sm-6 col-xl-4 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" title="<?= l('global.continent') ?>">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                            <i class="fas fa-fw fa-sm fa-globe-europe"></i>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= $data->contact->continent_code ? get_continent_from_continent_code($data->contact->continent_code) : l('global.unknown') ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" title="<?= l('global.country') ?>">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                            <i class="fas fa-fw fa-sm fa-flag"></i>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?php if($data->contact->country_code): ?>
                            <img src="<?= ASSETS_FULL_URL . 'images/countries/' . mb_strtolower($data->contact->country_code) . '.svg' ?>" class="img-fluid icon-favicon-small mr-2" />
                        <?php endif ?>
                        <?= $data->contact->country_code ? get_country_from_country_code($data->contact->country_code) : l('global.unknown') ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" title="<?= l('contacts.has_opted_out') ?>">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                            <?php if($data->contact->has_opted_out): ?>
                                <i class="fas fa-fw fa-sm fa-user-slash"></i>
                            <?php else: ?>
                                <i class="fas fa-fw fa-sm fa-user-check"></i>
                            <?php endif ?>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= $data->contact->has_opted_out ? ucfirst(l('global.yes')) : ucfirst(l('global.no')) ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden position-relative" data-toggle="tooltip" title="<?= l('contacts.total_sent_sms') ?>">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                            <a href="<?= url('sms?status=sent&contact_id=' . $data->contact->contact_id) ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-paper-plane text-gray-900"></i>
                            </a>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= nr($data->contact->total_sent_sms) ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" title="<?= l('contacts.custom_parameters') ?>">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-primary-100">
                            <i class="fas fa-fw fa-sm fa-fingerprint text-primary"></i>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= nr(count($data->contact->custom_parameters)) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-sm-4 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden position-relative" data-toggle="tooltip" title="<?= l('contacts.total_pending_sms') ?>">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                            <a href="<?= url('sms?status=pending&contact_id=' . $data->contact->contact_id) ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-spinner <?= $data->contact->total_pending_sms ? 'fa-spin' : null ?> text-gray-900"></i>
                            </a>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= nr($data->contact->total_pending_sms) ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-4 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden position-relative" data-toggle="tooltip" title="<?= l('contacts.total_failed_sms') ?>">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                            <a href="<?= url('sms?status=failed&contact_id=' . $data->contact->contact_id) ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-circle-exclamation text-gray-900"></i>
                            </a>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= nr($data->contact->total_failed_sms) ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-4 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden position-relative" data-toggle="tooltip" title="<?= l('contacts.total_received_sms') ?>">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                            <a href="<?= url('sms?status=received&contact_id=' . $data->contact->contact_id) ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-inbox text-gray-900"></i>
                            </a>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= nr($data->contact->total_received_sms) ?>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="mt-4 mb-5">
        <div class="d-flex align-items-center mb-3">
            <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-comment mr-1"></i> <?= l('sms.sms') ?></h2>

            <div class="flex-fill">
                <hr class="border-gray-100" />
            </div>

            <div class="ml-3">
                <a href="<?= url('sms-create?contact_id=' . $data->contact->contact_id) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('sms.send') ?></a>
                <a href="<?= url('sms?contact_id=' . $data->contact->contact_id) ?>" class="btn btn-sm btn-light" data-toggle="tooltip" title="<?= l('global.view_all') ?>"><i class="fas fa-fw fa-comment fa-sm"></i></a>
            </div>
        </div>

        <?php if (!empty($data->sms)): ?>
            <div class="table-responsive table-custom-container">
                <table class="table table-custom">
                    <thead>
                    <tr>
                        <th><?= l('sms.sms') ?></th>
                        <th><?= l('global.type') ?></th>
                        <th></th>
                        <th><?= l('devices.device') ?></th>
                        <th><?= l('global.details') ?></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php foreach($data->sms as $row): ?>
                        <?php $device = $data->devices[$row->device_id]; ?>

                        <tr>
                            <td class="text-nowrap">
                                <div class="text-wrap" style="width: 150px;">
                                    <span class="text-muted small cursor-pointer" data-toggle="modal" data-target="#sms_view_content_modal" data-tooltip title="<?= l('global.view') ?>" data-content="<?= e($row->content) ?>">
                                        <?= string_truncate(e($row->content), 50) ?>
                                    </span>
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <?php if($row->type == 'received'): ?>
                                    <span class="badge badge-light"><i class="fas fa-fw fa-sm fa-inbox mr-1"></i> <?= l('sms.' . $row->type) ?></span>

                                <?php elseif($row->type == 'sent'): ?>
                                    <span class="badge badge-primary"><i class="fas fa-fw fa-sm fa-paper-plane mr-1"></i> <?= l('sms.' . $row->type) ?></span>
                                <?php endif ?>
                            </td>

                            <td class="text-nowrap">
                                <?php if($row->type == 'received'): ?>
                                    <spann class="badge badge-pill badge-success" data-toggle="tooltip" title="<?= l('sms.received') ?>"><i class="fas fa-fw fa-sm fa-check-circle"></i></spann>

                                <?php elseif($row->type == 'sent'): ?>

                                    <?php if($row->status == 'sent'): ?>
                                        <span class="badge badge-pill badge-success" data-toggle="tooltip" title="<?= l('sms.sent') ?>"><i class="fas fa-fw fa-sm fa-check-circle"></i></span>
                                    <?php elseif($row->status == 'pending'): ?>
                                        <span class="badge badge-pill badge-warning" data-toggle="tooltip" title="<?= l('sms.pending') ?>"><i class="fas fa-fw fa-sm fa-spinner fa-spin"></i></span>
                                    <?php elseif($row->status == 'failed'): ?>
                                        <span class="badge badge-pill badge-danger" data-toggle="tooltip" data-html="true" title="<?= l('sms.failed') . '<br />' . $row->error ?>"><i class="fas fa-fw fa-sm fa-exclamation-circle"></i></span>
                                    <?php endif ?>

                                <?php endif ?>
                            </td>

                            <td class="text-nowrap">
                                <div class="d-flex flex-column">
                                    <div>
                                        <a href="<?= url('device/' . $device->device_id) ?>">
                                            <?= $device->name ?>
                                        </a>
                                    </div>

                                    <div class="small">
                                        <span class="font-weight-bold"><?= $device->device_brand . ' ' . $device->device_model ?></span>

                                        <?php foreach($device->sims as $sim): ?>
                                            <span class="text-muted"><?= $sim->phone_number ?></span>
                                            <span class="text-muted">(<?= $sim->display_name ?>)</span>
                                        <?php endforeach ?>
                                    </div>
                                </div>
                            </td>


                            <td class="text-nowrap">
                                <div class="d-flex align-items-center">
                                    <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('sms.scheduled_datetime') . ($row->scheduled_datetime ? '<br />' . \Altum\Date::get($row->scheduled_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->scheduled_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_time_until($row->scheduled_datetime) . ')</small>' : '<br />' . l('global.na')) ?>">
                                        <i class="fas fa-fw fa-calendar-day text-muted"></i>
                                    </span>

                                    <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>') ?>">
                                        <i class="fas fa-fw fa-clock text-muted"></i>
                                    </span>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex justify-content-end">
                                    <?= include_view(THEME_PATH . 'views/sms/sms_dropdown_button.php', ['id' => $row->sms_id, 'resource_name' => $data->contact->phone_number, 'has_opted_out' => $data->contact->has_opted_out]) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach ?>

                    </tbody>
                </table>
            </div>
        <?php else: ?>

            <?= include_view(THEME_PATH . 'views/partials/no_data.php', [
                'filters_get' => $data->filters->get ?? [],
                'name' => 'sms',
                'has_secondary_text' => true,
            ]); ?>

        <?php endif ?>
    </div>

    <?php if (!empty($data->contact->custom_parameters)): ?>
        <div class="mt-4 mb-5">
            <div class="d-flex align-items-center mb-3">
                <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-fingerprint mr-1"></i> <?= l('contacts.custom_parameters') ?></h2>

                <div class="flex-fill">
                    <hr class="border-gray-100" />
                </div>
            </div>

            <div class="table-responsive table-custom-container">
                <table class="table table-custom">
                    <tbody>

                    <?php foreach($data->contact->custom_parameters as $key => $value): ?>
                        <tr>
                            <td class="font-weight-bold text-truncate text-muted">
                                <?= e($key) ?>
                            </td>
                            <td class="text-truncate">
                                <?= e($value) ?>
                            </td>
                        </tr>
                    <?php endforeach ?>

                    </tbody>
                </table>
            </div>
        </div>
    <?php endif ?>

    <div class="mt-4 mb-5">
        <div class="d-flex align-items-center mb-3">
            <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-laptop-code mr-1"></i> <?= l('contact_view.advanced') ?></h2>

            <div class="flex-fill">
                <hr class="border-gray-100" />
            </div>
        </div>

        <div class="row">
            <div class="col-12 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                            <i class="fas fa-fw fa-sm fa-fingerprint text-muted"></i>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <div class="font-weight-bold text-muted small"><?= l('contacts.contact_id') ?></div>
                        <span><?= $data->contact->contact_id ?></span>
                    </div>

                    <div class="pr-3 d-flex flex-column justify-content-center">
                        <button
                                type="button"
                                class="btn btn-light p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center"
                                data-toggle="tooltip"
                                title="<?= l('global.clipboard_copy') ?>"
                                aria-label="<?= l('global.clipboard_copy') ?>"
                                data-copy="<?= l('global.clipboard_copy') ?>"
                                data-copied="<?= l('global.clipboard_copied') ?>"
                                data-clipboard-text="<?= $data->contact->contact_id ?>"
                        >
                            <i class="fas fa-fw fa-sm fa-copy text-muted"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include_view(THEME_PATH . 'views/partials/clipboard_js.php') ?>
<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/sms/sms_view_content_modal.php'), 'modals', 'sms_view_content_modal'); ?>
