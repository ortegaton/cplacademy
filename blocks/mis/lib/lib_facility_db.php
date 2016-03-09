<?php

/**
 * This file contains the facility database class
 *
 * @author Alan Hardy / Guy Thomas
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package mis
 * @copyright Alan Hardy / Guy Thomas 2008
 */

// REQUIRES ADODB TO BE LOADED


global $CFG;

require_once ($CFG->dirroot .'/lib/adodb/adodb.inc.php');

// include adodb exceptions file if debug mode is true
if ($CFG->mis->debug){
    require_once ($CFG->dirroot .'/lib/adodb/adodb-exceptions.inc.php');
}


class facilityData{
    var $db;
    var $dbtype;
    var $prefix; // table prefix
    var $debug; // debug mode on or off
    var $lqrystarttime; // last query run start time
    var $lqryendtime; // last query run end time
    var $lqryexectime; // last query run execution time
    
    /**
     * FacilityData class constructor
     * automatically runs DB connect function
     * @param boolean $debug // GT mod 2009012200 - enable config debug mode to be overriden
     * @return object
    */
    function facilityData($debug=null){
        global $CFG; $fdata;
        
        // set debug mode
        if ($debug===null){
            $this->debug=$CFG->mis->debug;
        } else {
            $this->debug=$debug;
        }
        
        $this->prefix=$CFG->mis->cmisTabPrefix;
        // GT MOD - if facility data model already exists then don't bother reconnecting
        if (isset($fdata)){
            $this->db = $fdata->db;
            $this->dbtype=$CFG->mis->cmisDBType;
        } else {
            $this->db = $this->facilityConnect();
        }
    }
    
    /**
     * GT MOD 2009012200
     * return array of dangerous expressions
     * @return array
     */
    function sqldangers(){
        $dangers=array(
            'update',
            'insert',
            'delete',
            'create',
            'create db',
            'create table',
            'create index',
            'drop',
            'alter'
        );
        return ($dangers);
    }
    
    /**
    * Connect to the facility database
    *
    * @uses $CFG
    * @return object
    */
    function facilityConnect($persistent=false){
        global $CFG;
        @define('ADODB_ASSOC_CASE', 0); // set all fields to lower case for this database  
        @define('DB_MODE_DSN', 10);
        @define('DB_MODE_DB', 1);

        $this->dbtype=$CFG->mis->cmisDBType;   

        // GT MOD 2009011600 - set mode according to whether cmisDSN specified
        if (isset($CFG->mis->cmisDSN)){
            $mode=DB_MODE_DSN;
        } else {
            $mode=DB_MODE_DB;
        }

        if ($this->dbtype=="mssql"){
            $this->debuglog('Attempting Facility Database Connection to', array('style'=>'color:blue'));
            
            // GT MOD 2009011600 - Report connection mode according to config
            if ($mode==DB_MODE_DB){
                $this->debuglog('SERVER: '.$CFG->mis->cmisDBServer, array('style'=>'color:orange'));
                $this->debuglog('DATABASE: '.$CFG->mis->cmisDB, array('style'=>'color:orange'));
            } else {
                $this->debuglog('DSN: '.$CFG->mis->cmisDSN, array('style'=>'color:orange'));
            }
            $this->debuglog('AS: '.$CFG->mis->cmisDBUser, array('style'=>'color:orange'));


            // GT MOD 2009011600 - Only try dsn mode if dsn config variable exists or OS is windows
            // NOTE: linux users using unixODBC- set $CFG->mis->dsn to your unixODBC DSN entry in odbc.ini
            if ($mode==DB_MODE_DSN){
                $db=$this->_mssql_connect_odbc($persistent);

                // if connection fails with odbc_mssql then try mssql extension connection instead
                if (!$db || !$db->IsConnected()){
                    $this->debuglog('Could not connect with "odbc_mssql" extension, now trying "mssql"', array('style'=>'color:blue'));
                    $db=$this->_mssql_connect($persistent);
                }
            } else {
            
                if (stristr(PHP_OS, 'WIN')){
                    // If windows, connect via odbc - dsn will be created even if not set
                    $db=$this->_mssql_connect_odbc($persistent);
                } else {            
                    // If not windows, connect via mssql adodb mode
                    $db=$this->_mssql_connect($persistent);              
                }
            }
        } else if ($CFG->mis->cmisDBType=="access"){
            $db=$this->_msaccess_connect();
        }

        @$db->SetFetchMode(ADODB_FETCH_ASSOC);
        if (!$db || !$db->IsConnected()){
            $this->debuglog('Failed to connect to database!', array('class'=>'debug_error'));
        }else{
            $this->debuglog('Successfully connected to database!', array('class'=>'debug_success'));
        }
        return ($db);
    }
    
