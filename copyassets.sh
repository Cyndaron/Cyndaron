#!/bin/bash
FILES=$(grep -IriEoh "\/(src|vendor)\/.*\.(js|css|map|png|jpg|jpeg|gif|html)" src/ vendor/cyndaron/ | uniq)

rm -Rf public_html/asset/*

for FILE in $FILES
do
   DIRNAME=$(dirname "$FILE")
   mkdir -p public_html/asset"$DIRNAME"
   cp ."$FILE" public_html/asset"$FILE"
done

mkdir -p public_html/asset/vendor/ckeditor
cp -R vendor/ckeditor/ckeditor public_html/asset/vendor/ckeditor
