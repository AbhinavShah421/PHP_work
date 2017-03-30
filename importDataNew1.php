
<?php
//table Name
$tableName = "MyTable";
//database name
$dbName = "db_name";
//load the database configuration file
$conn = mysql_connect("localhost", "root", "") or die(mysql_error());
 mysql_select_db($dbName) or die(mysql_error());



if(isset($_POST['importSubmit'])){
    
    //validate whether uploaded file is a csv file
    $csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
    if(!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'],$csvMimes)){
        if(is_uploaded_file($_FILES['file']['tmp_name'])){
            
            //open uploaded csv file with read only mode
            
           if (($handle = fopen($_FILES['file']['tmp_name'], "r")) !== FALSE) {
                
            if(($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $num = count($data);
            $fieldsInsert .= '(';
            for ($c=0; $c < $num; $c++) {
            $fieldsInsert .=($c==0) ? '' : ', ';
            $fieldsInsert .="`".$data[$c]."`";
            $fields .="`".$data[$c]."` varchar(500) DEFAULT NULL,";
        }
       
        $fieldsInsert .= ')';
    }
                //drop table if exist
    if(mysql_num_rows(mysql_query("SHOW TABLES LIKE '".$tableName."'"))>=1) {
      mysql_query('DROP TABLE IF EXISTS `'.$tableName.'`') or die(mysql_error());
    }
            
           
                 //create table
    $sql = "CREATE TABLE `".$tableName."` (
              `Id` int(100) unsigned NOT NULL AUTO_INCREMENT,
              ".$fields."
              PRIMARY KEY (`Id`)
            ) ";
   
    $retval = mysql_query( $sql, $conn );
   
    if(! $retval )
    {
      die('Could not create table: ' . mysql_error());
    }
    else {
        while(($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
           
                $num = count($data);
                $fieldsInsertvalues="";
                //get field values of each row
                for ($c=0; $c < $num; $c++) {
                    $fieldsInsertvalues .=($c==0) ? '(' : ', ';
                    $fieldsInsertvalues .="'".$data[$c]."'";
                }
                $fieldsInsertvalues .= ')';
                //insert the values to table
                $sql = "INSERT INTO ".$tableName." ".$fieldsInsert."  VALUES  ".$fieldsInsertvalues;
                mysql_query($sql,$conn);   
        }
       // echo 'Table Created';
         $qstring = '?status=succ';
    }
           
            }
            
            //close opened csv file
            fclose($csvFile);

           
        }else{
            $qstring = '?status=err';
        }
    }else{
        $qstring = '?status=invalid_file';
    }
}

//redirect to the listing page
header("Location: index2.php".$qstring);