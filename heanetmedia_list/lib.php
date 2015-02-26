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
 * Heanet Media repository list
 *
 * @package    repository_heanetmedia_list
 * @copyright  2015 Heanet swdev@heanet.ie
 * @author     Luis Naia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/repository/lib.php');

class repository_heanetmedia_list extends repository {

    public function __construct(
        $repositoryid,
        $context = SYSCONTEXTID,
        $options = array()
    ) {
        parent::__construct($repositoryid, $context, $options);
    }

    public static function get_type_option_names() {
        return array('user_guid', 'pluginname');
    }

    public static function type_config_form(
        $mform,
        $classname = 'repository_heanetmedia_list'
    ) {
        $user_guid = get_config('heanetmedia', 'user_guid');

        if (empty($user_guid)) {
            $user_guid = '';
        }

        $mform->addElement('hidden', 'pluginname',
            get_string('pluginname', 'repository_heanetmedia_list'),
            array('value'=> "",'size' => '40'));

        $mform->addElement('text', 'user_guid', 'Heanet Media GUID',
            array('value'=>$user_guid,'size' => '40'));

        $mform->addRule(
            'user_guid',
            get_string('userguiderror', 'repository_heanetmedia_list'),
            'required',
            null,
            'client'
        );
    }

    public function get_listing($path = '', $page = '') {
        $ret  = array();
        $ret['nologin'] = true;
        $ret['page'] = (int)$page;
        if ($ret['page'] < 1) {
            $ret['page'] = 1;
        }
        $ret['list'] = $this->_get_collection();
        $ret['norefresh'] = true;
        $ret['nosearch'] = true;
        return $ret;
    }

    private function _get_collection() {
        $curl = new curl();
        $content = $curl->get('http://localhost:3000/search');
        $items = json_decode($content, true);

        array_walk($items, function(&$item, $key) {
          if (empty($item['title'])) {
            $item['title'] = 'n/a';
          }
          // This is a hack so that moodles file picker accepts this file by
          // extension
          $item['title'] = $item['title'] . '.avi';
        });
        return $items;
    }

    public function global_search() {
        return false;
    }

    public function supported_filetypes() {
        return array('video');
    }

    public function supported_returntypes() {
        return FILE_EXTERNAL;
    }

    public function contains_private_data() {
        return false;
    }
}
