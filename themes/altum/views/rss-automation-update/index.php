<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('rss-automations') ?>"><?= l('rss_automations.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('rss_automation_update.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <h1 class="h4 text-truncate"><i class="fas fa-fw fa-xs fa-rss mr-1"></i> <?= l('rss_automation_update.header') ?></h1>
    <p></p>

    <div class="card">
        <div class="card-body">

            <form id="form" action="" method="post" role="form" enctype="multipart/form-data">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="form-group">
                    <label for="name"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('global.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control <?= \Altum\Alerts::has_field_errors('name') ? 'is-invalid' : null ?>" value="<?= $data->rss_automation->name ?>" required="required" />
                    <?= \Altum\Alerts::output_field_error('name') ?>
                </div>

                <div class="form-group">
                    <label for="rss_url"><i class="fas fa-fw fa-sm fa-rss text-muted mr-1"></i> <?= l('rss_automations.rss_url') ?></label>
                    <input type="text" id="rss_url" name="rss_url" class="form-control <?= \Altum\Alerts::has_field_errors('rss_url') ? 'is-invalid' : null ?>" value="<?= $data->rss_automation->rss_url ?>" placeholder="<?= l('rss_automations.rss_url_placeholder') ?>" required="required" />
                    <?= \Altum\Alerts::output_field_error('rss_url') ?>
                    <small class="form-text text-muted"><?= l('rss_automations.rss_url_help') ?></small>
                </div>

                <div class="form-group" data-character-counter="textarea">
                    <label for="content" class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-fw fa-sm fa-heading text-muted mr-1"></i> <?= l('sms.content') ?></span>
                        <small class="text-muted" data-character-counter-wrapper></small>
                    </label>
                    <textarea id="content" name="content" class="form-control <?= \Altum\Alerts::has_field_errors('content') ? 'is-invalid' : null ?>" maxlength="1000" required="required"><?= $data->rss_automation->content ?></textarea>
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
                                <?= $data->rss_automation->device_id == $device->device_id ? 'selected="selected"' : null ?>
                            >
                                <?= $device->name ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                    <?= \Altum\Alerts::output_field_error('device_id') ?>
                </div>

                <div class="form-group">
                    <label for="sim_subscription_id"><i class="fas fa-fw fa-sm fa-sim-card text-muted mr-1"></i> <?= l('devices.sim_subscription_id') ?></label>
                    <select id="sim_subscription_id" name="sim_subscription_id" class="form-control <?= \Altum\Alerts::has_field_errors('sim_subscription_id') ? 'is-invalid' : null ?>" required="required" data-selected-sim-subscription-id="<?= $data->rss_automation->sim_subscription_id ?>"></select>
                    <?= \Altum\Alerts::output_field_error('sim_subscription_id') ?>
                </div>

                <div class="form-group">
                    <div class="d-flex flex-wrap flex-row justify-content-between">
                        <label for="segment"><i class="fas fa-fw fa-sm fa-layer-group text-muted mr-1"></i> <?= l('campaigns.segment') ?> <span id="segment_count"></span></label>
                        <a href="<?= url('segment-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('segments.create') ?></a>
                    </div>
                    <select id="segment" name="segment" class="form-control <?= \Altum\Alerts::has_field_errors('segment') ? 'is-invalid' : null ?>" required="required">
                        <option value="all" <?= $data->rss_automation->segment == 'all' ? 'selected="selected"' : null ?>><?= l('campaigns.segment.all') ?></option>
                        <?php if (!empty($data->segments)): ?>
                            <optgroup label="<?= l('campaigns.segment.saved') ?>">
                                <?php foreach($data->segments as $segment): ?>
                                    <option value="<?= $segment->segment_id ?>" <?= $data->rss_automation->segment == $segment->segment_id ? 'selected="selected"' : null ?>><?= $segment->name ?></option>
                                <?php endforeach ?>
                            </optgroup>
                        <?php endif ?>
                    </select>
                    <?= \Altum\Alerts::output_field_error('segment') ?>
                </div>

                <div class="form-group custom-control custom-switch">
                    <input id="is_enabled" name="is_enabled" type="checkbox" class="custom-control-input" <?= $data->rss_automation->is_enabled ? 'checked="checked"' : null?>>
                    <label class="custom-control-label" for="is_enabled"><?= l('rss_automations.is_enabled') ?></label>
                </div>

                <button class="btn btn-sm btn-block btn-light my-3" type="button" data-toggle="collapse" data-target="#rss_container" aria-expanded="false" aria-controls="rss_container">
                    <i class="fas fa-fw fa-rss fa-sm mr-1"></i> <?= l('rss_automations.rss') ?>
                </button>

                <div class="collapse" id="rss_container">
                    <div class="form-group">
                        <label for="check_interval_seconds"><i class="fas fa-fw fa-sm fa-sync text-muted mr-1"></i> <?= l('rss_automations.check_interval_seconds') ?></label>
                        <select id="check_interval_seconds" name="check_interval_seconds" class="custom-select" required="required">
                            <?php foreach($data->rss_automations_check_intervals as $key => $value): ?>
                                <option value="<?= $key ?>" <?= $data->rss_automation->settings->check_interval_seconds == $key ? 'selected="selected"' : null ?>><?= $value ?></option>
                            <?php endforeach ?>
                        </select>
                        <small class="form-text text-muted"><?= l('rss_automations.check_interval_seconds_help') ?></small>
                    </div>

                    <div class="form-group">
                        <label for="items_count"><i class="fas fa-fw fa-sm fa-list-ol text-muted mr-1"></i> <?= l('rss_automations.items_count') ?></label>
                        <div class="input-group">
                            <input type="number" min="1" max="100" id="items_count" name="items_count" class="form-control <?= \Altum\Alerts::has_field_errors('items_count') ? 'is-invalid' : null ?>" value="<?= $data->rss_automation->settings->items_count ?>" required="required" />

                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <?= l('rss_automations.items') ?>
                                </span>
                            </div>
                        </div>
                        <?= \Altum\Alerts::output_field_error('items_count') ?>
                    </div>

                    <div class="form-group">
                        <label for="campaigns_delay"><i class="fas fa-fw fa-sm fa-hourglass-half text-muted mr-1"></i> <?= l('rss_automations.campaigns_delay') ?></label>
                        <div class="input-group">
                            <input type="number" min="5" max="1440" id="campaigns_delay" name="campaigns_delay" class="form-control <?= \Altum\Alerts::has_field_errors('campaigns_delay') ? 'is-invalid' : null ?>" value="<?= $data->rss_automation->settings->campaigns_delay ?>" required="required" />

                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <?= l('global.date.minutes') ?>
                                </span>
                            </div>
                        </div>
                        <?= \Altum\Alerts::output_field_error('campaigns_delay') ?>
                    </div>

                    <div class="form-group">
                        <label for="unique_item_identifier"><i class="fas fa-fw fa-sm fa-hashtag text-muted mr-1"></i> <?= l('rss_automations.unique_item_identifier') ?></label>
                        <select id="unique_item_identifier" name="unique_item_identifier" class="custom-select" required="required">
                            <?php foreach(['url', 'publication_date', 'id'] as $key): ?>
                                <option value="<?= $key ?>" <?= $data->rss_automation->settings->unique_item_identifier == $key ? 'selected="selected"' : null ?>><?= l('rss_automations.unique_item_identifier.' . $key) ?></option>
                            <?php endforeach ?>
                        </select>
                        <small class="form-text text-muted"><?= l('rss_automations.unique_item_identifier_help') ?></small>
                    </div>
                </div>

                <button type="submit" name="submit" class="btn btn-block btn-primary mt-4"><?= l('global.update') ?></button>
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
        console.log(selected_sim_subscription_id);
        if(!selected_sim_subscription_id) {
            document.querySelector('#sim_subscription_id').selectedIndex = 0;
        } else {
            document.querySelector('#sim_subscription_id').value = selected_sim_subscription_id;
            document.querySelector('#sim_subscription_id').dispatchEvent(new Event('change'));
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
