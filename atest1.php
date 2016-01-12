<?php
$_reqUrl = $_GET['req_url'];
echo '<script type="text/javascript">(window.parent.location.href='.$_reqUrl.');</script>';

$_reqUrl = $_GET['req_url'];
header( 'Location: '.$_reqUrl);
echo "test";
?>