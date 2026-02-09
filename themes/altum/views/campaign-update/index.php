<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('campaigns') ?>"><?= l('campaigns.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li>
                    <a href="<?= url('campaign/' . $data->campaign->campaign_id) ?>"><?= l('campaign.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('campaign_update.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <div class="d-flex justify-content-between mb-4">
        <h1 class="h4 text-truncate mb-0"><i class="fas fa-fw fa-xs fa-rocket mr-1"></i> <?= l('campaign_update.header') ?></h1>

        <?= include_view(THEME_PATH . 'views/campaigns/campaign_dropdown_button.php', ['id' => $data->campaign->campaign_id, 'resource_name' => $data->campaign->name,]) ?>
    </div>

    <div class="card">
        <div class="card-body">

            <form id="form" action="" method="post" role="form" enctype="multipart/form-data">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                <div class="form-group">
                    <label for="name"><i class="fas fa-fw fa-signature fa-sm text-muted mr-1"></i> <?= l('global.name') ?></label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= $data->campaign->name ?>" required="required" />
                </div>

                <div class="form-group" data-character-counter="textarea">
                    <label for="content" class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-fw fa-sm fa-heading text-muted mr-1"></i> <?= l('sms.content') ?></span>
                        <small class="text-muted" data-character-counter-wrapper></small>
                    </label>
                    <textarea id="content" name="content" class="form-control <?= \Altum\Alerts::has_field_errors('content') ? 'is-invalid' : null ?>" maxlength="1000" required="required" <?= $data->campaign->status == 'sent' ? 'disabled="disabled"' : null ?>><?= $data->campaign->content ?></textarea>
                    <?= \Altum\Alerts::output_field_error('content') ?>
                    <small class="form-text text-muted"><?= l('campaigns.content_help') ?></small>
                    <small class="form-text text-muted"><?= sprintf(l('global.variables'), '<code data-copy>' . implode('</code> , <code data-copy>',  ['{{NAME}}', '{{PHONE_NUMBER}}', '{{CONTINENT_NAME}}', '{{COUNTRY_NAME}}', '{{CUSTOM_PARAMETERS:KEY}}']) . '</code>') ?></small>
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
                                    <?= $data->campaign->device_id == $device->device_id ? 'selected="selected"' : null ?>
                            >
                                <?= $device->name ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                    <?= \Altum\Alerts::output_field_error('device_id') ?>
                </div>

                <div class="form-group">
                    <label for="sim_subscription_id"><i class="fas fa-fw fa-sm fa-sim-card text-muted mr-1"></i> <?= l('devices.sim_subscription_id') ?></label>
                    <select id="sim_subscription_id" name="sim_subscription_id" class="form-control <?= \Altum\Alerts::has_field_errors('sim_subscription_id') ? 'is-invalid' : null ?>" required="required" data-selected-sim-subscription-id="<?= $data->campaign->sim_subscription_id ?>"></select>
                    <?= \Altum\Alerts::output_field_error('sim_subscription_id') ?>
                </div>

                <div class="form-group">
                    <div class="d-flex flex-wrap flex-row justify-content-between">
                        <label for="segment"><i class="fas fa-fw fa-sm fa-layer-group text-muted mr-1"></i> <?= l('campaigns.segment') ?> <span id="segment_count"></span></label>
                        <a href="<?= url('segment-create') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('segments.create') ?></a>
                    </div>
                    <select id="segment" name="segment" class="form-control <?= \Altum\Alerts::has_field_errors('segment') ? 'is-invalid' : null ?>" required="required" <?= $data->campaign->status == 'sent' ? 'disabled="disabled"' : null ?>>
                        <option value="all" <?= $data->campaign->segment == 'all' ? 'selected="selected"' : null ?>><?= l('campaigns.segment.all') ?></option>
                        <option value="bulk" <?= $data->values['segment'] == 'bulk' ? 'selected="selected"' : null ?>><?= l('campaigns.segment.bulk') ?></option>
                        <option value="custom" <?= $data->campaign->segment == 'custom' ? 'selected="selected"' : null ?>><?= l('campaigns.segment.custom') ?></option>
                        <option value="filter" <?= $data->campaign->segment == 'filter' ? 'selected="selected"' : null ?>><?= l('campaigns.segment.filter') ?></option>
                        <?php if (!empty($data->segments)): ?>
                        <optgroup label="<?= l('campaigns.segment.saved') ?>">
                            <?php foreach($data->segments as $segment): ?>
                            <option value="<?= $segment->segment_id ?>" <?= $data->campaign->segment == $segment->segment_id ? 'selected="selected"' : null ?>><?= $segment->name ?></option>
                            <?php endforeach ?>
                        </optgroup>
                        <?php endif ?>
                    </select>
                    <?= \Altum\Alerts::output_field_error('segment') ?>
                </div>

                <div class="form-group" data-segment="bulk">
                    <label for="phone_numbers"><i class="fas fa-fw fa-sm fa-address-book text-muted mr-1"></i> <?= l('campaigns.phone_numbers') ?></label>
                    <textarea id="phone_numbers" name="phone_numbers" class="form-control <?= \Altum\Alerts::has_field_errors('phone_numbers') ? 'is-invalid' : null ?>" required="required" placeholder="<?= l('contacts.phone_number_placeholder') . "\r\n" . l('contacts.phone_number_placeholder') ?>"><?= $data->campaign->settings->phone_numbers ?? '' ?></textarea>
                    <?= \Altum\Alerts::output_field_error('phone_numbers') ?>
                    <small class="form-text text-muted"><?= l('campaigns.phone_numbers_help') ?></small>
                </div>

                <div class="form-group" data-segment="custom">
                    <label for="contacts_ids"><i class="fas fa-fw fa-sm fa-users text-muted mr-1"></i> <?= l('campaigns.contacts_ids') ?></label>
                    <input type="text" id="contacts_ids" name="contacts_ids" value="<?= $data->campaign->contacts_ids ?>" class="form-control <?= \Altum\Alerts::has_field_errors('contacts_ids') ? 'is-invalid' : null ?>" placeholder="<?= l('campaigns.contacts_ids_placeholder') ?>" required="required" <?= $data->campaign->status == 'sent' ? 'disabled="disabled"' : null ?> />
                    <?= \Altum\Alerts::output_field_error('contacts_ids') ?>
                    <small class="form-text text-muted"><?= l('campaigns.contacts_ids_help') ?></small>
                </div>

                <div class="form-group" data-segment="filter">
                    <div class="form-group">
                        <label for="filters_continents"><i class="fas fa-fw fa-sm fa-globe-europe text-muted mr-1"></i> <?= l('global.continents') ?></label>
                        <select id="filters_continents" name="filters_continents[]" class="custom-select" multiple="multiple" <?= $data->campaign->status == 'sent' ? 'disabled="disabled"' : null ?>>
                            <?php foreach(get_continents_array() as $continent_code => $continent_name): ?>
                                <option value="<?= $continent_code ?>" <?= in_array($continent_code, $data->campaign->settings->filters_continents ?? []) ? 'selected="selected"' : null ?>><?= $continent_name ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>

                <div class="form-group" data-segment="filter">
                    <div class="form-group">
                        <label for="filters_countries"><i class="fas fa-fw fa-sm fa-flag text-muted mr-1"></i> <?= l('global.countries') ?></label>
                        <select id="filters_countries" name="filters_countries[]" class="custom-select" multiple="multiple" <?= $data->campaign->status == 'sent' ? 'disabled="disabled"' : null ?>>
                            <?php foreach(get_countries_array() as $key => $value): ?>
                                <option value="<?= $key ?>" <?= in_array($key,$data->campaign->settings->filters_countries ?? []) ? 'selected="selected"' : null ?>><?= $value ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>

                <div class="form-group" data-segment="filter">
                    <label><i class="fas fa-fw fa-fingerprint fa-sm text-muted mr-1"></i> <?= l('contacts.custom_parameters') ?></label>
                    <div id="custom_parameters">
                        <?php foreach($data->campaign->settings->filters_custom_parameters ?? [] as $key => $custom_parameter): ?>
                            <div class="custom_parameter p-3 bg-gray-50 rounded mb-4">
                                <div class="form-row">
                                    <div class="form-group col-lg-4">
                                        <label for="<?= 'filters_custom_parameter_key[' . $key . ']' ?>"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('contacts.custom_parameter_key') ?></label>
                                        <input id="<?= 'filters_custom_parameter_key[' . $key . ']' ?>" type="text" name="filters_custom_parameter_key[<?= $key ?>]" class="form-control" value="<?= $custom_parameter->key ?>" required="required" />
                                    </div>

                                    <div class="form-group col-lg-4">
                                        <label for="<?= 'filters_custom_parameter_condition[' . $key . ']' ?>"><i class="fas fa-fw fa-sm fa-code text-muted mr-1"></i> <?= l('segments.custom_parameter_condition') ?></label>
                                        <select id="<?= 'filters_custom_parameter_condition[' . $key . ']' ?>" name="filters_custom_parameter_condition[<?= $key ?>]" class="form-control" required="required">
                                            <?php foreach(['exact', 'not_exact', 'contains', 'not_contains', 'starts_with', 'not_starts_with', 'ends_with', 'not_ends_with', 'bigger_than', 'lower_than'] as $condition): ?>
                                                <option value="<?= $condition ?>" <?= ($custom_parameter->condition ?? 'exact') == $condition ? 'selected="selected"' : null ?>><?= l('segments.' . $condition) ?></option>
                                            <?php endforeach ?>
                                        </select>
                                    </div>

                                    <div class="form-group col-lg-4">
                                        <label for="<?= 'filters_custom_parameter_value[' . $key . ']' ?>"><i class="fas fa-fw fa-sm fa-pen text-muted mr-1"></i> <?= l('contacts.custom_parameter_value') ?></label>
                                        <input id="<?= 'filters_custom_parameter_value[' . $key . ']' ?>" type="text" name="filters_custom_parameter_value[<?= $key ?>]" class="form-control" value="<?= $custom_parameter->value ?>" required="required" />
                                    </div>
                                </div>

                                <button type="button" data-remove="custom_parameters" class="btn btn-block btn-outline-danger"><i class="fas fa-fw fa-times fa-sm mr-1"></i> <?= l('global.delete') ?></button>
                            </div>
                        <?php endforeach ?>
                    </div>

                    <div class="mb-4">
                        <button data-add="custom_parameters" type="button" class="btn btn-block btn-outline-success"><i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('global.create') ?></button>
                    </div>
                </div>


                <button class="btn btn-sm btn-block btn-light my-3" type="button" data-toggle="collapse" data-target="#scheduling_container" aria-expanded="false" aria-controls="scheduling_container">
                    <i class="fas fa-fw fa-calendar-day fa-sm mr-1"></i> <?= l('campaigns.scheduling') ?>
                </button>

                <div class="collapse" id="scheduling_container">
                    <div class="form-group custom-control custom-switch">
                        <input
                                id="is_scheduled"
                                name="is_scheduled"
                                type="checkbox"
                                class="custom-control-input"
                            <?= $data->campaign->settings->is_scheduled && !empty($data->campaign->scheduled_datetime) ? 'checked="checked"' : null ?>
                            <?= $data->campaign->status == 'sent' ? 'disabled="disabled"' : null ?>
                        >
                        <label class="custom-control-label" for="is_scheduled"><?= l('campaigns.is_scheduled') ?></label>
                    </div>

                    <div id="is_scheduled_container" class="d-none">
                        <div class="form-group">
                            <label for="scheduled_datetime"><i class="fas fa-fw fa-calendar-day fa-sm text-muted mr-1"></i> <?= l('campaigns.scheduled_datetime') ?></label>
                            <input
                                    id="scheduled_datetime"
                                    type="text"
                                    class="form-control"
                                    name="scheduled_datetime"
                                    value="<?= (new \DateTime($data->campaign->scheduled_datetime, new \DateTimeZone(\Altum\Date::$default_timezone)))->setTimezone(new \DateTimeZone($this->user->timezone))->format('Y-m-d H:i:s'); ?>"
                                    placeholder="<?= l('campaigns.scheduled_datetime') ?>"
                                    autocomplete="off"
                                    data-daterangepicker
                                    <?= $data->campaign->status == 'sent' ? 'disabled="disabled"' : null ?>
                            />
                        </div>
                    </div>
                </div>

                <?php if($data->campaign->status == 'sent'): ?>
                    <button type="submit" name="save" class="btn btn-block btn-primary mt-3"><?= l('global.update') ?></button>
                <?php else: ?>
                    <button type="submit" name="save" class="btn btn-sm btn-block btn-outline-primary mt-4"><?= l('campaigns.save') ?></button>
                    <button type="submit" name="send" class="btn btn-block btn-primary mt-3"><?= l('campaigns.send') ?></button>
                <?php endif ?>
            </form>

        </div>
    </div>
</div>

<template id="template_custom_parameters">
    <div class="custom_parameter p-3 bg-gray-50 rounded mb-4">
        <div class="form-row">
            <div class="form-group col-lg-4">
                <label for="filters_custom_parameter_key"><i class="fas fa-fw fa-sm fa-signature text-muted mr-1"></i> <?= l('segments.custom_parameter_key') ?></label>
                <input id="filters_custom_parameter_key" type="text" name="filters_custom_parameter_key[]" class="form-control" value="" required="required" />
            </div>

            <div class="form-group col-lg-4">
                <label for="filters_custom_parameter_condition"><i class="fas fa-fw fa-sm fa-code text-muted mr-1"></i> <?= l('segments.custom_parameter_condition') ?></label>
                <select id="filters_custom_parameter_condition" name="filters_custom_parameter_condition[]" class="form-control" required="required">
                    <?php foreach(['exact', 'not_exact', 'contains', 'not_contains', 'starts_with', 'not_starts_with', 'ends_with', 'not_ends_with', 'bigger_than', 'lower_than'] as $condition): ?>
                        <option value="<?= $condition ?>"><?= l('segments.' . $condition) ?></option>
                    <?php endforeach ?>
                </select>
            </div>

            <div class="form-group col-lg-4">
                <label for="filters_custom_parameter_value"><i class="fas fa-fw fa-sm fa-pen text-muted mr-1"></i> <?= l('segments.custom_parameter_value') ?></label>
                <input id="filters_custom_parameter_value" type="text" name="filters_custom_parameter_value[]" class="form-control" value="" required="required" />
            </div>
        </div>

        <button type="button" data-remove="request" class="btn btn-block btn-outline-danger"><i class="fas fa-fw fa-times"></i> <?= l('global.delete') ?></button>
    </div>
</template>

<?php ob_start() ?>
<link href="<?= ASSETS_FULL_URL . 'css/libraries/daterangepicker.min.css?v=' . PRODUCT_CODE ?>" rel="stylesheet" media="screen,print">
<?php \Altum\Event::add_content(ob_get_clean(), 'head') ?>

<?php ob_start() ?>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/moment.min.js?v=' . PRODUCT_CODE ?>"></script>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/daterangepicker.min.js?v=' . PRODUCT_CODE ?>"></script>
<script src="<?= ASSETS_FULL_URL . 'js/libraries/moment-timezone-with-data-10-year-range.min.js?v=' . PRODUCT_CODE ?>"></script>

<script>
    'use strict';
    
/* Schedule */
    let schedule_handler = () => {
        if(document.querySelector('#is_scheduled').checked) {
            document.querySelector('#is_scheduled_container').classList.remove('d-none');
        } else {
            document.querySelector('#is_scheduled_container').classList.add('d-none');
        }
    };

    document.querySelector('#is_scheduled').addEventListener('change', schedule_handler);

    schedule_handler();

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

    /* Daterangepicker */
    let locale = <?= json_encode(require APP_PATH . 'includes/daterangepicker_translations.php') ?>;
    $('[data-daterangepicker]').daterangepicker({
        minDate: "<?= (new \DateTime('', new \DateTimeZone(\Altum\Date::$default_timezone)))->setTimezone(new \DateTimeZone($this->user->timezone))->format('Y-m-d H:i:s'); ?>",
        alwaysShowCalendars: true,
        singleCalendar: true,
        singleDatePicker: true,
        locale: {...locale, format: 'YYYY-MM-DD HH:mm:ss'},
        timePicker: true,
        timePicker24Hour: true,
        timePickerSeconds: true,
    }, (start, end, label) => {});

    /* add new custom parameter */
    let add = async event => {
        let type = event.currentTarget.getAttribute('data-add');
        let clone = document.querySelector(`#template_${type}`).content.cloneNode(true);
        let count = document.querySelectorAll(`#${type} .mb-4`).length;

        if(count >= 50) return;

        clone.querySelector(`input[name="filters_custom_parameter_key[]"`).closest('.form-group').querySelector('label').setAttribute('for', `filters_custom_parameter_key_${count}`);
        clone.querySelector(`input[name="filters_custom_parameter_key[]"`).setAttribute('id', `filters_custom_parameter_key_${count}`);
        clone.querySelector(`input[name="filters_custom_parameter_key[]"`).setAttribute('name', `filters_custom_parameter_key[${count}]`);

        clone.querySelector(`select[name="filters_custom_parameter_condition[]"`).closest('.form-group').querySelector('label').setAttribute('for', `filters_custom_parameter_condition_${count}`);
        clone.querySelector(`select[name="filters_custom_parameter_condition[]"`).setAttribute('id', `filters_custom_parameter_condition_${count}`);
        clone.querySelector(`select[name="filters_custom_parameter_condition[]"`).setAttribute('name', `filters_custom_parameter_condition[${count}]`);

        clone.querySelector(`input[name="filters_custom_parameter_value[]"`).closest('.form-group').querySelector('label').setAttribute('for', `filters_custom_parameter_value_${count}`);
        clone.querySelector(`input[name="filters_custom_parameter_value[]"`).setAttribute('id', `filters_custom_parameter_value_${count}`);
        clone.querySelector(`input[name="filters_custom_parameter_value[]"`).setAttribute('name', `filters_custom_parameter_value[${count}]`);

        document.querySelector(`#${type}`).appendChild(clone);

        remove_initiator();
        initiate_filters_listener();
    };

    document.querySelectorAll('[data-add]').forEach(element => {
        element.addEventListener('click', add);
    })

    /* remove  */
    let remove = event => {
        event.currentTarget.closest('.custom_parameter').remove();
    };

    let remove_initiator = () => {
        document.querySelectorAll('#custom_parameters [data-remove]').forEach(element => {
            element.removeEventListener('click', remove);
            element.addEventListener('click', remove)
        })
    };

    remove_initiator();

    type_handler('[name="segment"]', 'data-segment');
    document.querySelector('[name="segment"]') && document.querySelectorAll('[name="segment"]').forEach(element => element.addEventListener('change', () => { type_handler('[name="segment"]', 'data-segment'); }));

    document.querySelector('#segment').addEventListener('change', async event => {
        await get_segment_count();
    });

    let initiate_filters_listener = () => {
        document.querySelectorAll('[name^="filters_"]').forEach(element => element.removeEventListener('change', async event => await get_segment_count()));
        document.querySelectorAll('[name^="filters_"]').forEach(element => element.addEventListener('change', async event => await get_segment_count()));
    }
    initiate_filters_listener();

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
