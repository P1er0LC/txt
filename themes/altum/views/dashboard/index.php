<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <div class="mb-3 d-flex justify-content-between">
        <div>
            <h1 class="h4 mb-0 text-truncate"><i class="fas fa-fw fa-xs fa-table-cells mr-1"></i> <?= l('dashboard.header') ?></h1>
        </div>
    </div>

    <div class="my-4">
        <div class="row m-n2">
            <!-- Total Contacts -->
            <div class="col-12 col-sm-6 col-xl-3 p-2 position-relative text-truncate">
                <div id="total_contacts_wrapper" class="card d-flex flex-row h-100 overflow-hidden" style="background: var(--body-bg)" data-toggle="tooltip" data-html="true">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <a href="<?= url('contacts') ?>" class="stretched-link">
                            <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                                <i class="fas fa-fw fa-sm fa-address-book text-gray-900"></i>
                            </div>
                        </a>
                    </div>
                    <div class="card-body text-truncate">
                        <div id="total_contacts" class="text-truncate">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </div>
                        <div id="total_contacts_progress" class="progress" style="height: .25rem;">
                            <div class="progress-bar <?= $this->user->plan_settings->contacts_limit == -1 ? 'bg-success' : null ?>" role="progressbar" style="width: 0%" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Devices -->
            <div class="col-12 col-sm-6 col-xl-3 p-2 position-relative text-truncate">
                <div id="total_devices_wrapper" class="card d-flex flex-row h-100 overflow-hidden" style="background: var(--body-bg)" data-toggle="tooltip" data-html="true">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <a href="<?= url('devices') ?>" class="stretched-link">
                            <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                                <i class="fas fa-fw fa-sm fa-mobile text-gray-900"></i>
                            </div>
                        </a>
                    </div>
                    <div class="card-body text-truncate">
                        <div id="total_devices" class="text-truncate">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </div>
                        <div id="total_devices_progress" class="progress" style="height: .25rem;">
                            <div class="progress-bar <?= $this->user->plan_settings->devices_limit == -1 ? 'bg-success' : null ?>" role="progressbar" style="width: 0%" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Campaigns This Month -->
            <div class="col-12 col-sm-6 col-xl-3 p-2 position-relative text-truncate">
                <div id="total_campaigns_this_month_wrapper" class="card d-flex flex-row h-100 overflow-hidden" style="background: var(--body-bg)" data-toggle="tooltip" data-html="true">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <a href="<?= url('campaigns') ?>" class="stretched-link">
                            <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                                <i class="fas fa-fw fa-sm fa-rocket text-gray-900"></i>
                            </div>
                        </a>
                    </div>
                    <div class="card-body text-truncate">
                        <div id="total_campaigns_this_month" class="text-truncate">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </div>
                        <div id="total_campaigns_this_month_progress" class="progress" style="height: .25rem;">
                            <div class="progress-bar <?= $this->user->plan_settings->campaigns_per_month_limit == -1 ? 'bg-success' : null ?>" role="progressbar" style="width: 0%" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total SMS This Month -->
            <div class="col-12 col-sm-6 col-xl-3 p-2 position-relative text-truncate">
                <div id="total_sent_sms_wrapper" class="card d-flex flex-row h-100 overflow-hidden" style="background: var(--body-bg)" data-toggle="tooltip" data-html="true">
                    <div class="pl-3 d-flex flex-column justify-content-center">
                        <a href="<?= url('sms') ?>" class="stretched-link">
                            <div class="p-2 rounded-2x index-widget-icon d-flex align-items-center justify-content-center bg-gray-100">
                                <i class="fas fa-fw fa-sm fa-fire text-gray-900"></i>
                            </div>
                        </a>
                    </div>
                    <div class="card-body text-truncate">
                        <div id="total_sent_sms" class="text-truncate">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </div>
                        <div id="total_sent_sms_progress" class="progress" style="height: .25rem;">
                            <div class="progress-bar <?= $this->user->plan_settings->sent_sms_per_month_limit == -1 ? 'bg-success' : null ?>" role="progressbar" style="width: 0%" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-body">
            <div class="chart-container d-none" id="sms_chart_container">
                <canvas id="sms_chart"></canvas>
            </div>

            <div id="sms_chart_no_data" class="d-none">
                <?= include_view(THEME_PATH . 'views/partials/no_chart_data.php', ['has_wrapper' => false]); ?>
            </div>

            <div id="sms_chart_loading" class="chart-container d-flex align-items-center justify-content-center">
                <span class="spinner-border spinner-border-lg" role="status"></span>
            </div>

            <?php if(settings()->main->chart_cache): ?>
                <small class="text-muted d-none" id="sms_chart_help">
                    <span data-toggle="tooltip" title="<?= sprintf(l('global.chart_help'), settings()->main->chart_cache ?? 12, settings()->main->chart_days ?? 30) ?>"><i class="fas fa-fw fa-sm fa-info-circle mr-1"></i></span>
                    <span class="d-lg-none"><?= sprintf(l('global.chart_help'), settings()->main->chart_cache ?? 12, settings()->main->chart_days ?? 30) ?></span>
                </small>
            <?php endif ?>
        </div>
    </div>

    <?php require THEME_PATH . 'views/partials/js_chart_defaults.php' ?>


    <?php ob_start() ?>
    <script>
    'use strict';

        (async function fetch_statistics() {
            /* Send request to server */
            let response = await fetch(`${url}dashboard/get_stats_ajax`, {
                method: 'get',
            });

            let data = null;
            try {
                data = await response.json();
            } catch (error) {
                /* :)  */
            }

            if(!response.ok) {
                /* :)  */
            }

            if(data.status == 'error') {
                /* :)  */
            } else if(data.status == 'success') {

                /* update total_contacts */
                const total_contacts_element = document.querySelector('#total_contacts');
                if (total_contacts_element) {
                    let total_contacts_translation = <?= json_encode(l('dashboard.total_contacts')) ?>;
                    let total_contacts = data.details.total_contacts ? data.details.total_contacts : 0;
                    let total_contacts_html = total_contacts_translation.replace('%s', `<span class='h6' id='total_contacts'>${nr(total_contacts)}</span>`);

                    let contacts_plan_limit = <?= (int) $this->user->plan_settings->contacts_limit ?>;

                    /* calculate progress */
                    let progress = 0;
                    if (contacts_plan_limit > 0) {
                        progress = Math.min((total_contacts / contacts_plan_limit) * 100, 100);
                    }

                    document.querySelector('#total_contacts_progress .progress-bar').style.width = `${progress}%`;

                    document.querySelector('#total_contacts_wrapper').setAttribute('title', get_plan_feature_limit_info(total_contacts, contacts_plan_limit, true, <?= json_encode(l('global.info_message.plan_feature_limit_info')) ?>));
                    total_contacts_element.innerHTML = total_contacts_html;
                }

                /* update total_devices */
                const total_devices_element = document.querySelector('#total_devices');
                if (total_devices_element) {
                    let total_devices_translation = <?= json_encode(l('dashboard.total_devices')) ?>;
                    let total_devices = data.details.total_devices ? data.details.total_devices : 0;
                    let total_devices_html = total_devices_translation.replace('%s', `<span class='h6' id='total_devices'>${nr(total_devices)}</span>`);

                    let devices_plan_limit = <?= (int) $this->user->plan_settings->devices_limit ?>;

                    /* calculate progress */
                    let progress = 0;
                    if (devices_plan_limit > 0) {
                        progress = Math.min((total_devices / devices_plan_limit) * 100, 100);
                    }

                    document.querySelector('#total_devices_progress .progress-bar').style.width = `${progress}%`;

                    document.querySelector('#total_devices_wrapper').setAttribute('title', get_plan_feature_limit_info(total_devices, devices_plan_limit, true, <?= json_encode(l('global.info_message.plan_feature_limit_info')) ?>));
                    total_devices_element.innerHTML = total_devices_html;
                }

                /* update total_sms_this_month */
                const total_sent_sms_element = document.querySelector('#total_sent_sms');
                if (total_sent_sms_element) {
                    let total_sent_sms_translation = <?= json_encode(l('dashboard.total_sms')) ?>;
                    let total_sent_sms = data.details.total_sent_sms ? data.details.total_sent_sms : 0;
                    let total_sent_sms_this_month = data.details.usage.text_sent_sms_current_month ? data.details.usage.text_sent_sms_current_month : 0;
                    let total_sent_sms_html = total_sent_sms_translation.replace('%s', `<span class='h6' id='total_sent_sms'>${nr(total_sent_sms)}</span>`);

                    let sent_sms_per_month_limit = <?= (int) $this->user->plan_settings->sent_sms_per_month_limit ?>;

                    /* calculate progress */
                    let progress = 0;
                    if (sent_sms_per_month_limit > 0) {
                        progress = Math.min((total_sent_sms / sent_sms_per_month_limit) * 100, 100);
                    }

                    document.querySelector('#total_sent_sms_progress .progress-bar').style.width = `${progress}%`;

                    document.querySelector('#total_sent_sms_wrapper').setAttribute('title', get_plan_feature_limit_info(total_sent_sms_this_month, sent_sms_per_month_limit, true, <?= json_encode(l('global.info_message.plan_feature_limit_month_info')) ?>));
                    total_sent_sms_element.innerHTML = total_sent_sms_html;
                }

                /* update total_campaigns_this_month */
                const total_campaigns_this_month_element = document.querySelector('#total_campaigns_this_month');
                if (total_campaigns_this_month_element) {
                    let total_campaigns_this_month_translation = <?= json_encode(l('dashboard.total_campaigns')) ?>;
                    let total_campaigns_this_month = data.details.usage.text_campaigns_current_month ? data.details.usage.text_campaigns_current_month : 0;
                    let total_campaigns_this_month_html = total_campaigns_this_month_translation.replace('%s', `<span class='h6' id='total_campaigns_this_month'>${nr(total_campaigns_this_month)}</span>`);

                    let campaigns_per_month_limit = <?= (int) $this->user->plan_settings->campaigns_per_month_limit ?>;

                    /* calculate progress */
                    let progress = 0;
                    if (campaigns_per_month_limit > 0) {
                        progress = Math.min((total_campaigns_this_month / campaigns_per_month_limit) * 100, 100);
                    }

                    document.querySelector('#total_campaigns_this_month_progress .progress-bar').style.width = `${progress}%`;

                    document.querySelector('#total_campaigns_this_month_wrapper').setAttribute('title', get_plan_feature_limit_info(total_campaigns_this_month, campaigns_per_month_limit, true, <?= json_encode(l('global.info_message.plan_feature_limit_month_info')) ?>));
                    total_campaigns_this_month_element.innerHTML = total_campaigns_this_month_html;
                }

                tooltips_initiate();

                /* Remove loading */
                document.querySelector('#sms_chart_loading').classList.add('d-none');
                document.querySelector('#sms_chart_loading').classList.remove('d-flex');

                /* Chart */
                if(data.details.sms_chart.is_empty) {
                    document.querySelector('#sms_chart_no_data').classList.remove('d-none');
                } else {
                    /* Display chart data */
                    document.querySelector('#sms_chart_container').classList.remove('d-none');
                    let help_element = document.querySelector('#sms_chart_help');
                    if(help_element) {
                        help_element.classList.remove('d-none');
                    }

                    let css = window.getComputedStyle(document.body);
                    let sent_sms_color = css.getPropertyValue('--primary');
                    let sent_sms_color_gradient = null;

                    let pending_sms_color = css.getPropertyValue('--warning');
                    let pending_sms_color_gradient = null;

                    let failed_sms_color = css.getPropertyValue('--danger');
                    let failed_sms_color_gradient = null;

                    let received_sms_color = css.getPropertyValue('--gray-300');
                    let received_sms_color_gradient = null;

                    /* Chart */
                    let sms_chart = document.getElementById('sms_chart').getContext('2d');

                    /* Colors */
                    sent_sms_color_gradient = sms_chart.createLinearGradient(0, 0, 0, 250);
                    sent_sms_color_gradient.addColorStop(0, set_hex_opacity(sent_sms_color, 0.9));
                    sent_sms_color_gradient.addColorStop(1, set_hex_opacity(sent_sms_color, 0.6));

                    pending_sms_color_gradient = sms_chart.createLinearGradient(0, 0, 0, 250);
                    pending_sms_color_gradient.addColorStop(0, set_hex_opacity(pending_sms_color, 0.9));
                    pending_sms_color_gradient.addColorStop(1, set_hex_opacity(pending_sms_color, 0.6));

                    failed_sms_color_gradient = sms_chart.createLinearGradient(0, 0, 0, 250);
                    failed_sms_color_gradient.addColorStop(0, set_hex_opacity(failed_sms_color, 0.9));
                    failed_sms_color_gradient.addColorStop(1, set_hex_opacity(failed_sms_color, 0.6));

                    received_sms_color_gradient = sms_chart.createLinearGradient(0, 0, 0, 250);
                    received_sms_color_gradient.addColorStop(0, set_hex_opacity(received_sms_color, 0.9));
                    received_sms_color_gradient.addColorStop(1, set_hex_opacity(received_sms_color, 0.6));

                    new Chart(sms_chart, {
                        type: 'bar',
                        data: {
                            labels: JSON.parse(data.details.sms_chart.labels ?? '[]'),
                            datasets: [
                                {
                                    label: <?= json_encode(l('sms.received')) ?>,
                                    data: JSON.parse(data.details.sms_chart.received ?? '[]'),
                                    backgroundColor: received_sms_color_gradient,
                                    borderColor: received_sms_color,
                                    fill: true
                                },
                                {
                                    label: <?= json_encode(l('sms.sent')) ?>,
                                    data: JSON.parse(data.details.sms_chart.sent ?? '[]'),
                                    backgroundColor: sent_sms_color_gradient,
                                    borderColor: sent_sms_color,
                                    fill: true
                                },
                                {
                                    label: <?= json_encode(l('sms.pending')) ?>,
                                    data: JSON.parse(data.details.sms_chart.pending ?? '[]'),
                                    backgroundColor: pending_sms_color_gradient,
                                    borderColor: pending_sms_color,
                                    fill: true
                                },
                                {
                                    label: <?= json_encode(l('sms.failed')) ?>,
                                    data: JSON.parse(data.details.sms_chart.failed ?? '[]'),
                                    backgroundColor: failed_sms_color_gradient,
                                    borderColor: failed_sms_color,
                                    fill: true
                                },
                            ]
                        },
                        options: chart_options
                    });
                }
            }
        })();
    </script>
    <?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>



    <?php $dashboard_features = ((array) $this->user->preferences->dashboard) + array_fill_keys(['contacts', 'devices', 'sms', 'campaigns'], true) + array_fill_keys(['rss_automations', 'recurring_campaigns', 'flows', 'segments'], false) ?>

    <?php foreach($dashboard_features as $feature => $is_enabled): ?>

        <?php if($is_enabled && $feature == 'devices'): ?>
            <div class="mt-4 mb-5">
                <div class="d-flex align-items-center mb-3">
                    <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-user-check mr-1 text-subscriber"></i> <?= l('dashboard.devices.header') ?></h2>

                    <div class="flex-fill">
                        <hr class="border-gray-100" />
                    </div>

                    <div class="ml-3">
                        <a href="<?= url('device-create') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('devices.create') ?></a>

                        <a href="<?= url('devices') ?>" class="btn btn btn-sm btn-light" data-toggle="tooltip" title="<?= l('global.view_all') ?>"><i class="fas fa-fw fa-mobile fa-sm"></i></a>
                    </div>
                </div>

                <?php if (!empty($data->devices)): ?>
                    <div class="table-responsive table-custom-container">
                        <table class="table table-custom">
                            <thead>
                            <tr>
                                <th><?= l('devices.device') ?></th>
                                <th><?= l('sms.sms') ?></th>
                                <th><?= l('global.details') ?></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>

                            <?php foreach($data->devices as $row): ?>

                                <tr>
                                    <td class="text-nowrap">
                                        <div class="d-flex flex-column">
                                            <div>
                                                <a href="<?= url('device/' . $row->device_id) ?>">
                                                    <?= $row->name ?>
                                                </a>
                                            </div>

                                            <div class="small">
                                                <span class="font-weight-bold"><?= $row->device_brand . ' ' . $row->device_model ?></span>

                                                <?php if($row->sims): ?>
                                                    <?php foreach($row->sims as $sim): ?>
                                                        <span class="text-muted"><?= $sim->phone_number ?></span>
                                                        <span class="text-muted">(<?= $sim->display_name ?>)</span>
                                                    <?php endforeach ?>
                                                <?php else: ?>
                                                    <span class="text-muted"><?= l('global.no_data') ?></span>
                                                <?php endif ?>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="text-nowrap">
                                        <?php ob_start() ?>
                                        <div class='d-flex flex-column text-left'>
                                            <div class='d-flex flex-column my-1'>
                                                <div><?= l('contacts.total_sent_sms') ?></div>
                                                <strong>
                                                    <?= nr($row->total_sent_sms) ?>
                                                </strong>
                                            </div>

                                            <div class='d-flex flex-column my-1'>
                                                <div><?= l('contacts.total_pending_sms') ?></div>
                                                <strong>
                                                    <?= nr($row->total_pending_sms) ?>
                                                </strong>
                                            </div>

                                            <div class='d-flex flex-column my-1'>
                                                <div><?= l('contacts.total_failed_sms') ?></div>
                                                <strong>
                                                    <?= nr($row->total_failed_sms) ?>
                                                </strong>
                                            </div>

                                            <div class='d-flex flex-column my-1'>
                                                <div><?= l('contacts.total_received_sms') ?></div>
                                                <strong>
                                                    <?= nr($row->total_received_sms) ?>
                                                </strong>
                                            </div>
                                        </div>
                                        <?php $tooltip = ob_get_clean(); ?>

                                        <a href="<?= url('sms?device_id=' . $row->device_id) ?>" class="badge text-gray-900 bg-gray-100 mr-1" data-toggle="tooltip" data-html="true" title="<?= $tooltip ?>">
                                            <i class="fas fa-fw fa-sm fa-paper-plane mr-1"></i> <?= nr($row->total_sent_sms) ?>
                                        </a>
                                    </td>

                                    <td class="text-nowrap">
                                        <div class="d-flex align-items-center">
                                            <?php ob_start() ?>
                                            <div class='d-flex flex-column text-left'>
                                                <div class='d-flex flex-column my-1'>
                                                    <div><?= l('devices.battery') ?></div>
                                                    <strong>
                                                        <?= is_null($row->device_battery) ? l('global.no_data') : nr($row->device_battery) . '%' ?>
                                                    </strong>
                                                </div>

                                                <div class='d-flex flex-column my-1'>
                                                    <div><?= l('devices.is_charging') ?></div>
                                                    <strong>
                                                        <?= is_null($row->device_battery) ? l('global.no_data') : ($row->device_is_charging ? ucfirst(l('yes')) : ucfirst(l('no'))) ?>
                                                    </strong>
                                                </div>
                                            </div>
                                            <?php $tooltip = ob_get_clean(); ?>

                                            <span class="mr-2 position-relative" data-toggle="tooltip" data-html="true" title="<?= $tooltip ?>">
                                                <?php if($row->device_is_charging): ?>
                                                    <i class='fas fa-fw fa-bolt fa-fade text-success' style="position: absolute; top: 6px; left: 50%; transform: translateX(-50%); font-size: 0.7em; z-index: 2;"></i>
                                                <?php endif ?>

                                                <?php if(is_null($row->device_battery)): ?>
                                                    <i class="fas fa-fw fa-battery-empty text-muted"></i>
                                                <?php elseif($row->device_battery >= 90): ?>
                                                    <i class="fas fa-fw fa-battery-full text-success"></i>
                                                <?php elseif($row->device_battery >= 65): ?>
                                                    <i class="fas fa-fw fa-battery-three-quarters text-muted"></i>
                                                <?php elseif($row->device_battery >= 35): ?>
                                                    <i class="fas fa-fw fa-battery-half text-muted"></i>
                                                <?php elseif($row->device_battery >= 10): ?>
                                                    <i class="fas fa-fw fa-battery-quarter text-warning"></i>
                                                <?php elseif($row->device_battery < 10): ?>
                                                    <i class="fas fa-fw fa-battery-empty text-danger"></i>
                                                <?php endif ?>
                                            </span>

                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('devices.last_ping_datetime') . ($row->last_ping_datetime ? '<br />' . \Altum\Date::get($row->last_ping_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_ping_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_ping_datetime) . ')</small>' : '<br />' . l('global.na')) ?>">
                                                <i class="fas fa-fw fa-heartbeat text-muted"></i>
                                            </span>

                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('contacts.last_sent_datetime') . ($row->last_sent_datetime ? '<br />' . \Altum\Date::get($row->last_sent_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_sent_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_sent_datetime) . ')</small>' : '<br />' . l('global.na')) ?>">
                                                <i class="fas fa-fw fa-arrow-up text-muted"></i>
                                            </span>

                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('contacts.last_received_datetime') . ($row->last_received_datetime ? '<br />' . \Altum\Date::get($row->last_received_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_received_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_received_datetime) . ')</small>' : '<br />' . l('global.na')) ?>">
                                                <i class="fas fa-fw fa-arrow-down text-muted"></i>
                                            </span>

                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>') ?>">
                                                <i class="fas fa-fw fa-clock text-muted"></i>
                                            </span>

                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
                                                <i class="fas fa-fw fa-history text-muted"></i>
                                            </span>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="d-flex justify-content-end">
                                            <?= include_view(THEME_PATH . 'views/devices/device_dropdown_button.php', ['id' => $row->device_id, 'resource_name' => $row->name, 'is_connected' => $row->last_ping_datetime]) ?>
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
                            'name' => 'contacts',
                            'has_secondary_text' => true,
                    ]); ?>

                <?php endif ?>

            </div>
        <?php endif ?>

        <?php if($is_enabled && $feature == 'contacts'): ?>
            <div class="mt-4 mb-5">
                <div class="d-flex align-items-center mb-3">
                    <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-user-check mr-1 text-subscriber"></i> <?= l('dashboard.contacts.header') ?></h2>

                    <div class="flex-fill">
                        <hr class="border-gray-100" />
                    </div>

                    <div class="ml-3">
                        <a href="<?= url('contact-create') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('contacts.create') ?></a>

                        <a href="<?= url('contacts') ?>" class="btn btn btn-sm btn-light" data-toggle="tooltip" title="<?= l('global.view_all') ?>"><i class="fas fa-fw fa-user-check fa-sm"></i></a>
                    </div>
                </div>

                <?php if (!empty($data->contacts)): ?>
                    <div class="table-responsive table-custom-container">
                        <table class="table table-custom">
                            <thead>
                            <tr>
                                <th><?= l('contacts.contact') ?></th>
                                <th><?= l('sms.sms') ?></th>
                                <th><?= l('global.details') ?></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>

                            <?php foreach($data->contacts as $row): ?>

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
                                                <?= $row->name ?: l('contacts.unknown_name') ?>
                                            </span>
                                        </div>
                                    </td>

                                    <td class="text-nowrap">
                                        <?php ob_start() ?>
                                        <div class='d-flex flex-column text-left'>
                                            <div class='d-flex flex-column my-1'>
                                                <div><?= l('contacts.total_sent_sms') ?></div>
                                                <strong>
                                                    <?= nr($row->total_sent_sms) ?>
                                                </strong>
                                            </div>

                                            <div class='d-flex flex-column my-1'>
                                                <div><?= l('contacts.total_pending_sms') ?></div>
                                                <strong>
                                                    <?= nr($row->total_pending_sms) ?>
                                                </strong>
                                            </div>

                                            <div class='d-flex flex-column my-1'>
                                                <div><?= l('contacts.total_failed_sms') ?></div>
                                                <strong>
                                                    <?= nr($row->total_failed_sms) ?>
                                                </strong>
                                            </div>

                                            <div class='d-flex flex-column my-1'>
                                                <div><?= l('contacts.total_received_sms') ?></div>
                                                <strong>
                                                    <?= nr($row->total_received_sms) ?>
                                                </strong>
                                            </div>
                                        </div>
                                        <?php $tooltip = ob_get_clean(); ?>

                                        <a href="<?= url('sms?contact_id=' . $row->contact_id) ?>" class="badge text-gray-900 bg-gray-100 mr-1" data-toggle="tooltip" data-html="true" title="<?= $tooltip ?>">
                                            <i class="fas fa-fw fa-sm fa-paper-plane mr-1"></i> <?= nr($row->total_sent_sms) ?>
                                        </a>
                                    </td>

                                    <td class="text-nowrap">
                                        <div class="d-flex align-items-center">
                                            <?php if($row->has_opted_out): ?>
                                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('contacts.has_opted_out') . '<br />' . l('global.yes') ?>">
                                                    <i class="fas fa-fw fa-user-slash text-warning"></i>
                                                </span>
                                            <?php else: ?>
                                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('contacts.has_opted_out') . '<br />' . l('global.no') ?>">
                                                    <i class="fas fa-fw fa-user-check text-muted"></i>
                                                </span>
                                            <?php endif ?>

                                            <?php if(($row->custom_parameters = json_decode($row->custom_parameters ?? '', true)) && count($row->custom_parameters)): ?>
                                                <?php ob_start() ?>
                                                <div class='d-flex flex-column text-left'>
                                                    <div class='d-flex flex-column my-1'>
                                                        <strong><?= sprintf(l('contacts.custom_parameters_x'), count($row->custom_parameters)) ?></strong>
                                                    </div>

                                                    <?php foreach($row->custom_parameters as $key => $value): ?>
                                                        <div class='d-flex flex-column my-1'>
                                                            <div><?= e($key) ?></div>
                                                            <strong><?= e($value) ?></strong>
                                                        </div>
                                                    <?php endforeach ?>
                                                </div>

                                                <?php $tooltip = ob_get_clean() ?>

                                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= $tooltip ?>">
                                                    <i class="fas fa-fw fa-fingerprint text-primary"></i>
                                                </span>
                                            <?php else: ?>
                                                <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('contacts.custom_parameters_x'), 0) ?>">
                                                    <i class="fas fa-fw fa-fingerprint text-muted"></i>
                                                </span>
                                            <?php endif ?>

                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('contacts.last_sent_datetime') . ($row->last_sent_datetime ? '<br />' . \Altum\Date::get($row->last_sent_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_sent_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_sent_datetime) . ')</small>' : '<br />' . l('global.na')) ?>">
                                                <i class="fas fa-fw fa-arrow-up text-muted"></i>
                                            </span>

                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('contacts.last_received_datetime') . ($row->last_received_datetime ? '<br />' . \Altum\Date::get($row->last_received_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_received_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_received_datetime) . ')</small>' : '<br />' . l('global.na')) ?>">
                                                <i class="fas fa-fw fa-arrow-down text-muted"></i>
                                            </span>

                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>') ?>">
                                                <i class="fas fa-fw fa-clock text-muted"></i>
                                            </span>

                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
                                                <i class="fas fa-fw fa-history text-muted"></i>
                                            </span>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="d-flex justify-content-end">
                                            <?= include_view(THEME_PATH . 'views/contacts/contact_dropdown_button.php', ['id' => $row->contact_id, 'resource_name' => $row->phone_number, 'has_opted_out' => $row->has_opted_out]) ?>
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
                            'name' => 'contacts',
                            'has_secondary_text' => true,
                    ]); ?>

                <?php endif ?>

            </div>
        <?php endif ?>

        <?php if($is_enabled && $feature == 'campaigns'): ?>
            <div class="mt-4 mb-5">
                <div class="d-flex align-items-center mb-3">
                    <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-rocket mr-1 text-campaign"></i> <?= l('dashboard.campaigns.header') ?></h2>

                    <div class="flex-fill">
                        <hr class="border-gray-100" />
                    </div>

                    <div class="ml-3">
                        <a href="<?= url('campaign-create') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('campaigns.create') ?></a>
                        <a href="<?= url('campaigns') ?>" class="btn btn btn-sm btn-light" data-toggle="tooltip" title="<?= l('global.view_all') ?>"><i class="fas fa-fw fa-rocket fa-sm"></i></a>
                    </div>
                </div>

                <?php if (!empty($data->campaigns)): ?>
                    <div class="table-responsive table-custom-container">
                        <table class="table table-custom">
                            <thead>
                            <tr>
                                <th><?= l('campaigns.campaign') ?></th>
                                <th><?= l('campaigns.segment') ?></th>
                                <th><?= l('sms.sms') ?></th>
                                <th><?= l('global.status') ?></th>
                                <th></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>

                            <?php foreach($data->campaigns as $row): ?>

                                <tr>
                                    <td class="text-nowrap">
                                        <div>
                                            <?php if(in_array($row->status, ['draft', 'scheduled'])): ?>
                                                <a href="<?= url('campaign-update/' . $row->campaign_id) ?>"><?= $row->name ?></a>
                                            <?php elseif(in_array($row->status, ['processing', 'sent'])): ?>
                                                <a href="<?= url('campaign/' . $row->campaign_id) ?>"><?= $row->name ?></a>
                                            <?php endif ?>
                                        </div>

                                        <div class="d-flex align-items-center">

                                        </div>
                                    </td>

                                    <td class="text-nowrap">
                                        <?php if(is_numeric($row->segment)): ?>
                                            <a href="<?= url('segment-update/' . $row->segment) ?>" class="badge badge-light">
                                                <i class="fas fa-fw fa-sm fa-layer-group mr-1"></i> <?= l('campaigns.segment.saved') ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge badge-light">
                                                <i class="fas fa-fw fa-sm fa-layer-group mr-1"></i> <?= l('campaigns.segment.' . $row->segment) ?>
                                            </span>
                                        <?php endif ?>
                                    </td>

                                    <td class="text-nowrap">
                                        <?php ob_start() ?>
                                        <div class='d-flex flex-column text-left'>
                                            <div class='d-flex flex-column my-1'>
                                                <div><?= l('contacts.total_sent_sms') ?></div>
                                                <strong>
                                                    <?= nr($row->total_sent_sms) ?>
                                                </strong>
                                            </div>

                                            <div class='d-flex flex-column my-1'>
                                                <div><?= l('contacts.total_pending_sms') ?></div>
                                                <strong>
                                                    <?= nr($row->total_pending_sms) ?>
                                                </strong>
                                            </div>

                                            <div class='d-flex flex-column my-1'>
                                                <div><?= l('contacts.total_failed_sms') ?></div>
                                                <strong>
                                                    <?= nr($row->total_failed_sms) ?>
                                                </strong>
                                            </div>
                                        </div>
                                        <?php $tooltip = ob_get_clean(); ?>

                                        <a href="<?= url('sms?campaign_id=' . $row->campaign_id) ?>" class="badge text-gray-900 bg-gray-100 mr-1" data-toggle="tooltip" data-html="true" title="<?= $tooltip ?>">
                                            <i class="fas fa-fw fa-sm fa-paper-plane mr-1"></i> <?= nr($row->total_sent_sms) ?>
                                        </a>
                                    </td>

                                    <td class="text-nowrap">
                                        <?php if($row->status == 'draft'): ?>
                                            <span class="badge badge-light"><i class="fas fa-fw fa-sm fa-save mr-1"></i> <?= l('campaigns.status.draft') ?></span>
                                        <?php elseif($row->status == 'scheduled'): ?>
                                            <span class="badge badge-gray-300" data-toggle="tooltip" title="<?= \Altum\Date::get_time_until($row->scheduled_datetime) ?>"><i class="fas fa-fw fa-sm fa-calendar-day mr-1"></i> <?= l('campaigns.status.scheduled') ?></span>
                                        <?php elseif($row->status == 'processing'): ?>
                                            <span class="badge badge-warning"><i class="fas fa-fw fa-sm fa-spinner fa-spin mr-1"></i> <?= l('campaigns.status.processing') ?></span>
                                        <?php elseif($row->status == 'sent'): ?>
                                            <span class="badge badge-success"><i class="fas fa-fw fa-sm fa-check mr-1"></i> <?= l('campaigns.status.sent') ?></span>
                                        <?php endif ?>
                                    </td>

                                    <td class="text-nowrap">
                                        <div class="d-flex align-items-center">
                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('campaigns.scheduled_datetime') . ($row->scheduled_datetime && $row->settings->is_scheduled ? '<br />' . \Altum\Date::get($row->scheduled_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->scheduled_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_time_until($row->scheduled_datetime) . ')</small>' : '<br />' . l('global.na')) ?>">
                                                <i class="fas fa-fw fa-calendar-day text-muted"></i>
                                            </span>

                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('contacts.last_sent_datetime') . ($row->last_sent_datetime ? '<br />' . \Altum\Date::get($row->last_sent_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_sent_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_sent_datetime) . ')</small>' : '<br />' . l('global.na')) ?>">
                                                <i class="fas fa-fw fa-arrow-up text-muted"></i>
                                            </span>

                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>') ?>">
                                                <i class="fas fa-fw fa-clock text-muted"></i>
                                            </span>

                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
                                                <i class="fas fa-fw fa-history text-muted"></i>
                                            </span>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="d-flex justify-content-end">
                                            <?= include_view(THEME_PATH . 'views/campaigns/campaign_dropdown_button.php', ['id' => $row->campaign_id, 'resource_name' => $row->name,]) ?>
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
                            'name' => 'campaigns',
                            'has_secondary_text' => true,
                    ]); ?>

                <?php endif ?>

            </div>
        <?php endif ?>

        <?php if($is_enabled && $feature == 'rss_automations'): ?>
            <div class="mt-4 mb-5">
                <div class="d-flex align-items-center mb-3">
                    <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-feed mr-1 text-campaign"></i> <?= l('dashboard.rss_automations.header') ?></h2>

                    <div class="flex-fill">
                        <hr class="border-gray-100" />
                    </div>

                    <div class="ml-3">
                        <a href="<?= url('rss-automation-create') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('rss_automations.create') ?></a>
                        <a href="<?= url('rss-automations') ?>" class="btn btn-sm btn-light" data-toggle="tooltip" title="<?= l('global.view_all') ?>"><i class="fas fa-fw fa-feed fa-sm"></i></a>
                    </div>
                </div>

                <?php if (!empty($data->rss_automations)): ?>
                    <div class="table-responsive table-custom-container">
                        <table class="table table-custom">
                            <thead>
                            <tr>
                                <th><?= l('rss_automations.rss_automation') ?></th>
                                <th><?= l('campaigns.segment') ?></th>
                                <th><?= l('rss_automations.total_campaigns') ?></th>
                                <th><?= l('sms.sms') ?></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>

                            <?php foreach($data->rss_automations as $row): ?>

                                <tr>
                                    <td class="text-nowrap">
                                        <div>
                                            <a href="<?= url('rss-automation/' . $row->rss_automation_id) ?>"><?= $row->name ?></a>
                                        </div>
                                    </td>

                                    <td class="text-nowrap">
                                        <?php if(is_numeric($row->segment)): ?>
                                            <a href="<?= url('segment-update/' . $row->segment) ?>" class="badge badge-light">
                                                <i class="fas fa-fw fa-sm fa-layer-group mr-1"></i> <?= l('campaigns.segment.saved') ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge badge-light">
                                                <i class="fas fa-fw fa-sm fa-layer-group mr-1"></i> <?= l('campaigns.segment.' . $row->segment) ?>
                                            </span>
                                        <?php endif ?>
                                    </td>

                                    <td class="text-nowrap">
                                        <a href="<?= url('campaigns?rss_automation_id=' . $row->rss_automation_id) ?>" class="badge badge-secondary">
                                            <i class="fas fa-fw fa-sm fa-rocket mr-1"></i> <?= nr($row->total_campaigns) ?>
                                        </a>
                                    </td>

                                    <td class="text-nowrap">
                                        <?php ob_start() ?>
                                        <div class='d-flex flex-column text-left'>
                                            <div class='d-flex flex-column my-1'>
                                                <div><?= l('contacts.total_sent_sms') ?></div>
                                                <strong>
                                                    <?= nr($row->total_sent_sms) ?>
                                                </strong>
                                            </div>

                                            <div class='d-flex flex-column my-1'>
                                                <div><?= l('contacts.total_pending_sms') ?></div>
                                                <strong>
                                                    <?= nr($row->total_pending_sms) ?>
                                                </strong>
                                            </div>

                                            <div class='d-flex flex-column my-1'>
                                                <div><?= l('contacts.total_failed_sms') ?></div>
                                                <strong>
                                                    <?= nr($row->total_failed_sms) ?>
                                                </strong>
                                            </div>
                                        </div>
                                        <?php $tooltip = ob_get_clean(); ?>

                                        <a href="<?= url('sms?rss_automation_id=' . $row->rss_automation_id) ?>" class="badge text-gray-900 bg-gray-100 mr-1" data-toggle="tooltip" data-html="true" title="<?= $tooltip ?>">
                                            <i class="fas fa-fw fa-sm fa-paper-plane mr-1"></i> <?= nr($row->total_sent_sms) ?>
                                        </a>
                                    </td>

                                    <td class="text-nowrap">
                                        <?php if($row->is_enabled): ?>
                                            <span class="badge badge-success" data-toggle="tooltip" title="<?= l('global.active') ?>"><i class="fas fa-fw fa-check"></i></span>
                                        <?php else: ?>
                                            <span class="badge badge-warning" data-toggle="tooltip" title="<?= l('global.disabled') ?>"><i class="fas fa-fw fa-pause"></i></span>
                                        <?php endif ?>
                                    </td>

                                    <td class="text-nowrap">
                                        <div class="d-flex align-items-center">
                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('rss_automations.last_check_datetime') . ($row->last_check_datetime ? '<br />' . \Altum\Date::get($row->last_check_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_check_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_check_datetime) . ')</small>' : '<br />' . l('global.na')) ?>">
                                                <i class="fas fa-fw fa-calendar-check text-muted"></i>
                                            </span>

                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('rss_automations.next_check_datetime') . ($row->next_check_datetime ? '<br />' . \Altum\Date::get($row->next_check_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->next_check_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_time_until($row->next_check_datetime) . ')</small>' : '<br />' . l('global.na')) ?>">
                                                <i class="fas fa-fw fa-calendar-day text-muted"></i>
                                            </span>

                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>') ?>">
                                                <i class="fas fa-fw fa-clock text-muted"></i>
                                            </span>

                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
                                                <i class="fas fa-fw fa-history text-muted"></i>
                                            </span>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="d-flex justify-content-end">
                                            <?= include_view(THEME_PATH . 'views/rss-automations/rss_automation_dropdown_button.php', ['id' => $row->rss_automation_id, 'resource_name' => $row->name]) ?>
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
                            'name' => 'rss_automations',
                            'has_secondary_text' => true,
                    ]); ?>

                <?php endif ?>

            </div>
        <?php endif ?>

        <?php if($is_enabled && $feature == 'recurring_campaigns'): ?>
            <div class="mt-4 mb-5">
                <div class="d-flex align-items-center mb-3">
                    <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-retweet mr-1"></i> <?= l('dashboard.recurring_campaigns.header') ?></h2>

                    <div class="flex-fill">
                        <hr class="border-gray-100" />
                    </div>

                    <div class="ml-3">
                        <a href="<?= url('recurring-campaign-create') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('recurring_campaigns.create') ?></a>
                        <a href="<?= url('recurring-campaigns') ?>" class="btn btn-sm btn-light" data-toggle="tooltip" title="<?= l('global.view_all') ?>"><i class="fas fa-fw fa-retweet fa-sm"></i></a>
                    </div>
                </div>

                <?php if (!empty($data->recurring_campaigns)): ?>
                    <div class="table-responsive table-custom-container">
                        <table class="table table-custom">
                            <thead>
                            <tr>
                                <th><?= l('recurring_campaigns.recurring_campaign') ?></th>
                                <th><?= l('campaigns.segment') ?></th>
                                <th><?= l('recurring_campaigns.total_campaigns') ?></th>
                                <th><?= l('sms.sms') ?></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>

                            <?php foreach($data->recurring_campaigns as $row): ?>

                                <tr>
                                    <td class="text-nowrap">
                                        <div>
                                            <a href="<?= url('recurring-campaign/' . $row->recurring_campaign_id) ?>"><?= $row->name ?></a>
                                        </div>
                                    </td>

                                    <td class="text-nowrap">
                                        <?php if(is_numeric($row->segment)): ?>
                                            <a href="<?= url('segment-update/' . $row->segment) ?>" class="badge badge-light">
                                                <i class="fas fa-fw fa-sm fa-layer-group mr-1"></i> <?= l('campaigns.segment.saved') ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge badge-light">
                                                <i class="fas fa-fw fa-sm fa-layer-group mr-1"></i> <?= l('campaigns.segment.' . $row->segment) ?>
                                            </span>
                                        <?php endif ?>
                                    </td>

                                    <td class="text-nowrap">
                                        <a href="<?= url('campaigns?recurring_campaign_id=' . $row->recurring_campaign_id) ?>" class="badge badge-secondary">
                                            <i class="fas fa-fw fa-sm fa-rocket mr-1"></i> <?= nr($row->total_campaigns) ?>
                                        </a>
                                    </td>

                                    <td class="text-nowrap">
                                        <?php ob_start() ?>
                                        <div class='d-flex flex-column text-left'>
                                            <div class='d-flex flex-column my-1'>
                                                <div><?= l('contacts.total_sent_sms') ?></div>
                                                <strong>
                                                    <?= nr($row->total_sent_sms) ?>
                                                </strong>
                                            </div>

                                            <div class='d-flex flex-column my-1'>
                                                <div><?= l('contacts.total_pending_sms') ?></div>
                                                <strong>
                                                    <?= nr($row->total_pending_sms) ?>
                                                </strong>
                                            </div>

                                            <div class='d-flex flex-column my-1'>
                                                <div><?= l('contacts.total_failed_sms') ?></div>
                                                <strong>
                                                    <?= nr($row->total_failed_sms) ?>
                                                </strong>
                                            </div>
                                        </div>
                                        <?php $tooltip = ob_get_clean(); ?>

                                        <a href="<?= url('sms?rss_automation_id=' . $row->rss_automation_id) ?>" class="badge text-gray-900 bg-gray-100 mr-1" data-toggle="tooltip" data-html="true" title="<?= $tooltip ?>">
                                            <i class="fas fa-fw fa-sm fa-paper-plane mr-1"></i> <?= nr($row->total_sent_sms) ?>
                                        </a>
                                    </td>

                                    <td class="text-nowrap">
                                        <?php if($row->is_enabled): ?>
                                            <span class="badge badge-success" data-toggle="tooltip" title="<?= l('global.active') ?>"><i class="fas fa-fw fa-check"></i></span>
                                        <?php else: ?>
                                            <span class="badge badge-warning" data-toggle="tooltip" title="<?= l('global.disabled') ?>"><i class="fas fa-fw fa-pause"></i></span>
                                        <?php endif ?>
                                    </td>

                                    <td class="text-nowrap">
                                        <div class="d-flex align-items-center">
                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('recurring_campaigns.last_run_datetime') . ($row->last_run_datetime ? '<br />' . \Altum\Date::get($row->last_run_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_run_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_run_datetime) . ')</small>' : '<br />' . l('global.na')) ?>">
                                                <i class="fas fa-fw fa-calendar-check text-muted"></i>
                                            </span>

                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('recurring_campaigns.next_run_datetime') . ($row->next_run_datetime ? '<br />' . \Altum\Date::get($row->next_run_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->next_run_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_time_until($row->next_run_datetime) . ')</small>' : '<br />' . l('global.na')) ?>">
                                                <i class="fas fa-fw fa-calendar-day text-muted"></i>
                                            </span>

                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>') ?>">
                                                <i class="fas fa-fw fa-clock text-muted"></i>
                                            </span>

                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
                                                <i class="fas fa-fw fa-history text-muted"></i>
                                            </span>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="d-flex justify-content-end">
                                            <?= include_view(THEME_PATH . 'views/recurring-campaigns/recurring_campaign_dropdown_button.php', ['id' => $row->recurring_campaign_id, 'resource_name' => $row->name]) ?>
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
                            'name' => 'recurring_campaigns',
                            'has_secondary_text' => true,
                    ]); ?>

                <?php endif ?>

            </div>
        <?php endif ?>

        <?php if($is_enabled && $feature == 'flows'): ?>
            <div class="mt-4 mb-5">
                <div class="d-flex align-items-center mb-3">
                    <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-tasks mr-1"></i> <?= l('dashboard.flows.header') ?></h2>

                    <div class="flex-fill">
                        <hr class="border-gray-100" />
                    </div>

                    <div class="ml-3">
                        <a href="<?= url('flow-create') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('flows.create') ?></a>
                        <a href="<?= url('flows') ?>" class="btn btn-sm btn-light" data-toggle="tooltip" title="<?= l('global.view_all') ?>"><i class="fas fa-fw fa-tasks fa-sm"></i></a>
                    </div>
                </div>

                <?php if (!empty($data->flows)): ?>
                    <div class="table-responsive table-custom-container">
                        <table class="table table-custom">
                            <thead>
                            <tr>
                                <th><?= l('flows.flow') ?></th>
                                <th><?= l('campaigns.segment') ?></th>
                                <th><?= l('flows.wait_time') ?></th>
                                <th><?= l('sms.sms') ?></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>

                            <?php foreach($data->flows as $row): ?>

                                <tr>
                                    <td class="text-nowrap">
                                        <div>
                                            <a href="<?= url('flow/' . $row->flow_id) ?>"><?= $row->name ?></a>
                                        </div>
                                    </td>

                                    <td class="text-nowrap">
                                        <?php if(is_numeric($row->segment)): ?>
                                            <a href="<?= url('segment-update/' . $row->segment) ?>" class="badge badge-light">
                                                <i class="fas fa-fw fa-sm fa-layer-group mr-1"></i> <?= l('campaigns.segment.saved') ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge badge-light">
                                                <i class="fas fa-fw fa-sm fa-layer-group mr-1"></i> <?= l('campaigns.segment.' . $row->segment) ?>
                                            </span>
                                        <?php endif ?>
                                    </td>

                                    <td class="text-nowrap">
                                        <span class="badge badge-light">
                                            <i class="fas fa-fw fa-sm fa-hourglass mr-1"></i> <?= $row->wait_time . ' ' . l('global.date.' . $row->wait_time_type) ?>
                                        </span>
                                    </td>

                                    <td class="text-nowrap">
                                        <?php ob_start() ?>
                                        <div class='d-flex flex-column text-left'>
                                            <div class='d-flex flex-column my-1'>
                                                <div><?= l('contacts.total_sent_sms') ?></div>
                                                <strong>
                                                    <?= nr($row->total_sent_sms) ?>
                                                </strong>
                                            </div>

                                            <div class='d-flex flex-column my-1'>
                                                <div><?= l('contacts.total_pending_sms') ?></div>
                                                <strong>
                                                    <?= nr($row->total_pending_sms) ?>
                                                </strong>
                                            </div>

                                            <div class='d-flex flex-column my-1'>
                                                <div><?= l('contacts.total_failed_sms') ?></div>
                                                <strong>
                                                    <?= nr($row->total_failed_sms) ?>
                                                </strong>
                                            </div>
                                        </div>
                                        <?php $tooltip = ob_get_clean(); ?>

                                        <a href="<?= url('sms?flow_id=' . $row->flow_id) ?>" class="badge text-gray-900 bg-gray-100 mr-1" data-toggle="tooltip" data-html="true" title="<?= $tooltip ?>">
                                            <i class="fas fa-fw fa-sm fa-paper-plane mr-1"></i> <?= nr($row->total_sent_sms) ?>
                                        </a>
                                    </td>

                                    <td class="text-nowrap">
                                        <?php if($row->is_enabled): ?>
                                            <span class="badge badge-success" data-toggle="tooltip" title="<?= l('global.active') ?>"><i class="fas fa-fw fa-check"></i></span>
                                        <?php else: ?>
                                            <span class="badge badge-warning" data-toggle="tooltip" title="<?= l('global.disabled') ?>"><i class="fas fa-fw fa-pause"></i></span>
                                        <?php endif ?>
                                    </td>

                                    <td class="text-nowrap">
                                        <div class="d-flex align-items-center">
                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= l('contacts.last_sent_datetime') . ($row->last_sent_datetime ? '<br />' . \Altum\Date::get($row->last_sent_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_sent_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_sent_datetime) . ')</small>' : '<br />' . l('global.na')) ?>">
                                                <i class="fas fa-fw fa-arrow-up text-muted"></i>
                                            </span>

                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>') ?>">
                                                <i class="fas fa-fw fa-clock text-muted"></i>
                                            </span>

                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
                                                <i class="fas fa-fw fa-history text-muted"></i>
                                            </span>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="d-flex justify-content-end">
                                            <?= include_view(THEME_PATH . 'views/flows/flow_dropdown_button.php', ['id' => $row->flow_id, 'resource_name' => $row->name]) ?>
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
                            'name' => 'flows',
                            'has_secondary_text' => true,
                    ]); ?>

                <?php endif ?>

            </div>
        <?php endif ?>

        <?php if($is_enabled && $feature == 'segments'): ?>
            <div class="mt-4 mb-5">
                <div class="d-flex align-items-center mb-3">
                    <h2 class="small font-weight-bold text-uppercase text-muted mb-0 mr-3"><i class="fas fa-fw fa-sm fa-layer-group mr-1"></i> <?= l('dashboard.segments.header') ?></h2>

                    <div class="flex-fill">
                        <hr class="border-gray-100" />
                    </div>

                    <div class="ml-3">
                        <a href="<?= url('segment-create') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('segments.create') ?></a>
                        <a href="<?= url('segments') ?>" class="btn btn-sm btn-light" data-toggle="tooltip" title="<?= l('global.view_all') ?>"><i class="fas fa-fw fa-layer-group fa-sm"></i></a>
                    </div>
                </div>

                <?php if (!empty($data->segments)): ?>
                    <div class="table-responsive table-custom-container">
                        <table class="table table-custom">
                            <thead>
                            <tr>
                                <th data-bulk-table class="d-none">
                                    <div class="custom-control custom-checkbox">
                                        <input id="bulk_select_all" type="checkbox" class="custom-control-input" />
                                        <label class="custom-control-label" for="bulk_select_all"></label>
                                    </div>
                                </th>
                                <th><?= l('segments.segment') ?></th>
                                <th><?= l('global.type') ?></th>
                                <th colspan="2"><?= l('contacts.total') ?></th>
                                <th></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>

                            <?php foreach($data->segments as $row): ?>

                                <tr>
                                    <td data-bulk-table class="d-none">
                                        <div class="custom-control custom-checkbox">
                                            <input id="selected_segment_id_<?= $row->segment_id ?>" type="checkbox" class="custom-control-input" name="selected[]" value="<?= $row->segment_id ?>" />
                                            <label class="custom-control-label" for="selected_segment_id_<?= $row->segment_id ?>"></label>
                                        </div>
                                    </td>

                                    <td class="text-nowrap">
                                        <div>
                                            <a href="<?= url('segment-update/' . $row->segment_id) ?>"><?= $row->name ?></a>
                                        </div>
                                    </td>

                                    <td class="text-nowrap">
                                        <span class="badge badge-light">
                                            <i class="fas fa-fw fa-sm fa-layer-group mr-1"></i> <?= l('segments.type.' . $row->type) ?>
                                        </span>
                                    </td>

                                    <td class="text-nowrap">
                                        <span class="badge badge-dark">
                                            <i class="fas fa-fw fa-sm fa-user-check mr-1"></i> <?= nr($row->total_contacts) ?>
                                        </span>
                                    </td>

                                    <td class="text-nowrap text-muted">
                                        <a href="<?= url('campaigns?segment=' . $row->segment_id) ?>" class="mr-2" data-toggle="tooltip" title="<?= l('campaigns.title') ?>">
                                            <i class="fas fa-fw fa-rocket text-muted"></i>
                                        </a>

                                        <a href="<?= url('rss-automations?segment=' . $row->segment_id) ?>" class="mr-2" data-toggle="tooltip" title="<?= l('rss_automations.title') ?>">
                                            <i class="fas fa-fw fa-rss text-muted"></i>
                                        </a>

                                        <a href="<?= url('recurring-campaigns?segment=' . $row->segment_id) ?>" class="mr-2" data-toggle="tooltip" title="<?= l('recurring_campaigns.title') ?>">
                                            <i class="fas fa-fw fa-retweet text-muted"></i>
                                        </a>

                                        <a href="<?= url('flows?segment=' . $row->segment_id) ?>" class="mr-2" data-toggle="tooltip" title="<?= l('flows.title') ?>">
                                            <i class="fas fa-fw fa-tasks text-muted"></i>
                                        </a>
                                    </td>

                                    <td class="text-nowrap">
                                        <div class="d-flex align-items-center">
                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->datetime) . ')</small>') ?>">
                                                <i class="fas fa-fw fa-clock text-muted"></i>
                                            </span>

                                            <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' . '<br /><small>(' . \Altum\Date::get_timeago($row->last_datetime) . ')</small>' : '<br />' . l('global.na'))) ?>">
                                                <i class="fas fa-fw fa-history text-muted"></i>
                                            </span>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="d-flex justify-content-end">
                                            <?= include_view(THEME_PATH . 'views/segments/segment_dropdown_button.php', ['id' => $row->segment_id, 'resource_name' => $row->name,]) ?>
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
                            'name' => 'segments',
                            'has_secondary_text' => true,
                    ]); ?>

                <?php endif ?>

            </div>
        <?php endif ?>
    <?php endforeach ?>
</div>
