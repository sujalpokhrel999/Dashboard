<?php

$host="localhost";
$user="root";
$pass="";
$db="effortease";
$conn=new mysqli($host,$user,$pass,$db);

if($conn-> connect_error){
    echo "Failed to connect DataBase".$conn->connect_error;
}
?>