<?php
class datatable{

    private $rs;
    private $fields;

    /**
     * @param array $rs recordset array of row objects     
     */     
    function __construct ($rs){
        $this->rs=$rs; 
        $this->getfields();
        $this->castrowsasarrays();
    }
    
    private function getfields(){
        $frow=reset($this->rs);
        $this->fields=array();
        foreach ($frow as $fldname=>$fldval){
            $this->fields[]=$fldname;
        }
    }
    
    private function castrowsasarrays(){
        $rows=array();
        $r=0;
        foreach ($this->rs as $row){
            if ($row){
                $rows[$r]=(array) $row;
            }
            $r++;
        }
        $this->rs=$rows;
    }
    
    function display($pagination=false){
        $table=new stdclass();
        $table->head=$this->fields;
        $table->data=$this->rs;
        print_table($table);
    }
}
?>