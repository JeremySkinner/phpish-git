<?php

require 'git.php';
require 'settings.php';
require 'logger.php';
require 'param-tab-expansion.php';
require 'prompt.php';
require 'tab-expansion.php';
require 'text-utils.php';

if(isset($argv[0])) {
  if($argv[0] == 'prompt') {
    $settings = GitSettings::create();
    $prompt = Prompt::create($settings);
    $prompt->writePrompt();
  }
  else if ($argv[0] == 'complete' && isset($argv[1])) {
    $expansion = new TabExpansion(new TabSettings());
    $result = $expansion->expand($argv[1]);
    foreach($result as $elem) {
      print "$elem\n";
    }
  }
}