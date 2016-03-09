<?php

// Set to display errors on screen
ini_set('display_errors', 'stdout');

/**
 * Usage example for Image_Graph.
 * 
 * Main purpose: 
 * Show bar chart
 * 
 * Other: 
 * None specific
 * 
 * $Id: plot_bar.php,v 1.4 2005/08/03 21:21:52 nosey Exp $
 * 
 * @package Image_Graph
 * @author Jesper Veggerby <pear.nosey@veggerby.dk>
 */

require_once dirname(__FILE__).'../../Graph.php';

// create the graph
$Graph =& Image_Graph::factory('graph', array(400, 300)); 
// add a TrueType font
$Font =& $Graph->addNew('font', 'Verdana');
// set the font size to 11 pixels
$Font->setSize(8);

$Graph->setFont($Font);


$Graph->add(
    Image_Graph::vertical(
        Image_Graph::factory('title', array('Bar Chart Sample', 12)),        
        Image_Graph::vertical(
            $Plotarea = Image_Graph::factory('plotarea'),         
            $Legend = Image_Graph::factory('legend'),
            90
        ),
        5
    )
);

$Grid_SmoothedLine =& $Plotarea->addNew('line_grid', false, IMAGE_GRAPH_AXIS_Y);
$Grid_SmoothedLine->setLineColor('#eeeeee');

$Legend->setPlotarea($Plotarea);        

// create the dataset
$Dataset1 =& Image_Graph::factory('dataset_trivial', array(array('a'=>10, 'b'=>20, 'c'=>15)));
$Dataset1->setName('series1');
$Dataset2 =& Image_Graph::factory('dataset_trivial', array(array('a'=>12, 'b'=>24, 'c'=>19)));
$Dataset2->setName('series2');
$Dataset3 =& Image_Graph::factory('dataset_trivial', array(array('a'=>2, 'b'=>4, 'c'=>6)));
$Dataset3->setName('series3');
$Dataset4 =& Image_Graph::factory('dataset_trivial', array(array('a'=>4, 'b'=>8, 'c'=>22)));
$Dataset4->setName('series4');
// create the 1st plot as smoothed area chart using the 1st dataset

$Datasets=array($Dataset1, $Dataset2, $Dataset3, $Dataset4);

$Plot =& $Plotarea->addNew('bar', array($Datasets));


// set a line color
$Plot->setLineColor('gray');



// Set colors
$FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
$FillArray->addColor('red@0.6');
$FillArray->addColor('green@0.6');
$FillArray->addColor('blue@0.6');
$FillArray->addColor('gray@0.6');

// set a standard fill style
$Plot->setFillStyle($FillArray);





// output the Graph
$Graph->done();
?>