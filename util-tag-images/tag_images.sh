#!/bin/bash
##
# Pull the application Docker images with a particular tag.
# Tag them, then push them.
##
# Docker login prior to running this!
set -eo pipefail

export DOCKER_REPO=cshrrpg.azurecr.io

export APPLICATION_IMAGES=(
    ${DOCKER_REPO}/location-service:latest
    ${DOCKER_REPO}/cshr-api:develop
    ${DOCKER_REPO}/cshr-ats-adaptor:develop
    ${DOCKER_REPO}/rpg-candidate-interface-ui:develop
)

if [[ -z ${1} ]]; then
    echo "Usage: ${0} <tag to apply>"
    exit 1
fi

for image in "${APPLICATION_IMAGES[@]}"
do
    echo "Pulling image: ${image}"
    docker pull ${image}
    
    image_base=$(echo ${image} | cut -d ':' -f 1)
    echo "Image base is: ${image_base}"

    new_image=${image_base}:${1}
    echo "New tag is: ${new_image}... tagging"
    docker tag ${image} ${new_image}

    echo "Pushing... ${new_image}"
    docker push ${new_image}

done
