git-prompt () {
    local my_dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
    local prompt=`php "${my_dir}/git-prompt.php"`
    export PS1=$prompt
}