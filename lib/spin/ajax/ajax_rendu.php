<?php
require_once __DIR__ . '/../../../../../../bootstrap.php';

use spin\ajax;

// Sécurité ------------------------------------------------------------------------------------
if (count($_POST)==0)	die();
// ---------------------------------------------------------------------------------------------


// test ----------------------------------------------------------------------------------------
// print_r($_POST);
// ---------------------------------------------------------------------------------------------


// ---------------------------------------------------------------------------------------------
$ajax = new ajax();
echo $ajax->hydrateInitResult($_POST);
// ---------------------------------------------------------------------------------------------
