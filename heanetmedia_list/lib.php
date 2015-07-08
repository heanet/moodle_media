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

    protected $list_url;
    protected $bind_url;
    protected $check_login_url;

    public function __construct(
        $repositoryid,
        $context = SYSCONTEXTID,
        $options = array()
    ) {
        parent::__construct($repositoryid, $context, $options);

        $this->list_url = "https://media.heanet.ie/api/1.0/media_list.php?";
        $this->check_login_url = "https://media.heanet.ie/api/1.0/media_list.php?";
        $this->bind_url = "https://media.heanet.ie/secure/account/user_setguid.php?";
    }

    public static function plugin_init() {
        set_config('moodle_uniqid', uniqid(), 'heanetmedia');
        return true;
    }

    public static function get_type_option_names() {
        return array('user_guid', 'pluginname');
    }

    public static function type_config_form($mform,
        $class = 'repository_heanetmedia_list') {
        parent::type_config_form($mform);
    }

    public function get_listing($path = '', $page = '') {
        $ret  = array();
        $ret['nologin'] = false;
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
        $response = $this->get_media_list();

        if (empty($response['Data'])) {
            $message = __CLASS__ . ' says: '
            . get_string('missing_data', 'repository_heanetmedia_list')
            . json_encode($items);
            error_log($message);
            $response['Data'] = array();
        }

        $items = $response['Data'];

        array_walk($items, function(&$item, $key) {
          if (empty($item['title'])) {
            $item['title'] = 'n/a';
          }

          if (empty($item['thumbnail_width'])) {
            $item['thumbnail_width'] = 128;
          }

            if (empty($item['thumbnail_height'])) {
            $item['thumbnail_height'] = 128;
          }

          // This is a hack so that moodle file picker accepts this file
          $item['title'] = $item['title'] . '.avi';
        });
        return $items;
    }

    public function get_media_list () {
        $curl = new curl();
        $content = $curl->get($this->wrap_url($this->list_url));
        return json_decode($content, true);
    }

    public function check_login() {
        $curl = new curl();
        $content = $curl->get($this->wrap_url($this->check_login_url));
        $items = json_decode($content, true);
        return isset($items['Status']) && $items['Status'] === 'Found';
    }

    public function wrap_url($url) {
        global $USER;

        $website_guid = get_config('heanetmedia', 'moodle_uniqid');
        if (empty($website_guid)) {
            $message = __CLASS__ . ' says: '
            . get_string('missing_guid', 'repository_heanetmedia_list');
            error_log($message);
        }

        $user_id_hash = md5($USER->id . $website_guid);
        error_log("user id hash: $user_id_hash");
        return $url . "UserGUID=$user_id_hash";
    }

    public function print_login($ajax = true) {
        $ret = array();
        $ret['object'] = array();
        $ret['object']['type'] = 'text/html';
        $ret['object']['src'] = $this->wrap_url($this->bind_url);
        return $ret;
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
