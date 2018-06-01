<?php

require 'src/git.php';
require 'src/settings.php';
require 'src/param-tab-expansion.php';
require 'src/tab-expansion.php';
require 'src/text-utils.php';
require 'src/logger.php';

// First element of command line args is the script name
// Join all args into a string omitting the first.

if(isset($argv[1])) {
  $expansion = new TabExpansion(new TabSettings());
  $result = $expansion->expand($argv[1]);
  foreach($result as $elem) {
    print "$elem\n";
  }

}