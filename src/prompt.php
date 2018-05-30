<?php

class Prompt {

  private $settings;

  private $status;

  private $output = '';

  public static function create(GitSettings $settings) {
    return new static($settings, getGitStatus($settings));
  }

  public function __construct(GitSettings $settings, array $status) {
    $this->settings = $settings;
    $this->status = $status;
  }

  public function writePrompt() {
    print $this->getPrompt();
  }

  public function getPrompt() {
    $status = $this->status;
    $s = $this->settings;

    if (!$status || !$s) {
      return '';
    }

    # When prompt is first (default), place the separator before the status summary
    if (!$s->defaultPromptWriteStatusFirst) {
      $this->write($s->pathStatusSeparator);
    }

    $this->write($s->beforeStatus);
    $this->write($s->beforeStatus);
    $this->writeBranchName(TRUE);
    $this->writeBranchStatus();


    if ($s->enableFileStatus && $status['has_index']) {
      $this->write($s->beforeIndex);
      $this->writeGitIndexStatus();

      if ($status['has_working']) {
        $this->write($s->delimStatus);
      }
    }

    if ($s->enableFileStatus && $status['has_working']) {
      $this->writeGitWorkingDirStatus();
    }

    $this->writeGitWorkingDirStatusSummary();

    if ($s->enableStashStatus && ($status['stash_count'] > 0)) {
      $this->writeGitStashCount();
    }

    $this->write($s->afterStatus);

    # When status is first, place the separator after the status summary
    if ($s->defaultPromptWriteStatusFirst) {
      $this->write($s->pathStatusSeparator);
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

    $this->write($branchNameTextSpan);
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

      $this->write($branchStatusTextSpan);
    }

  }

  private function writeGitIndexStatus($no_leading_space = FALSE) {
    $s = $this->settings;

    $status = $this->status;
    if ($status['has_index']) {
      if ($s->showStatusWhenZero || count($status['index']['added'])) {
        $indexStatusText = ' ';
        if ($no_leading_space) {
          $indexStatusText = '';
          $no_leading_space = FALSE;
        }

        $indexStatusText .= $s->fileAddedText . count($status['index']['added']);
        $this->write($indexStatusText, $s->indexColor);
      }

      if ($s->showStatusWhenZero || count($status['index']['modified'])) {
        $indexStatusText = ' ';
        if ($no_leading_space) {
          $indexStatusText = '';
          $no_leading_space = FALSE;
        }

        $indexStatusText .= $s->fileModifiedText . count($status['index']['modified']);

        $this->write($indexStatusText, $s->indexColor);
      }

      if ($s->showStatusWhenZero || count($status['index']['deleted'])) {
        $indexStatusText = ' ';
        if ($no_leading_space) {
          $indexStatusText = '';
          $no_leading_space = FALSE;
        }

        $indexStatusText .= $s->fileRemovedText . count($status['index']['deleted']);

        $this->write($indexStatusText, $s->indexColor);
      }

      if (count($status['index']['unmerged'])) {
        $indexStatusText = ' ';
        if ($no_leading_space) {
          $indexStatusText = '';
          $no_leading_space = FALSE;
        }

        $indexStatusText .= $s->fileConflictedText . count($status['index']['unmerged']);

        $this->write($indexStatusText, $s->indexColor);
      }
    }

  }

  private function writeGitWorkingDirStatus($no_leading_space = FALSE) {
    $status = $this->status;
    $s = $this->settings;

    if ($status['has_working']) {
      if ($s->showStatusWhenZero || count($status['working']['added'])) {
        $workingStatusText = ' ';
        if ($no_leading_space) {
          $workingStatusText = '';
          $no_leading_space = FALSE;
        }

        $workingStatusText .= $s->fileAddedText . count($status['working']['added']);

        $this->write($workingStatusText, $s->workingColor);
      }

      if ($s->showStatusWhenZero || count($status['working']['modified'])) {
        $workingStatusText = ' ';
        if ($no_leading_space) {
          $workingStatusText = '';
          $no_leading_space = FALSE;
        }

        $workingStatusText .= $s->fileModifiedText . count($status['working']['modified']);
        $this->write($workingStatusText, $s->workingColor);
      }

      if ($s->showStatusWhenZero || count($status['working']['deleted'])) {
        $workingStatusText = ' ';
        if ($no_leading_space) {
          $workingStatusText = '';
          $no_leading_space = FALSE;
        }

        $workingStatusText .= $s->fileRemovedText . count($status['working']['deleted']);


        $this->write($workingStatusText, $s->workingColor);
      }

      if (count($status['working']['modified'])) {
        $workingStatusText = ' ';
        if ($no_leading_space) {
          $workingStatusText = "";
          $no_leading_space = FALSE;
        }

        $workingStatusText .= $s->fileConflictedText . count($status['working']['unmerged']);
        $this->write($workingStatusText, $s->workingColor);
      }
    }

  }

  private function writeGitWorkingDirStatusSummary($no_leading_space = FALSE) {
    $s = $this->settings;
    $status = $this->status;

    # No uncommited changes
    $localStatusSymbol = $s->localDefaultStatusSymbol;

    if ($status['has_working']) {
      # We have un-staged files in the working tree
      $localStatusSymbol = $s->localWorkingStatusSymbol;
    }
    elseif ($status['has_index']) {
      # We have staged but uncommited files
      $localStatusSymbol = $s->localStagedStatusSymbol;
    }

    if ($localStatusSymbol[0]) {
      $textSpan = $localStatusSymbol;
      if (!$no_leading_space) {
        $textSpan[0] = ' ' . $localStatusSymbol[0];
      }

      $this->write($textSpan);
    }

  }

  private function writeGitStashCount() {
    $s = $this->settings;
    $status = $this->status;

    if ($status['stash_count'] > 0) {
      $stashText = $status['stash_count'];

      $this->write($s->beforeStash);
      $this->write($stashText);
      $this->write($s->afterStash);
    }
  }

  private function write($object, $foregroundColor = NULL, $backgroundColor = NULL, $color = NULL) {
    global $ansi_esc;
    $output = &$this->output;


    if ($object === NULL || $object === '' || (is_array($object) && !$object[0])) {
      return;
    }

    //    if ($PSCmdlet.ParameterSetName -eq "CellColor") {
    //      $bgColor = $Color.BackgroundColor
    //        $fgColor = $Color.ForegroundColor
    //    }
    //    else {
    $bgColor = $backgroundColor;
    $fgColor = $foregroundColor;
    //    }

    $s = $this->settings;


    if (NULL == $fgColor) {
      $fgColor = $s->defaultColor; //.ForegroundColor
    }

    //      if (null == $bgColor) {
    //        $bgColor = $s.DefaultColor.BackgroundColor
    //      }

    if (is_array($object)) {
      //$bgColor = $Object.BackgoundColor
      $fgColor = $object[1];
      $object = $object[0];
    }

    $fg = foreground($fgColor);
    $bg = background(''); //@todo support bg colors

    $output .= $fg . $bg . $object . $ansi_esc . '0m';
  }

  public function toString() {
    return $this->output;
  }

}