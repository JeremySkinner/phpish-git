<?php

class GitSettings { 
    public $enablePromptStatus = true;
    public $debug = true;
    public $enableFileStatus = true;
    public $untrackedFilesMode = "Normal"; //Normal, All, No
    public $enableStashStatus = false;
}

$settings = new GitSettings();

class Logger {

    function __construct($enabled=true) {
        $this->enabled = $enabled;
        $this->start = microtime(true);
    }

    public function log($msg) {
        if(!$this->enabled) {
            return;
        }
        $now = microtime(true);
        $ellapsed = ($now - $this->start) * 1000;
        print "$ellapsed:$msg\n";
    }

    public static function null() {
        return new Logger(false);
    }
}

function getGitDirectory() {
    $rtn;
    $out=[];

    $dir = exec('git rev-parse --git-dir', $out, $rtn);

    if ($rtn !== 0) {
        return null;
    }

    return $dir;
}

function getGitBranch($dir = null, Logger $log = null) {
    if(!$dir) $dir = getGitDirectory();
    if(!$dir) return;
    if(!$log) $log = Logger::null();

    $log->log('Finding branch');

    $r = ''; $b = ''; $c = '';

    if (file_exists("$dir/rebase-merge/interactive")) {
        $log->log('Found rebase merge interactive');
        $r = '|REBASE-i';
        $b = file_get_contents("$dir/rebase-merge/head-name");
    }
    elseif (file_exists("$dir\rebase-merge")) {
        $log->log('Found rebase-merge');
        $r = '|REBASE-m';
        $b = file_get_contents("$dir\rebase-merge\head-name");
    }
    else {
        if (file_exists("$dir\rebase-apply")) {
            $log->log('Found rebase-apply');
            if (file_exists("$dir\rebase-apply\rebasing")) {
                $log->log('Found rebase-apply\rebasing');
                $r = '|REBASE';
            }
            elseif (file_exists("$dir\rebase-apply\applying")) {
                $log->log('Found rebase-apply\applying');
                $r = '|AM';
            }
            else {
                $log->log('Found rebase-apply');
                $r = '|AM/REBASE';
            }
        }
        elseif (file_exists("$dir\MERGE_HEAD")) {
            $log->log('Found MERGE_HEAD');
            $r = '|MERGING';
        }
        elseif (file_exists("$dir\CHERRY_PICK_HEAD")) {
            $log->log('Found CHERRY_PICK_HEAD');
            $r = '|CHERRY-PICKING';
        }
        elseif (file_exists("$dir\BISECT_LOG")) {
            $log->log('Found BISECT_LOG');
            $r = '|BISECTING';
        }

        $log->log('Trying symbolic ref');

        $b = exec('git symbolic-ref HEAD -q', $out, $rtn);

        //@todo posh-git tries to use describe or tag if sym ref failed.
        //if these fail then it tries to parse the contents of HEAD
        //if this fails it tries rev-parse HEAD
    }

    $log->log('Inside git directory?');

    if ('true' == exec('git rev-parse --is-inside-git-dir')) {
        $log->log('Inside git directory');
        if ('true' == exec('git rev-parse --is-bare-repository')) {
            $c = 'BARE:';
        }
        else {
            $b = 'GIT_DIR!';
        }
    }

    $b = str_replace('refs/heads/', '', $b);
    return "$c$b$r";
}

function inDotGitOrBareRepoDir($gitDir) {
    if (strpos(getcwd(), $gitDir) === 0) {
        return true;
    }
    return false;
}