    /**
     * Connect via odbc adodb mode
     */
    private function _mssql_connect_odbc($persistent=false){
        global $CFG;       
        $dsnset=isset($CFG->mis->cmisDSN) && $CFG->mis->cmisDSN!=''; // is dsn set in config?
        $dsn=$dsnset ?  $CFG->mis->cmisDSN : 'Driver={SQL Server};Server='.$CFG->mis->cmisDBServer.';Database='.$CFG->mis->cmisDB.';'; // use config dsn or create
        $db = ADONewConnection('odbc_mssql');
        if (!$persistent){
            @$db->Connect($dsn,$CFG->mis->cmisDBUser, $CFG->mis->cmisDBPwd);
        } else {
            @$db->PConnect($dsn,$CFG->mis->cmisDBUser, $CFG->mis->cmisDBPwd);
        }
        return ($db);
    }

    /**
     * Connect via mssql adodb mode
     */    
    private function _mssql_connect($persistent=false){
        global $CFG;
        $db = ADONewConnection('mssql');
        $db->Connect($CFG->mis->cmisDBServer, $CFG->mis->cmisDBUser, $CFG->mis->cmisDBPwd, $CFG->mis->cmisDB);
        return ($db);
    }
    
    /**
     * Connect to access db
     */
     private function _msaccess_connect(){
        global $CFG;
        //NOTE: NEVER BOTHER WITH PERSISTENT CONNECTIONS WITH MS ACCESS
        $db =& ADONewConnection('access');
        $dsn = 'Driver={Microsoft Access Driver (*.mdb)};Dbq='.$CFG->mis->cmisDBQ.';Uid='.$CFG->mis->cmisDBUser.';Pwd='.$CFG->mis->cmisDBPwd.';';
        $db->Connect($dsn);
        return ($db);
     }


    /**
    * Convert the contents of a recordset into an associative array
    *
    * @uses $CFG
    * @param object $rs required - recordset
    * @return object
    * NOTE: This will only work as you expect if every row has a unique first column, otherwise it will collapse the data
    */
    function recordset_to_assoc_array($rs) {
        global $CFG;
        if ($rs && $rs->RecordCount() > 0) {
            $firstcolumn = $rs->FetchField(0);
            /// Get the whole associative array
            if ($records = $rs->GetAssoc(true)) {
                foreach ($records as $key => $record) {
                    // GT MOD - force lower case for array hash before its re-cased as an object
                    $record=array_change_key_case($record, CASE_LOWER);                    
                    $record[strtolower($firstcolumn->name)] = $key;/// Re-add the assoc field
                    $objects[$key] = (object) $record; /// To object
                }
                return ($objects);
                /// Fallback in case we only have 1 field in the recordset. MDL-5877
            } else if ($rs->_numOfFields == 1 && $records = $rs->GetRows()) {
                    foreach ($records as $key => $record) {
                        // GT MOD - force lower case for array hash before its re-cased as an object
                        $record=array_change_key_case($record, CASE_LOWER);                        
                        $objects[$record[strtolower($firstcolumn->name)]] = (object) $record; /// The key is the first column value (like Assoc)
                    }
                   
                    return($objects);
            } else {
                    return false;
            }
        } else {
            return false;
        }
    }
    
        /**
         * Convert the contents of a recordset into an array
         *
         * @uses $CFG
         * @param object $rs required - recordset
         * @return object
         */
        function recordset_to_array($rs) {
            global $CFG;
            if ($rs && $rs->RecordCount() > 0) {
                //$firstcolumn = $rs->FetchField(0);
                /// Get the whole associative array
                if ($records = $rs->GetRows()) {                
                    $i=0;
                    foreach ($records as $record) {
                        // GT MOD - force lower case for array hash before its re-cased as an object
                        $record=array_change_key_case($record, CASE_LOWER);
                        $objects[$i] =  (object) $record;
                        $i++;
                    }
                      return ($objects);
                    /// Fallback in case we only have 1 field in the recordset. MDL-5877
                } else {
                        return false;
                }
            } else {
                return false;
            }
    }

    
    /**
     * Detect dangerous sql commands, e.g. delete
     */
    function querydangers($sql){
        $sqldangers=$this->sqldangers();
        $detected=array();
        foreach ($sqldangers as $danger){        
            if (stripos($sql, $danger)!==false){            
                $detected[]=$danger;
            }
        }
        if (empty($detected)){
            $detected=false;
        }
        return ($detected);
    }

