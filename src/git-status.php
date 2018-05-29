<?php

require 'settings.php';
require 'logger.php';
require 'git.php';
require 'prompt.php';

$settings = new GitSettings();
$git = Git::createFromWorkingDirectory($settings);
$status = $git->getGitStatus();
print_r($status);