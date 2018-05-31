<?php

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
}