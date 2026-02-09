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

namespace Altum\controllers;

use Altum\Title;

defined('ALTUMCODE') || die();

class Device extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        $device_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$device = db()->where('device_id', $device_id)->where('user_id', $this->user->user_id)->getOne('devices')) {
            redirect('devices');
        }

        $device->sims = json_decode($device->sims ?? '');

        /* Get the sms list for the user */
        $sms = [];
        $sms_result = database()->query("
            SELECT `sms`.*, `contacts`.`phone_number`, `contacts`.`has_opted_out`, `contacts`.`country_code`, `contacts`.`name`
            FROM `sms` 
            LEFT JOIN `contacts` ON `contacts`.`contact_id` = `sms`.`contact_id`
            WHERE `sms`.`device_id` = {$device_id} 
            ORDER BY `sms`.`sms_id` DESC
            LIMIT 5
        ");

        while($row = $sms_result->fetch_object()) {
            $sms[] = $row;
        }

        /* Set a custom title */
        Title::set(sprintf(l('device.title'), $device->name));

        /* Prepare the view */
        $data = [
            'device' => $device,
            'sms' => $sms,
        ];

        $view = new \Altum\View('device/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
