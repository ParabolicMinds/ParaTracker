#!/bin/bash

input_dir="./input"
pt_output_dir="./paratracker_output"
src_output_dir="./source_output"

if [[ ! -d "$input_dir" ]]
then
    echo "$input_dir does not exist! Cannot continue!"
    exit 1
fi

mkdir -p "$pt_output_dir"
mkdir -p "$src_output_dir"

while read img_input
do
	# copy lowercased source
	src_output="$src_output_dir/$(echo ${img_input#$input_dir} | tr '[:upper:]' '[:lower:]')"
	mkdir -p "$(dirname "$src_output")"
	cp "$img_input" "$src_output"

	# generate ParaTracker image
	pt_output="$pt_output_dir/$(echo ${img_input#$input_dir} | tr '[:upper:]' '[:lower:]')"
	pt_output=${pt_output%.*}.png
	mkdir -p "$(dirname "$pt_output")"
	convert -resize 300x225^ -gravity center -crop 300x225+0+0 "$img_input" "$pt_output";
	optipng -quiet "$pt_output"
	
	# done
	echo "Finished: $pt_output"
done <<< "$(find $input_dir -type f)"
