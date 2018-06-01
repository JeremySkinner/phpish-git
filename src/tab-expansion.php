<?php

class TabExpansion {

  protected $subComands = [
    'bisect' => "start bad good skip reset visualize replay log run",
    'notes' => 'add append copy edit get-ref list merge prune remove show',
    'reflog' => "show delete expire",
    'remote' => "
        add rename remove set-head set-branches
        get-url set-url show prune update
        ",
    'rerere' => "clear forget diff remaining status gc",
    'stash' => 'push save list show apply clear drop pop create branch',
    'submodule' => "add status init deinit update summary foreach sync",
    'svn' => "
        init fetch clone rebase dcommit log find-rev
        set-tree commit-diff info create-ignore propget
        proplist show-ignore show-externals branch tag blame
        migrate mkdirs reset gc
        ",
    'tfs' => "
        list-remote-branches clone quick-clone bootstrap init
        clone fetch pull quick-clone unshelve shelve-list labels
        rcheckin checkin checkintool shelve shelve-delete
        branch
        info cleanup cleanup-workspaces help verify autotag subtree reset-remote checkout
        ",
    'flow' => "init feature release hotfix support help version",
    'worktree' => "add list lock move prune remove unlock",
  ];

  protected $flow_sub_commands = [
    'init' => 'help',
    'feature' => 'list start finish publish track diff rebase checkout pull help delete',
    'bugfix' => 'list start finish publish track diff rebase checkout pull help delete',
    'release' => 'list start finish track publish help delete',
    'hotfix' => 'list start finish track publish help delete',
    'support' => 'list start help',
    'config' => 'list set base',
  ];

  protected $someCommands = [
    'add',
    'am',
    'annotate',
    'archive',
    'bisect',
    'blame',
    'branch',
    'bundle',
    'checkout',
    'cherry',
    'cherry-pick',
    'citool',
    'clean',
    'clone',
    'commit',
    'config',
    'describe',
    'diff',
    'difftool',
    'fetch',
    'format-patch',
    'gc',
    'grep',
    'gui',
    'help',
    'init',
    'instaweb',
    'log',
    'merge',
    'mergetool',
    'mv',
    'notes',
    'prune',
    'pull',
    'push',
    'rebase',
    'reflog',
    'remote',
    'rerere',
    'reset',
    'revert',
    'rm',
    'shortlog',
    'show',
    'stash',
    'status',
    'submodule',
    'svn',
    'tag',
    'whatchanged',
    'worktree',
  ];

  protected $gitCommandsWithLongParams;

  protected $gitCommandsWithShortParams;

  protected $gitCommandsWithParamValues;

  protected $settings;


  public function __construct(TabSettings $settings) {
    $this->settings = $settings;
    $this->gitCommandsWithLongParams = implode('|', array_keys(ParamTabExpansion::$longGitParams));
    $this->gitCommandsWithShortParams = implode('|', array_keys(ParamTabExpansion::$shortGitParams));
    $this->gitCommandsWithParamValues = implode('|', array_keys(ParamTabExpansion::$gitParamValues));
    //$this->vstsCommandsWithShortParams = implode('|', array_keys(ParamTabExpansion::shortVstsParams));
    //$this->vstsCommandsWithLongParams = implode('|', array_keys(ParamTabExpansion::longVstsParams));

    //@todo support git flow
    /*
    try {
        if ($null -ne (git help -a 2>&1 | Select-String flow)) {
            $script:someCommands += 'flow'
        }
    }
    catch {
        Write-Debug "Search for 'flow' in 'git help' output failed with error: $_"
    }
    */
  }


  private function cmdOperations($commands, $command, $filter) {
    $parts = preg_split("/\s+/", $commands[$command]);
    return array_filter($parts, function ($x) use ($filter) {
      return $x && startsWith($x, $filter);
    });
  }

  private function quoteStringWithSpecialChars($input) {
    return $input;

    // @todo this doesn't seem to work properly.
    //    if ($input && preg_match("/\s+|#|@|\$|;|,|''|\{|\}|\(|\)/", $input)) {
    //      $input = str_replace("'", "''", $input);
    //      return "'$input'";
    //    }
    return $input;
  }

