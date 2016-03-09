<?php

require_once('../class.propsbyparam.php');

class chart_3dpie extends propsbyparam{
    
    ////////////////////////////////////////////////////////////////    
    // Adapted from PHP script made by Rasmus Beyer Petersen
    // - http://www.peters1.dk
    // - http://www.peters1.dk/webtools/php/lagkage.php?sprog=en
    // Adaption by Guy Thomas 2007/12/04
    ////////////////////////////////////////////////////////////////

    // public variables (can be set by params array)
    var $show_label = true; // true = show label, false = don't show label.
    var $show_percent = true; // true = show percentage, false = don't show percentage.
    var $show_text = true; // true = show text, false = don't show text.
    var $show_parts = false; // true = show parts, false = don't show parts.
    var $label_form = 'square'; // 'square' or 'round' label.
    var $width = 200;
    var $height = false;
    var $background_color = 'FFFFFF'; // background-color of the chart...
    var $text_color = '000000'; // text-color.
    var $colors = array('003366', 'CCD6E0', '7F99B2','F7EFC6', 'C6BE8C', 'CC6600','990000','520000','BFBFC1','808080'); // default colors of the slices.
    var $shadow_height = 16; // Height on shadown.
    var $shadow_dark = true; // true = darker shadow, false = lighter shadow...
    var $label = array(); // labels for each data item
    var $data = array(); // data items
    var $outputoncreate=true; // force the pie chart to output on object construction
    var $decplace='.'; // decimal place character
    var $thousep=','; // thousands seperator
    var $imagetype='png'; // can be png, jpg, gif
    var $imagequality=100; // default is best quality
    var $start=270;
    var $legendunderchart=false; // if true, puts legend underneath chart
    var $preventcache=true; // if true, adds headers to prevent caching of image
    
    // private variables
    private $_numformat = array(); // number formats for each data item
    private $_text_length = 0; // largest item of text used in labels
    private $_xtra_height = 0;
    private $_xtra_width = 0;
    
    //
    // Purpose: Class constructor
    // In:
    // data - array of data items
    // label - array of data item labels
    // params - array of key value pairs to set public props of this class
    //
    function chart_3dpie ($data=false, $label=false, $params=array()){
        // Set public class properties defined in parameters array
        if (!empty($params)){
            parent::propsbyparam($params);
        }
        // If data and label have not been passed in then try get / post vars
        if (!$data){
            $data=$this->_getorpostval('data');            
        }
        if (!$label){
            $label=$this->_getorpostval('label');
        }
        
        // Convert data and label to array        
        if ($data && !is_array($data)){
            $data=explode('~', $data);
        }
        if ($label && !is_array($label)){
            $label=explode('~', $label);
        }        
        
                        
        // Report error if no data passed in
        if (is_null($data)){
            $data=array(100);
            $label=array('No Data!');
        }
        
        // Create labels if none passed in
        if (is_null($label)){
            $label=array();
            $lc=0;
            foreach ($data as $di){
                $lc++;
                $label[]='item '.$lc;
            }
        }
        
        // Make sure size of label is equal to size of data or report error
        if (count($label)!=count($data)){
            $data=array(100);
            $label=array('#labels does not match #data items!');
        }
        
        // Set class properties
        $this->data=$data;
        $this->label=$label;               
        $this->height=!$this->height ? $this->width/2 : $this->height;
        $this->height-=ceil($this->shadow_height); // subtract shadow_height from height
        
        // Set properties based on data array and label array
        $this->_setpropsbasedondata();

        // Generate image
        $this->_genimage();
    }
    
    //
    // Purpose: Sets number format according to data and text length according to label
    //
    private function _setpropsbasedondata(){
    
        // set number format and text length
        for ($i = 0; $i < count($this->label); $i++){
        	if ($this->data[$i]/array_sum($this->data) < 0.1) $this->_numformat[$i] = ' '.number_format(($this->data[$i]/array_sum($this->data))*100,1,$this->decplace, $this->thousep).'%';
        	else $this->_numformat[$i] = number_format(($this->data[$i]/array_sum($this->data))*100,1,$this->decplace,$this->thousep).'%';
        	if (strlen($this->label[$i]) > $this->_text_length) $this->_text_length = strlen($this->label[$i]);
        }
        
        // set extra width and height
        $antal_label = count($this->label);
        
        
        // GT mod- set extra height and width according to position of legend
        $wgt_xtra = 0;
        $wgt_xtra += $this->show_label ? 20 : 0;
        $wgt_xtra += $this->show_percent ? 45 : 0;
        $wgt_xtra += $this->show_text ? $this->_text_length*8 : 0;
        $wgt_xtra += $this->show_parts ? 35 : 0;        
        if ($this->legendunderchart){
            $hgt_xtra = (5+15*$antal_label);
            $this->_xtra_height =  $hgt_xtra;
            if ($wgt_xtra > $this->width) $this->_xtra_width = $wgt_xtra-$this->width;
        } else {
            $hgt_xtra = (5+15*$antal_label)-$this->height;
            if ($hgt_xtra > 0) $this->_xtra_height = $hgt_xtra;
            $this->_xtra_width = $wgt_xtra+5;
        }

        

        
    }
    
    //
    // Purpose: Tries to return a value from $_GET or $_POST
    //
    private function _getorpostval($valname){
        if (isset($_GET[$valname])){
            return ($_GET[$valname]);
        } else if (isset($_POST[$valname])){
            return ($_POST[$valname]);
        } else {
            return null;
        }
    }
    
