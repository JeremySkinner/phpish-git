<?php

use PHPUnit\Framework\Assert;

/**
 * @param $input
 *
 * @return \ExpandResult
 */
function expand($input) {
  $expansion = new TabExpansion(new TabSettings());
  return new ExpandResult($expansion->expand($input, TabExpansionTest::$status));
}

function addAlias($name, $value) {
  global $added_aliases;
  $added_aliases[] = $name;
  Git::exec("config --global \"alias.$name\" $value",$rtn);
}

function removeAllAliases() {
  global $added_aliases;
  if ($added_aliases) {
    foreach ($added_aliases as $a) {
      Git::exec("config --global --unset \"alias.$a\"", $rtn);
    }
  }
}

class ExpandResult {

  /**
   * @var array
   */
  public $expansions;

  /**
   * ExpandResult constructor.
   *
   * @param $result
   */
  public function __construct($result) {
    $this->expansions = $result;
  }

  /**
   * @return $this
   */
  public function contains($value, $contains=true) {
    $msg = $contains ? "Result should contain '$value'" : "Result should not contain '$value'";
    $msg .= "\n" . print_r($this->expansions, true);
    Assert::assertThat(in_array($value, $this->expansions) == $contains, Assert::isTrue(), $msg);
    return $this;
  }

  public function single($value) {
    $msg = "Result should be '$value'\n" . print_r($this->expansions, true);
    Assert::assertThat(count($this->expansions) == 1 && $this->expansions[0] === $value, Assert::isTrue(), $msg);
  }

  public function empty() {
    Assert::assertThat(count($this->expansions) == 0, Assert::isTrue(), 'Results should be empty.');
  }
}