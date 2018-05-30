<?php
require 'src/settings.php';
require 'src/logger.php';
require 'src/git.php';
require 'src/prompt.php';
require 'src/utils.php';

$settings = GitSettings::create();
$prompt = Prompt::create($settings);
$prompt->writePrompt();

/*
Example shell variables to set for Jeremy's prompt:
export GIT_PROMPT_ShowStatusWhenZero=0
export GIT_PROMPT_BeforeStatus="on "
export GIT_PROMPT_AfterStatus=""
export GIT_PROMPT_AfterStatusColor="Yellow"
export GIT_PROMPT_DelimStatus=" working"
export GIT_PROMPT_BeforeIndex=" index"
export GIT_PROMPT_BeforeIndexColor="DarkGray"
export GIT_PROMPT_DelimStatusColor="DarkGray"
export GIT_PROMPT_IndexColor="Yellow"
export GIT_PROMPT_DefaultPromptSuffix="
$ "
*/