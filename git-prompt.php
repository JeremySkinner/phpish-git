<?php
require 'src/settings.php';
require 'src/logger.php';
require 'src/git.php';
require 'src/prompt.php';
require 'src/text-utils.php';

$settings = GitSettings::create();
$prompt = Prompt::create($settings);
$prompt->writePrompt();