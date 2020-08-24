#!/bin/bash

git fetch origin
git checkout master
git pull

composer install -n -d $PWD/../api.cr0ss.org
