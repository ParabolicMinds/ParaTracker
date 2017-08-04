for f in ./*.jpg; do convert $f -resize 300x225 $f; done;
