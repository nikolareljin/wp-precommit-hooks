#!/bin/bash

# -----------------------------

color_end="\033[0m"
color_black="\033[0;30m"
color_blackb="\033[1;30m"
color_white="\033[0;37m"
color_whiteb="\033[1;37m"
color_red="\033[0;31m"
color_redb="\033[1;31m"
color_green="\033[0;32m"
color_greenb="\033[1;32m"
color_yellow="\033[0;33m"
color_yellowb="\033[1;33m"
color_blue="\033[0;34m"
color_blueb="\033[1;34m"
color_purple="\033[0;35m"
color_purpleb="\033[1;35m"
color_lightblue="\033[0;36m"
color_lightblueb="\033[1;36m"


print(){
    text=$1
    color=$2
    echo -e "${color}${text}${color_end}"
}

print_error(){
    text=$1
    print "${text}" $color_red
    print "[Error]" $color_redb
}

print_success(){
    text=$1
    print "${text}" $color_green
    print "[OK]" $color_greenb
}

print_important(){
    text=$1
    print "${text}" $color_yellow
}

