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

use Altum\Logger;
use Altum\Models\User;

defined('ALTUMCODE') || die();

class Cron extends Controller {

    public function index() {
        die();
    }

    private function initiate() {
        /* Initiation */
        set_time_limit(0);

        /* Make sure the key is correct */
        if(!isset($_GET['key']) || (isset($_GET['key']) && $_GET['key'] != settings()->cron->key)) {
            die();
        }

        /* Send webhook notification if needed */
        if(settings()->webhooks->cron_start) {
            $backtrace = debug_backtrace();
            fire_and_forget('post', settings()->webhooks->cron_start, [
                'type' => $backtrace[1]['function'] ?? null,
                'datetime' => get_date(),
            ]);
        }
    }

    private function close() {
        /* Send webhook notification if needed */
        if(settings()->webhooks->cron_end) {
            $backtrace = debug_backtrace();
            fire_and_forget('post', settings()->webhooks->cron_end, [
                'type' => $backtrace[1]['function'] ?? null,
                'datetime' => get_date(),
            ]);
        }
    }

    private function update_cron_execution_datetimes($key) {
        $date = get_date();

        /* Database query */
        database()->query("UPDATE `settings` SET `value` = JSON_SET(`value`, '$.{$key}', '{$date}') WHERE `key` = 'cron'");
    }

    public function reset() {

        $this->initiate();

        $this->users_plan_expiry_checker();

        $this->users_deletion_reminder();

        $this->auto_delete_inactive_users();

        $this->auto_delete_unconfirmed_users();

        $this->users_plan_expiry_reminder();

        $this->users_campaigns_notice();

        $this->users_sent_sms_notice();

        $this->sms_cleanup();

        $this->update_cron_execution_datetimes('reset_datetime');

        /* Make sure the reset date month is different than the current one to avoid double resetting */
        $reset_date = settings()->cron->reset_date ? (new \DateTime(settings()->cron->reset_date))->format('m') : null;
        $current_date = (new \DateTime())->format('m');

        if($reset_date != $current_date) {
            $this->logs_cleanup();

            $this->users_logs_cleanup();

            $this->internal_notifications_cleanup();

            $this->users_text_reset();

            $this->update_cron_execution_datetimes('reset_date');

            /* Clear the cache */
            cache()->deleteItem('settings');
        }

        $this->close();
    }

