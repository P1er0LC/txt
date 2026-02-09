<?php
/*
 * Copyright (c) 2025 AltumCode (https://altumcode.com/)
 *
 * This software is licensed exclusively by AltumCode and is sold only via https://altumcode.com/.
 * Unauthorized distribution, modification, or use of this software without a valid license is not permitted and may be subject to applicable legal actions.
 *
 * 🌍 View all other existing AltumCode projects via https://altumcode.com/
 * 📧 Get in touch for support or general queries via https://altumcode.com/contact
 * 📤 Download the latest version via https://altumcode.com/downloads
 *
 * 🐦 X/Twitter: https://x.com/AltumCode
 * 📘 Facebook: https://facebook.com/altumcode
 * 📸 Instagram: https://instagram.com/altumcode
 */

namespace Altum\models;

defined('ALTUMCODE') || die();

class Contacts extends Model {

    public function get_contact_by_phone_number($phone_number) {

        /* Try to check if the store posts exists via the cache */
        $cache_instance = cache()->getItem('contact?phone_number=' . md5($phone_number));

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            /* Get data from the database */
            $data = db()->where('phone_number', $phone_number)->getOne('contacts');

            if($data) {
                /* Save to cache */
                cache()->save(
                    $cache_instance->set($data)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('user_id=' . $data->user_id)
                );
            }

        } else {

            /* Get cache */
            $data = $cache_instance->get();

        }

        return $data;
    }

    public function get_contact_by_contact_id($contact_id) {

        /* Try to check if the store posts exists via the cache */
        $cache_instance = cache()->getItem('contact?contact_id=' . $contact_id);

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            /* Get data from the database */
            $data = db()->where('contact_id', $contact_id)->getOne('contacts');

            if($data) {
                /* Save to cache */
                cache()->save(
                    $cache_instance->set($data)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('user_id=' . $data->user_id)
                );
            }

        } else {

            /* Get cache */
            $data = $cache_instance->get();

        }

        return $data;
    }

    public function simple_bulk_insert($phone_numbers = []) {
        $inserted_contacts_ids = [];

        foreach($phone_numbers as $phone_number) {
            $country_code = null;
            try {
                $phone_number_util = \libphonenumber\PhoneNumberUtil::getInstance();
                $phone_number_object = $phone_number_util->parse($phone_number, null);
                $country_code = $phone_number_util->getRegionCodeForNumber($phone_number_object);
            } catch (\Exception $exception) {
                /* :) */
            }

            $continent_code = get_continent_code_from_country_code($country_code);

            /* Insert / update in the database */
            db()->onDuplicate(['phone_number'], 'contact_id')->insert('contacts', [
                'user_id' => user()->user_id,
                'phone_number' => $phone_number,
                'custom_parameters' => '[]',
                'continent_code' => $continent_code,
                'country_code' => $country_code,
                'datetime' => get_date(),
            ]);

            $inserted_contacts_ids[] = db()->getInsertId();
        }

        return $inserted_contacts_ids;
    }

}
