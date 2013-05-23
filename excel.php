<?php
// Neuer Pfad, der hinzugefuegt werden soll 
$pear_path='/www/htdocs/w00a0f00/orgadev/PEAR'; 
// Alten Pfad auslesen 
$std_path=ini_get('include_path'); 

// UNIX => Doppelpunkt 
$new_path="$std_path:$pear_path"; 
 
// Neuen Pfad setzen 
ini_set('include_path',$new_path);



// Include PEAR::Spreadsheet_Excel_Writer
require_once "Spreadsheet/Excel/Writer.php";

// Create an instance
$xls =& new Spreadsheet_Excel_Writer();

// Send HTTP headers to tell the browser what's coming
$xls->send("test.xls");

// Add a worksheet to the file, returning an object to add data to
$sheet =& $xls->addWorksheet('Binary Count');

// Write some numbers
for ( $i=0;$i<11;$i++ ) {
 // Use PHP's decbin() function to convert integer to binary
 $sheet->write($i,0,decbin($i));
}

// Finish the spreadsheet, dumping it to the browser
$xls->close();
?>