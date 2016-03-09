<?php

//
// Author: Guy Thomas
// Date:2007 - 10 - 30
// Purpose: SUPER Class for reports
//

global $CFG, $extenderDb;

require_once("class.report.super.php");
require_once dirname(__FILE__).'/imagegraph/Graph.php';
require_once dirname(__FILE__).'/3dpie/class.chart.3dpie.php';

class supergraph extends propsbyparam{

    //
    // Protected vars
    //
    //protected $_rowcatColorArray=array('#FF0000', '#00CC00', '#CEBE9A', '#2470CB', '#FFFF55', '#AA00FF', '#FFAA2A', '#55FFFF', '#956A29', '#FCAAAA', '#CCCCCC');
    protected $_rowcatColorArray=array('#DD0000', '#FFFF00', '#2470CB', '#22DD22', '#AA00FF', '#FFAA2A', '#55FFFF', '#956A29', '#FCAAAA', '#CCCCCC');    
    protected $_barOpacity='0.9';
    protected $_lineColor='gray';
    protected $_reportObj=null;
    protected $_reportOutput=array();
    protected $_title='';
    
    
    
    //
    // Public settable vars
    //
    var $pieKeyVertical=true;
    var $excluderowcats=array();
    
    //
    // Purpose: Class constructor
    //
    function supergraph ($reportObj, $params=array()){
        $this->_reportObj=$reportObj;
        parent::propsbyparam($params);
        
        // force excluderowcats to be an array
        if (!is_array($this->excluderowcats)){
            $this->excluderowcats=array($this->excluderowcats);
        }
        
        
        // Refine report output object
        $this->setReportOutput();
    }
    
    //
    // Purpose: Sets (and refines) report output
    //
    function setReportOutput(){
        //just set reportOutput to that of reportObject if no row categories are to be excluded
        if (empty($this->excluderowcats)){
            $this->_reportOutput=$this->_reportObj->getOutput();
            return;
        } else {       
            // Set reportOutput to report object output but exclude specific row categories
            $this->_reportOutput=array();       
            $repOut=$this->_reportObj->getOutput();
            foreach ($repOut as $rowcat=>$row){
                // if not excluded then add to reportOutput array             
                if (!in_array($rowcat,$this->excluderowcats)){
                    $this->_reportOutput[$rowcat]=$row;
                }
            }
        }
    }

    //
    // Purpose: Output png graph
    //
    function outputGraph($title='', $charttype='bar'){ 
        $this->_title=$title;
        $evalstr='$this->'.$charttype.'Chart();';
        eval ($evalstr);        
    }
    
    //
    // Purpose: Gets data sets from report to be used with chart
    //
    function setDatasets(){
        // Add table body data
        $datasets=array();
        foreach ($this->_reportOutput as $rowcat=>$row){
            // create new dataarray
            $dataarray=array();
            foreach ($this->_reportObj->columns as $col){
                $colcode=$col['code'];
                $coltitle=$col['title'];
                $dataarray[$coltitle]=$row[$colcode]['val'];
            }
            // create dataset and add to datasets array
            $dataset =& Image_Graph::factory('dataset_trivial', array($dataarray));
            $dataset->setName($rowcat);
            $datasets[]=clone($dataset);
        }
        return ($datasets);
    }
    
    //
    // Purpose: Outputs a bar chart
    //
    function barChart(){
        // create the graph
        $Graph =& Image_Graph::factory('graph', array(400, 300)); 
        // add a TrueType font
        $Font =& $Graph->addNew('font', 'Verdana');
        // set the font size to 11 pixels
        $Font->setSize(8);
        $Graph->setFont($Font);
 
        // Add components to the graph
        $Graph->add(
            Image_Graph::vertical(
                Image_Graph::factory('title', array($this->_title, 12)),        
                Image_Graph::vertical(
                    $Plotarea = Image_Graph::factory('plotarea'),         
                    $Legend = Image_Graph::factory('legend'),
                    90
                ),
                5
            )
        );
        
        // Add lines to grid
        $Grid_SmoothedLine =& $Plotarea->addNew('line_grid', false, IMAGE_GRAPH_AXIS_Y);
        $Grid_SmoothedLine->setLineColor('#eeeeee');
        
        $Legend->setPlotarea($Plotarea);        
 
        // Add table body data
        $datasets=$this->setDatasets();
        
        // Add data sets to plot area as bars
        $Plot =& $Plotarea->addNew('bar', array($datasets));

        // set a line color
        $Plot->setLineColor($this->_lineColor);
        
        // Set colors
        $rowcatColIdx=-1;
        $FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
        foreach ($this->_reportOutput as $rowcat=>$row){
            $rowcatColIdx++; // increment rowcat color index        
            $rowcatColIdx=$rowcatColIdx>=count($this->_rowcatColorArray) ? 0 : $rowcatColIdx; // reset color index if past array size
            $colorRef=$this->_rowcatColorArray[$rowcatColIdx].'@'.$this->_barOpacity;
            $FillArray->addColor($colorRef, $rowcat);
        }

        //var_dump($FillArray);
        
        // set a standard fill style
        $Plot->setFillStyle($FillArray);

        // output the Graph
        $Graph->done();    
    }
    
