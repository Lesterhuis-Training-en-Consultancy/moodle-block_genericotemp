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

/**
 * Form for editing genericotemp block instances.
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package   block_genericotemp
 * @copyright 29/12/2019 Mfreak.nl | LdesignMedia.nl - Luuk Verhoeven
 * @author    Luuk Verhoeven
 */

/**
 * Class block_genericotemp
 */
class block_genericotemp extends block_base {

    /**
     * @throws coding_exception
     */
    function init() {
        $this->title = get_string('pluginname', 'block_genericotemp');
    }

    /**
     * @return bool
     */
    function has_config() {
        return true;
    }

    /**
     * @return array
     */
    function applicable_formats() {
        return ['all' => true];
    }

    /**
     * @throws coding_exception
     */
    function specialization() {
        if (isset($this->config->title)) {
            $this->title = $this->title = format_string($this->config->title, true, ['context' => $this->context]);
        } else {
            $this->title = get_string('newhtmlblock', 'block_genericotemp');
        }
    }

    /**
     * @return bool
     */
    function instance_allow_multiple() {
        return true;
    }

    /**
     * @return stdClass|stdObject
     * @throws coding_exception
     */
    function get_content() {
        global $CFG;

        require_once($CFG->libdir . '/filelib.php');

        if ($this->content !== null) {
            return $this->content;
        }

        $filteropt = new stdClass;
        $filteropt->overflowdiv = true;
        if ($this->content_is_trusted()) {
            // fancy html allowed only on course, category and system blocks.
            $filteropt->noclean = true;
        }

        $this->content = new stdClass;
        $this->content->footer = '';
        if (isset($this->config->text)) {
            // rewrite url
            $this->config->text = file_rewrite_pluginfile_urls($this->config->text, 'pluginfile.php', $this->context->id, 'block_genericotemp', 'content', null);
            // Default to FORMAT_HTML which is what will have been used before the
            // editor was properly implemented for the block.
            $format = FORMAT_HTML;
            // Check to see if the format has been properly set on the config
            if (isset($this->config->format)) {
                $format = $this->config->format;
            }
            $this->content->text = format_text($this->config->text, $format, $filteropt);
        } else {
            $this->content->text = '';
        }

        unset($filteropt); // memory footprint

        return $this->content;
    }

    /**
     * @param core_renderer $output
     *
     * @return stdClass
     * @throws coding_exception
     */
    public function get_content_for_external($output) {
        global $CFG;
        require_once($CFG->libdir . '/externallib.php');

        $bc = new stdClass;
        $bc->title = null;
        $bc->content = '';
        $bc->contenformat = FORMAT_MOODLE;
        $bc->footer = '';
        $bc->files = [];

        if (!$this->hide_header()) {
            $bc->title = $this->title;
        }

        if (isset($this->config->text)) {
            $filteropt = new stdClass;
            if ($this->content_is_trusted()) {
                // Fancy html allowed only on course, category and system blocks.
                $filteropt->noclean = true;
            }

            $format = FORMAT_HTML;
            // Check to see if the format has been properly set on the config.
            if (isset($this->config->format)) {
                $format = $this->config->format;
            }
            [$bc->content, $bc->contentformat] =
                external_format_text($this->config->text, $format, $this->context, 'block_genericotemp', 'content', null, $filteropt);
            $bc->files = external_util::get_area_files($this->context->id, 'block_genericotemp', 'content', false, false);

        }

        return $bc;
    }

    /**
     * Serialize and store config data
     */
    function instance_config_save($data, $nolongerused = false) {
        $config = clone($data);
        // Move embedded files into a proper filearea and adjust HTML links to match
        $config->text = file_save_draft_area_files($data->text['itemid'], $this->context->id, 'block_genericotemp', 'content', 0, ['subdirs' => true], $data->text['text']);
        $config->format = $data->text['format'];

        parent::instance_config_save($config, $nolongerused);
    }

    function instance_delete() {
        global $DB;
        $fs = get_file_storage();
        $fs->delete_area_files($this->context->id, 'block_genericotemp');

        return true;
    }

    /**
     * Copy any block-specific data when copying to a new block instance.
     *
     * @param int $fromid the id number of the block instance to copy from
     *
     * @return boolean
     */
    public function instance_copy($fromid) {
        $fromcontext = context_block::instance($fromid);
        $fs = get_file_storage();
        // This extra check if file area is empty adds one query if it is not empty but saves several if it is.
        if (!$fs->is_area_empty($fromcontext->id, 'block_genericotemp', 'content', 0, false)) {
            $draftitemid = 0;
            file_prepare_draft_area($draftitemid, $fromcontext->id, 'block_genericotemp', 'content', 0, ['subdirs' => true]);
            file_save_draft_area_files($draftitemid, $this->context->id, 'block_genericotemp', 'content', 0, ['subdirs' => true]);
        }

        return true;
    }

    /**
     * @return bool
     * @throws coding_exception
     */
    function content_is_trusted() {
        global $SCRIPT;

        if (!$context = context::instance_by_id($this->instance->parentcontextid, IGNORE_MISSING)) {
            return false;
        }
        //find out if this block is on the profile page
        if ($context->contextlevel == CONTEXT_USER) {
            if ($SCRIPT === '/my/index.php') {
                // this is exception - page is completely private, nobody else may see content there
                // that is why we allow JS here
                return true;
            } else {
                // no JS on public personal pages, it would be a big security issue
                return false;
            }
        }

        return true;
    }

    /**
     * The block should only be dockable when the title of the block is not empty
     * and when parent allows docking.
     *
     * @return bool
     */
    public function instance_can_be_docked() {
        return (!empty($this->config->title) && parent::instance_can_be_docked());
    }

    /**
     * Add custom html attributes to aid with theming and styling
     *
     * @return array
     */
    function html_attributes() {
        global $CFG;

        $attributes = parent::html_attributes();

        if (!empty($CFG->block_genericotemp_allowcssclasses)) {
            if (!empty($this->config->classes)) {
                $attributes['class'] .= ' ' . $this->config->classes;
            }
        }

        return $attributes;
    }
}
