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
 * Heanet Media filter
 * Most of this code was taken from Moodle filter mediaplugin
 * It was then changed to match the simply needs of Heanet.3
 *
 * @package    filter_heanetmedia
 * @copyright  2015 Heanet swdev@heanet.ie
 * @author     Luis Naia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class filter_heanetmedia extends moodle_text_filter
{
    /** @var bool True if currently filtering trusted text */
    private $trusted;

    /** @var core_media_renderer Media renderer */
    private $mediarenderer;

    /** @var string Partial regex pattern indicating possible embeddable content */
    private $embedmarkers;

    public function filter($text, array $options = array())
    {

        global $CFG, $PAGE;

        if (!is_string($text) or empty($text)) {
            // non string data can not be filtered anyway
            return $text;
        }

        if (stripos($text, '</a>') === false) {
            // Performance shortcut - if not </a> tag, nothing can match.
            return $text;
        }

        // Looking for tags.
        $matches = preg_split('/(<[^>]*>)/i', $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        if (!$matches) {
            return $text;
        }

        // Regex to find media extensions in an <a> tag.
        $re = '~<a\s[^>]*href="([^"]*(?:' . $this->embedmarkers . ')[^"]*)"[^>]*>([^>]*)</a>~is';

        $newtext = '';
        $validtag = '';
        $sizeofmatches = count($matches);

        // We iterate through the given string to find valid <a> tags
        // and build them so that the callback function can check it for
        // embedded content. Then we rebuild the string.
        foreach ($matches as $idx => $tag) {
            if (preg_match('|</a>|', $tag) && !empty($validtag)) {
                $validtag .= $tag;

                // Given we now have a valid <a> tag to process it's time for
                // ReDoS protection. Stop processing if a word is too large.
                if (strlen($validtag) < 4096) {
                    $processed = preg_replace_callback($re, array($this, 'callback'), $validtag);
                }
                // Rebuilding the string with our new processed text.
                $newtext .= !empty($processed) ? $processed : $validtag;
                // Wipe it so we can catch any more instances to filter.
                $validtag = '';
                $processed = '';
            } else if (preg_match('/<a\s[^>]*/', $tag) && $sizeofmatches > 1) {
                // Looking for a starting <a> tag.
                $validtag = $tag;
            } else {
                // If we have a validtag add to that to process later,
                // else add straight onto our newtext string.
                if (!empty($validtag)) {
                    $validtag .= $tag;
                } else {
                    $newtext .= $tag;
                }
            }
        }

        // Return the same string except processed by the above.
        return $newtext;
    }

    /**
     * Replace link with embedded content, if supported.
     *
     * @param array $matches
     * @return string
     */
    private function callback(array $matches)
    {
        global $CFG;

        // Get name.
        $name = trim($matches[2]);
        if (empty($name) or strpos($name, 'http') === 0) {
            $name = ''; // Use default name.
        }

        $regex = '/https:\/\/media.heanet.ie\/player\/.+/';
        if (preg_match($regex, $matches[1])) {
          $width = $CFG->filter_heanetmedia_width;
          $height = $CFG->filter_heanetmedia_height;
          $result = '<iframe src="' . $matches[1] .'" width="' . $width . '"'
            .' height="' . $height. '" marginwidth="0" marginheight="0" '
            .'scrolling="no" frameborder="0" webkitAllowFullScreen '
            .'allowFullScreen></iframe>';
        }

        if (isset($result) && $result !== '') {
            return $result;
        } else {
            return $matches[0];
        }
    }
}
