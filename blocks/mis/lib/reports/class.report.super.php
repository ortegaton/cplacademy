<?php

//
// Author: Guy Thomas
// Date:2007 - 10 - 30
// Purpose: SUPER Class for reports
//

global $CFG, $extenderDb;

require_once(dirname(__FILE__).'/../../../../config.php');
require_once($CFG->dirroot.'/blocks/mis/cfg/config.php');
require_once($CFG->dirroot.'/blocks/mis/lib/urllib.php');
require_once('lib_results.php');
require_once('class.propsbyparam.php');
require_once('lib_extender_db.php');


class superreport extends propsbyparam{

    //
    // protected properties (only this class and subclasses can access)
    //    
    protected $_args; // arguements on construction
    protected $_outputArray=array(); // e.g. an array of totaled values    
    protected $_reportName=null; // this must be set by all sub classes or caching will fail
    protected $_cacheAge=array('days'=>0, 'hours'=>0, 'minutes'=>30); // array number of days, hours, minutes old for a cached report
    protected $_cacheId;
    protected $_fdata;
     
    //
    // Optional public properties (passed in params)
    //
    var $cfg=null;
    var $refreshCash=false; // force cash refresh
    var $primarySet='';
    var $dataSets=array(); // this will be set later if not specified in parameters
    var $anyDataSet=false;
    var $examIds=array();
    var $anyDSCriteriaInc=array(); // array of criteria to include on merge
    var $anyDSCriteriaExc=array(); // array of criteria to exclude on merge    
    var $columns=array();   // array of columns to display (excluding rowcat Column)
                            // each column must be an array with a 'code' and 'title' value
                            // - e.g. array(array('code'=>'l2', 'title'=>'Level2s'), array('code'=>'l3', 'title'=>'Level3s'))
                            // each column can have an optional attributes value (applies to all cells in column)
                            // - e.g. array(array('code'=>'l2', 'title'=>'Level2s', 'colatts'=>array('style'=>'background-color:black')))
                            // each column can have an optional title attributes value (applies only to the column title cell)
                            // - e.g. array(array('code'=>'l2', 'title'=>'Level2s', 'cellatts'=>array('style'=>'background-color:black')))
                            
    var $rowcatColumnTitle='Perspective';
    var $rowcatColumnHide=false;
    var $rowcatLinks=array(); // rowcat links provide links to specific rowcat within the table.
    var $headerRowAtts=array(); // attributes for table header row
    var $reportTitle='report'; // title to be used in exports, etc.
    
    //
    // Purpose: Class constructor
    //
    function superreport ($params=array()){
    
        // set config property
        global $CFG, $fdata;
        $this->cfg=$CFG->mis;
        $this->cfg->reporturlbase=get_mis_blockwww().'/lib/reports/';
        
        // set db property
        $this->setdb();
    
        // set default columns
        $this->setDefaultColumns();
        
        //get properties by parameters
        $this->_args=func_get_args();   
        if (!empty($params)){
            parent::propsbyparam($params);
        }
        
        // set datasets
        $this->setDataSets();
        
        // provide global extender database
        $this->extenderDb();
        
    }
    
    protected function setdb(){
		// If msdb hasn't been set then try global variable
		if(!isset($this->_fdata)){
            if (isset($GLOBALS['fdata'])){
                $this->_fdata=$GLOBALS['fdata'];
            } else {
                $GLOBALS['fdata']=new facilityData();
                $this->_fdata=$GLOBALS['fdata'];
            }
		}    
    }
    
    //
    // Purpose: All functions must have this function
    //
    protected function setDefaultColumns(){
    }
    
