<?php
error_reporting(0);

/* Load the framework. */
include 'framework/router.class.php';
include 'framework/control.class.php';
include 'framework/model.class.php';
include 'framework/helper.class.php';

/* Instance the app. */
$startTime = getTime();
$app = router::createApp('secret');

/* Run the app. */
$common = $app->loadCommon();

$app->parseRequest();
// TODO $common->checkPriv();
$app->loadModule();