DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

git-prompt () {
    local ps1pc_prefix= 
    local ps1pc_suffix=
    local D=$'\e[37;0m'
    local PINK=$'\e[35;40m'
    local GREEN=$'\e[32;40m'
    local ORANGE=$'\e[33;40m'
    local BLUE=$'\e[34;40m'
    local CYAN=$'\e[36;40m'
    local RED=$'\e[0;31m'

    case "$#" in
        2)
            ps1pc_prefix=$1
            ps1pc_suffix=$2
            ;;
        *)
            #ps1pc_prefix="\[\033]0;\w\007\]\n${CYAN}\u${D}@${ORANGE}\h${D} ${GREEN}\W${D}"  
            ps1pc_prefix=
            ps1pc_suffix="$ ";
            ;;
    esac

    local gitstring=`php "${DIR}/git-prompt.php"`
    PS1=$ps1pc_prefix$gitstring$ps1pc_suffix
}