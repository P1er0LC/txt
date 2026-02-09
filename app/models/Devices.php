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

class Devices extends Model {

    public function get_device_by_device_id($device_id) {

        /* Try to check if the store posts exists via the cache */
        $cache_instance = cache()->getItem('device?device_id=' . $device_id);

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            /* Get data from the database */
            $data = db()->where('device_id', $device_id)->getOne('devices');

            if($data) {
                $data->sims = json_decode($data->sims ?? '');
                $data->settings = json_decode($data->settings ?? '');
				$data->notifications = json_decode($data->notifications ?? '');
				$data->sms_status_notifications = json_decode($data->sms_status_notifications ?? '');

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

    public function get_devices_by_user_id($user_id) {
        if(!$user_id) return [];

        /* Get the user notification handlers */
        $devices = [];

        /* Try to check if the user posts exists via the cache */
        $cache_instance = cache()->getItem('devices?user_id=' . $user_id);

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            /* Get data from the database */
            $devices_result = database()->query("SELECT * FROM `devices` WHERE `user_id` = {$user_id}");
            while($row = $devices_result->fetch_object()) {
                $row->sims = json_decode($row->sims ?? '');
                $row->settings = json_decode($row->settings ?? '');
				$row->notifications = json_decode($row->notifications ?? '');
				$row->sms_status_notifications = json_decode($row->sms_status_notifications ?? '');
                $devices[$row->device_id] = $row;
            }

            cache()->save(
                $cache_instance->set($devices)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('user_id=' . $user_id)
            );

        } else {

            /* Get cache */
            $devices = $cache_instance->get();

        }

        return $devices;

    }

}