    //
    // Purpose: Generates the image and outputs it
    //
    private function _genimage(){
        $img = ImageCreateTrueColor($this->width+$this->_xtra_width, $this->height+$this->_xtra_height);

        ImageFill($img, 0, 0, $this->_colorHex($img, $this->background_color));

        foreach ($this->colors as &$colorkode) 
        {
            $colorkode=str_replace('#', '', $colorkode); // remove hashes if there are any
        	$fill_color[] = $this->_colorHex($img, $colorkode);
        	$shadow_color[] = $this->_colorHexshadow($img, $colorkode, $this->shadow_dark);
        }

        if ($this->legendunderchart){
            $label_place = $this->height+5;
            $lpos=0;
        } else {
            $label_place = 5;
            $lpos=$this->width;
        }

        for ($i = 0; $i < count($this->label); $i++) 
        {
        	if ($this->label_form == 'round' && $this->show_label)
        	{
        		imagefilledellipse($img,$lpos+11,$label_place+5,10,10,$this->_colorHex($img, $this->colors[$i % count($this->colors)]));
        		imageellipse($img,$lpos+11,$label_place+5,10,10,$this->_colorHex($img, $this->text_color));
        	}
        	else if ($this->label_form == 'square' && $this->show_label)
        	{	
        		imagefilledrectangle($img,$lpos+6,$label_place,$lpos+16,$label_place+10,$this->_colorHex($img, $this->colors[$i % count($this->colors)]));
        		imagerectangle($img,$lpos+6,$label_place,$lpos+16,$label_place+10,$this->_colorHex($img, $this->text_color));
        	}

        	if ($this->show_percent) $this->label_output = $this->_numformat[$i].' ';
        	if ($this->show_text) $this->label_output = $this->label_output.$this->label[$i].' ';
        	if ($this->show_parts) $this->label_output = $this->label_output.$this->data[$i];

        	imagestring($img,'4',$lpos+20,$label_place,$this->label_output,$this->_colorHex($img, $this->text_color));
        	$this->label_output = '';

        	$label_place = $label_place + 15;
        }
        
        $centerX = round($this->width/2);
        $centerY = round(($this->height-$this->shadow_height)/2);
        $diameterX = $this->width-8;
        $diameterY = ($this->height-8)-$this->shadow_height;

        $this->data_sum = array_sum($this->data);

        $start = $this->start;
        $value=0;
        $value_counter=0;
        for ($i = 0; $i < count($this->data); $i++) 
        {
        	$value += $this->data[$i];
        	$end = ceil(($value/$this->data_sum)*360) + $this->start;
            // GT bug fix - you can't draw a slice if its start is the same as its end - it would have no width! Don't bother adding to slices array if start is equal to end *this was causing some versions of php to draw an arc that wiped out the entire pie chart.
            if ($start!==$end){
                $slice[] = array($start, $end, $shadow_color[$value_counter % count($shadow_color)], $fill_color[$value_counter % count($fill_color)]);
            }
        	$start = $end;
        	$value_counter++;
        }

        for ($i=$centerY+$this->shadow_height; $i>$centerY; $i--) 
        {
        	for ($j = 0; $j < count($slice); $j++)
        	{
        		ImageFilledArc($img, $centerX, $i, $diameterX, $diameterY, $slice[$j][0], $slice[$j][1], $slice[$j][2], IMG_ARC_PIE);
        	}
        }

        for ($j = 0; $j < count($slice); $j++)
        {
        	ImageFilledArc($img, $centerX, $centerY, $diameterX, $diameterY, $slice[$j][0], $slice[$j][1], $slice[$j][3], IMG_ARC_PIE);
        }

        if ($this->outputoncreate){
            $this->OutputImage($img);
            ImageDestroy($img);
        }
    }
    
    //
    // Purpose returns rgb color val for hex val
    //
    private function _colorHex($img, $HexColorString){
    		$R = hexdec(substr($HexColorString, 0, 2));
    		$G = hexdec(substr($HexColorString, 2, 2));
    		$B = hexdec(substr($HexColorString, 4, 2));
    		return ImageColorAllocate($img, $R, $G, $B);
    }

    //
    // Purpose returns rgb color val for hex val but adjusted for shadow (lighter or darker)
    //
    private function _colorHexshadow($img, $HexColorString, $mork){
    	$R = hexdec(substr($HexColorString, 0, 2));
    	$G = hexdec(substr($HexColorString, 2, 2));
    	$B = hexdec(substr($HexColorString, 4, 2));

    	if ($mork)
    	{
    		($R > 99) ? $R -= 100 : $R = 0;
    		($G > 99) ? $G -= 100 : $G = 0;
    		($B > 99) ? $B -= 100 : $B = 0;
    	}
    	else
    	{
    		($R < 220) ? $R += 35 : $R = 255;
    		($G < 220) ? $G += 35 : $G = 255;
    		($B < 220) ? $B += 35 : $B = 255;				
    	}
    	return ImageColorAllocate($img, $R, $G, $B);
    }

    function OutputImage($img){
        // set mime type
    	header('Content-type: image/'.$this->imagetype);
        
        if ($this->preventcache){
            // prevent caching
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Cache-Control: post-check=0, pre-check=0', false);
            header('Pragma: no-cache');        
        }
        
        // output appropraite image type
        if ($this->imagetype=='jpg' || $this->imagetype=='jpeg'){
            imagejpeg($img,NULL,$this->imagequality);
        } else if ($this->imagetype=='png'){
            $qual=10-($this->imagequality/10);
            imagepng($img,NULL,$qual);
        } else if ($this->imagetype=='gif'){
            imagegif($img);
        }
    }    
    
}

?>