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

namespace mod_etherpadlite\output\component;

/**
 * Output component to render a notification.
 *
 * @package    mod_etherpadlite
 * @author     Andreas Grabs <moodle@grabs-edv.de>
 * @copyright  2019 Humboldt-Universität zu Berlin <moodle-support@cms.hu-berlin.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_tool_button implements \renderable, \templatable {
    /** @var array */
    protected $data = [];

    /**
     * Constructor for the test_tool_button class.
     *
     * Initializes the button data and loads necessary JavaScript if a URL is provided.
     *
     * @param \stdClass $mycfg Configuration object, expected to potentially contain a 'url' property.
     */
    public function __construct(\stdClass $mycfg) {
        global $PAGE;
        $this->data = [
            'id' => 'mod-etherpadlite-test-tool-button',
            'title' => get_string('connectiontest', 'etherpadlite'),
        ];

        if (!empty($mycfg->url)) {
            $PAGE->requires->js_call_amd('mod_etherpadlite/test_tool', 'init');
        }
    }

    /**
     * Get the mustache context data.
     *
     * @param  \renderer_base  $output
     * @return \stdClass|array
     */
    public function export_for_template(\renderer_base $output) {
        return $this->data;
    }
}
