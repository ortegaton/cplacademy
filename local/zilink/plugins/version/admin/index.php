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
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/lib.php');
require_once($CFG->libdir . '/adminlib.php');

$context = context_system::instance();
$PAGE->set_context($context);

$urlparams = array('sesskey' => sesskey());
$url = new moodle_url($CFG->httpswwwroot.'/local/zilink/plugins/version/admin/index.php', $urlparams);
$PAGE->https_required();
$PAGE->set_url($CFG->httpswwwroot.'/local/zilink/plugins/version/admin/index.php', $urlparams);
$PAGE->verify_https_required();
$strmanage = get_string('version_page_title', 'local_zilink');

admin_externalpage_setup('zilink_version_settings',null,null,$CFG->httpswwwroot.'/local/zilink/plugins/version/admin/index.php');

$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);

$PAGE->set_pagelayout('report');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('version_title', 'local_zilink'));
echo $OUTPUT->box(get_string('version_title_desc', 'local_zilink'));

$table              = new html_table();
$table->head        = array(get_string('plugins', 'local_zilink'), get_string('version_settings', 'local_zilink'),get_string('version_maturity', 'local_zilink'));
$table->align       = array('left','left','left');
$table->tablealign  = 'center';
$table->width       = '80%';


$table->data[] = array('<b>Moodle</b>',$CFG->release);

if(file_exists($CFG->dirroot .'/local/zilink/package_version.php')) {
    $package  = new stdClass();
    include($CFG->dirroot .'/local/zilink/package_version.php');
    $table->data[] = array('<b>ZiLink Release</b>',$package->release, '');
}

if(file_exists($CFG->dirroot .'/local/zilink/version.php')) {
    $plugin = new stdClass();
    include($CFG->dirroot .'/local/zilink/version.php');
    $table->data[] = array('<b>ZiLink Core</b>',$plugin->release, ZiLinkPluginMaturity($plugin->maturity));
}

$table->data[] = array('<b>ZiLink Core Modules</b>','','');

$path = $CFG->dirroot.'/local/zilink/plugins';
$directories = array();
$ignore = array( '.', '..','core');
$dh = @opendir( $path );

while( false !== ( $file = readdir( $dh ) ) )
{
        if( !in_array( $file, $ignore ) )
        {
            if(is_dir( "$path/$file" ) )
            {
                $directories[$file] = $file;
            }
    }
}
closedir( $dh );

ksort($directories);

foreach($directories as $directory)
{
    if(file_exists($CFG->dirroot.'/local/zilink/plugins/'.$directory.'/version.php'))
    {
        $plugin = new stdClass();
        include($CFG->dirroot.'/local/zilink/plugins/'.$directory.'/version.php');
        $table->data[] = array(get_string($directory, 'local_zilink'),$plugin->release, ZiLinkPluginMaturity($plugin->maturity));  
    }
    else {
        $table->data[] = array(get_string($directory, 'local_zilink'),get_string('plugin_missing', 'local_zilink'),'');
    }
}


$table->data[] = array('<b>ZiLink Blocks</b>','');

if(file_exists($CFG->dirroot .'/blocks/zilink/version.php')) {
    $plugin = new stdClass();
    include($CFG->dirroot .'/blocks/zilink/version.php');
    $table->data[] = array('Super Block',$plugin->release, ZiLinkPluginMaturity($plugin->maturity));
}

$table->data[] = array('<b>ZiLink Enrolments</b>','');

if(file_exists($CFG->dirroot .'/enrol/zilink/version.php')) {
    $plugin = new stdClass();
    include($CFG->dirroot .'/enrol/zilink/version.php');
    $table->data[] = array('Enrolment',$plugin->release, ZiLinkPluginMaturity($plugin->maturity));
}

if(file_exists($CFG->dirroot .'/enrol/zilink_cohort/version.php')) {
    $plugin = new stdClass();
    include($CFG->dirroot .'/enrol/zilink_cohort/version.php');
    $table->data[] = array('Cohort Enrolment',$plugin->release, ZiLinkPluginMaturity($plugin->maturity));
}

if(file_exists($CFG->dirroot .'/enrol/zilink_guardian/version.php')) {
    $plugin = new stdClass();
    include($CFG->dirroot .'/enrol/zilink_guardian/version.php');
    $table->data[] = array('Guardian',$plugin->release, ZiLinkPluginMaturity($plugin->maturity));
}

$table->data[] = array('<b>ZiLink Dependancies</b>','');

if(file_exists($CFG->dirroot .'/blocks/progress/version.php')) {
    $plugin = new stdClass();
    include($CFG->dirroot .'/blocks/progress/version.php');
    $table->data[] = array('Block - Progress',$plugin->version, ZiLinkPluginMaturity($plugin->maturity));
}
else
{
    $table->data[] = array('Block - Progress','MISSING - Please Install','');
}

echo html_writer::table($table,true);

echo $OUTPUT->footer();