<?php

class GitSettings {
    public $enablePromptStatus = true;
    public $debug = false;
    public $enableFileStatus = true;

    public $defaultColor = '';
    public $branchColor = 'Cyan';
    public $indexColor = 'Green';
    public $workingColor = 'Red';
    public $stashColor = 'DarkRed';
    public $errorColor = 'DarkRed';

    public $pathStatusSeparator = ' ';
    public $beforeStatus = '[';
    public $beforeStatusColor = '';
    public $delimStatus = ' |';
    public $delimStatusColor = 'Yellow';
    public $afterStatus = ']';
    public $afterStatusColor = '';

    public $beforeIndex      = '';
    public $beforeIndexColor = 'Green';
    public $beforeStash      = ' (';
    public $beforeStashColor = 'DarkRed';
    public $afterStash       = ')';
    public $afterStashColor  = 'DarkRed';

    public $localDefaultStatusSymbol = '';
    public $localDefaultStatusColor = 'Green';
    public $localWorkingStatusSymbol = '!';
    public $localWorkingStatusColor = 'Red';
    public $localStagedStatusSymbol  = '~';
    public $localStagedStatusColor = 'Cyan';

    public $branchGoneStatusSymbol           = "\u{00D7}"; # × Multiplication sign
    public $branchIdenticalStatusSymbol      = "\u{2261}"; # ≡ Three horizontal lines
    public $branchAheadStatusSymbol          = "\u{2191}"; # ↑ Up arrow
    public $branchBehindStatusSymbol         = "\u{2193}"; # ↓ Down arrow
    public $branchBehindAndAheadStatusSymbol = "\u{2195}"; # ↕ Up & Down arrow
    public $branchGoneColor                  = 'Cyan';
    public $branchIdenticalColor             = 'Cyan';
    public $branchAheadColor                 = 'Green';
    public $branchBehindColor                = 'Red';
    public $branchBehindAndAheadColor        = 'Yellow';

    public $branchBehindAndAheadDisplay = 'Full'; //Full, Compact, Minimal

    public $fileAddedText       = '+';
    public $fileModifiedText    = '~';
    public $fileRemovedText     = '-';
    public $fileConflictedText  = '!';
    public $branchUntrackedText = '';

    public $enableStashStatus     = false;
    public $showStatusWhenZero    = true;
    public $autoRefreshIndex      = true;

    public $untrackedFilesMode = 'Normal'; //Normal, All, No

    public $enableFileStatusFromCache = null;
    public $repositoriesInWhichToDisableFileStatus = [];

    public $describeStyle = '';
    //public $windowTitle = {param($GitStatus, [bool]$IsAdmin) "$(if ($IsAdmin) {'Admin: '})$(if ($GitStatus) {"$($GitStatus.RepoName) [$($GitStatus.Branch)]"} else {Get-PromptPath}) ~ PowerShell $($PSVersionTable.PSVersion) $([IntPtr]::Size * 8)-bit ($PID)"}

// "\[\033]0;\w\007\]\n${CYAN}\u ${D}at ${ORANGE}\h ${D}in ${GREEN}\w ${D}"

    public $defaultPromptPrefix       = '[{Cyan}\u{Reset}@{Yellow}\h {Green}\W{Reset}]';
    public $defaultPromptPath         = ''; //'$(Get-PromptPath)'
    public $defaultPromptBeforeSuffix = '';
//    public $defaultPromptDebug        = ' [DBG]:';
//    public $defaultPromptDebugColor   = 'Magenta';
    public $defaultPromptSuffix       = '$ ';
    public $defaultPromptSuffixColor = '';

    public $defaultPromptAbbreviateHomeDirectory = true;
    public $defaultPromptWriteStatusFirst        = false;
    public $defaultPromptEnableTiming            = false;
    public $defaultPromptTimingFormat = ' {0}ms';

    public $branchNameLimit = 0;
    public $truncatedBranchSuffix = '...';

  // Creates from environment variables
  public static function create() {
    $settings = new static();

    foreach(get_class_vars(static::class) as $property => $value) {
      $env_variable_name = 'GIT_PROMPT_' . ucwords($property);
      $env_value = getenv($env_variable_name);

      if ($env_value !== FALSE) {
        $settings->{$property} = $env_value;
      }
    }

    return $settings;
  }
}