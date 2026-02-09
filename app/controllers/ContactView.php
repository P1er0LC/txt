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

namespace Altum\Controllers;

use Altum\Title;

defined('ALTUMCODE') || die();

class ContactView extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        $contact_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$contact = db()->where('contact_id', $contact_id)->where('user_id', $this->user->user_id)->getOne('contacts')) {
            redirect('contacts');
        }

        $contact->custom_parameters = json_decode($contact->custom_parameters ?? '', true);

        /* Get the sms list for the user */
        $sms = db()->where('contact_id', $contact->contact_id)->orderBy('sms_id', 'DESC')->get('sms', 5);

        /* Existing devices */
        $devices = (new \Altum\Models\Devices())->get_devices_by_user_id($this->user->user_id);

        /* Set a custom title */
        Title::set(sprintf(l('contact_view.title'), $contact->phone_number));

        /* Prepare the view */
        $data = [
            'contact' => $contact,
            'sms' => $sms,
            'devices' => $devices,
        ];

        $view = new \Altum\View('contact-view/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
