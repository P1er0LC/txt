<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('recurring_campaigns') ?>"><?= l('recurring_campaigns.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('recurring_campaign.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <div class="card d-flex flex-row mb-4">
        <div class="pl-3 d-flex flex-column justify-content-center">
            <?php if($data->recurring_campaign->is_enabled): ?>
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
                <h1 class="h4 text-truncate mb-0"><?= sprintf(l('recurring_campaign.header'), $data->recurring_campaign->name) ?></h1>
            </div>

            <?= include_view(THEME_PATH . 'views/recurring-campaigns/recurring_campaign_dropdown_button.php', ['id' => $data->recurring_campaign->recurring_campaign_id, 'resource_name' => $data->recurring_campaign->name,]) ?>
        </div>
    </div>

    <div class="my-4">
        <div class="row">
            <div class="col-12 col-md-6 col-xl-3 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" data-html="true" title="<?= l('recurring_campaigns.last_run_datetime') . ($data->recurring_campaign->last_run_datetime ? '<br />' . \Altum\Date::get($data->recurring_campaign->last_run_datetime, 2) . '<br /><small>' . \Altum\Date::get($data->recurring_campaign->last_run_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($data->recurring_campaign->last_run_datetime) . ')</small>' : '<br />' . l('global.na')) ?>">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                            <i class="fas fa-fw fa-sm fa-calendar-check text-gray-900"></i>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= $data->recurring_campaign->last_run_datetime ? \Altum\Date::get_timeago($data->recurring_campaign->last_run_datetime) : l('global.na') ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" data-html="true" title="<?= l('recurring_campaigns.next_run_datetime') . ($data->recurring_campaign->next_run_datetime ? '<br />' . \Altum\Date::get($data->recurring_campaign->next_run_datetime, 2) . '<br /><small>' . \Altum\Date::get($data->recurring_campaign->next_run_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_time_until($data->recurring_campaign->next_run_datetime) . ')</small>' : '<br />' . l('global.na')) ?>">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                            <i class="fas fa-fw fa-sm fa-calendar-day text-gray-900"></i>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= $data->recurring_campaign->next_run_datetime ? \Altum\Date::get_time_until($data->recurring_campaign->next_run_datetime) : l('global.na') ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($data->recurring_campaign->datetime, 2) . '<br /><small>' . \Altum\Date::get($data->recurring_campaign->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($data->recurring_campaign->datetime) . ')</small>') ?>">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                            <i class="fas fa-fw fa-sm fa-clock text-gray-900"></i>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= $data->recurring_campaign->datetime ? \Altum\Date::get_timeago($data->recurring_campaign->datetime) : l('global.na') ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($data->recurring_campaign->last_datetime ? '<br />' . \Altum\Date::get($data->recurring_campaign->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($data->recurring_campaign->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($data->recurring_campaign->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                            <i class="fas fa-fw fa-sm fa-clock-rotate-left text-gray-900"></i>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= $data->recurring_campaign->last_datetime ? \Altum\Date::get_timeago($data->recurring_campaign->last_datetime) : l('global.na') ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-4 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" title="<?= l('recurring_campaigns.total_campaigns') ?>">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-campaign">
                            <i class="fas fa-fw fa-sm fa-rocket text-campaign"></i>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= nr($data->recurring_campaign->total_campaigns) ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden position-relative" data-toggle="tooltip" title="<?= l('contacts.total_sent_sms') ?>">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                            <a href="<?= url('sms?status=sent&recurring_campaign_id=' . $data->recurring_campaign->recurring_campaign_id) ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-paper-plane text-gray-900"></i>
                            </a>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= nr($data->recurring_campaign->total_sent_sms) ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden" data-toggle="tooltip" title="<?= l('campaigns.segment') ?>">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                            <i class="fas fa-fw fa-sm fa-layer-group text-gray-900"></i>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?php if(is_numeric($data->recurring_campaign->segment)): ?>
                            <a href="<?= url('segment-update/' . $data->recurring_campaign->segment) ?>">
                                <?= l('campaigns.segment.saved') ?>
                            </a>
                        <?php else: ?>
                            <?= l('campaigns.segment.' . $data->recurring_campaign->segment) ?>
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
                            <a href="<?= url('sms?status=pending&recurring_campaign_id=' . $data->recurring_campaign->contact_id) ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-spinner <?= $data->recurring_campaign->total_pending_sms ? 'fa-spin' : null ?> text-gray-900"></i>
                            </a>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= nr($data->recurring_campaign->total_pending_sms) ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 p-3 text-truncate">
                <div class="card d-flex flex-row h-100 overflow-hidden position-relative" data-toggle="tooltip" title="<?= l('contacts.total_failed_sms') ?>">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                            <a href="<?= url('sms?status=failed&recurring_campaign_id=' . $data->recurring_campaign->contact_id) ?>" class="stretched-link">
                                <i class="fas fa-fw fa-sm fa-circle-exclamation text-gray-900"></i>
                            </a>
                        </div>
                    </div>

                    <div class="card-body text-truncate">
                        <?= nr($data->recurring_campaign->total_failed_sms) ?>
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
                <a href="<?= url('sms?recurring_campaign_id=' . $data->recurring_campaign->recurring_campaign_id) ?>" class="btn btn-sm btn-light" data-toggle="tooltip" title="<?= l('global.view_all') ?>"><i class="fas fa-fw fa-comment fa-sm"></i></a>
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
            <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-rocket mr-1"></i> <?= l('campaigns.campaign') ?></h2>

            <div class="flex-fill">
                <hr class="border-gray-100" />
            </div>
        </div>

        <div class="table-responsive table-custom-container">
            <table class="table table-custom">
                <tbody>
                <tr>
                    <td class="font-weight-bold text-truncate text-muted">
                        <i class="fas fa-fw fa-heading fa-sm text-muted mr-1"></i>
                        <?= l('sms.content') ?>
                    </td>
                    <td class="text-truncate text-wrap">
                        <?= $data->recurring_campaign->content ?>
                    </td>
                </tr>

                <?php $device = $data->devices[$data->recurring_campaign->device_id]; ?>
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

                <?php $sim = array_values(array_filter($device->sims, fn($sim) => $sim->subscription_id == $data->recurring_campaign->sim_subscription_id))[0] ?? null; ?>
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

<?php include_view(THEME_PATH . 'views/partials/clipboard_js.php') ?>
