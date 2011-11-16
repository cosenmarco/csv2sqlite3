#!/usr/bin/php
<?php 
if($argc < 4){ 
  echo <<<EOT
Usage: csv2sqlite3.php filename db_path table_name column_name_1:integer:1 column_name_2:5 ...
  the format for columns is: `column_name:data_type:N' where N is an integer between 0 and the maximum column number in the csv minus one
  BE CAREFUL: the first row (that should contain column identifications) is skipped.
  THE TABLE MUST EXIST WITHIN THE DATABASE

EOT;
  exit(1);
}

$filename = $argv[1];
$dbfile = $argv[2];

$db = new PDO("sqlite:$dbfile"); 
if (!$db) die ("Could not open database..."); 

$tablename = $argv[3];
$colnames = array();
$indexes = array();

for($i=4; $i<$argc; $i++)
{
  $cols = explode(":", $argv[$i], 2);
  $colnames[] = $cols[0];
  $indices[] = $cols[1];
}

$infile = fopen($filename,"r");
if (!$infile) die ("Could not open input file...");

$firstrow = true;


$db->query("BEGIN;");
while( ($row = fgetcsv($infile)) ){
  if($firstrow){
    $firstrow = false;
    continue;
  }

  $insert_statement = "INSERT INTO $tablename(" . implode(",", $colnames) . ") VALUES (";
  $vals = array();
  for($j=0; $j<count($indices); $j++){
    $vals[] = '"' . $row[$indices[$j]] . '"';
  }
  $insert_statement .= implode(",", $vals) . ");";

  //print($insert_statement."\n");
  $db->query($insert_statement);
}
$db->query("COMMIT;");

?>