    /**
     * Execute query and return array of results
     * @param string $sql required
     * @param boolean $assoc optional
     */
    function doQuery($sql,$assoc=false){
        //echo $assoc; // GT Mod removed
        global $USER, $CFG;        

        $sttime=microtime(true); // seconds with microsecond accuracy
        
        // Set database debug mode to true if uk mis debug is on
        if ($this->debug){            
            $this->db->debug=true;
        }
        
        // GT Mod 2009012200 - abort if any dangerous query commands are detected in sql string
        $dangers=$this->querydangers($sql);
        if ($dangers){
            $dcommands='';
            foreach ($dangers as $danger){
                $dcommands.=$dcommands=='' ? '' : ', ';
                $dcommands.=$danger;
            }
            $this->debuglog('Error - sql query contains dangerous commands:'.$dcommands);
            return (false);
        }
                        
        $this->debuglog('Executing Query: '.$sql, array('style'=>'color:blue'));
        
        // if debug then add indented margin
        if ($this->debug && isset($_SERVER['REMOTE_ADDR'])){
            echo ('<div style="margin-left:4em">');
        }
        
        if (!$adoResultSet=$this->db->execute($sql)){
            $this->debuglog($this->db->ErrorMsg(), array('class'=>'debug_error'));            
        }
        
        // if debug then close indented margin
        if ($this->debug && isset($_SERVER['REMOTE_ADDR'])){
            echo ('</div>');
        }        
        
        $edtime=microtime(true); // seconds with microsecond accuracy
        $ttime=($edtime-$sttime); // execution time in seconds        
        $this->lqrystarttime=$sttime; // set object prop last query start time
        $this->lqryendtime=$edtime; // set object prop last query end time
        $this->lqryexectime=$ttime; // set object prop last query execution time
        
        // debug helper - identify queries that are over 1.5 seconds in amber, and over 2 seconds in red
        $secondstxt='(execution time:'.$ttime.' seconds)';
        if (isset($_SERVER['REMOTE_ADDR'])){
            if ($ttime>1.5){
                $tcolor=$ttime<2 ? '#ffaa00' : '#ff0000';
                $secondstxt='<span style="color:'.$tcolor.' !important">'.$secondstxt.'</span>';
            }            
        }
        
        
        if ($adoResultSet && $adoResultSet->RecordCount() > 0){
                $this->debuglog('Query successful, returned '.$adoResultSet->RecordCount().' records. '.$secondstxt, array('class'=>'debug_success'));                            
        } else{
            // GT Mod only show debug message to consider revising SQL if query fails
            if (!$adoResultSet){
                // this is an error
                $this->debuglog('Query failed!', array('class'=>'debug_error'));
            } else {
                // this was successful but just didn't return anything
                $this->debuglog('Query returned 0 records. '.$secondstxt, array('class'=>'debug_success'));
                // GT mod - return false now, no point in trying to convert to ado recordset and raising an error
                return false;
            }
        }
                
        $this->debuglog('Converting ADO recordset to Array: ', array('style'=>'color:blue'));
        
                        
        if($assoc==true){
            $objResult = $this->recordset_to_assoc_array($adoResultSet);
        }else{
            $objResult = $this->recordset_to_array($adoResultSet);
        }
        
        if ($objResult){                            
            $this->debuglog('Successfully converted ADO recordset to array.', array('class'=>'debug_success')); 
            
            // link to show sql results GT MOD 2009012200
            if ($this->debug){      
                // only add link if sql is a select statement
                if (strtolower(substr(trim($sql),0,6))=='select'){            
                    if (!isset($USER->mis)){
                        $USER->mis=new stdclass();
                    }        
                    if (!isset($USER->mis->sqldebug)){
                        $USER->mis->sqldebug=array();        
                    }
                    $sqlid=uniqid();
                    
                    // keep a maximum of 100 sql statements logged
                    if (count($USER->mis->sqldebug)>100){
                        array_shift($USER->mis->sqldebug);
                    }
                    
                                    
                    $USER->mis->sqldebug[$sqlid]=$sql; 
                    $debugstr='<a class="button" href="'.$CFG->wwwroot.'/blocks/mis/debugger/showsqlresults.php?id='.$sqlid.'">Show SQL Results</a>';
                    $this->debuglog($debugstr);
                }
            }            
            

            // add space to end of debug message if being viewed in a browser window
            if ($this->debug && isset($_SERVER['REMOTE_ADDR'])){
                echo ('<hr /><br />');
            }
            
            return $objResult;
        }else{
            $this->debuglog('Failed to convert ADO recordset to array!', array('class'=>'debug_error'));
            
            // add space to end of debug message if being viewed in a browser window
            if ($this->debug && isset($_SERVER['REMOTE_ADDR'])){
                echo ('<hr /><br />');
            }            
            
            return false;
        }
    }
    
