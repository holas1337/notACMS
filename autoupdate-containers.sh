#!/bin/bash

DIR="$(dirname "$(readlink -f "$0")")"
cd ${DIR}

export $(grep -e '^AUTO_UPDATE' .env | xargs -0)

if [[ "${AUTO_UPDATE}" -ne 1 ]]; then
    echo "Skipping containers autoupdate!"
    exit 0
fi

echo "Updating containers"
./notACMS deploy --prod
