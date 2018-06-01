<?php

require 'src/git.php';
require 'src/settings.php';
require 'src/param-tab-expansion.php';
require 'src/tab-expansion.php';
require 'src/text-utils.php';
require 'src/logger.php';

$str = '';
for($i = 1; $i < count($argv); $i++) {
  $str .= $argv[$i] . ' ';
}

print_r($str);
$settings = new TabSettings();
$status = Git::status(GitSettings::create());
$expansion = new TabExpansion($settings);
$result = $expansion->expand($str, $status);

print_r($result);