    /**
    * Get 1 specific field value from first row in sql to be executed
    **/
    function getFieldValue($sql,$fieldname){        
        $rowObj = $this->db->GetRow($sql);
        if (!$rowObj){
            // GT MOD 2007/07/08 - handle empty result
            return (false);
        }        
        // GT Mod 2008/04/07 - convert row objects hashes to lower case
        $rowObj = array_change_key_case($rowObj, CASE_LOWER);
        $retVal = $rowObj[strtolower($fieldname)]; // GT Mod 2009/01/08 - convert field name to lower case too
        return $retVal; 
    }

    /**
    * Return an entire row
    **/
    function getRowValues($sql){
        $rowObj = $this->db->GetRow($sql);
        // GT Mod 2008/04/07 - convert row objects hashes to lower case
        $rowObj = array_change_key_case($rowObj, CASE_LOWER);
        return $rowObj; 
    }
    
    
    /**
    * Function purpose: Get all data sets
    */
    function all_data_sets(){
        global $CFG;
        $facQry='SELECT setid FROM '.$this->prefix.'setiddata ORDER BY yearstart desc';
        $rs=$this->doQuery($facQry);
        $dsArray=array();
        foreach ($rs as $row){
            $ds=$row->setid;
            // this will ignore any datasets with the name 'test' in them 
            if (!isset($CFG->mis->inctestdatasets) || !$CFG->mis->inctestdatasets){
                if (strpos(strtolower($ds),'test')===false){
                    $dsArray[]=$ds;
                }
            }
        }
        return ($dsArray);
    }    
    