  private function commands($filter, $includeAliases) {
    $cmd_list = [];

    if (!$this->settings->allCommands) {
      $cmd_list += array_filter($this->someCommands, function ($x) use ($filter) {
        return startsWith($x, $filter
        );
      });
    }
    else {
      $output = Git::exec('help --all', $rtn, TRUE);
      $output = array_filter($output, function ($x) {
        return preg_match("/^  \S.*/", $x);
      });
      foreach ($output as $cmd) {
        foreach (explode(' ', $cmd) as $cmd2) {
          if (startsWith($cmd2, $filter)) {
            $cmd_list[] = $cmd2;
          }
        }
      }
    }

    if ($includeAliases) {
      $cmd_list += $this->aliases($filter);
    }

    asort($cmd_list);
    return $cmd_list;
  }

  private function remotes($filter) {
    $remotes = [];
    $output = Git::exec('remote', $rtn, TRUE);
    foreach ($output as $remote) {
      if ($remote && startsWith($remote, $filter)) {
        $remotes[] = $this->quoteStringWithSpecialChars($remote);
      }
    }
    return $remotes;
  }

  private function branches($filter, $includeHEAD = FALSE, $prefix = '') {
    if (preg_match("/^(?<from>\S*\.{2,3})(?<to>.*)/", $filter, $matches)) {
      $prefix .= $matches['from'];
      $filter = $matches['to'];
    }
    $branches = [];
    $git_branches = Git::exec('branch --no-color', $rtn, TRUE);

    foreach ($git_branches as $branch) {
      if (!preg_match("/^\* \(HEAD detached .+\)$/", $branch)) {
        if (preg_match("/^\*?\s*(?<ref>.*)/", $branch, $matches)) {
          $branches[] = $matches['ref'];
        }
      }
    }

    $git_branches = Git::exec('branch --no-color -r', $rtn, TRUE);

    foreach ($git_branches as $branch) {
      if (preg_match("/^  (?<ref>\S+)(?: -> .+)?/", $branch, $matches)) {
        $branches[] = $matches['ref'];
      }
    }


    if ($includeHEAD) {
      $branches[] = 'HEAD';
      $branches[] = 'FETCH_HEAD';
      $branches[] = 'ORIG_HEAD';
      $branches[] = 'MERGE_HEAD';
    }

    $result = [];
    foreach ($branches as $index => $branch) {
      if ($branch && $branch != '(no branch)' && startsWith($branch, $filter)) {
        $result[] = $this->quoteStringWithSpecialChars($prefix . $branch);
      }
    }
    return $result;
  }

  private function remoteUniqueBranches($filter) {
    $branches = [];
    $git_branches = Git::exec('branch --no-color -r', $rtn, TRUE);
    foreach ($git_branches as $branch) {
      if (preg_match("@^  (?<remote>[^/]+)/(?<branch>\S+)(?! -> .+)?$@", $branch, $matches)) {
        if (startsWith($branch, $filter)) {
          $branches[] = $matches['branch'];
        }
      }
    }

    array_unique($branches);
    return $branches;
  }

  private function tags($filter, $prefix = '') {
    $git_tags = Git::exec('tag', $rtn, TRUE);
    $tags = [];
    foreach ($git_tags as $tag) {
      if (startsWith($tag, $filter)) {
        $tags[] = $this->quoteStringWithSpecialChars($prefix . $tag);
      }
    }
    return $tags;
  }

  private function features($filter, $command) {
    $prefix = Git::exec("config --local --get \"gitflow.prefix.$command\"", $rtn);
    $git_branches = Git::exec('branch --no-color', $rtn, TRUE);
    $branches = [];

    foreach ($git_branches as $branch) {
      if (preg_match("/^\*?\s*$prefix(?<ref>.*)/", $branch, $matches)) {
        if ($branch != '(no branch)' && startsWith($branch, $filter)) {
          $branches[] = $this->quoteStringWithSpecialChars($prefix . $matches['ref']);

        }
      }
    }

    return $branches;
  }

  private function remoteBranches($remote, $ref, $filter, $prefix = '') {
    $git_branches = Git::exec('branch --no-color -r', $rtn, TRUE);
    $branches = [];
    foreach ($git_branches as $branch) {
      if (startsWith($branch, "  $remote/$filter")) {
        $branch = $prefix . $ref . str_replace("  $remote/", '', $branch);
        $branch = trim($branch);
        if ($branch) {
          $branches[] = $this->quoteStringWithSpecialChars(trim($branch));
        }
      }
    }
    return $branches;
  }