    //
    // Purpose: Output Pie Chart
    //
    function pieChart(){
        $verticalKey=$this->pieKeyVertical;
        $width=$verticalKey ? 400 : 400;

        // create the graph
        $Graph =& Image_Graph::factory('graph', array($width, 300));

        // add a TrueType font
        $Font =& $Graph->addNew('font', 'Verdana');
        // set the font size to 7 pixels
        $Font->setSize(7);

        $Graph->setFont($Font);
        	
        // create the plotarea
        $Graph->add(
            Image_Graph::vertical(
                Image_Graph::factory('title', array('', 12)),
                Image_Graph::horizontal(
                    $Plotarea = Image_Graph::factory('plotarea'),
                    $Legend = Image_Graph::factory('legend'),
                    70
                ),
                5            
            )
        );

        $Legend->setPlotarea($Plotarea);

        // only use the 1st dataset
        $datasets=$this->setDatasets();
        $dataset1=$datasets[0];

        // create the 1st plot as smoothed area chart using the 1st dataset
        $Plot =& $Plotarea->addNew('pie', array(&$dataset1));

        $Plotarea->hideAxis();

        // create a Y data value marker
        $Marker =& $Plot->addNew('Image_Graph_Marker_Value', IMAGE_GRAPH_PCT_Y_TOTAL);
        // create a pin-point marker type
        $PointingMarker =& $Plot->addNew('Image_Graph_Marker_Pointing_Angular', array(20, &$Marker));
        // and use the marker on the 1st plot
        $Plot->setMarker($PointingMarker);	
        // format value marker labels as percentage values
        $Marker->setDataPreprocessor(Image_Graph::factory('Image_Graph_DataPreprocessor_Formatted', '%0.1f%%'));

        $Plot->Radius = 2;

        $FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
        $Plot->setFillStyle($FillArray);

        $rowcatColIdx=-1;
        foreach ($dataset1->_data as $dataitem){
            $rowcatColIdx++; // increment rowcat color index        
            $rowcatColIdx=$rowcatColIdx>=count($this->_rowcatColorArray) ? 0 : $rowcatColIdx; // reset color index if past array size
            $FillArray->addColor($this->_rowcatColorArray[$rowcatColIdx].'@'.$this->_barOpacity);        
        }

        $Plot->explode(5);
        $Plot->setStartingAngle(90);

        $Plotarea->_canvas->startGroup('PieLegend');
        $fontstyle=array('name'=>'Verdana.ttf', 'size'=>12);
        $Plotarea->_canvas->setFont($fontstyle);

        $keycolorsize=10;
        $spacer=5;
        $xpos=0;
        $ypos=$verticalKey ? 20 : 0;
        $i=-1;
        $canvasWidth=$Plotarea->_canvas->getWidth();
        $widestText=0;
        
        // calculate widest piece of text for vertical keys
        if ($verticalKey){
            foreach ($dataset1->_data as $dataitem){
                $keytext=$dataitem['X'];
                $textsize=$Plotarea->_canvas->textWidth($keytext);
                $widestText=$textsize>$widestText ? $textsize : $widestText;
            }
        }
        
       
        
        foreach ($dataset1->_data as $dataitem){
            $i++;
            $keytext=$dataitem['X'];
            $keycolor=$FillArray->_fillStyles[$i];

            // write to pie legend
            
            if ($verticalKey){
                $xpos=$canvasWidth;
                $xpos-=($keycolorsize+$spacer);
                $xpos-=$widestText;
            }
            
            $Plotarea->_canvas->setFillColor($keycolor);
            $Plotarea->_canvas->rectangle( array ('x0'=>$xpos, 'y0'=>$ypos, 'x1'=>($xpos+$keycolorsize), 'y1'=>$ypos+$keycolorsize));
            $Plotarea->_canvas->addText(array('x' => $xpos+$keycolorsize+$spacer, 'y' => $ypos, 'text' => $keytext, 'alignment' => array('vertical'=>'top', 'horizontal'=>'left')));
            
            if ($verticalKey){
                $ypos+=$keycolorsize+$spacer+$Plotarea->_canvas->textHeight($keytext)+$spacer;
            } else {
                $xpos+=$keycolorsize+$spacer+$Plotarea->_canvas->textWidth($keytext)+$spacer;    
            }
            
        }

        $Plotarea->_canvas->endGroup();
        	   
        // output the Graph
        $Graph->done();    
    }
    
    //
    // Purpose: 3D Pie Chart
    //
    function pie3dChart(){
    
        // get labels and data for 3d pie chart
        $dataarray=array();        
        foreach ($this->_reportOutput as $rowcat=>$row){
            $label=array();
            $data=array();
            foreach ($this->_reportObj->columns as $col){
                $colcode=$col['code'];
                $coltitle=$col['title'];
                $label[]=$coltitle;
                $data[]=$row[$colcode]['val'];
            }
            $dataarray[]=array('label'=>$label, 'data'=>$data);
        }
        
        // only use first rowcat
        $label=$dataarray[0]['label'];
        $data=$dataarray[0]['data'];
          
        // output graph
        $params=array('colors'=>$this->_rowcatColorArray, 'width'=>250,'imagetype'=>'jpg', 'shadow_height'=>30);
        $pie=new chart_3dpie($data, $label, $params);        
    
    }
}