    /**
    * List all students in a particular Year, Teaching Group Or Form 
    * depending on the argument specified.
    *
    * if none of the optional parameters are specified then return all studentsx 
    *
    * @uses $CFG
    * @param Optional string $year - School year   
    * @param Optional string $form - Form Group
    * @param Optional string $tGroup - Teaching Group
    * @param Optional boolean  $get - if get is true return records or if false return record count
    * @param Optional string $sortCol -  The column alias to sort the records by
    * @param OPtional string $sortOrder - The Order to sort by
    * @return object
    */
    function listStudents($year="", $form="", $tgroup="", $get=1, $sortCol="", $sortOrder=""){
        global $CFG;
    
    
        $this->debuglog('Generating SQL Statement', array('style'=>'color:blue'));
        $this->debuglog('*** Variables passed in ***', array('class'=>'debug_vars'));
        $this->debuglog('year:'.$year, array('class'=>'debug_vars'));
        $this->debuglog('form:'.$form, array('class'=>'debug_vars'));
        $this->debuglog('group:'.$tgroup, array('class'=>'debug_vars'));
        $this->debuglog('get:'.$get, array('class'=>'debug_vars'));
        $this->debuglog('sortCol:'.$sortCol, array('class'=>'debug_vars'));
        $this->debuglog('sortOrder:'.$get, array('class'=>'debug_vars'));
        $this->debuglog('***************************', array('class'=>'debug_vars'));
    

        if ($get = 1){
            $sql = 'SELECT distinct st.studentid AS studentID, st.Name AS name, st.ClassGroupid AS form, st.CourseYear AS year ,ukst.UniqueNum AS upn FROM '.$this->prefix.'students AS st ';
        }else{
            $sql = 'SELECT COUNT(distinct st.studentid, st.Name, st.ClassGroupid, st.CourseYear ,ukst.UniqueNum)  FROM '.$this->prefix.'students AS st ';
        }
        $sql .=' INNER JOIN '.$this->prefix.'ukstustats AS ukst ON st.studentid = ukst.studentid ';
        $whereFlag = 0;
        
        if (!$year ==""){
            if (!$whereFlag){
                $whereClause .= ' WHERE st.CourseYear = \'' . $year . '\'';
                $whereFlag = 1;
            }else{
                $whereClause .= ' AND st.CourseYear = \'' . $year . '\'';
            }
        }
    
        if (!$form==""){
            if (!$whereFlag){
                $whereClause .= ' WHERE st.ClassGroupid = \'' . $form . '\'';
                $whereFlag = 1;
            }else{
                $whereClause .= ' AND st.ClassGroupid = \'' . $form . '\'';
            }
        }
        
        if (!$sortCol==''){
            $orderby= ' ORDER BY ' . $sortCol . ' ' . $sortOrder;
        }
        
        $sql .=  $whereClause;
        $sql .= ' AND st.setid=\'' . $CFG->mis->cmisDataSet . '\' and ukst.setid=\'' . $CFG->mis->cmisDataSet  . '\'';
        $sql .= ' GROUP BY CourseYear, ClassGroupid, Name, st.studentid, UniqueNum';
        $sql .= $orderby;
        
    
        if (!$tgroup==''){
            $sql =  'SELECT st.Name AS name, ukst.UniqueNum AS upn, nsp.DateOfBirth AS DoB ';
            $sql .= ' FROM '.$this->prefix.'nstupersonal AS nsp INNER JOIN (('.$this->prefix.'teachinggroups AS tgs INNER JOIN ('.$this->prefix.'stugroups AS sgs INNER JOIN '.$this->prefix.'students AS st ON sgs.StudentId = st.StudentId) ON tgs.GroupId = sgs.GroupId) INNER JOIN '.$this->prefix.'ukstustats AS ukst ON st.StudentId = ukst.StudentId) ON nsp.StudentId = ukst.StudentId ';
            $sql .= ' GROUP BY st.Name, ukst.UniqueNum, nsp.DateOfBirth, tgs.GroupCode, st.StudentId, sgs.SetId, st.SetId, tgs.SetId, ukst.SetId ';
            $sql .= ' HAVING (((tgs.GroupCode)=\'' . $tgroup  . '\') AND ((sgs.SetId)=\'' . $CFG->mis->cmisDataSet .'\') AND ((st.SetId)=\'' .  $CFG->mis->cmisDataSet . '\') AND ((tgs.SetId)=\'' . $CFG->mis->cmisDataSet . '\') AND ((ukst.SetId)=\'' . $CFG->mis->cmisDataSet . '\'))';
            $sql .= $orderby;
        }
    
    
        $this->debuglog($sql, array('style'=>'color:orange'));
        
        $objResults = $this->doQuery($sql);
        
    return $objResults;
    }


