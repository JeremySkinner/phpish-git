<?php
require 'src/settings.php';
require 'src/logger.php';
require 'src/git.php';
require 'src/prompt.php';
require 'src/text-utils.php';


$log = new Logger(getenv("GIT_PROMPT_Debug") == 1);
$log->log('Parsing settings');
$settings = GitSettings::create($log);
$log->log('Creating prompt'); 
$prompt = Prompt::create($settings, $log);
$prompt->writePrompt();