#!/bin/bash
FILES=$(grep -IriEoh "\/(src|vendor)\/.*\.(js|css|map|png|jpg|jpeg|gif|html)" src/ | uniq)

for FILE in $FILES
do
   DIRNAME=$(dirname "$FILE")
   mkdir -p public_html/asset"$DIRNAME"
   cp ."$FILE" public_html/asset"$FILE"
done