    /**
    * Display a Students Details
    * Student can be targeted by UPN, Surname, Firstname OR Fullname   
    *
    * @uses $CFG
    * @param Optional string $upn - Unique Pupil Number   
    * @param Optional string $sname - Surname
    * @param Optional string $fname - Firstname
    * @param Optional string $fullname - Fullname
    * @param Optional boolean  $get - if get is true return records or if false return record count
    * @param Optional string $sortCol -  The column alias to sort the records by
    * @param OPtional string $sortOrder - The Order to sort by
    * @param Optional string $dataset - GT Mod 2008/10/21 Get data from specific dataset. If not set, this becomes the current dataset
    * @return object
    */
    function getStudent($stu_unid="", $sname="", $fname="", $fullname="", $get = 1, $dataset=null){
        global $CFG;
        
        $upn=$this->getStuUpn($stu_unid);
        
        $dataset=is_null($dataset) ?  $CFG->mis->cmisDataSet : $dataset;
    
        if ($this->debug){
            echo "<font color='Blue' ><b>Generating SQL Statement</b></font><br>";
        }
        if ($get = 1){
            $sql = 'SELECT DISTINCT st.studentid AS studentID, st.Name AS name, st.ClassGroupid AS form, st.CourseYear AS year,ukst.UniqueNum AS upn FROM '.$this->prefix.'students AS st ';
        }else{
            $sql = 'SELECT COUNT(DISTINCT st.studentid, st.Name, st.ClassGroupid, st.CourseYear ,ukst.UniqueNum)  FROM '.$this->prefix.'students AS st ';
        }
        $sql .=' INNER JOIN '.$this->prefix.'ukstustats AS ukst on st.studentid = ukst.studentid ';
        $whereFlag = 0;
        $whereClause='';
        
        // bug fix by Allan Kealey 2009010500
        if (!$upn ==""){
            if (!$whereFlag){
                $whereClause .= ' WHERE ukst.UniqueNum = \'' . $upn . '\' ';
                $whereFlag = 1;
            }else{
                $whereClause .= ' AND ukst.UniqueNum = \'' . $upn . '\' ';
            }
        }
        
        if (!$sname ==""){
            if (!$whereFlag){
                $whereClause .= ' WHERE st.Name Like \'' . $sname . ',%\' ';
                $whereFlag = 1;
            }else{
                $whereClause .= ' AND st.Name Like \'' . $sname . ',%\' ';
            }
        }
        
        if (!$fname ==""){
            if (!$whereFlag){
                $whereClause .= ' WHERE st.Name Like \'' . $fname . ',%\' ';
                $whereFlag = 1;
            }else{
                $whereClause .= ' AND st.Name Like \'' . $fname . ',%\' ';
            }
        }
        
        $sql .=  $whereClause;
        $sql .= ' AND st.setid=\'' . $dataset . '\' AND ukst.setid=\'' . $dataset  . '\' ';
        $sql .= ' GROUP BY CourseYear, ClassGroupid, Name, st.studentid, UniqueNum;';
        
        if ($this->debug){
            echo "<font color='Orange' ><b>" . $sql . "</b></font><br>";
        }
        
        $objResults = $this->doQuery($sql);
        
        // GT Mod - return row object, not record set
        if ($objResults && is_array($objResults)){            
            return ($objResults[0]);
        } else {
            return (false);
        }        
    }
    
    
    
    /**
    * Purpose: Get student UPN
    * @param Required stu_unid (student unique id, either upn or admin number)
    * @return upn
    **/
    function getStuUpn($stu_unid){
        global $CFG;
		// If student id type is UPN, then simply return the stu_unid!        
        if ($CFG->mis->stu_unidtype==STU_UNID_UPN){
            return ($stu_unid);
        }
        $sql='SELECT UniqueNum FROM '.$this->prefix.'ukstustats WHERE studentid=\''.$stu_unid.'\'';
        return ($this->getFieldValue($sql,'UniqueNum'));
    }
	
    /**
    /* Purpose: Get student admin number
    /* @param Required stu_unid
    /* @return studentid (admin number)
    **/
    function getStuAdminNo($stu_unid){
        global $CFG;
        // If student id type is already the admin number then simply return it
        if ($CFG->mis->stu_unidtype==STU_UNID_ID){
            return ($stu_unid);
        }
        $sql='SELECT studentid FROM '.$this->prefix.'ukstustats WHERE UniqueNum=\''.$stu_unid.'\'';
        return ($this->getFieldValue($sql, 'studentid'));
    }


    /**
    * List Tutor Groups
    * List all the tutor groups for a particular year   
    *
    * @uses $CFG
    * @param Optional string $year - School Year ie:7   
    * @return object
    */
    function listTutorGroups($year="",$get = 1){
        global $CFG;

        if ($this->debug){
            echo "<font color='Blue' ><b>Generating SQL Statement</b></font><br>";
        }
        if ($get = 1){
            $sql = 'SELECT distinct ClassGroupid, CourseYear FROM '.$this->prefix.'students ';
        }else{
            $sql = 'SELECT COUNT(distinct ClassGroupid, CourseYear)  FROM '.$this->prefix.'students ';
        }
        
        $whereFlag = 0;

        if (!$year ==""){
            if (!$whereFlag){
                $whereClause .= ' WHERE CourseYear = \'' . $year . '\' ';
                $whereFlag = 1;
            }else{
                $whereClause .= ' AND CourseYear = \'' . $year . '\' ';
            }
        }

        $sql .=  $whereClause;
        $sql .= ' AND setid=\'' . $CFG->mis->cmisDataSet . '\' ';
        $sql .= ' GROUP BY ClassGroupid, CourseYear;';

        
        if ($this->debug){
            echo "<font color='Orange' ><b>" . $sql . "</b></font><br>";
        }
        
        $objResults = $this->doQuery($sql);
    return $objResults;
    }