    //
    // Purpose: Forces the dataSets property to have a value
    //
    protected function setDataSets(){   
        // If dataSets is empty, set it to current dataset or all datasets before and including current (if anyDataSet is true)
        if (empty($this->dataSets)){
            // get primary or config data set
            $firstSet=$this->primarySet!='' ? $this->primarySet : $this->cfg->cmisDataSet;        
            // If any data set is relevant, build array of all datasets before and including first dataset
            if ($this->anyDataSet){
                $this->dataSets=array_unique(array_merge(array($firstSet), DataSetsBeforeSpecific($firstSet)));
            } else {
                // Else, set dataSets to primary or config data set
                $this->dataSets=array($firstSet);
            }
        } else {     
            if ($this->anyDataSet){
                $this->dataSets=array_unique(array_merge($this->dataSets, DataSetsBeforeSpecific($this->dataSets[0])));
            }
        }
        
        // make sure primarySet is set
        $this->primarySet=$this->primarySet!='' ? $this->primarySet : $this->dataSets[0];
        
    }
    
    //
    // Purpose: Converts an attributes array to a string
    //
    protected function attributesArrayToString($attributes){
        $atSt='';        
        foreach ($attributes as $key=>$val){
            $atSt.=' ';
            $atSt.=$key.='="'.$val.'"';
        }
        return ($atSt);
    }
    
    //
    // Purpose: Merges attribute arrays (intended to merge column attributes with cell attributes)
    //
    protected function mergeAttributesArray($atts1, $atts2){
        $atts1=array_change_key_case($atts1, CASE_LOWER);
        $atts2=array_change_key_case($atts2, CASE_LOWER);
        foreach ($atts1 as $key=>$val){
            $glue=$key=='style' ? '; ' : ' ';
            // add glue if last character of att1 value is not glue
            if ($glue!=substr($val, 0-strlen($glue))){
                $val.=$glue;
            }
            // add value from $atts2 to value from $atts1
            $val.=$atts2[$key];
            $atts1[$key]=$val;
        }
        return ($atts1);
    }
    
    
    //
    // Purpose: Set global extender db if not already set
    //
    function extenderDb (){
        global $extenderDb;
        if (!isset($extenderDb)){
            $extenderDb=connectExtenderDB($this->cfg);
        }
    }
    
    //
    // Purpose: Returns totals array
    //
    function getOutput (){
        return ($this->_outputArray);
    }
    
    //
    // Purpose: Get cache age
    //
    function getCacheAge(){
        return ($this->_cacheAge);
    }
        
    //
    // Purpose: getCacheOrBuild()
    //
    function getCacheOrBuild ($refreshCash=null){
        global $extenderDb, $DB;
        $refreshCash=is_null($refreshCash) ? $this->refreshCash : $refreshCash;
        $this->refreshCash=$refreshCash;
       
        // Get cached output
        if (!is_null($this->_reportName)){
            //$sql='SELECT * FROM {block_mis_report_cache} WHERE reportname= ? AND args= ? ';
            $cacheRow=$DB->get_record('block_mis_report_cache',array('reportname'=>$this->_reportName,'args'=>str_replace('\'', '~sq~', serialize($this->_args))));
            if ($cacheRow){
                // get # days cached
                $cachedate=strtotime($cacheRow->datecached);
                $days=intval((time()-$cachedate)/86400);
                $hours=intval((((time()-$cachedate)/86400)-$days)*24);      
                $minutes=intval((((((time()-$cachedate)/86400)-$days)*24)-$hours)*60);
                $this->_cacheAge=array('days'=>$days, 'hours'=>$hours, 'minutes'=>$minutes);
                // if cash is too old then force refresh
                if ($this->_cacheAge['days']>=$this->cfg->eeDbMaxCacheAge['days'] && 
                $this->_cacheAge['hours']>=$this->cfg->eeDbMaxCacheAge['hours'] &&
                $this->_cacheAge['minutes']>$this->cfg->eeDbMaxCacheAge['minutes']){
                    $this->refreshCash=true;
                }
                $this->_cacheId=$cacheRow->id;
            }
        }
        
        if ($this->refreshCash || !$cacheRow){
            $this->build(); // force build of report            
            $sqlType=!$cacheRow ? 'INSERT' : 'UPDATE';
            // update cache
            $table='block_mis_report_cache';
			$misrecord = new stdClass();
            $misrecord->reportname=$this->_reportName;
            $misrecord->args=str_replace('\'', '~sq~', serialize($this->_args));
            $misrecord->cached=str_replace('\'', '~sq~', serialize($this->_outputArray));
            $misrecord->datecached=date('Y-m-d H:i:s');

            
            if ($sqlType=='INSERT'){
                $this->_cacheId=$DB->insert_record($table,$misrecord,$true);
            } else {
				$misrecord->id=$cacheRow->id;
				$DB->update_record($table,$misrecord);
                //$extenderDb->AutoExecute($table, $record, 'UPDATE', 'id='.$cacheRow['id']);
            }
            $this->_cacheAge=array('days'=>0, 'hours'=>0, 'minutes'=>0);// brand new cache
        } else {
            $this->_outputArray=unserialize(str_replace('~sq~', '\'', $cacheRow['cached']));
        }
    }
    
