<?php

require 'settings.php';
require 'logger.php';
require 'git.php';
require 'prompt.php';
require 'utils.php';

$settings = GitSettings::create();
$prompt = Prompt::create($settings);
$prompt->writePrompt();


/*

export GIT_PROMPT_ShowStatusWhenZero=0
export GIT_PROMPT_BeforeStatus="on "
export GIT_PROMPT_AfterStatus="
git"
export GIT_PROMPT_AfterStatusColor="Yellow"
export GIT_PROMPT_DelimStatus=" working"
export GIT_PROMPT_BeforeIndex=" index"
export GIT_PROMPT_IndexColor="Yellow"

*/