    /**
    * List Curriculum Areas --- Facility Module
    * 
    * @uses $CFG
    * @param Optional string $year - School Year ie:7
    * @return object
    */
    function listCurriculumAreas($year="",$get = 1){
        global $CFG;

        if ($this->debug){
            echo '<font color="Blue" ><b>Generating SQL Statement</b></font><br>';
        }
        if ($get = 1){
            $sql = 'SELECT md.mdId, md.Name, tg.CrsYear';
        }else{
            $sql = 'SELECT COUNT(DISTINCT md.mdId, md.Name, tg.CrsYear)';
        }
        $sql .=' FROM '.$this->prefix.'teachinggroups AS tg INNER JOIN '.$this->prefix.'module AS md ON tg.mdId = md.mdId';
        $sql .=' GROUP BY md.mdId, md.Name, tg.CrsYear, md.SetId, tg.SetId';
        
        if (!$year ==''){
            $criteria = '((tg.CrsYear)=' . $year . ') AND';
        }
        $sql .=' HAVING (' . $criteria . '((md.SetId)=\'' . $CFG->mis->cmisDataSet . '\') AND ((tg.SetId)=\'' . $CFG->mis->cmisDataSet . '\'));';
        
        if ($this->debug){
            echo '<font color=\'Orange\' ><b>' . $sql . '</b></font><br>';
        }
                    
        $objResults = $this->doQuery($sql);
    return $objResults;

    }
    
    
    /**
    * List teaching groups
    * List all the teaching groups for a particular year or Currriculum area  
    *
    * @uses $CFG
    * @param Optional string $year - School Year ie:7
    * @param Optional string $carea - Currriculum area ie: Maths
    * @return object
    */
    function listTeachingGroups($year='', $moduleid='',$get = 1){
        global $CFG;

        $this->debuglog('Generating SQL Statement', array('style'=>'color:blue'));
            
        if ($get = 1){
            $sql = 'SELECT tg.mdId, md.Name, tg.GroupCode, tg.CrsYear';
        }else{    
            $sql = 'SELECT COUNT(tg.mdId, md.Name, tg.GroupCode, tg.CrsYear)';
        }
        
        $sql .= ' FROM '.$this->prefix.'module AS md INNER JOIN '.$this->prefix.'teachinggroups AS tg ON md.mdId = tg.mdId';
        $sql .= ' GROUP BY tg.mdId, md.Name, tg.GroupCode, tg.CrsYear, tg.SetId, md.SetId';
        
        if (!$year ==''){
            $yearClause = '((tg.CrsYear)=' . $year . ') AND ';
        }

        if (!$mdid ==''){
            $modClause = '((tg.mdId)=\'' . $mdid . '\') AND ';
        }
        
        $sql .= ' HAVING (' . $yearClause . $modClause . '((tg.SetId)=\'' . $CFG->mis->cmisDataSet . '\') AND ((md.SetId)=\'' . $CFG->mis->cmisDataSet . '\'))';
        $sql .= ' ORDER BY md.Name;';
        
        $this->debuglog($sql,array('style'=>'color:orange'));
                                
        $objResults = $this->doQuery($sql);
    return $objResults;
    }
    
    /**
    * Author: GThomas
    * Date: 2008/04/04
    * Purpose: Simple function to output debug message
    * @param Required $msg - the message you want to log
    * @param Optional $params - array of params (color, class)
    */
    function debuglog($msg, $params=array()){
        global $CFG;    

        $style=isset($params['style']) ? $params['style'] : false;
        $class=isset($params['class']) ? $params['class'] : false;
        $attstyle=$style ? ' style="'.$style.';" ' : '';
        $attclass=$class ? ' class="'.$class.'" ' : '';
        
        // only output to screen if debug is true
        if (isset($this->debug) && $this->debug){
            // only output to screen if admin user or debugadminonly not set // GT mod 2009012200
            if (!isset($this->debugadminonly) || ($this->debugadminonly && isadmin())){
                echo ('<div'.$attclass.$attstyle.'>'.$msg.'</div>');
            };
        }
    }
    

    function dbCleanUp(){
        $db = nothing;
    }
}

?>
