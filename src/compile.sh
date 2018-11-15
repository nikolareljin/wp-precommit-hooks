#!/bin/bash

source ./.git/hooks/include.sh

print_important "Compiling the dependencies"

changedFiles="$(git diff-tree -r --name-only --no-commit-id HEAD)"

runOnChange() {
	print_important "$changedFiles" | grep -q "$1" && eval "$2"
}

runOnChange package.json "npm install && npm run build"
runOnChange composer.json "composer install && composer dump-autoload --optimize"

runOnChange package-lock.json "npm install && npm run build"
runOnChange composer.lock "composer install && composer dump-autoload --optimize"


print_success "Compile process completed."
