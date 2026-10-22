#! /bin/bash

# file needed copy
exists_file=(
"api/index-dev.php"
"api/config/main.php"
"api/config/params.php"
"backend/index-dev.php"
"backend/config/main.php"
"backend/config/params.php"
"common/config/main.php"
"common/config/params.php"
"console/config/main.php"
"console/config/params.php"
"h5/index-dev.php"
"h5/config/main.php"
"h5/config/params.php"
"yii-local"
)

# tag file
tag_file=(
"api/index.php"
"api/config/main-local.php"
"api/config/params-local.php"
"backend/index.php"
"backend/config/main-local.php"
"backend/config/params-local.php"
"common/config/main-local.php"
"common/config/params-local.php"
"console/config/main-local.php"
"console/config/params-local.php"
"h5/index.php"
"h5/config/main-local.php"
"h5/config/params-local.php"
"yii"
)

# copy file if no exists
for (( i = 0; i < ${#tag_file[*]}; i++ )); do
    if [[ ! -f ${tag_file[$i]} ]]; then
        echo "copy file ${exists_file[i]} to ${tag_file[i]}"
        cp ${exists_file[i]} ${tag_file[i]}
    fi
done

# chmod directory to 777
directory=(
"backend/assets"
"backend/runtime"
"api/runtime"
"h5/runtime"
"console/runtime"
)

for (( i = 0; i < ${#directory[*]}; i++ )); do
    chmod 777 ${directory[i]} -R
done