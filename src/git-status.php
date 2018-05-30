<?php

require 'settings.php';
require 'logger.php';
require 'git.php';
require 'prompt.php';
require 'utils.php';

$settings = new GitSettings();
$status = getGitStatus($settings);
print_r($status);