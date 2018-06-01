# phpish-git
This is a port of [posh-git](https://github.com/dahlbyk/posh-git) to PHP. It includes tab expansion and a custom prompt that shows the status of git repositories.

<img width="1004" alt="screen shot 2018-05-31 at 18 10 39" src="https://user-images.githubusercontent.com/90130/40796788-1df3980e-64fe-11e8-8aca-cb5ba83c40ef.png"> 

# Why did you make this?

I wanted the same functionality provided by posh-git when connected to Linux servers over SSH. Although git itself comes with bash scripts that provide prompt and tab expansion, they are not as powerful or feature-complete as posh-git. 

...and it was for fun!

# Why PHP and not Ruby/Python/Javascript/Some other language?

My current job is working with Drupal and PHP, after doing 10 years with C#/.net. This seemed like a fun way to get back into writing PHP after ot having done so for a long time. 

# How do I install it?

Checkout the repository and then add the following to your .bashrc:

```
source "path/to/phpish-git/git-prompt.sh"
source "path/to/phpish-git/git-tab-expansion.sh"
PROMPT_COMMAND='git-prompt'
```

# How do I configure it?

Layout and colours of the prompt can be configured using environment variables. For example, I like to use a verbose multi-lined prompt:

<img width="1004" alt="screen shot 2018-05-31 at 18 10 39" src="https://user-images.githubusercontent.com/90130/40796788-1df3980e-64fe-11e8-8aca-cb5ba83c40ef.png"> 

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

The complete list of environment variables is displayed below:

TODO

# Complete list of colour codes:

TODO