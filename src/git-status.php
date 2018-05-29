<?php

require 'settings.php';
require 'logger.php';
require 'git.php';

$settings = new GitSettings();
$git = Git::createFromWorkingDirectory($settings);
$status = $git->getGitStatus();
print_r($status);