<?php

class Prompt {

  private $settings;

  private $status;

  private $output = '';

  private $log;

  public static function create(GitSettings $settings, Logger $log) {
    return new static($settings, Git::status($settings, null, false, $log), $log);
  }

  public function __construct(GitSettings $settings, $status, Logger $log) {
    $this->settings = $settings;
    $this->status = $status;
    $this->log = $log;
  }

  public function writePrompt() {
    print $this->getPrompt();
  }

  public function getPrompt() {
    $status = $this->status;
    $s = $this->settings;

    $this->write($s->defaultPromptPrefix, null, null, true);

    if (!$status || !$s) {
      $this->write($s->defaultPromptBeforeSuffix);
      $this->write($s->defaultPromptSuffix, $s->defaultPromptSuffixColor);  
      return $this->output;
    }

    # When prompt is first (default), place the separator before the status summary
    if (!$s->defaultPromptWriteStatusFirst) {
      $this->write($s->pathStatusSeparator);
    }

    $this->write($s->beforeStatus, $s->beforeStatusColor);
    $this->writeBranchName(TRUE);
    $this->writeBranchStatus();


    if ($s->enableFileStatus && $status['has_index']) {
      $this->write($s->beforeIndex, $s->beforeIndexColor);
      $this->writeGitIndexStatus();

      if ($status['has_working']) {
        $this->write($s->delimStatus, $s->delimStatusColor);
      }
    }

    if ($s->enableFileStatus && $status['has_working']) {
      $this->writeGitWorkingDirStatus();
    }

    $this->writeGitWorkingDirStatusSummary();

    if ($s->enableStashStatus && ($status['stash_count'] > 0)) {
      $this->writeGitStashCount();
    }

    $this->write($s->afterStatus, $s->afterStatusColor);

    # When status is first, place the separator after the status summary
    if ($s->defaultPromptWriteStatusFirst) {
      $this->write($s->pathStatusSeparator);
    }

    $this->write($s->defaultPromptBeforeSuffix);
    $this->write($s->defaultPromptSuffix, $s->defaultPromptSuffixColor);

    $this->log->log('Finished creating prompt');

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

    if (($status['behind_by'] >= 1) && ($status['ahead_by'] >= 1)) {
      # We are both behind and ahead of remote
      $color = $s->branchBehindAndAheadColor;
    }
    elseif ($status['behind_by'] >= 1) {
      # We are behind remote
      $color = $s->branchBehindColor;
    }
    elseif ($status['ahead_by'] >= 1) {
      # We are ahead of remote
      $color = $s->branchAheadColor;
    }

    if(!isset($color)) {
      $color = $s->branchColor;
    }

    return $color;
  }

  private function writeBranchName($no_leading_space) {
    $status = $this->status;

    $branch_name = $this->formatGitBranchName($status['branch']);
    $branch_color = $this->getGitBranchStatusColor();

    if (!$no_leading_space) {
      $branch_name = ' ' . $branch_name;
    }

    $this->write($branch_name, $branch_color);
  }

  private function writeBranchStatus($no_leading_space = FALSE) {
    $s = $this->settings;
    $status = $this->status;

    $branchStatus = '';
    $branchColor = $this->getGitBranchStatusColor();

    if (!$status['upstream']) {
      $branchStatus = $s->branchUntrackedText;
    }
    elseif ($status['upstream_gone']) {
      # Upstream branch is gone
      $branchStatus = $s->branchGoneStatusSymbol;
      $branchColor = $s->branchGoneColor;
    }
    elseif (($status['behind_by'] == 0) && ($status['ahead_by'] == 0)) {
      # We are aligned with remote
      $branchStatus = $s->branchIdenticalStatusSymbol;
      $branchColor = $s->branchIdenticalColor;
    }
    elseif (($status['behind_by'] >= 1) && ($status['ahead_by'] >= 1)) {
      # We are both behind and ahead of remote
      if ($s->branchBehindAndAheadDisplay == 'Full') {
        $branchStatus = "{$s->branchBehindStatusSymbol}{$status['behind_by']} {$s->branchAheadStatusSymbol}{$status['ahead_by']}";
        $branchColor = $s->branchBehindAndAheadColor;
      }
      elseif ($s->branchBehindAndAheadDisplay == 'Compact') {
        $branchStatus = $status['behind_by'] . $s->branchBehindAndAheadStatusSymbol . $status['ahead_by'];
        $branchColor = $s->branchBehindAndAheadColor;
      }
    }
    elseif ($status['behind_by'] >= 1) {
      # We are behind remote
      if (($s->branchBehindAndAheadDisplay == 'Full') || ($s->branchBehindAndAheadDisplay == 'Compact')) {
        $branchStatus = $s->branchBehindStatusSymbol . $status['behind_by'];
        $branchColor = $s->branchBehindColor;
      }
    }
    elseif ($status['ahead_by'] >= 1) {
      # We are ahead of remote
      if (($s->branchBehindAndAheadDisplay == 'Full') || ($s->branchBehindAndAheadDisplay == 'Compact')) {
        $branchStatus = $s->branchAheadStatusSymbol . $status['ahead_by'];
        $branchColor = $s->branchAheadColor;
      }
    }
    else {
      # This condition should not be possible but defaulting the variables to be safe
      $branchStatus = '?';
      $branchColor = 'Red';
    }

    if ($branchStatus) {
      if (!$no_leading_space) {
        $branchStatus = ' ' . $branchStatus;
      }

      $this->write($branchStatus, $branchColor);
    }

  }

  private function writeGitIndexStatus($no_leading_space = FALSE) {
    $s = $this->settings;

    $status = $this->status;
    if ($status['has_index']) {
      if ($s->showStatusWhenZero || count($status['index']['added'])) {
        $this->log->log('Index added: ' . count($status['index']['added']));
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

      if (count($status['working']['unmerged'])) {

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
    $localStatusColor = $s->localDefaultStatusColor;

    if ($status['has_working']) {
      $this->log->log('has_working: ' . $status['has_working'] );
      # We have un-staged files in the working tree
      $localStatusSymbol = $s->localWorkingStatusSymbol;
      $localStatusColor = $s->localWorkingStatusColor;
    }
    elseif ($status['has_index']) {
      # We have staged but uncommited files
      $localStatusSymbol = $s->localStagedStatusSymbol;
      $localStatusColor = $s->localStagedStatusColor;
    }

    if ($localStatusSymbol) {
      if (!$no_leading_space) {
        $localStatusSymbol = ' ' . $localStatusSymbol;
      }

      $this->write($localStatusSymbol, $localStatusColor);
    }

  }

  private function writeGitStashCount() {
    $s = $this->settings;
    $status = $this->status;

    if ($status['stash_count'] > 0) {
      $stashText = $status['stash_count'];

      $this->write($s->beforeStash, $s->beforeStashColor);
      $this->write($stashText);
      $this->write($s->afterStash, $s->afterStashColor);
    }
  }

  private function write($object, $foregroundColor = NULL, $backgroundColor = NULL, $embedded_colors = false) {
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

    $ansi = true;

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

    if ($ansi) {
      if ($embedded_colors) {
        $output .= colorTextWithEmbeddedColors($object);
      }
      else {
        $output .= colorText($object, $fgColor);
      }
      
    }
    else {
      $output .= $object;
    }
  }

  public function toString() {
    return $this->output;
  }

}