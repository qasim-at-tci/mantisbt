#!/bin/bash
GIT_ROOT=$(git rev-parse --show-toplevel)
TARGET="$GIT_ROOT/doc"

case ${1:0:1} in
  "d")
    GUIDE=Developers_Guide
    TARGET="$TARGET/developers"
    mkdir -p $TARGET
    ;;
  "a"|*)
    GUIDE=Admin_Guide
    TARGET="$TARGET/admin"
    UNUSED="Author_Group.adoc Book_Info.adoc"
    mkdir -p "$TARGET/config"
    ;;
esac

echo "Converting $GUIDE from DocBook to AsciiDoc"

SOURCE="$GIT_ROOT/docbook/$GUIDE/en-US"
cd $SOURCE || exit 1

echo "Running Pandoc on DocBook files in $SOURCE"

find -name "*.xml" | cut -c3- |
while read filename
do
  if [[ "$filename" == 'Admin_Guide.xml' ]];
  then
    echo "Skipping $filename"
    continue
  fi
  pandoc "$filename" -f docbook -t asciidoc -s --atx-header -o "$TARGET/${filename%.xml}.adoc"
done

if [[ -v UNUSED ]]
then
  echo "Deleting unused files: $UNUSED"
  pushd "$TARGET" >/dev/null
  rm $UNUSED
  popd >/dev/null
fi

echo "Converted AsciiDoc files saved in $TARGET"