    private function users_plan_expiry_checker() {
        if(!settings()->payment->user_plan_expiry_checker_is_enabled) {
            return;
        }

        $date = get_date();

        $result = database()->query("
            SELECT 
                `user_id`,
                `plan_id`,
                `name`,
                `email`,
                `language`,
                `anti_phishing_code`
            FROM 
                `users`
            WHERE 
                `plan_id` <> 'free'
				AND `plan_expiration_date` < '{$date}' 
            LIMIT 25
        ");

        $plans = [];
        if($result->num_rows) {
            $plans = (new \Altum\Models\Plan())->get_plans();
        }

        /* Go through each result */
        while($user = $result->fetch_object()) {

            /* Switch the user to the default plan */
            db()->where('user_id', $user->user_id)->update('users', [
                'plan_id' => 'free',
                'plan_settings' => json_encode(settings()->plan_free->settings),
                'payment_subscription_id' => ''
            ]);

            /* Prepare the email */
            $email_template = get_email_template(
                [],
                l('global.emails.user_plan_expired.subject', $user->language),
                [
                    '{{USER_PLAN_RENEW_LINK}}' => url('pay/' . $user->plan_id),
                    '{{NAME}}' => $user->name,
                    '{{PLAN_NAME}}' => $plans[$user->plan_id]->name,
                ],
                l('global.emails.user_plan_expired.body', $user->language)
            );

            send_mail($user->email, $email_template->subject, $email_template->body, ['anti_phishing_code' => $user->anti_phishing_code, 'language' => $user->language]);

            /* Clear the cache */
            cache()->deleteItemsByTag('user_id=' .  \Altum\Authentication::$user_id);

            if(DEBUG) {
                echo sprintf('users_plan_expiry_checker() -> Plan expired for user_id %s - reverting account to free plan', $user->user_id);
            }
        }
    }

    private function users_deletion_reminder() {
        if(!settings()->users->auto_delete_inactive_users) {
            return;
        }

        /* Determine when to send the email reminder */
        $days_until_deletion = settings()->users->user_deletion_reminder;
        $days = settings()->users->auto_delete_inactive_users - $days_until_deletion;
        $past_date = (new \DateTime())->modify('-' . $days . ' days')->format('Y-m-d H:i:s');

        /* Get the users that need to be reminded */
        $result = database()->query("
            SELECT `user_id`, `name`, `email`, `language`, `anti_phishing_code` 
            FROM `users` 
            WHERE 
                `plan_id` = 'free' 
                AND `last_activity` < '{$past_date}' 
                AND `user_deletion_reminder` = 0 
                AND `type` = 0 
            LIMIT 25
        ");

        /* Go through each result */
        while($user = $result->fetch_object()) {

            /* Prepare the email */
            $email_template = get_email_template(
                [
                    '{{DAYS_UNTIL_DELETION}}' => $days_until_deletion,
                ],
                l('global.emails.user_deletion_reminder.subject', $user->language),
                [
                    '{{DAYS_UNTIL_DELETION}}' => $days_until_deletion,
                    '{{LOGIN_LINK}}' => url('login'),
                    '{{NAME}}' => $user->name,
                ],
                l('global.emails.user_deletion_reminder.body', $user->language)
            );

            if(settings()->users->user_deletion_reminder) {
                send_mail($user->email, $email_template->subject, $email_template->body, ['anti_phishing_code' => $user->anti_phishing_code, 'language' => $user->language]);
            }

            /* Update user */
            db()->where('user_id', $user->user_id)->update('users', ['user_deletion_reminder' => 1]);

            if(DEBUG) {
                if(settings()->users->user_deletion_reminder) echo sprintf('users_deletion_reminder() -> User deletion reminder email sent for user_id %s', $user->user_id);
            }
        }

    }

    private function auto_delete_inactive_users() {
        if(!settings()->users->auto_delete_inactive_users) {
            return;
        }

        /* Determine what users to delete */
        $days = settings()->users->auto_delete_inactive_users;
        $past_date = (new \DateTime())->modify('-' . $days . ' days')->format('Y-m-d H:i:s');

        /* Get the users that need to be reminded */
        $result = database()->query("
            SELECT `user_id`, `name`, `email`, `language`, `anti_phishing_code` FROM `users` WHERE `plan_id` = 'free' AND `last_activity` < '{$past_date}' AND `user_deletion_reminder` = 1 AND `type` = 0 LIMIT 25
        ");

        /* Go through each result */
        while($user = $result->fetch_object()) {

            /* Prepare the email */
            $email_template = get_email_template(
                [],
                l('global.emails.auto_delete_inactive_users.subject', $user->language),
                [
                    '{{INACTIVITY_DAYS}}' => settings()->users->auto_delete_inactive_users,
                    '{{REGISTER_LINK}}' => url('register'),
                    '{{NAME}}' => $user->name,
                ],
                l('global.emails.auto_delete_inactive_users.body', $user->language)
            );

            send_mail($user->email, $email_template->subject, $email_template->body, ['anti_phishing_code' => $user->anti_phishing_code, 'language' => $user->language]);

            /* Delete user */
            (new User())->delete($user->user_id);

            if(DEBUG) {
                echo sprintf('User deletion for inactivity user_id %s', $user->user_id);
            }
        }

    }

    private function auto_delete_unconfirmed_users() {
        if(!settings()->users->auto_delete_unconfirmed_users) {
            return;
        }

        /* Determine what users to delete */
        $days = settings()->users->auto_delete_unconfirmed_users;
        $past_date = (new \DateTime())->modify('-' . $days . ' days')->format('Y-m-d H:i:s');

        /* Get the users that need to be reminded */
        $result = database()->query("SELECT `user_id` FROM `users` WHERE `status` = '0' AND `datetime` < '{$past_date}' LIMIT 100");

        /* Go through each result */
        while($user = $result->fetch_object()) {

            /* Delete user */
            (new User())->delete($user->user_id);

            if(DEBUG) {
                echo sprintf('User deleted for unconfirmed account user_id %s', $user->user_id);
            }
        }
    }

    private function logs_cleanup() {
        /* Clear files caches */
        clearstatcache();

        $current_month = (new \DateTime())->format('m');

        $deleted_count = 0;

        /* Get the data */
        foreach(glob(UPLOADS_PATH . 'logs/' . '*.log') as $file_path) {
            $file_last_modified = filemtime($file_path);

            if((new \DateTime())->setTimestamp($file_last_modified)->format('m') != $current_month) {
                unlink($file_path);
                $deleted_count++;
            }
        }

        if(DEBUG) {
            echo sprintf('logs_cleanup: Deleted %s file logs.', $deleted_count);
        }
    }

    private function users_logs_cleanup() {
        /* Delete old users logs */
        $ninety_days_ago_datetime = (new \DateTime())->modify('-90 days')->format('Y-m-d H:i:s');
        db()->where('datetime', $ninety_days_ago_datetime, '<')->delete('users_logs');
    }

    private function internal_notifications_cleanup() {
        /* Delete old users notifications */
        $ninety_days_ago_datetime = (new \DateTime())->modify('-30 days')->format('Y-m-d H:i:s');
        db()->where('datetime', $ninety_days_ago_datetime, '<')->delete('internal_notifications');
    }

    private function sms_cleanup() {

        /* Only clean users that have not been cleaned recently */
        $now_datetime = get_date();

        /* Clean the sms table based on the users plan */
        $result = database()->query("SELECT `user_id`, `plan_settings` FROM `users` WHERE `status` = 1 AND `next_cleanup_datetime` < '{$now_datetime}'");

        /* Go through each result */
        while($user = $result->fetch_object()) {
            /* Update user cleanup date */
            db()->where('user_id', $user->user_id)->update('users', ['next_cleanup_datetime' => (new \DateTime())->modify('+1 days')->format('Y-m-d H:i:s')]);

            $user->settings = json_decode($user->settings ?? '');

            if($user->plan_settings->sms_retention == -1) continue;

            /* Clear out old notification statistics logs */
            $x_days_ago_datetime = (new \DateTime())->modify('-' . ($user->plan_settings->sms_retention ?? 90) . ' days')->format('Y-m-d H:i:s');
            database()->query("DELETE FROM `sms` WHERE `user_id` = {$user->user_id} AND `datetime` < '{$x_days_ago_datetime}'");

            if(DEBUG) {
                echo sprintf('sms cleanup done for user_id %s', $user->user_id);
            }
        }

    }

    private function users_text_reset() {
        db()->update('users', [
            'text_sent_sms_current_month' => 0,
            'text_campaigns_current_month' => 0,
            'plan_sent_sms_limit_notice' => 0,
            'plan_campaigns_limit_notice' => 0,
        ]);

        cache()->clear();
    }

    private function users_campaigns_notice() {
        /* Get the users that need to be reminded */
        $result = database()->query("
            SELECT
                `user_id`,
                `plan_id`,
                `name`,
                `email`,
                `language`,
                `anti_phishing_code`,
                `plan_settings`
            FROM
                users
            WHERE
                status = 1
                AND JSON_UNQUOTE(JSON_EXTRACT(plan_settings, '$.campaigns_per_month_limit')) != '-1'
                AND CAST(JSON_UNQUOTE(JSON_EXTRACT(plan_settings, '$.campaigns_per_month_limit')) AS UNSIGNED) < text_campaigns_current_month
                AND plan_campaigns_limit_notice = 0
            LIMIT 25        
        ");

        /* Go through each result */
        while($user = $result->fetch_object()) {
            if(!settings()->sms->email_notices_is_enabled) {
                return;
            }

            $user->plan_settings = json_decode($user->plan_settings ?? '');

            db()->where('user_id', $user->user_id)->update('users', [
                'plan_campaigns_limit_notice' => 1,
            ]);

            /* Clear the cache */
            cache()->deleteItemsByTag('user_id=' . $user->user_id);

            /* Prepare the email */
            $email_template = get_email_template(
                [],
                l('global.emails.user_campaigns_limit.subject', $user->language),
                [
                    '{{USER_PLAN_RENEW_LINK}}' => url('plan'),
                    '{{NAME}}' => $user->name,
                    '{{PLAN_NAME}}' => (new \Altum\Models\Plan())->get_plan_by_id($user->plan_id)->name,
                    '{{CAMPAIGNS_LIMIT}}' => $user->plan_settings->campaigns_per_month_limit,
                ],
                l('global.emails.user_campaigns_limit.body', $user->language)
            );

            send_mail($user->email, $email_template->subject, $email_template->body, ['anti_phishing_code' => $user->anti_phishing_code, 'language' => $user->language]);

            if(DEBUG) {
                echo sprintf('User impression limit notice email sent for user_id %s', $user->user_id);
            }
        }
    }

    private function users_sent_sms_notice() {
        if(!settings()->sms->email_notices_is_enabled) {
            return;
        }

        /* Get the users that need to be reminded */
        $result = database()->query("
            SELECT
                `user_id`,
                `plan_id`,
                `name`,
                `email`,
                `language`,
                `anti_phishing_code`,
                `plan_settings`
            FROM
                users
            WHERE
                status = 1
                AND JSON_UNQUOTE(JSON_EXTRACT(plan_settings, '$.sent_sms_per_month_limit')) != '-1'
                AND CAST(JSON_UNQUOTE(JSON_EXTRACT(plan_settings, '$.sent_sms_per_month_limit')) AS UNSIGNED) < text_sent_sms_current_month
                AND plan_sent_sms_limit_notice = 0
            LIMIT 25        
        ");

        /* Go through each result */
        while($user = $result->fetch_object()) {
            $user->plan_settings = json_decode($user->plan_settings ?? '');

            db()->where('user_id', $user->user_id)->update('users', [
                'plan_sent_sms_limit_notice' => 1,
            ]);

            /* Clear the cache */
            cache()->deleteItemsByTag('user_id=' . $user->user_id);

            /* Prepare the email */
            $email_template = get_email_template(
                [],
                l('global.emails.user_sent_sms_limit.subject', $user->language),
                [
                    '{{USER_PLAN_RENEW_LINK}}' => url('plan'),
                    '{{NAME}}' => $user->name,
                    '{{PLAN_NAME}}' => (new \Altum\Models\Plan())->get_plan_by_id($user->plan_id)->name,
                    '{{SENT_SMS_LIMIT}}' => $user->plan_settings->sent_sms_per_month_limit,
                ],
                l('global.emails.user_sent_sms_limit.body', $user->language)
            );

            send_mail($user->email, $email_template->subject, $email_template->body, ['anti_phishing_code' => $user->anti_phishing_code, 'language' => $user->language]);

            if(DEBUG) {
                echo sprintf('User impression limit notice email sent for user_id %s', $user->user_id);
            }
        }
    }

    private function users_plan_expiry_reminder() {
        if(!settings()->payment->user_plan_expiry_reminder) {
            return;
        }

        /* Determine when to send the email reminder */
        $days = settings()->payment->user_plan_expiry_reminder;
        $future_date = (new \DateTime())->modify('+' . $days . ' days')->format('Y-m-d H:i:s');

        /* Get potential monitors from users that have almost all the conditions to get an email report right now */
        $result = database()->query("
            SELECT
                `user_id`,
                `name`,
                `email`,
                `plan_id`,
                `plan_expiration_date`,
                `language`,
                `anti_phishing_code`
            FROM 
                `users`
            WHERE 
                `status` = 1
                AND `plan_id` <> 'free'
                AND `plan_expiry_reminder` = '0'
                AND (`payment_subscription_id` IS NULL OR `payment_subscription_id` = '')
				AND `plan_expiration_date` < '{$future_date}'
            LIMIT 25
        ");

        $plans = [];
        if($result->num_rows) {
            $plans = (new \Altum\Models\Plan())->get_plans();
        }

        /* Go through each result */
        while($user = $result->fetch_object()) {

            /* Determine the exact days until expiration */
            $days_until_expiration = (new \DateTime($user->plan_expiration_date))->diff((new \DateTime()))->days;

            /* Prepare the email */
            $email_template = get_email_template(
                [
                    '{{DAYS_UNTIL_EXPIRATION}}' => $days_until_expiration,
                ],
                l('global.emails.user_plan_expiry_reminder.subject', $user->language),
                [
                    '{{DAYS_UNTIL_EXPIRATION}}' => $days_until_expiration,
                    '{{USER_PLAN_RENEW_LINK}}' => url('pay/' . $user->plan_id),
                    '{{NAME}}' => $user->name,
                    '{{PLAN_NAME}}' => $plans[$user->plan_id]->name,
                ],
                l('global.emails.user_plan_expiry_reminder.body', $user->language)
            );

            send_mail($user->email, $email_template->subject, $email_template->body, ['anti_phishing_code' => $user->anti_phishing_code, 'language' => $user->language]);

            /* Update user */
            db()->where('user_id', $user->user_id)->update('users', ['plan_expiry_reminder' => 1]);

            if(DEBUG) {
                echo sprintf('users_plan_expiry_reminder() -> Email sent for user_id %s', $user->user_id);
            }
        }

    }

    public function broadcasts() {

        $this->initiate();
        $this->update_cron_execution_datetimes('broadcasts_datetime');

        /* We'll send up to 40 emails per run */
        $max_batch_size = 40;

        /* Fetch a broadcast in "processing" status */
        $broadcast = db()->where('status', 'processing')->getOne('broadcasts');
        if(!$broadcast) {
            $this->close();
            return;
        }

        $broadcast->users_ids = json_decode($broadcast->users_ids ?? '[]', true);
        $broadcast->sent_users_ids = json_decode($broadcast->sent_users_ids ?? '[]', true);
        $broadcast->settings = json_decode($broadcast->settings ?? '[]');

        /* Find which users are left to process */
        $remaining_user_ids = array_diff($broadcast->users_ids, $broadcast->sent_users_ids);

        /* If no one is left, mark broadcast as "sent" */
        if(empty($remaining_user_ids)) {
            db()->where('broadcast_id', $broadcast->broadcast_id)->update('broadcasts', [
                'status' => 'sent'
            ]);
            $this->close();
            return;
        }

        /* Get all batch users at once in one go */
        $user_ids_for_this_run = array_slice($remaining_user_ids, 0, $max_batch_size);

        $users = db()
            ->where('user_id', $user_ids_for_this_run, 'IN')
            ->get('users', null, [
                'user_id',
                'name',
                'email',
                'language',
                'anti_phishing_code',
                'continent_code',
                'country',
                'city_name',
                'device_type',
                'os_name',
                'browser_name',
                'browser_language'
            ]);

        $newly_sent_user_ids = array_diff($user_ids_for_this_run, array_column($users, 'user_id'));

        /* Initialize PHPMailer once for this batch */
        $mail = new \PHPMailer\PHPMailer\PHPMailer();
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->isHTML(true);

        /* SMTP connection settings */
        $mail->SMTPAuth = settings()->smtp->auth;
        $mail->Host = settings()->smtp->host;
        $mail->Port = settings()->smtp->port;
        $mail->Username = settings()->smtp->username;
        $mail->Password = settings()->smtp->password;

        if(settings()->smtp->encryption != '0') {
            $mail->SMTPSecure = settings()->smtp->encryption;
        }

        /* Keep the SMTP connection alive */
        $mail->SMTPKeepAlive = true;

        /* Set From / Reply-to */
        $mail->setFrom(settings()->smtp->from, settings()->smtp->from_name);
        if(!empty(settings()->smtp->reply_to) && !empty(settings()->smtp->reply_to_name)) {
            $mail->addReplyTo(settings()->smtp->reply_to, settings()->smtp->reply_to_name);
        } else {
            $mail->addReplyTo(settings()->smtp->from, settings()->smtp->from_name);
        }

        /* Optional CC/BCC */
        if(settings()->smtp->cc) {
            foreach (explode(',', settings()->smtp->cc) as $cc_email) {
                $mail->addCC(trim($cc_email));
            }
        }
        if(settings()->smtp->bcc) {
            foreach (explode(',', settings()->smtp->bcc) as $bcc_email) {
                $mail->addBCC(trim($bcc_email));
            }
        }

        /* Loop through users and send */
        foreach ($users as $user) {

            /* Prepare placeholders and the final template */
            $vars = [
                '{{USER:NAME}}'              => $user->name,
                '{{USER:EMAIL}}'             => $user->email,
                '{{USER:CONTINENT_NAME}}'    => get_continent_from_continent_code($user->continent_code),
                '{{USER:COUNTRY_NAME}}'      => get_country_from_country_code($user->country),
                '{{USER:CITY_NAME}}'         => $user->city_name,
                '{{USER:DEVICE_TYPE}}'       => l('global.device.' . $user->device_type),
                '{{USER:OS_NAME}}'           => $user->os_name,
                '{{USER:BROWSER_NAME}}'      => $user->browser_name,
                '{{USER:BROWSER_LANGUAGE}}'  => get_language_from_locale($user->browser_language),
            ];

            $email_template = get_email_template(
                $vars,
                htmlspecialchars_decode($broadcast->subject),
                $vars,
                convert_editorjs_json_to_html($broadcast->content)
            );

            /* Optional: tracking pixel & link rewriting */
            if(settings()->main->broadcasts_statistics_is_enabled) {
                $tracking_id = base64_encode('broadcast_id=' . $broadcast->broadcast_id . '&user_id=' . $user->user_id);
                $email_template->body .= '<img src="' . SITE_URL . 'broadcast?id=' . $tracking_id . '" style="display: none;" />';
                $email_template->body = preg_replace(
                    '/<a href=\"(.+)\"/',
                    '<a href="' . SITE_URL . 'broadcast?id=' . $tracking_id . '&url=$1"',
                    $email_template->body
                );
            }

            /* Clear addresses from previous iteration */
            $mail->clearAddresses();

            /* Add new email address */
            $mail->addAddress($user->email);

            /* Process the email title, template and body */
            extract(process_send_mail_template($email_template->subject, $email_template->body, ['is_broadcast' => true, 'is_system_email' => $broadcast->settings->is_system_email, 'anti_phishing_code' => $user->anti_phishing_code, 'language' => $user->language]));

            /* Set subject/body, then send */
            $mail->Subject = $title;
            $mail->Body = $email_template;
            $mail->AltBody = strip_tags($mail->Body);

            /* SEND */
            $mail->send();

            /* Track who we just emailed */
            $broadcast->sent_users_ids[] = $user->user_id;
            $newly_sent_user_ids[] = $user->user_id;

            Logger::users($user->user_id, 'broadcast.' . $broadcast->broadcast_id . '.sent');
        }

        /* Close this SMTP connection for the batch */
        $mail->smtpClose();

        /* Update broadcast once for the entire batch */
        db()->where('broadcast_id', $broadcast->broadcast_id)->update('broadcasts', [
            'sent_emails'             => db()->inc(count($newly_sent_user_ids)),
            'sent_users_ids'          => json_encode($broadcast->sent_users_ids),
            'status'                  => count($broadcast->users_ids) >= count($broadcast->sent_users_ids) ? 'sent' : 'processing',
            'last_sent_email_datetime'=> get_date(),
        ]);

        /* Debugging */
        if(DEBUG) {
            echo '<br />' . "broadcast_id - {$broadcast->broadcast_id} | sent emails to users ids (total - " . count($newly_sent_user_ids) . "): " . implode(',', $newly_sent_user_ids) . '<br />';
        }

        $this->close();
    }

    public function push_notifications() {
        if(\Altum\Plugin::is_active('push-notifications')) {

            $this->initiate();

            /* Update cron job last run date */
            $this->update_cron_execution_datetimes('push_notifications_datetime');

            require_once \Altum\Plugin::get('push-notifications')->path . 'controllers/Cron.php';

            $this->close();
        }
    }

    public function sms() {
        $this->initiate();

        /* mark cron execution */
        $this->update_cron_execution_datetimes('sms_datetime');

        /* static config */
        $max_per_run = settings()->sms->scheduled_and_flows_sms_per_cron ?? 1000;

        $insert_counter_global = 0;

        /* Caching */
        $devices = [];

        /* Get SMS */
        $sms = db()
            ->where('rss_automation_id', NULL, 'IS')
            ->where('recurring_campaign_id', NULL, 'IS')
            ->where('campaign_id', NULL, 'IS')
            ->where('status', 'pending')
            ->where('scheduled_datetime', get_date(), '<')
            ->orderBy('scheduled_datetime')
            ->get('sms', $max_per_run);

        /* Go through all of them */
        foreach($sms as $row) {
            if(!isset($devices[$row->device_id])) {
                $device = (new \Altum\Models\Devices())->get_device_by_device_id($row->device_id);
                $devices[$row->device_id] = $device;

                /* Wake device to start sending SMS */
                wake_device_to_send_sms($device->device_fcm_token);
            }

        }

        $this->close();
    }

	public function campaigns() {
		$this->initiate();

		/* mark cron execution */
		$this->update_cron_execution_datetimes('campaigns_datetime');

		/* static config */
		$max_per_run = settings()->sms->campaigns_sms_inserts_per_cron ?? 1000;

		$insert_counter_global = 0;

		/* keep looping campaigns until quota or queue exhausted */
		while (
			($campaign = db()
				->where('status', 'scheduled')
				->where('scheduled_datetime', get_date(), '<')
				->orderBy('scheduled_datetime')
				->getOne('campaigns'))
			&& $insert_counter_global < $max_per_run
		) {

			/* decode json fields once */
			$campaign->settings           = json_decode($campaign->settings ?? '[]');
			$campaign->contacts_ids       = json_decode($campaign->contacts_ids ?? '[]');
			$campaign->sent_contacts_ids  = json_decode($campaign->sent_contacts_ids ?? '[]');

			/* figure out remaining targets */
			$pending_contacts_ids = array_diff(
				$campaign->contacts_ids,
				$campaign->sent_contacts_ids
			);

			$device = (new \Altum\Models\Devices())->get_device_by_device_id($campaign->device_id);

			/* prepare the sms inserts */
			$insert_counter = 0;
			$insert_counter_campaign = 0;
			$notifications = json_encode($device->sms_status_notifications);
			$sms = [];

			foreach($pending_contacts_ids as $contact_id) {

				/* stop if global limit reached */
				if($insert_counter_global >= $max_per_run) {
					break;
				}

				$campaign->sent_contacts_ids[] = $contact_id;

				$sms[] = [
					'contact_id' => $contact_id,
					'campaign_id' => $campaign->campaign_id,
					'rss_automation_id' => $campaign->rss_automation_id,
					'recurring_campaign_id' => $campaign->recurring_campaign_id,
					'device_id' => $campaign->device_id,
					'sim_subscription_id' => $campaign->sim_subscription_id,
					'user_id' => $campaign->user_id,
					'type' => 'sent',
					'content' => $campaign->content,
					'status' => 'pending',
					'notifications' => $notifications,
					'scheduled_datetime' => $campaign->scheduled_datetime,
					'datetime' => get_date(),
				];

				$insert_counter++;
				$insert_counter_campaign++;
				$insert_counter_global++;

				/* batch insert every 5000 */
				if($insert_counter >= 5000) {
					db()->insertMulti('sms', $sms);
					$sms = [];
					$insert_counter = 0;
				}
			}

			/* insert remaining SMS that didn't reach the batch limit */
			if(count($sms)) {
				db()->insertMulti('sms', $sms);
			}

			/* update contacts stats */
			if(!empty($pending_contacts_ids)) {
				db()->where('contact_id', $pending_contacts_ids, 'IN')->update('contacts', [
					'total_pending_sms' => db()->inc()
				]);
			}

			/* update campaigns & stats */
			db()->where('campaign_id', $campaign->campaign_id)->update('campaigns', [
				'sent_contacts_ids' => json_encode($campaign->sent_contacts_ids),
				'total_pending_sms' => $insert_counter_campaign,
				'status' => 'sent',
				'last_datetime' => get_date(),
			]);

			/* update rss automation */
			if($campaign->rss_automation_id) {
				db()->where('rss_automation_id', $campaign->rss_automation_id)->update('rss_automations', [
					'total_pending_sms' => db()->inc($insert_counter_campaign),
				]);
			}

			/* update recurring campaign */
			if($campaign->recurring_campaign_id) {
				db()->where('recurring_campaign_id', $campaign->recurring_campaign_id)->update('recurring_campaigns', [
					'total_pending_sms' => db()->inc($insert_counter_campaign),
				]);
			}

			/* update device */
			db()->where('device_id', $campaign->device_id)->update('devices', [
				'total_pending_sms' => db()->inc($insert_counter_campaign),
			]);

			/* wake device */
			if($insert_counter_campaign > 0) {
				wake_device_to_send_sms($device->device_fcm_token);
			}

			cache()->deleteItem('campaigns_dashboard?user_id=' . $campaign->user_id);
		}

		$this->close();
	}

    public function flows() {
        $this->initiate();

        /* Update cron job last run date */
        $this->update_cron_execution_datetimes('flows_datetime');

        /* Cache in memory */
        $cached_flows = [];
        $cached_users = [];

        $i = 1;
        while(
            ($contact = db()->where('has_flows_processed', '0')->getOne('contacts'))
            && $i <= (settings()->sms->flows_contacts_per_cron ?? 100)
        ) {
            /* Get the flow */
            if(isset($cached_flows[$contact->user_id])) {
                $flows = $cached_flows[$contact->user_id];
            } else {
                $flows = (new \Altum\Models\Flow())->get_flows_by_user_id($contact->user_id);
                $cached_flows[$contact->user_id] = $flows;
            }

            $contact->custom_parameters = json_decode($contact->custom_parameters ?? '[]');

            /* Inserted SMS per contact */
            $sms_insert_counter = 0;

            /* Go through each flow and set up the scheduled sms */
            foreach($flows as $flow) {
                if(!$flow->is_enabled) continue;

                /* Make sure the contact triggers the selected segment */
                $flow_is_triggered = false;

                /* Segment */
                if(is_numeric($flow->segment)) {
                    /* Get settings from custom segments */
                    $segment = (new \Altum\Models\Segment())->get_segment_by_segment_id($flow->segment);

                    if(!$segment) {
                        $flow->segment = 'all';
                    }
                }

                switch($flow->segment) {
                    case 'all':
                        $flow_is_triggered = true;
                        break;

                    default:
                        /* Assume the flow is triggered */
                        $flow_is_triggered = true;

                        if(count($segment->settings->filters_countries) && !in_array($contact->country_code, $segment->settings->filters_countries)) {
                            $flow_is_triggered = false;
                        }

                        if(count($segment->settings->filters_continents) && !in_array($contact->continent_code, $segment->settings->filters_continents)) {
                            $flow_is_triggered = false;
                        }

                        if(count($segment->settings->filters_custom_parameters)) {
                            foreach($segment->settings->filters_custom_parameters as $key => $value) {
                                if(!isset($contact->custom_parameters[$key]) || $contact->custom_parameters[$key] != $value ) {
                                    $flow_is_triggered = false;
                                }
                            }
                        }

                        break;
                }

                /* Ignore if it's not triggered */
                if(!$flow_is_triggered) continue;

                /* Scheduled date */
                $scheduled_datetime = (new \DateTime())->modify('+' . $flow->wait_time . ' ' . $flow->wait_time_type)->format('Y-m-d H:i:s');

                /* Get the user */
                if(isset($cached_users[$contact->user_id])) {
                    $user = $cached_users[$contact->user_id];
                } else {
                    $user = db()->where('user_id', $contact->user_id)->getOne('users', ['`text_sent_sms_current_month`', 'plan_settings']);
                    $user->plan_settings = json_decode($user->plan_settings ?? '');
                    $cached_users[$contact->user_id] = $user;
                }

                /* Usage tracking */
                if($user->plan_settings->sent_sms_per_month_limit == -1 || $user->text_sent_sms_current_month <= $user->plan_settings->sent_sms_per_month_limit) {
                    $cached_users[$contact->user_id]->text_sent_sms_current_month++;

                    /* Prepare the sms content */
                    $replacers = [
                        '{{NAME}}'              => $contact->name,
                        '{{PHONE_NUMBER}}'      => $contact->phone_number,
                        '{{CONTINENT_NAME}}'    => get_continent_from_continent_code($contact->continent_code),
                        '{{COUNTRY_NAME}}'      => get_country_from_country_code($contact->country_code),
                    ];

                    /* Custom parameters */
                    foreach($contact->custom_parameters as $key => $value) {
                        $replacers['{{CUSTOM_PARAMETERS:' . $key . '}}'] = $value;
                    }

                    /* Process spintax and replacers */
                    $content = process_spintax(str_replace(
                        array_keys($replacers),
                        array_values($replacers),
                        $flow->content
                    ));

                    /* Insert the scheduled sms */
                    db()->insert('sms', [
                        'contact_id' => $contact->contact_id,
                        'user_id' => $contact->user_id,
                        'flow_id' => $flow->flow_id,
                        'device_id' => $flow->device_id,
                        'sim_subscription_id' => $flow->sim_subscription_id,
                        'scheduled_datetime' => $scheduled_datetime,
                        'type' => 'sent',
                        'status' => 'pending',
                        'content' => $content,
                        'datetime' => get_date(),
                    ]);

                    /* SMS counter per contact */
                    $sms_insert_counter++;

                    /* Update the flow */
                    db()->where('flow_id', $flow->flow_id)->update('flows', [
                        'total_pending_sms' => db()->inc()
                    ]);

                    /* Update the device */
                    db()->where('device_id', $flow->device_id)->update('devices', [
                        'total_pending_sms' => db()->inc()
                    ]);
                }
            }

            /* Update the contact */
            db()->where('contact_id', $contact->contact_id)->update('contacts', [
                'total_pending_sms' => db()->inc($sms_insert_counter),
                'has_flows_processed' => 1,
            ]);

            /* Make sure it does not hit the limits imposed */
            $i++;
            if($i >= (settings()->sms->flows_contacts_per_cron ?? 100)) {
                break;
            }
        }

        $this->close();
    }

    public function rss_automations() {
        $this->initiate();

        /* Update cron job last run date */
        $this->update_cron_execution_datetimes('rss_automations_datetime');

        $i = 1;
        while(
            ($rss_automation = db()->where('is_enabled', 1)->where('next_check_datetime', get_date(), '<')->getOne('rss_automations'))
            && $i <= (settings()->sms->rss_automations_per_cron ?? 10)
        ) {
            $i++;

            $rss_automation->settings = json_decode($rss_automation->settings ?? '');
            $rss_automation->rss_last_entries = json_decode($rss_automation->rss_last_entries ?? '[]');

            /* Calculate expected next run */
            $next_check_datetime = (new \DateTime())->modify('+' . $rss_automation->settings->check_interval_seconds . ' seconds')->format('Y-m-d H:i:s');

            /* Process the RSS feed */
            $rss = rss_feed_parse_url($rss_automation->rss_url);

            if(!$rss) {
                /* Wait and try again */
                sleep(3);

                $rss = rss_feed_parse_url($rss_automation->rss_url);
            }

            /* Disable the RSS automation on feed fail 2x times */
            if(!$rss) {
                db()->where('rss_automation_id', $rss_automation->rss_automation_id)->update('rss_automations', [
                    'is_enabled' => 0,
                    'next_check_datetime' => null,
                    'last_check_datetime' => get_date(),
                ]);

                continue;
            }

            /* Only use the last needed items */
            $rss = array_slice($rss, 0, $rss_automation->settings->items_count);

            /* Filter out already processed entries */
            $new_rss = [];
            foreach($rss as $entry) {
                if(!in_array($entry[$rss_automation->settings->unique_item_identifier ?? 'url'], $rss_automation->rss_last_entries)) {
                    $new_rss[] = $entry;
                }
            }

            /* Skip if no entry that needs to be processed */
            if(!count($new_rss)) {
                db()->where('rss_automation_id', $rss_automation->rss_automation_id)->update('rss_automations', [
                    'next_check_datetime' => $next_check_datetime,
                    'last_check_datetime' => get_date(),
                ]);

                continue;
            }

            $rss = $new_rss;

            /* Segment */
            if(is_numeric($rss_automation->segment)) {
                $segment = (new \Altum\Models\Segment())->get_segment_by_segment_id($rss_automation->segment);
                if(!$segment) {
                    $rss_automation->segment = 'all';
                }
            }

            if($rss_automation->segment == 'all') {
                $contacts = db()->where('user_id', $rss_automation->user_id)->where('has_opted_out', 0)->get('contacts', null, ['contact_id']);
            } else {
                switch($segment->type) {
                    case 'bulk':
                    case 'custom':
                        if(empty($segment->settings->contacts_ids)) {
                            $contacts = [];
                        } else {
                            $contacts = db()->where('user_id', $rss_automation->user_id)->where('contact_id', $segment->settings->contacts_ids, 'IN')->where('has_opted_out', 0)->get('contacts', null, ['contact_id']);
                        }
                        break;

                    case 'filter':
                        $query = db();
                        $has_filters = false;

                        if(isset($segment->settings->filters_countries)) $_POST['filters_countries'] = $segment->settings->filters_countries ?? [];
                        if(isset($segment->settings->filters_continents)) $_POST['filters_continents'] = $segment->settings->filters_continents ?? [];
                        if(isset($segment->settings->filters_custom_parameters) && count($segment->settings->filters_custom_parameters)) {
                            foreach($segment->settings->filters_custom_parameters as $key => $custom_parameter) {
                                $_POST['filters_custom_parameter_key'][$key] = $custom_parameter->key;
                                $_POST['filters_custom_parameter_condition'][$key] = $custom_parameter->condition;
                                $_POST['filters_custom_parameter_value'][$key] = $custom_parameter->value;
                            }
                        }

                        /* Custom parameters initialization */
                        $_POST['filters_custom_parameter_key'] = $_POST['filters_custom_parameter_key'] ?? [];
                        $_POST['filters_custom_parameter_value'] = $_POST['filters_custom_parameter_value'] ?? [];

                        $custom_parameters = [];
                        foreach($_POST['filters_custom_parameter_key'] as $key => $value) {
                            $custom_parameters[] = [
                                'key' => $value,
                                'value' => $_POST['filters_custom_parameter_value'][$key]
                            ];
                        }

                        if(count($custom_parameters)) {
                            $has_filters = true;
                            foreach($custom_parameters as $custom_parameter) {
                                $key = $custom_parameter['key'];
                                $value = $custom_parameter['value'];
                                $query->where("JSON_EXTRACT(`custom_parameters`, '$.{$key}') = '$value'");
                            }
                        }

                        if(isset($_POST['filters_countries'])) {
                            $has_filters = true;
                            $query->where('country_code', $_POST['filters_countries'], 'IN');
                        }

                        if(isset($_POST['filters_continents'])) {
                            $has_filters = true;
                            $query->where('continent_code', $_POST['filters_continents'], 'IN');
                        }

                        $contacts = $has_filters ? $query->where('has_opted_out', 0)->get('contacts', null, ['contact_id']) : [];

                        db()->reset();
                        break;
                }
            }

            $contacts_ids = array_column($contacts, 'contact_id');

            $user = db()->where('user_id', $rss_automation->user_id)->getOne('users', ['user_id', 'plan_settings', 'text_campaigns_current_month', 'text_sent_sms_current_month', 'timezone']);
            $user->plan_settings = json_decode($user->plan_settings ?? '');

            $available_campaigns = $user->plan_settings->campaigns_per_month_limit == -1 ? 9999999 : $user->plan_settings->campaigns_per_month_limit - $user->text_campaigns_current_month;
            $available_sms = $user->plan_settings->sent_sms_per_month_limit == -1 ? 9999999 : $user->plan_settings->sent_sms_per_month_limit - $user->text_sent_sms_current_month;

            $created_campaigns = 0;
            $processed_rss_entries = [];

            foreach($rss as $entry) {
                if($available_campaigns <= 0 || $available_sms <= count($contacts_ids)) break;

                $name = $rss_automation->name . ' - ' . string_truncate($entry['title'], 50);
                $status = 'scheduled';

                $content = $rss_automation->content;

                $replacers = [
                    '{{RSS_TITLE}}' => input_clean($entry['title'], 64),
                    '{{RSS_DESCRIPTION}}' => input_clean($entry['description'], 128),
                    '{{RSS_URL}}' => input_clean($entry['url'], 512),
                ];

                $content = str_replace(
                    array_keys($replacers),
                    array_values($replacers),
                    $content
                );

                $content = normalize_sms_text($content);

                $campaigns_delay = $created_campaigns == 0 ? 0 : $created_campaigns * $rss_automation->settings->campaigns_delay;
                $scheduled_datetime = (new \DateTime())->modify('+' . $campaigns_delay . ' minutes')->format('Y-m-d H:i:s');

                $settings = ['is_scheduled' => 1];

                $campaign_id = db()->insert('campaigns', [
                    'user_id' => $user->user_id,
                    'device_id' => $rss_automation->device_id,
                    'sim_subscription_id' => $rss_automation->sim_subscription_id,
                    'rss_automation_id' => $rss_automation->rss_automation_id,
                    'name' => $name,
                    'content' => $content,
                    'segment' => $rss_automation->segment,
                    'settings' => json_encode($settings),
                    'contacts_ids' => json_encode($contacts_ids),
                    'sent_contacts_ids' => '[]',
                    'total_pending_sms' => count($contacts_ids),
                    'status' => $status,
                    'scheduled_datetime' => $scheduled_datetime,
                    'datetime' => get_date(),
                ]);

                $available_campaigns--;
                $created_campaigns++;
                $available_sms -= count($contacts_ids);

                $processed_rss_entries[] = $entry[$rss_automation->settings->unique_item_identifier ?? 'url'];
            }

            /* Merge new processed entries into the saved history and cap size */
            $merged_rss_last_entries = array_unique(array_merge($processed_rss_entries, $rss_automation->rss_last_entries));
            $merged_rss_last_entries = array_slice($merged_rss_last_entries, 0, 100);

            db()->where('rss_automation_id', $rss_automation->rss_automation_id)->update('rss_automations', [
                'total_campaigns' => db()->inc($created_campaigns),
                'rss_last_entries' => json_encode($merged_rss_last_entries),
                'next_check_datetime' => $next_check_datetime,
                'last_check_datetime' => get_date(),
            ]);

            db()->where('user_id', $user->user_id)->update('users', [
                'text_campaigns_current_month' => db()->inc($created_campaigns)
            ]);
        }

        $this->close();
    }

    public function recurring_campaigns() {
        $this->initiate();

        /* Update cron job last run date */
        $this->update_cron_execution_datetimes('recurring_campaigns_datetime');

        $i = 1;
        while(
            ($recurring_campaign = db()->where('is_enabled', 1)->where('next_run_datetime', get_date(), '<')->getOne('recurring_campaigns'))
            && $i <= (settings()->sms->recurring_campaigns_per_cron ?? 10)
        ) {
            $i++;

            $recurring_campaign->settings = json_decode($recurring_campaign->settings ?? '');

            /* Segment */
            if(is_numeric($recurring_campaign->segment)) {
                /* Get settings from custom segments */
                $segment = (new \Altum\Models\Segment())->get_segment_by_segment_id($recurring_campaign->segment);

                if(!$segment) {
                    $recurring_campaign->segment = 'all';
                }
            }

            if($recurring_campaign->segment == 'all') {
                $contacts = db()->where('user_id', $recurring_campaign->user_id)->where('has_opted_out', 0)->get('contacts', null, ['contact_id']);
            }

            else {
                switch($segment->type) {
                    case 'bulk':
                    case 'custom':

                        if(empty($segment->settings->contacts_ids)) {
                            $contacts = [];
                        } else {
                            $contacts = db()->where('user_id', $recurring_campaign->user_id)->where('contact_id', $segment->settings->contacts_ids, 'IN')->where('has_opted_out', 0)->get('contacts', null, ['contact_id']);
                        }

                        break;

                    case 'filter':

                        if(isset($segment->settings->filters_countries)) $_POST['filters_countries'] = $segment->settings->filters_countries ?? [];
                        if(isset($segment->settings->filters_continents)) $_POST['filters_continents'] = $segment->settings->filters_continents ?? [];
                        if(isset($segment->settings->filters_custom_parameters) && count($segment->settings->filters_custom_parameters)) {
                            foreach($segment->settings->filters_custom_parameters as $key => $custom_parameter) {
                                $_POST['filters_custom_parameter_key'][$key] = $custom_parameter->key;
                                $_POST['filters_custom_parameter_condition'][$key] = $custom_parameter->condition;
                                $_POST['filters_custom_parameter_value'][$key] = $custom_parameter->value;
                            }
                        }

                        $query = db();

                        $has_filters = false;

                        /* Custom parameters */
                        if(!isset($_POST['filters_custom_parameter_key'])) {
                            $_POST['filters_custom_parameter_key'] = [];
                            $_POST['filters_custom_parameter_condition'] = [];
                            $_POST['filters_custom_parameter_value'] = [];
                        }

                        $custom_parameters = [];

                        foreach($_POST['filters_custom_parameter_key'] as $key => $value) {
                            $custom_parameters[] = [
                                'key' => $value,
                                'condition' => $_POST['filters_custom_parameter_condition'][$key],
                                'value' => $_POST['filters_custom_parameter_value'][$key],
                            ];
                        }

                        if(count($custom_parameters)) {
                            $has_filters = true;

                            foreach($custom_parameters as $custom_parameter) {
                                $key = $custom_parameter['key'];
                                $condition = $custom_parameter['condition'];
                                $value = $custom_parameter['value'];

                                /* reference JSON value once; unquote JSON for string ops, cast for numeric ops */
                                $json_value_expression = 'JSON_UNQUOTE(JSON_EXTRACT(`custom_parameters`, \'$."'.$key.'"\'))';
                                $numeric_expression = 'CAST('.$json_value_expression.' AS DECIMAL(65,10))';

                                switch($condition) {
                                    case 'exact':
                                        $query->where($json_value_expression.' = \''.$value.'\'');
                                        break;

                                    case 'not_exact':
                                        $query->where($json_value_expression.' != \''.$value.'\'');
                                        break;

                                    case 'contains':
                                        $query->where($json_value_expression.' LIKE \'%'.$value.'%\'');
                                        break;

                                    case 'not_contains':
                                        $query->where($json_value_expression.' NOT LIKE \'%'.$value.'%\'');
                                        break;

                                    case 'starts_with':
                                        $query->where($json_value_expression.' LIKE \''.$value.'%\'');
                                        break;

                                    case 'not_starts_with':
                                        $query->where($json_value_expression.' NOT LIKE \''.$value.'%\'');
                                        break;

                                    case 'ends_with':
                                        $query->where($json_value_expression.' LIKE \'%'.$value.'\'');
                                        break;

                                    case 'not_ends_with':
                                        $query->where($json_value_expression.' NOT LIKE \'%'.$value.'\'');
                                        break;

                                    case 'bigger_than':
                                        $query->where($numeric_expression.' > '.(is_numeric($value) ? $value : '0'));
                                        break;

                                    case 'lower_than':
                                        $query->where($numeric_expression.' < '.(is_numeric($value) ? $value : '0'));
                                        break;
                                }
                            }
                        }

                        /* Countries */
                        if(isset($_POST['filters_countries'])) {
                            $has_filters = true;
                            $query->where('country_code', $_POST['filters_countries'], 'IN');
                        }

                        /* Continents */
                        if(isset($_POST['filters_continents'])) {
                            $has_filters = true;
                            $query->where('continent_code', $_POST['filters_continents'], 'IN');
                        }

                        $contacts = $has_filters ? $query->where('has_opted_out', 0)->get('contacts', null, ['contact_id']) : [];

                        db()->reset();

                        break;
                }
            }

            /* Get all the users needed */
            $contacts_ids = array_column($contacts, 'contact_id');

            /* Get user limits */
            $user = db()->where('user_id', $recurring_campaign->user_id)->getOne('users', ['user_id', 'plan_settings', 'text_campaigns_current_month', 'text_sent_sms_current_month', 'timezone']);
            $user->plan_settings = json_decode($user->plan_settings ?? '');

            /* Campaigns usage tracking */
            $available_campaigns = $user->plan_settings->campaigns_per_month_limit == -1 ? 9999999 : $user->plan_settings->campaigns_per_month_limit - $user->text_campaigns_current_month;

            /* Sent sms tracking */
            $available_sms = $user->plan_settings->sent_sms_per_month_limit == -1 ? 9999999 : $user->plan_settings->sent_sms_per_month_limit - $user->text_sent_sms_current_month;

            /* Disable the recurring campaign */
            if($available_campaigns <= 0 || $available_sms <= count($contacts_ids)) {
                db()->where('recurring_campaign_id', $recurring_campaign->recurring_campaign_id)->update('recurring_campaigns', [
                    'is_enabled' => 0,
                    'next_run_datetime' => null,
                    'last_run_datetime' => get_date(),
                ]);
                continue;
            };

            /* Scheduled datetime */
            $scheduled_datetime = (new \DateTime($recurring_campaign->next_run_datetime))->modify('+15 minutes');

            /* Make sure it skips this run if too much time has passed */
            $current_datetime = new \DateTime();

            $interval = $current_datetime->diff($scheduled_datetime);

            /* Generate another run time */
            if($interval->days >= 1) {
                $next_run_datetime = get_next_run_datetime($recurring_campaign->settings->frequency, $recurring_campaign->settings->time, $recurring_campaign->settings->week_days, $recurring_campaign->settings->month_days, $user->timezone, '-15 minutes');

                db()->where('recurring_campaign_id', $recurring_campaign->recurring_campaign_id)->update('recurring_campaigns', [
                    'next_run_datetime' => $next_run_datetime,
                ]);

                continue;
            }

            $scheduled_datetime = get_next_run_datetime($recurring_campaign->settings->frequency, $recurring_campaign->settings->time, $recurring_campaign->settings->week_days, $recurring_campaign->settings->month_days, $user->timezone);

            /* Prepare the campaign */
            $name = $recurring_campaign->name . ' - #' . nr($recurring_campaign->total_campaigns + 1);
            $status = 'scheduled';

            /* Content */
            $content = $recurring_campaign->content;

            /* Settings */
            $settings = ['is_scheduled' => 1];

            /* Database query */
            $campaign_id = db()->insert('campaigns', [
                'user_id' => $user->user_id,
                'recurring_campaign_id' => $recurring_campaign->recurring_campaign_id,
                'device_id' => $recurring_campaign->device_id,
                'sim_subscription_id' => $recurring_campaign->sim_subscription_id,
                'rss_automation_id' => $recurring_campaign->rss_automation_id,
                'name' => $name,
                'content' => $content,
                'segment' => $recurring_campaign->segment,
                'settings' => json_encode($settings),
                'contacts_ids' => json_encode($contacts_ids),
                'sent_contacts_ids' => '[]',
                'total_pending_sms' => count($contacts_ids),
                'status' => $status,
                'scheduled_datetime' => $scheduled_datetime,
                'datetime' => get_date(),
            ]);

            /* Calculate the next run */
            $next_run_datetime = get_next_run_datetime($recurring_campaign->settings->frequency, $recurring_campaign->settings->time, $recurring_campaign->settings->week_days, $recurring_campaign->settings->month_days, $user->timezone, '-15 minutes', '+16 minutes');

            db()->where('recurring_campaign_id', $recurring_campaign->recurring_campaign_id)->update('recurring_campaigns', [
                'total_campaigns' => db()->inc(),
                'next_run_datetime' => $next_run_datetime,
                'last_run_datetime' => get_date(),
            ]);

            /* Database query */
            db()->where('user_id', $user->user_id)->update('users', [
                'text_campaigns_current_month' => db()->inc()
            ]);

        }

        $this->close();
    }

}