  private function stashes($filter) {
    $git_stashes = Git::exec('stash list', $rtn, TRUE);
    $stashes = [];
    foreach ($git_stashes as $stash) {
      $stash = str_replace(':.*', '', $stash);
      if (startsWith($stash, $filter)) {
        $stashes[] = $this->quoteStringWithSpecialChars($stash);
      }
    }
    return $stashes;
  }

  //  function script:gitTfsShelvesets($filter) {
  //  (git tfs shelve-list) |
  //          Where-Object { $_ -like "$filter*" } |
  //          quoteStringWithSpecialChars
  //  }

  private function files($filter, $files) {
    asort($files);
    $files = array_filter($files, function ($x) use ($filter) {
      return startsWith($x, $filter);
    });
    return array_map([$this, 'quoteStringWithSpecialChars'], $files);
  }

  private function index($status, $filter) {
    return $this->files($filter, $status['index']);
  }

  private function addFiles($status, $filter) {
    return $this->files($filter, $status['working']['unmerged'] + $status['working']['modified'] + $status['working']['added']);
  }

  private function checkoutFiles($status, $filter) {
    return $this->files($filter, $status['working']['unmerged'] + $status['working']['modified'] + $status['working']['deleted']);
  }

  private function diffFiles($status, $filter, $staged) {
    if ($staged) {
      return $this->files($filter, $status['index']['modified']);
    }
    else {
      return $this->files($filter, $status['working']['unmerged'] + $status['working']['modified'] + $status['index']['modified']);
    }
  }

  private function mergeFiles($status, $filter) {
    return $this->files($filter, $status['working']['unmerged']);
  }

  private function deleted($status, $filter) {
    return $this->files($filter, $status['working']['deleted']);
  }


  private function aliases($filter) {
    $git_aliases = Git::exec('config --get-regexp ^alias\.', $rtn, TRUE);
    $aliases = [];
    foreach ($git_aliases as $alias) {
      if (preg_match("/^alias\.(?<alias>\S+) .*/", $alias, $matches)) {
        $alias = $matches['alias'];
        if ($alias && startsWith($alias, $filter)) {
          $aliases[] = $alias;
        }
      }
    }

    $aliases = array_unique($aliases);
    asort($aliases);
    return $aliases;
  }

  private function expandGitAlias($cmd, $rest) {
    $alias = trim(Git::exec("config \"alias.$cmd\"", $rtn));
    if ($alias) {
      //        $known = $this->settings->knownAliases[$alias];
      //          if ($known) {
      //            return "git $known$rest";
      //          }

      return "git $alias$rest";
    }
    else {
      return "git $cmd$rest";
    }
  }

  private function expandLongParams($hash, $cmd, $filter) {
    $param_values = explode(' ', $hash[$cmd]);
    $param_values = array_filter($param_values, function ($x) use ($filter) {
      return startsWith($x, $filter);
    });
    asort($param_values);

    return array_map(function ($val) {
      return "--$val";
    }, $param_values);

    //    ForEach-Object { -join ("--", $_) }
  }

  private function expandShortParams($hash, $cmd, $filter) {
    $param_values = explode(' ', $hash[$cmd]);
    $param_values = array_filter($param_values, function ($x) use ($filter) {
      return startsWith($x, $filter);
    });
    asort($param_values);

    return array_map(function ($x) {
      return '-' . $x;
    }, $param_values);
    //          ForEach-Object { -join ("-", $_) }
  }

  private function expandParamValues($cmd, $param, $filter) {
    $param_values = explode(' ', ParamTabExpansion::$gitParamValues[$cmd][$param]);
    $param_values = array_filter($param_values, function ($x) use ($filter) {
      return startsWith($x, $filter);
    });
    asort($param_values);

    return array_map(function ($val) use ($param) {
      return "--$param=$val";
    }, $param_values);
  }

  //function Expand-GitCommand($Command) {
  //$res = Invoke-Utf8ConsoleCommand { GitTabExpansionInternal $Command $Global:GitStatus }
  //    $res
  //}

