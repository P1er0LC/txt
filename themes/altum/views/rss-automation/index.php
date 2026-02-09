<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('rss_automations') ?>"><?= l('rss_automations.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('rss_automation.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <div class="card d-flex flex-row mb-4">
        <div class="pl-3 d-flex flex-column justify-content-center">
            <?php if($data->rss_automation->is_enabled): ?>
                <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-light" data-toggle="tooltip" title="<?= l('global.active') ?>">
                    <i class="fas fa-fw fa-sm fa-check text-success"></i>
                </div>
            <?php else: ?>
                <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-light" data-toggle="tooltip" title="<?= l('global.disabled') ?>">
                    <i class="fas fa-fw fa-sm fa-pause text-warning"></i>
                </div>
            <?php endif ?>
        </div>

        <div class="card-body text-truncate d-flex justify-content-between align-items-center">
            <div class="text-truncate">
                <h1 class="h4 text-truncate mb-0"><?= sprintf(l('rss_automation.header'), $data->rss_automation->name) ?></h1>
            </div>

            <?= include_view(THEME_PATH . 'views/rss-automations/rss_automation_dropdown_button.php', ['id' => $data->rss_automation->rss_automation_id, 'resource_name' => $data->rss_automation->name,]) ?>
        </div>
    </div>

    <div class="my-4">
        <div class="row">
            <div class="col-12 col-md-6 col-xl-3 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" data-html="true" title="<?= l('rss_automations.last_check_datetime') . ($data->rss_automation->last_check_datetime ? '<br />' . \Altum\Date::get($data->rss_automation->last_check_datetime, 2) . '<br /><small>' . \Altum\Date::get($data->rss_automation->last_check_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($data->rss_automation->last_check_datetime) . ')</small>' : '<br />' . l('global.na')) ?>">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                            <i class="fas fa-fw fa-sm fa-calendar-check text-muted"></i>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= $data->rss_automation->last_check_datetime ? \Altum\Date::get_timeago($data->rss_automation->last_check_datetime) : l('global.na') ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" data-html="true" title="<?= l('rss_automations.next_check_datetime') . ($data->rss_automation->next_check_datetime ? '<br />' . \Altum\Date::get($data->rss_automation->next_check_datetime, 2) . '<br /><small>' . \Altum\Date::get($data->rss_automation->next_check_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($data->rss_automation->next_check_datetime) . ')</small>' : '<br />' . l('global.na')) ?>">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                            <i class="fas fa-fw fa-sm fa-calendar-day text-muted"></i>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= $data->rss_automation->next_check_datetime ? \Altum\Date::get_timeago($data->rss_automation->next_check_datetime) : l('global.na') ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($data->rss_automation->datetime, 2) . '<br /><small>' . \Altum\Date::get($data->rss_automation->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($data->rss_automation->datetime) . ')</small>') ?>">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                            <i class="fas fa-fw fa-sm fa-clock text-gray-900"></i>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= $data->rss_automation->datetime ? \Altum\Date::get_timeago($data->rss_automation->datetime) : l('global.na') ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($data->rss_automation->last_datetime ? '<br />' . \Altum\Date::get($data->rss_automation->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($data->rss_automation->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($data->rss_automation->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                            <i class="fas fa-fw fa-sm fa-clock-rotate-left text-gray-900"></i>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= $data->rss_automation->last_datetime ? \Altum\Date::get_timeago($data->rss_automation->last_datetime) : l('global.na') ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-sm-6 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden position-relative" data-toggle="tooltip" title="<?= l('contacts.total_sent_sms') ?>">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                            <a href="<?= url('sms?status=sent&rss_automation_id=' . $data->rss_automation->rss_automation_id) ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-paper-plane text-gray-900"></i>
                            </a>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= nr($data->rss_automation->total_sent_sms) ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" title="<?= l('campaigns.segment') ?>">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                            <i class="fas fa-fw fa-sm fa-layer-group text-gray-900"></i>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?php if(is_numeric($data->rss_automation->segment)): ?>
                            <a href="<?= url('segment-update/' . $data->rss_automation->segment) ?>">
                                <?= l('campaigns.segment.saved') ?>
                            </a>
                        <?php else: ?>
                            <?= l('campaigns.segment.' . $data->rss_automation->segment) ?>
                        <?php endif ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-sm-6 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden position-relative" data-toggle="tooltip" title="<?= l('contacts.total_pending_sms') ?>">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                            <a href="<?= url('sms?status=pending&rss_automation_id=' . $data->rss_automation->contact_id) ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-spinner <?= $data->rss_automation->total_pending_sms ? 'fa-spin' : null ?> text-gray-900"></i>
                            </a>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= nr($data->rss_automation->total_pending_sms) ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden position-relative" data-toggle="tooltip" title="<?= l('contacts.total_failed_sms') ?>">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                            <a href="<?= url('sms?status=failed&rss_automation_id=' . $data->rss_automation->contact_id) ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-circle-exclamation text-gray-900"></i>
                            </a>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= nr($data->rss_automation->total_failed_sms) ?>
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
                <a href="<?= url('sms?rss_automation_id=' . $data->rss_automation->rss_automation_id) ?>" class="btn btn-sm btn-light" data-toggle="tooltip" title="<?= l('global.view_all') ?>"><i class="fas fa-fw fa-comment fa-sm"></i></a>
            </div>
        </div>

        <?php if (!empty($data->sms)): ?>
            <div class="table-responsive table-custom-container">
                <table class="table table-custom">
                    <thead>
                    <tr>
                        <th><?= l('contacts.contact') ?></th>
                        <th><?= l('global.type') ?></th>
                        <th></th>
                        <th><?= l('global.details') ?></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php foreach($data->sms as $row): ?>
                        <?php $device = $data->devices[$row->device_id]; ?>

                        <tr>
                            <td class="text-nowrap">
                                <div class="d-flex flex-column">
                                    <div>
                                        <?php if($row->country_code): ?>
                                            <img src="<?= ASSETS_FULL_URL . 'images/countries/' . mb_strtolower($row->country_code) . '.svg' ?>" class="icon-favicon-small mr-1" data-toggle="tooltip" title="<?= get_country_from_country_code($row->country_code) ?>" />
                                        <?php else: ?>
                                            <span class="mr-1" data-toggle="tooltip" title="<?= l('global.unknown') ?>">
                                                <i class="fas fa-fw fa-xs fa-flag text-muted"></i>
                                            </span>
                                        <?php endif ?>

                                        <a href="<?= url('contact-view/' . $row->contact_id) ?>">
                                            <?php if($row->has_opted_out): ?>
                                                <s><?= $row->phone_number ?></s>
                                            <?php else: ?>
                                                <?= $row->phone_number ?>
                                            <?php endif ?>
                                        </a>
                                    </div>
                                    <span class="text-muted small">
                                        <?= $row->name ?>
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
                                    <?= include_view(THEME_PATH . 'views/sms/sms_dropdown_button.php', ['id' => $row->sms_id, 'resource_name' => $row->phone_number, 'has_opted_out' => $row->has_opted_out]) ?>
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

    <div class="mt-4 mb-5">
        <div class="d-flex align-items-center mb-3">
            <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-feed mr-1"></i> <?= l('rss_automations.rss_automation') ?></h2>

            <div class="flex-fill">
                <hr class="border-gray-100" />
            </div>
        </div>

        <div class="table-responsive table-custom-container">
            <table class="table table-custom">
                <tbody>
                <tr>
                    <td class="font-weight-bold text-truncate text-muted">
                        <i class="fas fa-fw fa-feed fa-sm text-muted mr-1"></i>
                        <?= l('rss_automations.rss_url') ?>
                    </td>
                    <td class="text-truncate">
                        <?= $data->rss_automation->rss_url ?>
                        <a href="<?= $data->rss_automation->rss_url ?>" target="_blank" rel="nofollow noreferrer">
                            <i class="fas fa-fw fa-xs fa-external-link text-muted ml-1"></i>
                        </a>
                    </td>
                </tr>

                <tr>
                    <td class="font-weight-bold text-truncate text-muted">
                        <i class="fas fa-fw fa-heading fa-sm text-muted mr-1"></i>
                        <?= l('sms.content') ?>
                    </td>
                    <td class="text-truncate text-wrap">
                        <?= $data->rss_automation->content ?>
                    </td>
                </tr>

                <?php $device = $data->devices[$data->rss_automation->device_id]; ?>
                <tr>
                    <td class="font-weight-bold text-truncate text-muted">
                        <i class="fas fa-fw fa-mobile fa-sm text-muted mr-1"></i>
                        <?= l('devices.device') ?>
                    </td>
                    <td class="text-truncate text-wrap">
                        <div><a href="<?= url('device/' . $device->device_id) ?>"><?= $device->name ?></a></div>
                        <div><?= $device->device_brand . ' ' . $device->device_model ?></div>
                    </td>
                </tr>

                <?php $sim = array_values(array_filter($device->sims, fn($sim) => $sim->subscription_id == $data->rss_automation->sim_subscription_id))[0] ?? null; ?>
                <tr>
                    <td class="font-weight-bold text-truncate text-muted">
                        <i class="fas fa-fw fa-sim-card fa-sm text-muted mr-1"></i>
                        <?= l('devices.sim_subscription_id') ?>
                    </td>
                    <td class="text-truncate text-wrap">
                        <?php if($sim): ?>
                            <?= $sim->display_name ?>
                            (<?= $sim->carrier_name ?>)
                        <?php else: ?>
                            <?= l('global.unknown') ?>
                        <?php endif ?>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

