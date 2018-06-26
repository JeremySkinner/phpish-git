<?php

class Git {
  public static function directory() {
    $rtn=0;
    $dir = Git::exec('rev-parse --git-dir', $rtn);

    if ($rtn !== 0) {
      return null;
    }

    return $dir;
  }

  public static function branch($dir = null, $log = null) {
    if (!$dir) $dir = Git::directory();
    if (!$log) $log = Logger::null();

    $log->log('Finding branch');

    $r = ''; $b = ''; $c = '';

    if (file_exists("$dir/rebase-merge/interactive")) {
      $log->log('Found rebase merge interactive');
      $r = '|REBASE-i';
      $b = file_get_contents("$dir/rebase-merge/head-name");
    }
    elseif (file_exists("$dir/rebase-merge")) {
      $log->log('Found rebase-merge');
      $r = '|REBASE-m';
      $b = file_get_contents("$dir/rebase-merge/head-name");
    }
    else {
      $log->log('Looking for rebase apply...');
      if (file_exists("$dir/rebase-apply")) {
        $log->log('Found rebase-apply');
        if (file_exists("$dir/rebase-apply/rebasing")) {
          $log->log('Found rebase-apply/rebasing');
          $r = '|REBASE';
        }
        elseif (file_exists("$dir/rebase-apply/applying")) {
          $log->log('Found rebase-apply/applying');
          $r = '|AM';
        }
        else {
          $log->log('Found rebase-apply');
          $r = '|AM/REBASE';
        }
      }
      elseif (file_exists("$dir/MERGE_HEAD")) {
        $log->log('Found MERGE_HEAD');
        $r = '|MERGING';
      }
      elseif (file_exists("$dir/CHERRY_PICK_HEAD")) {
        $log->log('Found CHERRY_PICK_HEAD');
        $r = '|CHERRY-PICKING';
      }
      elseif (file_exists("$dir/BISECT_LOG")) {
        $log->log('Found BISECT_LOG');
        $r = '|BISECTING';
      }

      $log->log('Trying symbolic ref');

      $b = Git::exec('symbolic-ref HEAD -q', $rtn);

      if(!$b) {
        if(file_exists("$dir/HEAD")) {
          $log->log('Reading from .git/HEAD');
          $ref = file_get_contents("$dir/HEAD");
        }
        else {
          $ref = Git::exec('rev-parse HEAD', $rtn);
        }

        if (preg_match('/ref: (?<ref>.+)/', $ref, $matches)) {
          $b = $matches['ref'];
        }
        elseif ($ref && strlen($ref) >= 7) {
          $b = substr($ref, 0, 7) . '...';
        }
        else {
          $b = 'unknown';
        }
      }
    }

    $log->log('Inside git directory?');

    if ('true' == Git::exec('rev-parse --is-inside-git-dir', $rtn)) {
      $log->log('Inside git directory');
      if ('true' == Git::exec('git rev-parse --is-bare-repository', $rtn)) {
        $c = 'BARE:';
      }
      else {
        $b = 'GIT_DIR!';
      }
    }

    $b = str_replace('refs/heads/', '', $b);
    return "$c$b$r";
  }

  public static function inDotGitOrBareRepoDir($gitDir) {
    if (strpos(getcwd(), $gitDir) === 0) {
      return true;
    }
    return false;
  }

  public static function status(GitSettings $settings, $gitDir = null, $force = false, Logger $log = null) {
    if (!$gitDir) $gitDir = Git::directory();

    $enabled = $force || $settings->enablePromptStatus;

    if (!$log) $log = new Logger($settings->debug);

    if ($enabled && $gitDir) {

      $branch = null;
      $aheadBy = 0;
      $behindBy = 0;
      $upstream = '';
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

      if($settings->enableFileStatus && !Git::inDotGitOrBareRepoDir($gitDir)) {
        // @todo posh-git has a check for disabled repositories in here.
        $log->log('Getting status');
        switch ($settings->untrackedFilesMode) {
          case "No":    $untrackedFilesOption = "-uno"; break;
          case "All":   $untrackedFilesOption = "-uall"; break;
          case "Normal":  $untrackedFilesOption = "-unormal"; break;
        }

        $status = Git::exec("-c core.quotepath=false -c color.status=false status $untrackedFilesOption --short --branch", $rtn);
        $status = explode("\n", $status);

        if($settings->enableStashStatus) {
          $log->log('Getting stash count');
          //@todo implement stash
          //$stashCount = $null | git stash list 2>$null | measure-object | Select-Object -expand Count
        }

        $log->log('Parsing status');

        foreach($status as $status_line) {
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
            $upstream = isset($matches['upstream']) ? $matches['upstream'] : '';
            $aheadBy = isset($matches['ahead']) ? (int)$matches['ahead'] : 0;
            $behindBy = isset($matches['behind']) ? (int)$matches['behind'] : 0;
            $gone = isset($matches['gone']) ? $matches['gone'] == 'gone' : false;
          }

          if(preg_match('/^## Initial commit on (?<branch>\S+)$/', $status_line, $matches)) {
            $log->log("Status 3: $status_line");
            $branch = $matches['branch'];
          }
        }

        if(!$branch) {
          $branch = Git::branch($gitDir, $log);
        }

        $log->log('Building status');

        #$indexPaths = GetUniquePaths($indexAdded,$indexModified,$indexDeleted,$indexUnmerged);
        #$workingPaths = GetUniquePaths($filesAdded,$filesModified,$filesDeleted,$filesUnmerged);

        $has_index = (count($indexAdded) + count($indexDeleted) + count($indexModified) + count($indexUnmerged)) > 0;
        $has_files = (count($filesAdded) + count($filesDeleted) + count($filesModified) + count($filesUnmerged)) > 0;

        $index = [
          'added' => $indexAdded,
          'deleted' => $indexDeleted,
          'modified' => $indexModified,
          'unmerged' => $indexUnmerged,
        ];

        $working = [
          'added' => $filesAdded,
          'deleted' => $filesDeleted,
          'modified' => $filesModified,
          'unmerged' => $filesUnmerged,
        ];

        $output = [
          'git_dir'      => $gitDir,
          //'RepoName'    => Split-Path (Split-Path $GitDir -Parent) -Leaf
          'branch'      => $branch,
          'ahead_by'     => $aheadBy,
          'behind_by'    => $behindBy,
          'upstream_gone'  => $gone,
          'upstream'    => $upstream,
          'has_index'    => $has_index,
          'index'       => $index,
          'has_working'    => $has_files,
          'working'     => $working,
          'has_untracked'  => count($filesAdded) > 0,
          'stash_count'    => $stashCount,
        ];

        $log->log('Finished');
        return $output;
      }
    }
  }

  public static function exec($args, &$rtn, $as_array=false) {
    $command = 'git ' . $args;

    $process = proc_open($command, [
      0 => ['pipe', 'r'],
      1 => ['pipe', 'w'],
      2 => ['pipe', 'w'],
    ],
      $pipes
    );

    if (!is_resource($process)) {
      throw new \RuntimeException('Could not create a valid process');
    }

    // This will prevent to program from continuing until the processes is complete
    // Note: exitcode is created on the final loop here
    $status = proc_get_status($process);
    while($status['running']) {
      $status = proc_get_status($process);
    }

    $stdOutput = stream_get_contents($pipes[1]);
    $stdError  = stream_get_contents($pipes[2]);

    proc_close($process);

    $rtn = $status['exitcode'];

    if ($as_array) {
      return array_filter(explode("\n", $stdOutput));
    }

    return trim($stdOutput);
  }
}







