# phpish-git
This is a port of [posh-git](https://github.com/dahlbyk/posh-git) to PHP. It includes tab expansion and a custom prompt that shows the status of git repositories.

<img width="1004" alt="screen shot 2018-05-31 at 18 10 39" src="https://user-images.githubusercontent.com/90130/40796788-1df3980e-64fe-11e8-8aca-cb5ba83c40ef.png"> 

# Why did you make this?

I wanted the same functionality provided by posh-git when connected to Linux servers over SSH. Although git itself comes with bash scripts that provide prompt and tab expansion, they are not as powerful or feature-complete as posh-git. 

...and it was for fun!

# Why PHP and not Ruby/Python/Javascript/Some other language?

My current job is working with Drupal and PHP after doing 10 years with C#/.net. This seemed like a fun way to get back into writing PHP after not having done so for a long time. 

# How do I install it?

Checkout the repository and then add the following to your .bashrc:

```
source "path/to/phpish-git/git-prompt.sh"
source "path/to/phpish-git/git-tab-expansion.sh"
PROMPT_COMMAND='git-prompt'
```

# What do the symbols mean?

(This section of the documentation is taken from posh-git)

The Git status summary information provides a wealth of "Git status" information at a glance, all the time in your prompt.

By default, the status summary has the following format:

    [{HEAD-name} S +A ~B -C !D | +E ~F -G !H W]

- `[` (`BeforeStatus`)
- `{HEAD-name}` is the current branch, or the SHA of a detached HEAD
  - Cyan means the branch matches its remote
  - Green means the branch is ahead of its remote (green light to push)
  - Red means the branch is behind its remote
  - Yellow means the branch is both ahead of and behind its remote
- `S` represents the branch status in relation to remote (tracked origin) branch. Note: This information reflects
  the state of the remote tracked branch after the last `git fetch/pull` of the remote.
  - `≡` = The local branch in at the same commit level as the remote branch (`BranchIdenticalStatus`)
  - `↑<num>` = The local branch is ahead of the remote branch by the specified number of commits; a `git push` is
    required to update the remote branch (`BranchAheadStatus`)
  - `↓<num>` = The local branch is behind the remote branch by the specified number of commits; a `git pull` is
    required to update the local branch (`BranchBehindStatus`)
  - `<a>↕<b>` = The local branch is both ahead of the remote branch by the specified number of commits (a) and behind
    by the specified number of commits (b); a rebase of the local branch is required before pushing local changes to
    the remote branch (`BranchBehindAndAheadStatus`).  NOTE: this status is only available if
    `$GitPromptSettings.BranchBehindAndAheadDisplay` is set to `Compact`.
  - `×` = The local branch is tracking a branch that is gone from the remote (`BranchGoneStatus`)
- `ABCD` represent the index; `|` (`DelimStatus`); `EFGH` represent the working directory
  - `+` = Added files
  - `~` = Modified files
  - `-` = Removed files
  - `!` = Conflicted files
  - As with `git status` output, index status is displayed in dark green and working directory status in dark red

- `W` represents the overall status of the working directory
  - `!` = There are unstaged changes in the working tree (`LocalWorkingStatusSymbol`)
  - `~` = There are uncommitted changes i.e. staged changes in the working tree waiting to be committed (`LocalStagedStatusSymbol`)
  - None = There are no unstaged or uncommitted changes to the working tree (`LocalDefaultStatusSymbol`)
- `]` (`AfterStatus`)

For example, a status of `[master ≡ +0 ~2 -1 | +1 ~1 -0]` corresponds to the following `git status`:

```bash
# On branch master
#
# Changes to be committed:
#   (use "git reset HEAD <file>..." to unstage)
#
#        modified:   this-changed.txt
#        modified:   this-too.txt
#        deleted:    gone.ps1
#
# Changed but not updated:
#   (use "git add <file>..." to update what will be committed)
#   (use "git checkout -- <file>..." to discard changes in working directory)
#
#        modified:   not-staged.ps1
#
# Untracked files:
#   (use "git add <file>..." to include in what will be committed)
#
#        new.file
```

# How do I customize it?

Layout and colours of the prompt can be configured using environment variables. For example, I like to use a verbose multi-lined prompt:

<img width="1022" alt="screen shot 2018-05-31 at 18 14 46" src="https://user-images.githubusercontent.com/90130/40796931-84da0a26-64fe-11e8-9953-9e6f9f51fc00.png">

This layout is achieved done by setting the following environment variables in my .bashrc:

```
export GIT_PROMPT_ShowStatusWhenZero=0
export GIT_PROMPT_BeforeStatus="on "
export GIT_PROMPT_AfterStatus=""
export GIT_PROMPT_AfterStatusColor="Yellow"
export GIT_PROMPT_DelimStatus=" working"
export GIT_PROMPT_BeforeIndex=" index"
export GIT_PROMPT_BeforeIndexColor="DarkGray"
export GIT_PROMPT_DelimStatusColor="DarkGray"
export GIT_PROMPT_IndexColor="Yellow"
export GIT_PROMPT_DefaultPromptSuffix="
$ "
export GIT_PROMPT_DefaultPromptPrefix="\[\033]0;\w\007\]\n{Cyan}\u{Reset} at {Yellow}\h {Reset}in {Green}\W{Reset}"
```

The complete list of environment variables [can be found here](https://github.com/JeremySkinner/phpish-git/wiki/Environment-Variables)

# Complete list of colour codes:

TODO
