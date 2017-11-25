#!/bin/bash

# tracks the exit status
EXIT=0

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

cd ${DIR}
cd ../
echo "Current directory: $PWD"

declare -a laravel_versions=("5.1" "5.2" "5.3" "5.4" "5.5")

## now loop through the above array
for laravel_version in "${laravel_versions[@]}"
do
   echo "Testing (with laravel version $laravel_version)"

   echo "Installing dependencies"
   rm -f composer-test.*
   rm -rf vendor/*
   cp composer.json composer-test.json
   COMPOSER=composer-test.json /usr/bin/env composer require "laravel/framework:~$laravel_version.0" -q

   echo "Installing phpunit as recommended by orchestra/testbench"
   PHPUNIT_VERSION=$(grep -m1 "phpunit/phpunit" vendor/orchestra/testbench/composer.json | awk -F: '{ print $2 }' | sed 's/[",]//g')
   COMPOSER=composer-test.json /usr/bin/env composer require "phpunit/phpunit" "$PHPUNIT_VERSION" -q

   echo "Running phpunit"
   ./vendor/bin/phpunit --verbose || EXIT=$?

   if [[ ${EXIT} != 0 ]]; then
     echo "Oops, looks like something went wrong! ¯\_(ツ)_/¯"
     exit ${EXIT}
   fi
done

# To see the exit status of a command, run it and then afterwards run #> echo $?
# echo $?
echo

if [[ ${EXIT} == 0 ]]; then
    echo "Yay, green all the things"
fi

echo

exit ${EXIT}