    //
    // Purpose: All sub-classes MUST have this function
    // report engine - gets the data
    // This function is never directly called - it is called by the class superreport::getCacheOrBuild
    //
    protected function build(){
    }
    
    //
    // Purpose: Get cache id
    //
    function getcacheid(){
        $cacheid=isset($this->_cacheId) ?  $this->_cacheId : false;
        return ($cacheid);
    }
    
    //
    // Purpose: Render (to a string) a HTML table based on data in $_outputArray
    //
    function renderHTMLTable($className='epExt_table', $caption=''){
    
        $className=$className!=null ? $className : 'epExt_table';
        $output='<table class="'.$className.'"><caption>'.$caption.'</caption>';
        
        // If header has attributes then create attribute string
        if (!empty($this->headerRowAtts)){
            $rowAtSt=' '.$this->attributesArrayToString($this->headerRowAtts);
        } else {
            $rowAtSt='';
        }
        
        // Add table head data
        $output.='<thead><tr'.$rowAtSt.'>';
        // first column title is always rowcat title (unless hidden)
        if (!$this->rowcatColumnHide){
            $output.='<th>'.$this->rowcatColumnTitle.'</th>';
        }
        foreach ($this->columns as $col){        
            // Apply column title cell attributes
            if (isset($col['cellatts'])){
                $atSt=$this->attributesArrayToString($col['cellatts']);
            } else {
                $atSt='';
            }
            // Apply column attributes (for entire column)
            if (isset($col['colatts'])){
                $atSt.=' '.$this->attributesArrayToString($col['colatts']);
            }     
            $output.='<th'.$atSt.'>'.$col['title'].'</th>';
        }
        $output.='</tr></thead>';
        
        // Add table body data
        $output.='<tbody>';        
        foreach ($this->_outputArray as $rowcat=>$row){
            $output.='<tr>';
            // first column title is always rowcat key (unless hidden)
            if (!$this->rowcatColumnHide){
                if (isset($this->rowcatLinks[$rowcat])){
                    $rowcatHTML='<a href="'.$this->rowcatLinks[$rowcat].'">'.$rowcat.'</a>';
                } else {
                    $rowcatHTML=$rowcat;
                }
                $output.='<td>'.$rowcatHTML.'</td>';
            }
            foreach ($this->columns as $col){
                $colcode=$col['code'];
                               
                // If this cell is empty then give it an empty val
                if (!isset($row[$colcode])){
                    $row[$colcode]['val']='';
                }
                
                unset ($attributesArray);
                // try merge cell attributes with entire column attributes
                if (isset($row[$colcode]['cellatts'])){
                    if (isset($col['colatts'])){
                        $attributesArray=$this->mergeAttributesArray($col['colatts'], $row[$colcode]['cellatts']);
                    } else {
                        $attributesArray=$row[$colcode]['cellatts'];
                    }
                } else if (isset($col['colatts'])){
                    $attributesArray=$col['colatts'];
                }
                
                // apply attributes for cell (and entire column if merged)
                if (isset($attributesArray)){
                    $atSt=$this->attributesArrayToString($attributesArray);
                } else {
                    $atSt='';
                }
                
                $output.='<td'.$atSt.'>'.$row[$colcode]['val'].'</td>';
                
            }
            $output.='</tr>';
        }
        
        $output.='</tbody>';
        $output.='</table>';
        // Add export to excel if report is cached
        $cacheid=$this->getcacheid();
        if ($cacheid!==false){
            $output.='<div style="margin-top:8px"><a class="fileLink ext_xls" href="'.$this->cfg->reporturlbase.'excelreport.php?cacheid='.$cacheid.'&amp;filename='.$this->reportTitle.'">Export to Excel</a></div>';
        }
        return ($output);
    }
    
