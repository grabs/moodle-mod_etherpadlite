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

/**
 * Test tool for checking the connection and credentials of a given Etherpad Lite API-URL and API-key.
 *
 * @module     mod_etherpadlite/test_tool
 * @copyright  2025 André Menrath <andre.menrath@uni-graz.at>, University of Graz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Notification from 'core/notification';
import Templates from 'core/templates';
import {call as fetchMany} from 'core/ajax';
import {getString} from 'core/str';

const SELECTORS = {
    TEST_TOOL_BUTTON: '#mod-etherpadlite-test-tool-button',
};

/**
 * Entrypoint of the JS.
 *
 * @method init
 * @param {string} wwwroot The Moodle wwwroot URL
 */
export const init = (wwwroot) => {
    let actualpath = window.location.origin + window.location.pathname;
    if (actualpath === wwwroot + '/admin/settings.php' || actualpath === wwwroot + '/admin/search.php') {
        registerListenerEvents();
    }
};

/**
 * Register snippet related event listeners.
 *
 * @method registerListenerEvents
 */
const registerListenerEvents = () => {
    const testButton = document.querySelector(SELECTORS.TEST_TOOL_BUTTON);

     // If the button for the Connection Test Tool is not found, no listeners are registered.
    if (!testButton) {
        return;
    }

    // Add event listener which will trigger the connection test with the given Etherpad Lite API credentials.
    testButton.addEventListener('click', (event) => {
        event.preventDefault();
        testConnection().catch(Notification.exception);
    });

    // Make the button clickable after the event listener is added.
    if (testButton.hasAttribute('disabled')) {
        testButton.removeAttribute('disabled');
    }
    if (testButton.classList.contains('disabled')) {
        testButton.classList.remove('disabled');
    }
};

/**
 * Execute the tests via webservice.
 *
 * @returns string The HTML of the test.
 */
export const getTestResults = () => fetchMany([{
    methodname: 'mod_etherpadlite_test_tool',
    args: {}
}])[0];

/**
 * Build the modal with the provided data.
 *
 * @method buildModal
 */
const testConnection = async() => {
    let title = await getString('testmodaltitle', 'mod_etherpadlite');
    let body;

    const testResult = await getTestResults();

    body = await Templates.render('mod_etherpadlite/test_tool_result', testResult);
    Notification.alert(title, body);
};
