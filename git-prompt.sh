git-prompt () {
    local prompt=`php "${DIR}/phpish-git/git-prompt.php"`
    export PS1=$prompt
}