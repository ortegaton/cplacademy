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
 * Defines the capabilities for the ZiLink block
 *
 * @package     local_zilink
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @copyright   Includes sub plugins that are based on and/or adapted from other plugins please see sub plugins for credits and notices. 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(dirname(__FILE__) . '/forms/collations.php');

if (!defined('ZILINK_DISABLE_TOOLS_COLLATIONS') && ZILINK_DISABLE_TOOLS_COLLATIONS == true ) {
    redirect($CFG->wwwroot,'', 0);
}

@set_time_limit(0);
raise_memory_limit(MEMORY_HUGE);

$context = context_system::instance();
$PAGE->set_context($context);

$urlparams = array('sesskey' => sesskey());
$PAGE->requires->css('/local/zilink/plugins/tools/styles.css');
$url = new moodle_url($CFG->httpswwwroot.'/local/zilink/plugins/tools/admin/collations.php', $urlparams);
$PAGE->https_required();
$PAGE->set_url($CFG->httpswwwroot.'/local/zilink/plugins/tools/admin/collations.php', $urlparams);
$PAGE->verify_https_required();
$strmanage = get_string('tools_page_title', 'local_zilink');

admin_externalpage_setup('zilink_tools_database_collations',null,null,$CFG->httpswwwroot.'/local/zilink/plugins/tools/admin/collations.php');

$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);

$PAGE->set_pagelayout('report');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('tools_database_title', 'local_zilink')) . ' ';
echo $OUTPUT->box(get_string('tools_database_collations_desc', 'local_zilink'));
echo $OUTPUT->box(get_string('tools_support_desc', 'local_zilink').html_writer::link('https://schoolsict.zendesk.com/hc/',get_string('support_site','local_zilink'),array('target'=> '_blank')));

$form = new zilink_tools_database_collation_form(); 
if ($data = $form->get_data())
{
    echo $OUTPUT->box_start('generalbox', null);
    
    $oldmaxexectime = ini_get('max_execution_time');
    if ($oldmaxexectime < 300) {
        set_time_limit(300);
    }
    
    $dbman = $DB->get_manager();
        
    $DB->set_debug(true);
    
    if (!$DB->execute("ALTER DATABASE `$CFG->dbname` DEFAULT CHARACTER SET $data->tools_database_charset COLLATE $data->tools_database_collation")) {
            echo $OUTPUT->box_end();
            notify('... aborted!');
    } else {
        if($tables = $DB->get_records_sql('SHOW TABLES')) {
            foreach ($tables as $table => $value) {
                $DB->execute("ALTER TABLE `$table` CONVERT TO CHARACTER SET $data->tools_database_charset COLLATE $data->tools_database_collation");
                
                if($columns = $DB->get_records_sql('SHOW COLUMNS FROM '.$table))
                {
                    foreach ($columns as $column => $value) {
                         if (preg_match('~char|text|enum|set~i', $value->type)) {
                            $type = $value->type;
    
                            $DB->execute("ALTER TABLE `$table` MODIFY `$column` $type CHARACTER SET $data->tools_database_charset");
                            $DB->execute("ALTER TABLE `$table` MODIFY `$column` $type COLLATE $data->tools_database_collation");
                        }
    
                    }
                }
            }
        }
        echo $OUTPUT->box_end();
        echo $OUTPUT->single_button($CFG->wwwroot,get_string('done','local_zilink'));
    }
    $DB->set_debug(false);
    
} else{
    echo $form->Display();
}

echo $OUTPUT->footer();