    //
    // Purpose : Exports to Excel (must be called without anything written to page)
    //
    function renderExcel($filename, $sheetname){
    
        header("Content-Type: application/force-download"); // set header to force download
        header("Content-Description: File Transfer");
        header("Content-Transfer-Encoding: binary");
    	
		global $fdata;
        
        $filename=str_ireplace('.xls', '', $filename); // remove .xls
        $filename=$this->strToFileName($filename); // make file name excel friendly
        $filename.='.xls'; // re -add .xls
        
        $sheetname=$this->strToFileName($sheetname); // make sheet name excel friendly
        
	
		require_once(dirname(__FILE__)."/lib_results.php");
		require_once(dirname(__FILE__)."/excel/Writer.php");
	
		// Creating a workbook
		$wb = new Spreadsheet_Excel_Writer();
						
		// sending HTTP headers
		$wb->send($filename);
				
		// Creating a worksheet
		$ws =& $wb->addWorksheet($sheetname);

        $r=0; // row
        $c=0; // column
        
        // Write header
        // first column title is always rowcat title (unless hidden)
        if (!$this->rowcatColumnHide){
            $ws->write($r, $c, $this->rowcatColumnTitle);
            $c++;
        }
        foreach ($this->columns as $col){  
            $ws->write($r, $c, $col['title']);
            $c++;
        }     
        $c=0;
        $r=1;
        
        // Write rows        
        foreach ($this->_outputArray as $rowcat=>$row){
            $c=0;
            // first column title is always rowcat key (unless hidden)
            if (!$this->rowcatColumnHide){
                $ws->write($r, $c, $rowcat);
                $c++;
            }
            foreach ($this->columns as $col){
                $colcode=$col['code'];
                
                // If this cell is empty then give it an empty val
                if (!isset($row[$colcode])){
                    $row[$colcode]['val']='';
                }

                $ws->write($r, $c, $row[$colcode]['val']);
                $c++;
                
            }
            $r++;
        }        
        
        // Write the file
        $wb->close();
    }
    
    //
    // Purpose: Converts a string to a filename friendly string (note that str should not include the directory - it should only be the file portion of the path
    //
	function strToFileName($str){		
		$bannedChars=array("*", "<", ">", "[", "]", "=", "+", "\"", "\\", "/", ",",".",":",";");
						
		foreach ($bannedChars as $banned){
			$str=str_replace($banned,"_", $str);
		}
		
		return ($str);
		
	}    
    
    //
    // Purpose: Render (to a string) XML for producing swf chart
    //
    function renderXMLChart($charttype=''){
        $charttypexml=$charttype!='' ? '<chart_type>'.$charttype.'</chart_type>' : '';
        $output='<chart>';
        $output.=$charttypexml;    
        $output.='<chart_data>';               
        // Add table head data
        $output.='<row>';
        $output.='<null/>'; // rowcat title is not used for charts
        foreach ($this->columns as $col){
            $output.='<string>'.$col['title'].'</string>';
        }        
        $output.='</row>';
        
        // Add table body data
        foreach ($this->_outputArray as $rowcat=>$row){
            $output.='<row>';
            // first column is always rowcat key
            $output.='<string>'.$rowcat.'</string>';
            foreach ($this->columns as $col){
                $colcode=$col['code'];
                if (is_numeric($row[$colcode]['val'])){
                    $output.='<number>'.$row[$colcode]['val'].'</number>';
                } else {
                    $output.='<string>'.$row[$colcode]['val'].'</string>';
                }
            }
            $output.='</row>';
        }        
        
        $output.='</chart_data>';      
        $output.='</chart>';
        return ($output);
    }
  
}