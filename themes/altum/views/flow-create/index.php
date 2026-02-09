<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('flows') ?>"><?= l('flows.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('flow_create.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <h1 class="h4 text-truncate"><i class="fas fa-fw fa-xs fa-tasks mr-1"></i> <?= l('flow_create.header') ?></h1>
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

                <div class="form-row">
                    <div class="form-group col">
                        <label for="wait_time"><i class="fas fa-fw fa-sm fa-sync text-muted mr-1"></i> <?= l('flows.wait_time') ?></label>
                        <input type="number" min="1" step="1" id="wait_time" name="wait_time" class="form-control" value="<?= $data->values['wait_time'] ?>" />
                        <small class="form-text text-muted"><?= l('flows.wait_time_help') ?></small>
                    </div>

                    <div class="form-group col">
                        <label>&nbsp;</label>
                        <select id="wait_time_type" name="wait_time_type" class="custom-select">
                            <option value="minutes" <?= $data->values['wait_time_type'] == 'minutes' ? 'selected="selected"' : null ?>><?= l('global.date.minutes') ?></option>
                            <option value="hours" <?= $data->values['wait_time_type'] == 'hours' ? 'selected="selected"' : null ?>><?= l('global.date.hours') ?></option>
                            <option value="days" <?= $data->values['wait_time_type'] == 'days' ? 'selected="selected"' : null ?>><?= l('global.date.days') ?></option>
                        </select>
                    </div>
                </div>

                <div class="form-group" data-character-counter="textarea">
                    <label for="content" class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-fw fa-sm fa-heading text-muted mr-1"></i> <?= l('sms.content') ?></span>
                        <small class="text-muted" data-character-counter-wrapper></small>
                    </label>
                    <textarea id="content" name="content" class="form-control <?= \Altum\Alerts::has_field_errors('content') ? 'is-invalid' : null ?>" maxlength="1000" required="required"><?= $data->values['content'] ?></textarea>
                    <?= \Altum\Alerts::output_field_error('content') ?>
                    <small class="form-text text-muted"><?= l('campaigns.content_help') ?></small>
                    <small class="form-text text-muted"><?= sprintf(l('global.variables'), '<code data-copy>' . implode('</code> , <code data-copy>',  ['{{NAME}}', '{{PHONE_NUMBER}}', '{{CONTINENT_NAME}}', '{{COUNTRY_NAME}}', '{{CUSTOM_PARAMETERS:KEY}}']) . '</code>') ?></small>
                    <small class="form-text text-muted"><?= l('global.spintax_help') ?></small>
                </div>

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
                    <label class="custom-control-label" for="is_enabled"><?= l('flows.is_enabled') ?></label>
                </div>

                <button type="submit" name="submit" class="btn btn-block btn-primary"><?= l('global.create') ?></button>
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
