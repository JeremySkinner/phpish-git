<?php

require 'param-tab-expansion.php';

class TabExpansion {
  protected $sub_commands = [
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

  protected $some_commands = [
    'add','am','annotate','archive','bisect','blame','branch','bundle','checkout','cherry',
    'cherry-pick','citool','clean','clone','commit','config','describe','diff','difftool','fetch',
    'format-patch','gc','grep','gui','help','init','instaweb','log','merge','mergetool','mv',
    'notes','prune','pull','push','rebase','reflog','remote','rerere','reset','revert','rm',
    'shortlog','show','stash','status','submodule','svn','tag','whatchanged', 'worktree'
  ];

  protected $gitCOmmandsWithLongParams;
  protected $gitCommandsWithShortParams;
  protected $gitCommandsWithParamValues;

  protected $settings;

  public function __construct(TabSettings $settings) {
    $this->settings = $settings;
    $this->gitCommandsWithLongParams = implode('|', array_keys(ParamTabExpansion::longGitParams));
    $this->gitCommandsWithShortParams = implode('|', array_keys(ParamTabExpansion::shortGitParams));
    $this->gitCommandsWithParamValues = implode('|', array_keys(ParamTabExpansion::gitParamValues));
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

  private function quoteStringWithSpecialChars($input) {
    if ($input && preg_match("/\s+|#|@|\$|;|,|''|\{|\}|\(|\)/")) {
      $input = str_replace("'", "''");
      return "'$input'";
    }
    return $input;
  }

  public function commands($filter, $includeAliases) {
    $cmdList = [];

    if (!$this->settings->allCommands) {
        $cmdList += array_filter($this->someCommands, function($x) { return startsWith($filter, $x); });
    }
    else {
      $output = execGit('help --all', $rtn, true);
      $output = array_filter($output, function($x) { return preg_match("/^  \S.*/", $x); });
      foreach($output as $cmd) {
        foreach(explode(' ', $cmd) as $cmd2) {
          if(startsWith($filter, $cmd2)) {
            $cmdList[] = $cmd2;
          }
        }
      }
    }

    if ($includeAliases) {
        $cmdList += $this->aliases($filter);
    }

    return asort($cmdList);
  }

  protected function remotes($filter) {
    $remotes = [];
    $output = execGit('remote', $rtn, true);
    foreach($output as $remote) {
      if(startsWith($filter, $remote)) {
        $remotes[] = $this->quoteStringWithSpecialChars($remote);
      }
    }
  }

  protected function branches($filter, $includeHEAD=false, $prefix = '') {
    
  }

}

function startsWith($needle, $haystack) {
  return substr($haystack, 0, strlen($needle)) === $needle;
}