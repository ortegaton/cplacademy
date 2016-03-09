<?php

/**
 * @author Guy Thomas
 * date:2007-02-20
 * extends class.resultcriteria.php
 * purpose: Class with tools for modifying criteria
 */
class resultcriteria extends propsbyparam{

    var $cfg;
    var $fdata;

    /**
     * Class Constructor
     * @param array $params optional
     */
    function resultcriteria($params=array()){    

        global $CFG;

        parent::propsbyparam($params);
 
        // If config hasn't been set then try global variable
        if(!isset($this->cfg)){
            $this->cfg=$CFG->mis;
        }
        // If msdb hasn't been set then try global variable
        if(!isset($this->fdata)){
            $this->fdata=$GLOBALS['fdata'];
        }
    }


    /**
     * Purpose: Returns map value(s) for criteria label for specific assessment
     * @param string $assessID required
     * @param string $critlabel required
     * @return array
     */
    function mapValsForCritLabel($assessID,$critlabel){
        $fdata=$this->fdata;
        $facQry='SELECT mapvalue FROM '.$fdata->prefix.'assesscriteria WHERE assessid=\''.$assessID.'\' AND critLabel=\''.$critlabel.'\' ORDER BY mapValue ASC';
        $result=$fdata->doQuery($facQry, true);
        $mapVals=Array();
        foreach ($result as $row){
            $mapValue=$row->mapvalue;
            $mapVals[]=$mapValue;
        };
        return ($mapVals);
    }


    /**
     * Purpose: Returns a criteria label for a specific assessment and map value
     * @param string $assessID required
     * @param int $mapVal required
     * @return string
     */
    function critLabelForMapVal($assessID, $mapVal){
        $fdata=$this->fdata;
        $facQry='SELECT critlabel FROM '.$fdata->prefix.'assesscriteria WHERE assessid=\''.$assessID.'\' AND mapvalue=\''.$mapVal.'\' ORDER BY critLabel ASC';
        $row=$fdata->getRowValues($facQry);
        if (!$row){return(false);} // return false if no criteria label was found for $mapVal        
        return ($row['critlabel']);
    }

    /**
     * Purpose: Gets an assessmentid for a specific exam
     * @param string $examID required
     * @return string
     */
    function AssessmentIdForExam($examID){
        $fdata=$this->fdata;
        $facQry='SELECT assessid FROM '.$fdata->prefix.'examinations WHERE examid=\''.$examID.'\'';
        $row = $fdata->getRowValues($facQry);
        return ($row['assessid']);        
    }




    /**
     * Converts a flat criteria data string to an array with map values as keys
     * @param string $critData required
     * @return array
     */
    function flatCriteriaToArray(&$critData){
        if ($critData==""){return(false);}
        $critArray=Array();
        $expArray=explode(chr(10), trim(strval($critData)));
        for ($c=0; $c<count($expArray); $c+=2){
            $critKey=$expArray[$c];
            $critVal=($c+1)<=count($expArray) ? $expArray[$c+1] : "";
            $critArray[$critKey]=$critVal;
        }
        return ($critArray);
    }

    /**
     * Creates a data array with criteria labels as keys (instead of map values) and adds point values
     * @param string $assessId required
     * @param array $critArray required
     * @param array $cpArray required
     * @param boolean $lkey optional
     * @return array
     */
    function createNiceDataArray($assessID, &$critArray, &$cpArray, $lkey=true){
        $niceArray=array();
        foreach ($critArray as $key => $val){
            $niceKey=$this->critLabelForMapVal($assessID, $key); // convert key to nice key
            // convert nice key to lower case if lkey is true (necessary for interegation)
            if ($lkey){
                $niceKey=strtolower($niceKey);
            }
            unset($critObj);
            $critObj->val=$val;
            $critObj->pts=$cpArray[$niceKey][$val];
            $niceArray[$niceKey]=$critObj;
        }
        return ($niceArray);
    }

    /**
     * Purpose: Gets a specific value from criteria data 
     * @param string $critData required
     * @param string $mapValue required
     * @return array
     */
    function getValFromCritData(&$critData, &$mapValue){
        //$critData.=ord(10); // always end critData with chr10 (REMOVED- was causing trouble)
        $critArray=explode(chr(10), trim(strval($critData)));
        for ($c=0; $c<count($critArray); $c+=2){
            if ($mapValue==$critArray[$c]){
                if (($c+1)<count($critArray)){
                    return ($critArray[$c+1]);
                }
            }
        }
    }


    /**
     * Gets exam columns for a specific exam code
     * @param string $examCode required
     * @return array
     */
    function examColumns($examCode){
        $fdata=$this->fdata;
        $cols=array();
        $facQry='SELECT a.critlabel FROM '.$fdata->prefix.'examinations AS e LEFT JOIN '.$fdata->prefix.'assesscriteria AS a ON a.assessid= e.assessid WHERE e.examid=\''.$examCode.'\'';
        $rs=$fdata->db->execute($facQry);
        while ($row = $rs->fetchrow()){
            $cols[]=$row['critlabel'];
        }
        return ($cols);
    }

    /**
     * @param string $assessId required
     * @return array
     */
    function AssessmentCriteriaPoints($assessId){
        $fdata=$this->fdata;
        $cpArray=Array();
        $facQry='SELECT * FROM '.$fdata->prefix.'critervalues WHERE assessid=\''.$assessId.'\' ORDER BY critlabel, gradepoints, critercode';
        $rs=$fdata->doQuery($facQry, true);
        if($rs){
            foreach ($rs as $row){
                $critLabel=$row->critlabel;
                $critCode=$row->critercode;
                $gradePts=$row->gradepoints;
                $cpArray[$critLabel][$critCode]=$gradePts;
            }
        }
        return ($cpArray);
    }

    /**
     * Increments a criteria value by $incBy (can be negative to decrement)
     * NOTE - INCREMENTS BY VALUES NOT POINTS - so C+2 would be A
     * @param array $cpArray required - criteria points array
     * @param string $critLabel required - criteria label
     * @param string $critCode required - value code to get points by
     * @param int $incBy optional - increment value code by $incBy
     * @return array
     */
    function AssCritValPoint(&$cpArray, $critLabel, $critCode, $incBy=0){
        $checkArray=$cpArray[$critLabel];
        $caKeys=array_keys($checkArray);
        $cCodePos=-1;

        // Find crit code position in array
        for ($c=0; $c<count($caKeys); $c++){
            if ($caKeys[$c] == $critCode) {
                $cCodePos=$c;
                break;
            }
        }

        if ($cCodePos==-1){
            return (null);
        }

        // Set value position + increment
        $cCodePos+=$incBy;

        if ($cCodePos>count($caKeys)){
            $cCodePos=count($caKeys); // cant go above array size
        } else if ($cCodePos<0){
            $cCodePos=0; // cant go below 0
        }

        // Find adjusted crit code points
        $checkArrayVals=array_values($checkArray);
        $adjustedPts=$checkArrayVals[$cCodePos];

        // Find adjusted crit code value
        $caKeysVals=array_values($caKeys);
        $adjustedVal=$caKeysVals[$cCodePos];    

        // New val points array
        $valPoints=array('pts'=>$adjustedPts, 'val'=>$adjustedVal);

        return ($valPoints);

    }
}

?>