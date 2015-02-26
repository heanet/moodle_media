<?php
// Heanet Media filter plugin
// Copyright (C) 2015 swdev@heanet.ie

// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Heanet Media filter settings
 *
 * @package    filter_heanetmedia
 * @copyright  2015 Heanet swdev@heanet.ie
 * @author     Luis Naia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

  $settings->add(new admin_setting_heading(
    'filter_heanetmedia/localinstall',
    new lang_string('introheading', 'filter_heanetmedia'),
    new lang_string('introtext', 'filter_heanetmedia')
  ));

  $settings->add(new admin_setting_configtext(
    'filter_heanetmedia_width',
    new lang_string('width', 'filter_heanetmedia'),
    new lang_string('widthhelp', 'filter_heanetmedia'),
    '640',
    PARAM_NOTAGS
  ));

  $settings->add(new admin_setting_configtext(
    'filter_heanetmedia_height',
    new lang_string('height', 'filter_heanetmedia'),
    new lang_string('heighthelp', 'filter_heanetmedia'),
    '480',
    PARAM_NOTAGS
  ));
}
