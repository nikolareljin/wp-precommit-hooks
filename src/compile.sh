#!/bin/bash

source ./.git/hooks/include.sh

print_important "Compiling the dependencies"

changedFiles="$(git diff-tree -r --name-only --no-commit-id HEAD)"

runOnChange() {
	echo "$changedFiles" | grep -q "$1" && eval "$2"
}

runOnChange package-lock.json "npm install"
runOnChange composer.lock "composer install"

print_success "Compile process completed."
