<?php

class Prompt {

  private $settings;

  private $status;

  private $output = '';

  public function __construct(GitSettings $settings, array $status) {
    $this->settings = $settings;
    $this->status = $status;
  }

  private function writePrompt($object, $foregroundColor = NULL, $backgroundColor = NULL, $color = NULL) {
    $output = &$this->output;
  }

  public function write() {
    $status = $this->status;
    $s = $this->settings;

    if (!$status || !$s) {
      return '';
    }

    $sb = '';

    # When prompt is first (default), place the separator before the status summary
    if (!$s->defaultPromptWriteStatusFirst) {
      $this->writePrompt($s->pathStatusSeparator);
    }

    $this->writePrompt($s->beforeStatus);
    $this->writePrompt($s->beforeStatus);
    $this->writeBranchName(TRUE);
    $this->writeBranchStatus();


    if ($s->enableFileStatus && $status['has_index']) {
      $this->writePrompt($s->beforeIndex);
      $this->writeGitIndexStatus();

      if ($status['has_working']) {
        $this->writePrompt($s->delimStatus);
      }
    }

    if ($s->enableFileStatus && $status['has_working']) {
      $this->writeGitWorkingDirStatus();
    }

    $this->writeGitWorkingDirStatusSummary();

    if ($s->enableStashStatus && ($status['stash_count'] > 0)) {
      $this->writeGitStatshCount();
    }

    $this->writePrompt($s->afterStatus);

    # When status is first, place the separator after the status summary
    if ($s->defaultPromptWriteStatusFirst) {
      $this->writePrompt($s->pathStatusSeparator);
    }

    return $this->output;
  }

  private function formatGitBranchName($branch_name) {
    if (!$branch_name) {
      return $branch_name;
    }

    if (($this->settings->branchNameLimit > 0) && (strlen($branch_name) > $this->settings->branchNameLimit)) {
      $branch_name = substr($branch_name, 0, $this->settings->branchNameLimit);
      $branch_name .= $this->settings->truncatedBranchSuffix;
    }

    return $branch_name;
  }

  private function getGitBranchStatusColor() {
    $s = $this->settings;
    $status = $this->status;

    if (!$s) {
      return [];
    }

    $branchStatusTextSpan = ['', $s->branchColor];

    if (($status['behind_by'] >= 1) && ($status['ahead_by'] >= 1)) {
      # We are both behind and ahead of remote
      $branchStatusTextSpan = ['', $s->branchBehindAndAheadStatusSymbol];
    }
    elseif ($status['behind_by'] >= 1) {
      # We are behind remote
      $branchStatusTextSpan = ['', $s->branchBehindStatusSymbol];
    }
    elseif ($status['ahead_by'] >= 1) {
      # We are ahead of remote
      $branchStatusTextSpan = ['', $s->branchAheadStatusSymbol];
    }

    return $branchStatusTextSpan;
  }

  private function writeBranchName($no_leading_space) {
    $status = $this->status;

    $branchNameTextSpan = $this->getGitBranchStatusColor();
    $branchNameTextSpan[0] = $this->formatGitBranchName($status['branch']);

    if (!$no_leading_space) {
      $branchNameTextSpan[0] = ' ' . $branchNameTextSpan[0];
    }

    $this->writePrompt($branchNameTextSpan);
  }

  private function writeBranchStatus($no_leading_space = FALSE) {
    $s = $this->settings;
    $status = $this->status;

    $branchStatusTextSpan = $this->getGitBranchStatusColor();

    if (!$status['upstream']) {
      $branchStatusTextSpan[0] = $s->branchUntrackedText;
    }
    elseif ($status['upstream_gone']) {
      # Upstream branch is gone
      $branchStatusTextSpan[0] = $s->branchGoneStatusSymbol[0];
    }
    elseif (($status['behind_by'] == 0) && ($status['ahead_by'] == 0)) {
      # We are aligned with remote
      $branchStatusTextSpan[0] = $s->branchIdenticalStatusSymbol[0];
    }
    elseif (($status['behind_by'] >= 1) && ($status['ahead_by'] >= 1)) {
      # We are both behind and ahead of remote
      if ($s->branchBehindAndAheadDisplay == 'Full') {
        $branchStatusTextSpan[0] = "{$s->branchBehindStatusSymbol[0]}{$s['behind_by']} {$s->branchAheadStatusSymbol[0]}{$status['ahead_by']}";
      }
      elseif ($s->branchBehindAndAheadDisplay == 'Compact') {
        $branchStatusTextSpan[0] = $status['behind_by'] . $s->branchBehindAndAheadStatusSymbol[0] . $status['ahead_by'];
      }
    }
    elseif ($status['behind_by'] >= 1) {
      # We are behind remote
      if (($s->branchBehindAndAheadDisplay == 'Full') || ($s->branchBehindAndAheadDisplay == 'Compact')) {
        $branchStatusTextSpan[0] = $s->branchBehindStatusSymbol[0] . $status['behind_by'];
      }
    }
    elseif ($status['ahead_by'] >= 1) {
      # We are ahead of remote
      if (($s->branchBehindAndAheadDisplay == 'Full') || ($s->branchBehindAndAheadDisplay == 'Compact')) {
        $branchStatusTextSpan[0] = $s->branchAheadStatusSymbol[0] . $status['ahead_by'];
      }
    }
    else {
      # This condition should not be possible but defaulting the variables to be safe
      $branchStatusTextSpan[0] = '?';
    }

    if ($branchStatusTextSpan[0]) {
      if (!$no_leading_space) {
        $branchStatusTextSpan[0] = ' ' . $branchStatusTextSpan[0];
      }

      $this->writePrompt($branchStatusTextSpan);
    }

  }

}