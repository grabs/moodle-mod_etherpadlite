<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace mod_etherpadlite\external;

use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_api;
use core_external\external_value;

/**
 * Implementation of web service mod_etherpadlite_test_tool
 *
 * @package    mod_etherpadlite
 * @copyright  2025 André Menrath <andre.menrath@uni-graz.at>, University of Graz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_tool extends external_api {
    /**
     * Describes the parameters for mod_etherpadlite_test_tool
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([]);
    }

    /**
     * Executes the Etherpad Lite connection test.
     *
     * This function performs the following tasks:
     * 1. Verifies the context and user capabilities.
     * 2. Retrieves the Etherpad Lite configuration.
     * 3. Checks if the URL is set and not blocked.
     * 4. Attempts to establish a connection to the Etherpad Lite server.
     * 5. Prepares information about blocked hosts if applicable.
     *
     * @return array An associative array containing the connection test results.
     */
    public static function execute() {
        // Verify context and capability.
        $context = \context_system::instance();
        self::validate_context($context);

        \require_admin();

        $mycfg = get_config('etherpadlite');
        $url = $mycfg->url ?? '';
        $apikey = $mycfg->apikey ?? '';

        $connected = false;
        $infotext  = '';
        $blockedhostinfo = '';

        // Do not run the test if the URL is not set.
        if (empty($url)) {
            $infotext = get_string('urlnotset', 'mod_etherpadlite');
            return static::get_result($connected, $infotext, $blockedhostinfo);
        }

        // Is the current host blocked?
        $blockedhost    = \mod_etherpadlite\api\client::is_url_blocked($url);
        $ignoresecurity = !empty(get_config('etherpadlite', 'ignoresecurity'));

        if (!$blockedhost || $ignoresecurity) {
            // Try to establish a connection.
            try {
                $client = \mod_etherpadlite\api\client::get_instance($apikey, $url);
                $connected = true;
                unset($client);
            } catch (\mod_etherpadlite\api\api_exception $e) {
                $infotext = $e->getMessage();
            }
        }

        // Prepare blocked host information.
        if ($blockedhost) {
            $blockedhostinfo = $ignoresecurity
                ? get_string('urlisblocked_but_ignored', 'etherpadlite', $blockedhost)
                : get_string('urlisblocked', 'etherpadlite', $blockedhost);
        }

        return static::get_result($connected, $infotext, $blockedhostinfo);
    }

    /**
     * Describe the return structure for mod_etherpadlite_test_tool
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'connection' => new external_single_structure([
                'success'         => new external_value(PARAM_BOOL, 'The connection result'),
                'info'            => new external_value(PARAM_RAW, 'Any warning or info message from the connection'),
                'blockedhostinfo' => new external_value(PARAM_RAW, 'Any warning or info message from the blocked check'),
            ]),
        ]);
    }

    /**
     * Prepares and returns the result of the connection test.
     *
     * @param bool   $connected       The result of the connection test. True if successful, false otherwise.
     * @param string $info            Any warning or info message from the connection.
     * @param string $blockedhostinfo Any warning or info message from the blocked check.
     *
     * @return array An associative array containing the connection result.
     */
    protected static function get_result(bool $connected, string $info, string $blockedhostinfo): array {
        return [
            'connection' => [
                'success'          => $connected,
                'info'            => $info,
                'blockedhostinfo' => $blockedhostinfo,
            ],
        ];
    }
}
