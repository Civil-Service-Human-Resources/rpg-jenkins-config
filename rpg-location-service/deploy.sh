#!/bin/bash

# Create the container definition
DOCKER_TAG=${1}
DEPLOY_ENV=${2}

CONTAINER_DEFINITION=$(cat <<EOF
{
        "containerDefinitions": [
          {
            "name": "location-service",
            "image": "cshr-docker-rpg.bintray.io/rpg/location-service:${DOCKER_TAG}",
            "memory": 256,
            "essential": true,
            "hostname": "location-service",
            "portMappings": [
                {
                    "containerPort": 8080,
                    "hostPort": 82
                }
            ]
          }
        ],
        "family": "${DEPLOY_ENV}-location-service"
}
EOF
)

echo ${CONTAINER_DEFINITION}

aws ecs register-task-definition --family ${DEPLOY_ENV}-location-service --container-definitions ${CONTAINER_DEFINITION}
