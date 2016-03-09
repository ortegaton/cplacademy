 <?php
    
    global $CFG, $fdata;
    
    require_once(dirname(__FILE__).'/../../../../config.php');
    require_once($CFG->dirroot.'/blocks/mis/cfg/config.php');
    require_once($CFG->dirroot.'/course/lib.php');    
    require_once($CFG->dirroot.'/blocks/mis/lib/lib_facility_db.php');    
    require_once($CFG->dirroot.'/blocks/mis/lib/reports/lib_results.php');
    require_once($CFG->dirroot.'/blocks/mis/lib/reports/class.results.student.php');
    require_once($CFG->dirroot.'/blocks/mis/lib/chart/FusionCharts.php');
    require_once($CFG->dirroot.'/blocks/mis/tabs/assessment/lib/assLibs.php');
    require_once($CFG->dirroot.'/blocks/mis/lib/moodledb.php');
    require_once($CFG->dirroot.'/blocks/mis/lib/reports/class.report.studentexam.php');
    
    $fdata=new facilityData();


    /**
     * @author Guy Thomas     
     * @copyright Guy Thomas, Alan Hardy -  Ossett School / Frederick Gent School 2008 (For Moodle > 1.9)
	 * @copyright Marc Coyles - Ossett Academy 2012 (For Moodle 2.2+)
     * @licence http://www.gnu.org/licenses/gpl.html
     * class tab_assessment
     */
    class tab_assessment extends tab_base{
        
        function init($name){
             $this->setName($name);
         }
         
         function getTitle(){
            $this->title = "Assessment";
            return $this->title;
        }
        
        function getJs(){
            global $CFG;
            $this->js=parent::getJs();
            $gtlibloc=$CFG->wwwroot.'/lib/gtlib_yui';            
            $this->js.='<script type="text/javascript" src="'.$gtlibloc.'/lib.gt.ajax.js"></script>';
            $this->js.='<script type="text/javascript" src="'.$gtlibloc.'/lib.gt.moodle.js"></script>';
            $this->js.='<script type="text/javascript" src="'.$this->blockwww.'/tabs/assessment/js/assessment.js"></script>';
            return ($this->js);
        }
        
        function getContent(){
            global $CFG, $fdata;
            
            // Get facility student id

            if($this->mdluser === false) {
                $content=print_simple_box('Unable to locate this user', '', '', '', '', 'errorbox');
                return $content;
            }
            $studentid = $fdata->getStuAdminNo($this->mdluser->idnumber);
            $content  = html_writer::start_tag('div', array('id'=>'block_mis_wrapper'));
            $content .= html_writer::start_tag('div', array('class'=>'misMain'));
            $datasets=AllDataSets();       

            // create filtered array of datasets
            $setsfilt=array();
            foreach ($datasets as $set){
                $cfgset=db_mis::get_set($set);
                $showset=$cfgset && $cfgset->display==1;                
                if ($showset){
                    $setsfilt[]=$set;
                }            
            }
            
            // use data set according to what was posted
            $fv_dataset=isset($_POST['datasets']) ? $_POST['datasets'] : false;
            if (!$fv_dataset){
                if (!empty($setsfilt)){
                    if (in_array($CFG->mis->cmisDataSet, $setsfilt)){
                        $fv_dataset=$CFG->mis->cmisDataSet;
                    } else {
                        $fv_dataset=$setsfilt[0];
                    }
                } else {
                    $fv_dataset=$CFG->mis->cmisDataSet;
                }
            }
            
            if (!empty($setsfilt)){
                $content .= "    <form id=\"settings\" action=\"" . $this->blockwww."/?userid=".$this->mdlstuid."&tab=assessment\" method=\"post\">";
                $content .= "        <fieldset>";            
                $content .= "<label for='datasets'>Academic Year</label>";            
                $content .= "<select name='datasets' id='datasets'>";            
                foreach ($setsfilt as $set){
                    $selectedStr=$fv_dataset==$set ? " selected='selected'" : "";
                    $content .="<option value='$set'$selectedStr>$set</option>";
                }
                $content .= "</select>";
        
                $content .= "<input type='submit' value='submit' name='submit' />";
                $content .= "</fieldset>";
                $content .= "</form>";
            }
            
            // GT MOD 2008/09/08
            // dont get exams from student result object - it takes too long
            // just get exams using new ExamsForStudent object
            // $stures=new results_students($studentid, array('anyDataSet'=>true, 'dataSets'=>array($fv_dataset)));
            // $results=$stures->getAllResultsByExam();               
            // $exams=$stures->getExams();
            
            $exams=ExamsForStudent($studentid, $fv_dataset);
            
            // student record for selected data set
            $facstu=$fdata->getStudent($this->mdluser->idnumber, '', '', '', 1, $fv_dataset);
            if ($facstu){
                $stuyear=$facstu->year;
            } else {
                $stuyear=-999; // student wasn't here for that dataset
            }
            
            // Build new exams array
            $examsarr=array();
            $now=time(); 
            if (!empty($exams)){
                foreach ($exams as $exam){
                    // is this exam appropriate to the student according to their year group at time of dataset
                    $examyear=db_mis::get_exam_year($fv_dataset, $exam->examid, $stuyear);
                     
                     // Make sure dates are ok.
                    $datesok=false;
                    if (!isset($examyear->displayfrom) || $examyear->displayfrom==null){
                        $datesok=true;
                    } else if ($now>=$examyear->displayfrom){
                        if (!isset($examyear->displayto) || $examyear->displayto==null){
                            $datesok=true;
                        } else if ($examyear->displayto==0){
                            $datesok=true;
                        } else if ($now<=$examyear->displayto){
                            $datesok=true;
                        } else {
                            $datesok=false;
                        }
                    } else {
                        $datesok=false;
                    }
                    
                    if ($examyear && $examyear->display==1 && $datesok){
                        $examsarr[]=$exam;
                    }
                }
            }
        
            if (!empty($examsarr)){
                $content.="\n".'<h1>Select an assessment to view details</h1>';
                $content.="\n".'<ul id="studentexams">';
                
                $rowalt=0;
                $e=0;
                        
                foreach ($examsarr as $exam){                                       
                    $rowalt=$rowalt==0 ? 1 : 0;
                    $e++;
                    $id=$e.'~'.strtolower($exam->examid);
                    $content.="\n".'<li id="'.$id.'" class="collapsed"><span id="clickhandle~'.$id.'" class="clickhandle collapsed">'.$exam->name.'</span></li>';                    
                }
                
                $content.="\n".'</ul>';            
                $content.="\n".'</div>';                
            } else {
                if (!empty($setsfilt)){
                    $content.="\n".'<h1>No assessments exist for the selected academic year!</h1>';
                } else {
                    $content.="\n".'<h1>Sorry, no assessment data is available at the moment</h1>';
                }
            }
            // END GT MOD 2008/09/08
           $content .= html_writer::end_tag('div');
		   $content .= html_writer::end_tag('div');
            return $content;
        }
     }
 
 ?>
 
