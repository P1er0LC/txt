<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('recurring-campaigns') ?>"><?= l('recurring_campaigns.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('recurring_campaign_create.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <h1 class="h4 text-truncate"><i class="fas fa-fw fa-xs fa-retweet mr-1"></i> <?= l('recurring_campaign_create.header') ?></h1>
    <p></p>

    <div class="card">
        <div class="card-body">

            <form id="form" action="" method="post" role="form" enctype="multipart/form-data">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="form-group">
                    <label for="name"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('global.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control <?= \Altum\Alerts::has_field_errors('name') ? 'is-invalid' : null ?>" value="<?= $data->values['name'] ?>" required="required" />
                    <?= \Altum\Alerts::output_field_error('name') ?>
                </div>

                <div class="form-group" data-character-counter="textarea">
                    <label for="content" class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-fw fa-sm fa-heading text-muted mr-1"></i> <?= l('sms.content') ?></span>
                        <small class="text-muted" data-character-counter-wrapper></small>
                    </label>
                    <textarea id="content" name="content" class="form-control <?= \Altum\Alerts::has_field_errors('content') ? 'is-invalid' : null ?>" maxlength="1000" required="required"><?= $data->values['content'] ?></textarea>
                    <?= \Altum\Alerts::output_field_error('content') ?>
                    <small class="form-text text-muted"><?= l('campaigns.content_help') ?></small>
                    <small class="form-text text-muted"><?= sprintf(l('global.variables'), '<code data-copy>' . implode('</code> , <code data-copy>',  ['{{RSS_TITLE}}', '{{RSS_DESCRIPTION}}', '{{RSS_URL}}', '{{NAME}}', '{{PHONE_NUMBER}}', '{{CONTINENT_NAME}}', '{{COUNTRY_NAME}}', '{{CUSTOM_PARAMETERS:KEY}}']) . '</code>') ?></small>
                    <small class="form-text text-muted"><?= l('global.spintax_help') ?></small>
                </div>

                <?php if(!$this->user->plan_settings->removable_branding_is_enabled && settings()->sms->branding): ?>
                    <div class="form-group">
                        <label for="branding"><i class="fas fa-fw fa-sm fa-shuffle text-muted mr-1"></i> <?= l('sms.branding') ?></label>
                        <input type="text" id="branding" name="branding" class="form-control <?= \Altum\Alerts::has_field_errors('branding') ? 'is-invalid' : null ?>" value="<?= settings()->sms->branding ?>" disabled="disabled" />
                        <?= \Altum\Alerts::output_field_error('branding') ?>
                        <small class="form-text text-muted"><?= l('sms.branding_help') ?></small>
                    </div>
                <?php endif ?>

                <div class="form-group">
                    <div class="d-flex flex-wrap flex-row justify-content-between">
                        <label for="device_id"><i class="fas fa-fw fa-sm fa-mobile text-muted mr-1"></i> <?= l('devices.device') ?></label>
                        <a href="<?= url('device-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('devices.create') ?></a>
                    </div>
                    <select id="device_id" name="device_id" class="form-control <?= \Altum\Alerts::has_field_errors('device_id') ? 'is-invalid' : null ?>" required="required">
                        <?php foreach($data->devices as $device): ?>
                            <option
                                    value="<?= $device->device_id ?>"
                                    data-sims='<?= json_encode($device->sims) ?>'
                                <?= $data->values['device_id'] == $device->device_id ? 'selected="selected"' : null ?>
                            >
                                <?= $device->name ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                    <?= \Altum\Alerts::output_field_error('device_id') ?>
                </div>

                <div class="form-group">
                    <label for="sim_subscription_id"><i class="fas fa-fw fa-sm fa-sim-card text-muted mr-1"></i> <?= l('devices.sim_subscription_id') ?></label>
                    <select id="sim_subscription_id" name="sim_subscription_id" class="form-control <?= \Altum\Alerts::has_field_errors('sim_subscription_id') ? 'is-invalid' : null ?>" required="required" data-selected-sim-subscription-id="<?= $data->values['sim_subscription_id'] ?>"></select>
                    <?= \Altum\Alerts::output_field_error('sim_subscription_id') ?>
                </div>

                <div class="form-group">
                    <div class="d-flex flex-wrap flex-row justify-content-between">
                        <label for="segment"><i class="fas fa-fw fa-sm fa-layer-group text-muted mr-1"></i> <?= l('campaigns.segment') ?> <span id="segment_count"></span></label>
                        <a href="<?= url('segment-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('segments.create') ?></a>
                    </div>
                    <select id="segment" name="segment" class="form-control <?= \Altum\Alerts::has_field_errors('segment') ? 'is-invalid' : null ?>" required="required">
                        <option value="all" <?= $data->values['segment'] == 'all' ? 'selected="selected"' : null ?>><?= l('campaigns.segment.all') ?></option>
                        <?php if (!empty($data->segments)): ?>
                            <optgroup label="<?= l('campaigns.segment.saved') ?>">
                                <?php foreach($data->segments as $segment): ?>
                                    <option value="<?= $segment->segment_id ?>" <?= $data->values['segment'] == $segment->segment_id ? 'selected="selected"' : null ?>><?= $segment->name ?></option>
                                <?php endforeach ?>
                            </optgroup>
                        <?php endif ?>
                    </select>
                    <?= \Altum\Alerts::output_field_error('segment') ?>
                </div>

                <div class="form-group custom-control custom-switch">
                    <input id="is_enabled" name="is_enabled" type="checkbox" class="custom-control-input" <?= $data->values['is_enabled'] ? 'checked="checked"' : null?>>
                    <label class="custom-control-label" for="is_enabled"><?= l('recurring_campaigns.is_enabled') ?></label>
                </div>

                <button class="btn btn-sm btn-block btn-light my-3" type="button" data-toggle="collapse" data-target="#recurring_container" aria-expanded="false" aria-controls="recurring_container">
                    <i class="fas fa-fw fa-retweet fa-sm mr-1"></i> <?= l('recurring_campaigns.recurring_campaign_settings') ?>
                </button>

                <div class="collapse show" id="recurring_container">
                    <div class="form-group">
                        <label for="frequency"><i class="fas fa-fw fa-sm fa-calendar-alt text-muted mr-1"></i> <?= l('recurring_campaigns.frequency') ?></label>
                        <div class="row btn-group-toggle" data-toggle="buttons">
                            <div class="col-12 col-lg-4">
                                <label class="btn btn-light btn-block text-truncate <?=  $data->values['frequency'] == 'daily' ? 'active"' : null?>">
                                    <input type="radio" name="frequency" value="daily" class="custom-control-input" <?=  $data->values['frequency'] == 'daily' ? 'checked="checked"' : null?> required="required" />
                                    <i class="fas fa-calendar-day fa-fw fa-sm mr-1"></i> <?= l('recurring_campaigns.frequency.daily') ?>
                                </label>
                            </div>

                            <div class="col-12 col-lg-4">
                                <label class="btn btn-light btn-block text-truncate <?=  $data->values['frequency'] == 'weekly' ? 'active"' : null?>">
                                    <input type="radio" name="frequency" value="weekly" class="custom-control-input" <?=  $data->values['frequency'] == 'weekly' ? 'checked="checked"' : null?> required="required" />
                                    <i class="fas fa-calendar-week fa-fw fa-sm mr-1"></i> <?= l('recurring_campaigns.frequency.weekly') ?>
                                </label>
                            </div>

                            <div class="col-12 col-lg-4">
                                <label class="btn btn-light btn-block text-truncate <?=  $data->values['frequency'] == 'monthly' ? 'active"' : null?>">
                                    <input type="radio" name="frequency" value="monthly" class="custom-control-input" <?=  $data->values['frequency'] == 'monthly' ? 'checked="checked"' : null?> required="required" />
                                    <i class="fas fa-calendar-alt fa-fw fa-sm mr-1"></i> <?= l('recurring_campaigns.frequency.monthly') ?>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" data-frequency="weekly">
                        <label for="week_days"><i class="fas fa-fw fa-sm fa-calendar-week text-muted mr-1"></i> <?= l('recurring_campaigns.week_days') ?></label>

                        <div class="row">
                            <?php foreach(range(1,7) as $key): ?>
                                <div class="col-12 col-lg-6">
                                    <div class="custom-control custom-checkbox my-2">
                                        <input id="week_days_<?= $key ?>" name="week_days[]" value="<?= $key ?>" type="checkbox" class="custom-control-input" <?= in_array($key, $data->values['week_days'] ?? []) ? 'checked="checked"' : null ?>>
                                        <label class="custom-control-label" for="week_days_<?= $key ?>">
                                            <span><?= l('global.date.long_days.' . $key) ?></span>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach ?>
                        </div>
                    </div>

                    <div class="form-group" data-frequency="monthly">
                        <label for="month_days"><i class="fas fa-fw fa-sm fa-calendar-alt text-muted mr-1"></i> <?= l('recurring_campaigns.month_days') ?></label>

                        <div class="row">
                            <?php foreach(range(1,31) as $key): ?>
                                <div class="col-2">
                                    <div class="custom-control custom-checkbox my-2">
                                        <input id="month_days_<?= $key ?>" name="month_days[]" value="<?= $key ?>" type="checkbox" class="custom-control-input" <?= in_array($key, $data->values['month_days'] ?? []) ? 'checked="checked"' : null ?>>
                                        <label class="custom-control-label" for="month_days_<?= $key ?>">
                                            <span><?= $key ?></span>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="time"><i class="fas fa-fw fa-sm fa-clock text-muted mr-1"></i> <?= l('recurring_campaigns.time') ?></label>
                        <input type="time" id="time" name="time" class="form-control <?= \Altum\Alerts::has_field_errors('time') ? 'is-invalid' : null ?>" value="<?= $data->values['time'] ?>" required="required" />
                        <?= \Altum\Alerts::output_field_error('time') ?>
                    </div>
                </div>

                <button type="submit" name="submit" class="btn btn-block btn-primary mt-4"><?= l('global.create') ?></button>
            </form>

        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
    'use strict';
    
/* Device */
    let device_handler = () => {
        let device = document.querySelector('#device_id');
        let selected_device = device.options[device.selectedIndex];
        let sims = JSON.parse(selected_device.getAttribute('data-sims'));

        let sim_subscription_id = document.querySelector('#sim_subscription_id');

        /* Clear all existing options */
        sim_subscription_id.options.length = 0;

        if(sims && sims.length) {
            sims.forEach(sim => {
                sim_subscription_id.options.add(new Option(sim.display_name, sim.subscription_id))
            })
        }

        /* Selected */
        let selected_sim_subscription_id = document.querySelector('#sim_subscription_id').getAttribute('data-selected-sim-subscription-id');
        if(!selected_sim_subscription_id) {
            document.querySelector('#sim_subscription_id').selectedIndex = 0;
        } else {
            document.querySelector('#sim_subscription_id').value = selected_sim_subscription_id;
        }
    };

    document.querySelector('#device_id').addEventListener('change', device_handler);

    device_handler();

    type_handler('input[name="frequency"]', 'data-frequency');
    document.querySelector('input[name="frequency"]') && document.querySelectorAll('input[name="frequency"]').forEach(element => element.addEventListener('change', () => { type_handler('input[name="frequency"]', 'data-frequency'); }));


    type_handler('[name="segment"]', 'data-segment');
    document.querySelector('[name="segment"]') && document.querySelectorAll('[name="segment"]').forEach(element => element.addEventListener('change', () => { type_handler('[name="segment"]', 'data-segment'); }));

    document.querySelector('#segment').addEventListener('change', async event => {
        await get_segment_count();
    });

    document.querySelectorAll('[name^="filters_"]').forEach(element => element.addEventListener('change', async event => {
        await get_segment_count();
    }));

    let get_segment_count = async () => {
        let segment = document.querySelector('#segment').value;

        if(segment == 'custom' || segment == 'bulk') {
            document.querySelector('#segment_count').innerHTML = ``;
            return;
        }

        /* Display a loader */
        document.querySelector('#segment_count').innerHTML = `<div class="spinner-border spinner-border-sm" role="status"></div>`;

        /* Prepare query string */
        let query = new URLSearchParams();

        /* Filter preparing on query string */
        if(segment == 'filter') {
            query = new URLSearchParams(new FormData(document.querySelector('#form')));
        }

        query.set('type', segment);

        /* Send request to server */
        let response = await fetch(`${url}segments/get_segment_count?${query.toString()}`, {
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
            document.querySelector('#segment_count').innerHTML = `(${data.details.count})`;
        }
    }

    get_segment_count();
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>

<?php include_view(THEME_PATH . 'views/partials/clipboard_js.php') ?>