  public function expand($input, $status = NULL) {
    $ignoreGitParams = "(?<params>\s+-(?:[aA-zZ0-9]+|-[aA-zZ0-9][aA-zZ0-9-]*)(?:=\S+)?)*";

    if (preg_match("/^git (?<cmd>\S+)(?<args> .*)$/", $input, $matches)) {
      $input = $this->expandGitAlias($matches['cmd'], $matches['args']);
    }
    //    # Handles tgit <command> (tortoisegit)
    //    if ($lastBlock -match "^$(Get-AliasPattern tgit) (?<cmd>\S*)$") {
    //    # Need return statement to prevent fall-through.
    //    return $Global:TortoiseGitSettings.TortoiseGitCommands.Keys.GetEnumerator() | Sort-Object | Where-Object { $_ -like "$($matches['cmd'])*" }
    //    }

    // Handles gitk
    if (preg_match("/^gitk.* (?<ref>\S*)$/", $input, $matches)) {
      return $this->branches($matches['ref'], TRUE);
    }

    if (startsWith($input, 'git ')) {
      $input = substr($input, 4);
    }
    else {
      return [];
    }

    //    switch -regex ($lastBlock -replace "^$(Get-AliasPattern git) ","") {

    # Handles git <cmd> <op>
    $subcommands = implode('|', array_keys($this->subComands));
    if (preg_match("/^(?<cmd>$subcommands)\s+(?<op>\S*)$/", $input, $matches)) {
      return $this->cmdOperations($this->subComands, $matches['cmd'], $matches['op']);
    }

    //        # Handles git flow <cmd> <op>
    //        "^flow (?<cmd>$($gitflowsubcommands.Keys -join '|'))\s+(?<op>\S*)$" {
    //      gitCmdOperations $gitflowsubcommands $matches['cmd'] $matches['op']
    //        }

    # Handles git flow <command> <op> <name>
    //        "^flow (?<command>\S*)\s+(?<op>\S*)\s+(?<name>\S*)$" {
    //      gitFeatures $matches['name'] $matches['command']
    //        }

    # Handles git remote (rename|rm|set-head|set-branches|set-url|show|prune) <stash>
    if (preg_match("/^remote.* (?:rename|rm|set-head|set-branches|set-url|show|prune).* (?<remote>\S*)$/", $input, $matches)) {
      return $this->remotes($matches['remote']);
    }

    # Handles git stash (show|apply|drop|pop|branch) <stash>
    if (preg_match("/^stash (?:show|apply|drop|pop|branch).* (?<stash>\S*)$/", $input, $matches)) {
      return $this->stashes($matches['stash']);
    }

    # Handles git bisect (bad|good|reset|skip) <ref>
    if (preg_match("/^bisect (?:bad|good|reset|skip).* (?<ref>\S*)$/", $input, $matches)) {
      return $this->branches($matches['ref'], TRUE);
    }

    # Handles git tfs unshelve <shelveset>
    //        "^tfs +unshelve.* (?<shelveset>\S*)$" {
    //      gitTfsShelvesets $matches['shelveset']
    //        }

    # Handles git branch -d|-D|-m|-M <branch name>
    # Handles git branch <branch name> <start-point>
    if (preg_match("/^branch.* (?<branch>\S*)$/", $input, $matches)) {
      return $this->branches($matches['branch']);
    }

    # Handles git <cmd> (commands & aliases)
    if (preg_match("/^(?<cmd>\S*)$/", $input, $matches)) {
      return $this->commands($matches['cmd'], TRUE);
    }

    # Handles git help <cmd> (commands only)
    if (preg_match("/^help (?<cmd>\S*)$/", $input, $matches)) {
      return $this->commands($matches['cmd'], FALSE);
    }

    # Handles git push remote <ref>:<branch>
    # Handles git push remote +<ref>:<branch>
    if (preg_match("/^push{$ignoreGitParams}\s+(?<remote>[^\s-]\S*).*\s+(?<force>\+?)(?<ref>[^\s\:]*\:)(?<branch>\S*)$/", $input, $matches)) {
      return $this->remoteBranches($matches['remote'], $matches['ref'], $matches['branch'], $matches['force']); /* -prefix $matches['force'] */
    }

    # Handles git push remote <ref>
    # Handles git push remote +<ref>
    # Handles git pull remote <ref>
    if (preg_match("/^(?:push|pull){$ignoreGitParams}\s+(?<remote>[^\s-]\S*).*\s+(?<force>\+?)(?<ref>[^\s\:]*)$/", $input, $matches)) {
      return $this->branches($matches['ref'], FALSE, $matches['force'])
        + $this->tags($matches['ref'], $matches['force']);
    }

    # Handles git pull <remote>
    # Handles git push <remote>
    # Handles git fetch <remote>
    if (preg_match("/^(?:push|pull|fetch)${ignoreGitParams}\s+(?<remote>\S*)$/", $input, $matches)) {
      return $this->remotes($matches['remote']);
    }

    # Handles git reset HEAD <path>
    # Handles git reset HEAD -- <path>
    if (preg_match("/^reset.* HEAD(?:\s+--)? (?<path>\S*)$/", $input, $matches)) {
      return $this->index($status, $matches['path']);
    }

    # Handles git <cmd> <ref>
    if (preg_match("/^commit.*-C\s+(?<ref>\S*)$/", $input, $matches)) {
      return $this->branches($matches['ref'], TRUE);
    }

    # Handles git add <path>
    if (preg_match("/^add.* (?<files>\S*)$/", $input, $matches)) {
      return $this->addFiles($status, $matches['files']);
    }

    # Handles git checkout -- <path>
    if (preg_match("/^checkout.* -- (?<files>\S*)$/", $input, $matches)) {
      return $this->checkoutFiles($status, $matches['files']);
    }

    # Handles git rm <path>
    if (preg_match("/^rm.* (?<index>\S*)$/", $input, $matches)) {
      return $this->deleted($status, $matches['index']);
    }

    # Handles git diff/difftool <path>
    if (preg_match("/^(?:diff|difftool)(?:.* (?<staged>(?:--cached|--staged))|.*) (?<files>\S*)$/", $input, $matches)) {
      return $this->diffFiles($status, $matches['files'], $matches['staged']);
    }

    # Handles git merge/mergetool <path>
    if (preg_match("/^(?:merge|mergetool).* (?<files>\S*)$/", $input, $matches)) {
      return $this->mergeFiles($status, $matches['files']);
    }

    # Handles git checkout <ref>
    if (preg_match("/^(?:checkout).* (?<ref>\S*)$/", $input, $matches)) {
      $result = $this->branches($matches['ref'], TRUE);
      $result += $this->remoteUniqueBranches($matches['ref']);
      $result += $this->tags($matches['ref']);
      // Return only unique branches (to eliminate duplicates where the branch exists locally and on the remote)
      return array_unique($result);
    }

    # Handles git worktree add <path> <ref>
    if (preg_match("/^worktree add.* (?<files>\S+) (?<ref>\S*)$/", $input, $matches)) {
      return $this->branches($matches['ref']);
    }

    # Handles git <cmd> <ref>
    if (preg_match("/^(?:cherry|cherry-pick|diff|difftool|log|merge|rebase|reflog\s+show|reset|revert|show).* (?<ref>\S*)$/", $input, $matches)) {
      return $this->branches($matches['ref'], TRUE)
        + $this->tags($matches['ref']);
    }

    # Handles git <cmd> --<param>=<value>
    $p = $this->gitCommandsWithParamValues;
    if (preg_match("/^(?<cmd>$p).* --(?<param>[^=]+)=(?<value>\S*)$/", $input, $matches)) {
      return $this->expandParamValues($matches['cmd'], $matches['param'], $matches['value']);
    }

    # Handles git <cmd> --<param>
    $p = $this->gitCommandsWithLongParams;
    if (preg_match("/^(?<cmd>$p).* --(?<param>\S*)$/", $input, $matches)) {
      return $this->expandLongParams(ParamTabExpansion::$longGitParams, $matches['cmd'], $matches['param']);
    }

    # Handles git <cmd> -<shortparam>
    $p = $this->gitCommandsWithShortParams;
    if (preg_match("/^(?<cmd>$p).* -(?<shortparam>\S*)$/", $input, $matches)) {
      return $this->expandShortParams(ParamTabExpansion::$shortGitParams, $matches['cmd'], $matches['shortparam']);
    }

    return [];

    # Handles git pr alias
    //        "vsts\.pr\s+(?<op>\S*)$" {
    //      gitCmdOperations $subcommands 'vsts.pr' $matches['op']
    //        }

    # Handles git pr <cmd> --<param>
    //        "vsts\.pr\s+(?<cmd>$vstsCommandsWithLongParams).*--(?<param>\S*)$"
    //        {
    //          expandLongParams $longVstsParams $matches['cmd'] $matches['param']
    //        }

    # Handles git pr <cmd> -<shortparam>
    //        "vsts\.pr\s+(?<cmd>$vstsCommandsWithShortParams).*-(?<shortparam>\S*)$"
    //        {
    //          expandShortParams $shortVstsParams $matches['cmd'] $matches['shortparam']
    //        }

  }

}

