#!/bin/bash 
##
# Simple script to check the status of careers-site website.
# Parameters are: <selected_environment>

if [[ ${#} -lt 1 ]]; then
    echo "Usage ./checkEnv.sh <selected env - test | demo>"
    exit 1
fi

counter=0
selected_environment=${1}
while [[  ${counter} -lt 10 ]]; do
    return_code=$(curl --write-out '%{http_code}' --silent --output /dev/null "https://careers-site.${selected_environment}.cshr-gov.uk")
    if [[ ${return_code} -eq 200 ]]; then
        echo "Site is up"
        break
    fi
    echo "Waiting for site to start"
    sleep 30s
    if [[ ${counter} -eq 9 ]]; then
        echo "The site did not come up after ${counter} retries - exiting."
        exit 1
    fi
    let counter=counter+1 
done