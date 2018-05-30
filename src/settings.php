<?php

class GitSettings {
    public $enablePromptStatus = true;
    public $debug = true;
    public $enableFileStatus = true;

    public $defaultColor = '';
    public $branchColor = 'Cyan';
    public $indexColor = 'DarkGreen';
    public $workingColor = 'DarkRed';
    public $stashColor = 'Red';
    public $errorColor = 'Red';

    public $pathStatusSeparator = ' ';
    public $beforeStatus = ['[', 'Yellow'];
    public $delimStatus = [' |', 'Yellow'];
    public $afterStatus = [']', 'Yellow'];

    public $beforeIndex              = ['', 'DarkGreen'];
    public $beforeStash              = [' (', 'Red'];
    public $afterStash               = [')', 'Red'];

    public $localDefaultStatusSymbol = ['', 'DarkGreen'];
    public $localWorkingStatusSymbol = ['!', 'DarkRed'];
    public $localStagedStatusSymbol  = ['~', 'Cyan'];

    public $branchGoneStatusSymbol           = ["\u{00D7}", 'DarkCyan']; # × Multiplication sign
    public $branchIdenticalStatusSymbol      = ["\u{2261}", 'Cyan'];     # ≡ Three horizontal lines
    public $branchAheadStatusSymbol          = ["\u{2191}", 'Green'];    # ↑ Up arrow
    public $branchBehindStatusSymbol         = ["\u{2193}", 'Red'];      # ↓ Down arrow
    public $branchBehindAndAheadStatusSymbol = ["\u{2195}", 'Yellow'];   # ↕ Up & Down arrow

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

    public $defaultPromptPrefix       = '';
    public $defaultPromptPath         = ''; //'$(Get-PromptPath)'
    public $defaultPromptBeforeSuffix = '';
    public $defaultPromptDebug        = [' [DBG]:', 'Magenta'];
    public $defaultPromptSuffix       = '$(">" * ($nestedPromptLevel + 1)) ';

    public $defaultPromptAbbreviateHomeDirectory = true;
    public $defaultPromptWriteStatusFirst        = false;
    public $defaultPromptEnableTiming            = false;
    public $defaultPromptTimingFormat = ' {0}ms';

    public $branchNameLimit = 0;
    public $truncatedBranchSuffix = '...';
}