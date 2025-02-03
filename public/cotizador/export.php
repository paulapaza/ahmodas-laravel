<?php
//iniciar sesion 
session_start();

// guar en uan variable de sesion el contenido de la tabla

$_SESSION['datatable'] = json_decode($_POST['datatable'], true);
$_SESSION['total'] = json_decode($_POST['total'], true);


echo json_encode("success");


