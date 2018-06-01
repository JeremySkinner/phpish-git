_git_completion() {
    #local cur=${COMP_WORDS[COMP_CWORD]}
    local cur="${COMP_WORDS[@]}"
    local my_dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

    COMPREPLY=( $(php "${my_dir}/git-tab-expansion.php" "$cur" ) )

}

complete -F _git_completion git
complete -F _git_completion gitk