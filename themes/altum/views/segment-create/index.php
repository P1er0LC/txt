<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
        <nav aria-label="breadcrumb">
            <ol class="custom-breadcrumbs small">
                <li>
                    <a href="<?= url('segments') ?>"><?= l('segments.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
                </li>
                <li class="active" aria-current="page"><?= l('segment_create.breadcrumb') ?></li>
            </ol>
        </nav>
    <?php endif ?>

    <h1 class="h4 text-truncate"><i class="fas fa-fw fa-xs fa-layer-group mr-1"></i> <?= l('segment_create.header') ?></h1>
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

                <div class="form-group">
                    <label for="type"><i class="fas fa-fw fa-sm fa-layer-group text-muted mr-1"></i> <?= l('global.type') ?> <span id="segment_count"></span></label>
                    <select id="type" name="type" class="form-control <?= \Altum\Alerts::has_field_errors('type') ? 'is-invalid' : null ?>" required="required">
                        <option value="bulk" <?= $data->values['type'] == 'bulk' ? 'selected="selected"' : null ?>><?= l('segments.type.bulk') ?></option>
                        <option value="custom" <?= $data->values['type'] == 'custom' ? 'selected="selected"' : null ?>><?= l('segments.type.custom') ?></option>
                        <option value="filter" <?= $data->values['type'] == 'filter' ? 'selected="selected"' : null ?>><?= l('segments.type.filter') ?></option>
                    </select>
                    <?= \Altum\Alerts::output_field_error('segment') ?>
                </div>

                <div class="form-group" data-type="bulk">
                    <label for="phone_numbers"><i class="fas fa-fw fa-sm fa-address-book text-muted mr-1"></i> <?= l('campaigns.phone_numbers') ?></label>
                    <textarea id="phone_numbers" name="phone_numbers" class="form-control <?= \Altum\Alerts::has_field_errors('phone_numbers') ? 'is-invalid' : null ?>" required="required" placeholder="<?= l('contacts.phone_number_placeholder') . "\r\n" . l('contacts.phone_number_placeholder') ?>"><?= $data->values['phone_numbers'] ?></textarea>
                    <?= \Altum\Alerts::output_field_error('phone_numbers') ?>
                    <small class="form-text text-muted"><?= l('campaigns.phone_numbers_help') ?></small>
                </div>

                <div class="form-group" data-type="custom">
                    <label for="contacts_ids"><i class="fas fa-fw fa-sm fa-address-book text-muted mr-1"></i> <?= l('segments.contacts_ids') ?></label>
                    <input type="text" id="contacts_ids" name="contacts_ids" value="<?= $data->values['contacts_ids'] ?>" class="form-control <?= \Altum\Alerts::has_field_errors('contacts_ids') ? 'is-invalid' : null ?>" placeholder="<?= l('segments.contacts_ids_placeholder') ?>" required="required" />
                    <?= \Altum\Alerts::output_field_error('contacts_ids') ?>
                    <small class="form-text text-muted"><?= l('segments.contacts_ids_help') ?></small>
                </div>

                <div class="form-group" data-type="filter">
                    <div class="form-group">
                        <label for="filters_continents"><i class="fas fa-fw fa-sm fa-globe-europe text-muted mr-1"></i> <?= l('global.continents') ?></label>
                        <select id="filters_continents" name="filters_continents[]" class="custom-select" multiple="multiple">
                            <?php foreach(get_continents_array() as $continent_code => $continent_name): ?>
                                <option value="<?= $continent_code ?>" <?= in_array($continent_code,$data->values['filters_continents'] ?? []) ? 'selected="selected"' : null ?>><?= $continent_name ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>

                <div class="form-group" data-type="filter">
                    <div class="form-group">
                        <label for="filters_countries"><i class="fas fa-fw fa-sm fa-flag text-muted mr-1"></i> <?= l('global.countries') ?></label>
                        <select id="filters_countries" name="filters_countries[]" class="custom-select" multiple="multiple">
                            <?php foreach(get_countries_array() as $key => $value): ?>
                                <option value="<?= $key ?>" <?= in_array($key, $data->values['filters_countries'] ?? []) ? 'selected="selected"' : null ?>><?= $value ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>

                <div class="form-group" data-type="filter">
                    <label><i class="fas fa-fw fa-fingerprint fa-sm text-muted mr-1"></i> <?= l('contacts.custom_parameters') ?></label>
                    <div id="custom_parameters">
                        <?php foreach($data->values['filters_custom_parameters'] ?? [] as $key => $custom_parameter): ?>
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

                <button type="submit" name="submit" class="btn btn-block btn-primary mt-4"><?= l('global.create') ?></button>
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
<script>
    'use strict';
    
/* add new  */
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

    type_handler('[name="type"]', 'data-type');
    document.querySelector('[name="type"]') && document.querySelectorAll('[name="type"]').forEach(element => element.addEventListener('change', () => { type_handler('[name="type"]', 'data-type'); }));

    document.querySelector('#type').addEventListener('change', async event => {
        await get_segment_count();
    });

    let initiate_filters_listener = () => {
        document.querySelectorAll('[name^="filters_"]').forEach(element => element.removeEventListener('change', async event => await get_segment_count()));
        document.querySelectorAll('[name^="filters_"]').forEach(element => element.addEventListener('change', async event => await get_segment_count()));
    }
    initiate_filters_listener();

    let get_segment_count = async () => {
        let type = document.querySelector('#type').value;

        if(type == 'custom' || type == 'bulk') {
            document.querySelector('#segment_count').innerHTML = ``;
            return;
        }

        /* Display a loader */
        document.querySelector('#segment_count').innerHTML = `<div class="spinner-border spinner-border-sm" role="status"></div>`;

        /* Prepare query string */
        let query = new URLSearchParams();
        query.set('type', type);

        /* Filter preparing on query string */
        if(type == 'filter') {
            query = new URLSearchParams(new FormData(document.querySelector('#form')));
        }

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