function getGitStatus($gitDir = null, $force = false) {
    global $settings;
    $enabled = $force || !$settings || $settings->enablePromptStatus;

    if($enabled && !$gitDir) {
        $gitDir = getGitDirectory();
    }

    if ($enabled && $gitDir) {
        $log = new Logger($settings->debug);

        $branch = null;
        $aheadBy = 0;
        $behindBy = 0;
        $gone = false;
        $indexAdded = [];
        $indexModified = [];
        $indexDeleted = [];
        $indexUnmerged = [];
        $filesAdded = [];
        $filesModified = [];
        $filesDeleted = [];
        $filesUnmerged = [];
        $stashCount = 0;

        if($settings->enableFileStatus && !inDotGitOrBareRepoDir($gitDir)) {
            // @todo posh-git has a check for disabled repositories in here.
            $log->log('Getting status'); 
            switch ($settings->untrackedFilesMode) {
                case "No":      $untrackedFilesOption = "-uno"; break;
                case "All":     $untrackedFilesOption = "-uall"; break; 
                case "Normal":  $untrackedFilesOption = "-unormal"; break;
            }
            
            $status = exec("git -c core.quotepath=false -c color.status=false status $untrackedFilesOption --short --branch", $out, $ret);
            if($settings->EnableStashStatus) {
                $log->log('Getting stash count');
                //@todo implement stash
                //$stashCount = $null | git stash list 2>$null | measure-object | Select-Object -expand Count
            }

            $log->log('Parsing status');

            foreach($out as $status_line) {
                if (preg_match('/^(?<index>[^#])(?<working>.) (?<path1>.*?)(?: -> (?<path2>.*))?$/', $status_line, $matches)) {
                    $log->log("Status 1: $status_line");
                    switch ($matches['index']) {
                        case 'A':$indexAdded[] = $matches['path1']; break;
                        case 'M': $indexModified[] = $matches['path1']; break;
                        case 'R': $indexModified[] = $matches['path1']; break;
                        case 'C': $indexModified[] = $matches['path1']; break;
                        case 'D': $indexDeleted[] = $matches['path1']; break;
                        case 'U': $indexUnmerged[] = $matches['path1']; break;
                    }
                    switch ($matches['working']) {
                        case '?': $filesAdded[] = $matches['path1']; break;
                        case 'A': $filesAdded[] = $matches['path1']; break;
                        case 'M': $filesModified[] = $matches['path1']; break;
                        case 'D': $filesDeleted[] = $matches['path1']; break;
                        case 'U': $filesUnmerged[] = $matches['path1']; break;
                    }
                }
    
                if(preg_match('/^## (?<branch>\S+?)(?:\.\.\.(?<upstream>\S+))?(?: \[(?:ahead (?<ahead>\d+))?(?:, )?(?:behind (?<behind>\d+))?(?<gone>gone)?\])?$/', $status_line, $matches)) {
                    $log->log("Status 2: $status_line");
                    $branch = $matches['branch'];
                    $upstream = $matches['upstream'];
                    $aheadBy = (int)$matches['ahead'];
                    $behindBy = (int)$matches['behind'];
                    $gone = $matches['gone'] == 'gone';
                }
    
                if(preg_match('/^## Initial commit on (?<branch>\S+)$/', $status_line, $matches)) {
                    $log->log("Status 3: $status_line");
                    $branch = $matches['branch'];
                }
            }

            

            if(!$branch) { 
                $branch = getGitBranch($gitDir, $log); 
            }

            $log->log('Building status');

            #$indexPaths = GetUniquePaths($indexAdded,$indexModified,$indexDeleted,$indexUnmerged);
            #$workingPaths = GetUniquePaths($filesAdded,$filesModified,$filesDeleted,$filesUnmerged);
          
            $has_index = (count($indexAdded) + count($indexDeleted) + count($indexModified) + count($indexUnmerged)) > 0;
            $has_files = (count($filesAdded) + count($filesDeleted) + count($filesModified) + count($filesUnmerged)) > 0;

            $output = [
                'git_dir'          => $gitDir,
                //'RepoName'        => Split-Path (Split-Path $GitDir -Parent) -Leaf
                'branch'          => $branch,
                'ahead_by'         => $aheadBy,
                'behind_by'        => $behindBy,
                'upstream_gone'    => $gone,
                'upstream'        => $upstream,
                'has_index'        => $has_index,
                'index'           => $index,
                'has_working'      => $has_files,
                'working'         => $working,
                'has_untracked'    => count($filesAdded) > 0,
                'stash_count'      => $stashCount,
            ];

            print_r($output);

            $log->log('Finished');
            return $output;
        }
        
    }
}

getGitStatus();