<?php
/**
 * Builds a sortable html-table; needs column headers, data array and meta data.
 *
 * @author Alexander Schmalzhaf, 15.05.2010
 *
 */
class DynamicTable {

    protected $_htmlString;


    /**
     *
     * Usage:
     * pssible meta-infos:
     *    id
     *    class (expects array)
     *
     * @param array $cols
     * @param array  $data
     * @param array meta
     * @return string
     */
    public function __construct(array $cols, array $data, array $meta){

        //start table
        $htmlString = "<table ";
        //add css ID and class(es)
        $htmlString .=  "id=\"".$meta['id']."\" ";
        $htmlString .= "class=\"sortable";
        if(isset($meta['class'])){
            for($i = 0; $i < count($meta['class']); $i++){
                $htmlString .= ", ".$class;
            }
        }

        $htmlString .= "\" > \n";

        //headings
        $htmlString .= "<tr>\n";
        foreach($cols as $col){
            if(isset($col['type']) && $col['type'] == 'num'){
                $htmlString .= "<th class=\"sorttable_numeric\" title=\"" . $col['title'] . "\">" . $col['title'] . "</th> \n";
            }else{
                $htmlString .= "<th title=\"" . $col['title'] . "\">" . $col['title'] . "</th> \n";
            }
        }
        $htmlString .= "</tr> \n";
        
        //content
        foreach ($data as $entry){
            $htmlString .= "<tr> \n";
            foreach($cols as $col){
                if($col['type'] == 'num'){
                    $htmlString .= "<td title=\"" . $entry[$col['id']] . "\">" . $entry[$col['id']] . "</td> \n" ;
                }elseif($col['type'] == 'string'){
                    $htmlString .= "<td title=\"" . $entry[$col['id']] . "\">" . $entry[$col['id']] . "</td> \n" ;
                }elseif($col['type'] == 'text'){
                    $htmlString .= "<td title=\"" . $entry[$col['id']] . "\">" . substr($entry[$col['id']],0,15) . "</td> \n" ;
                }elseif($col['type'] == 'email'){
                    $htmlString .= "<td title=\"" . $entry[$col['id']] . "\"><a href=\"mailto:" . $entry[$col['id']] . "\">" . $entry[$col['id']] . "</a></td> \n" ;
                }elseif($col['type'] == 'link'){
                    $htmlString .= "<td >" . $entry[$col['id']] . "</td> \n" ;
                }

            }
            $htmlString .= "</tr> \n";
        }

        //table footer
        $htmlString .= "</table> \n";

        //return table
        $this->_htmlString = $htmlString;
        return $this->_htmlString;
    }

    public function getHtmlString(){
        
        return $this->_htmlString;
    }

}


