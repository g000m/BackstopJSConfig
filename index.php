<?php

require_once './BackstopJSConfig.php';

$config = new BackstopJSConfig('http://www.pfizermedicalinformation.co.za/en-za/sitemap.xml');
$config->generateConfig();
