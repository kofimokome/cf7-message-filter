#!/bin/sh

rm cf7-message-filter.zip
mkdir cf7-message-filter

excludes=("cf7-message-filter" "deploy.sh" "node_modules" "tests" "bin" "get-translation-strings.js" "package.json" "package-lock.json" "sonar-project.properties" "sonar-project.example.properties")

this_dir=$(pwd)
for entry in $this_dir/*; do
  entry_name=${entry/$this_dir\//''}
  if [[ ! " ${excludes[*]} " =~ " ${entry_name} " ]]; then
    echo including $entry
    cp -R $entry cf7-message-filter/$entry_name
    # whatever you want to do when array doesn't contain value
  fi
done

zip -r cf7-message-filter.zip cf7-message-filter
rm -r cf7-message-filter
