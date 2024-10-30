#!/bin/bash

mkdir -p ./public/build/static/icons
cp -rf ./node_modules/huisstijl2016/static/icons/* ./public/build/static/icons

mkdir -p ./public/build/static/images
cp -rf ./node_modules/huisstijl2016/static/images/* ./public/build/static/images
cp -rf ./assets/images/* ./public/build/static/icons

