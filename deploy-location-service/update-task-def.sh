#!/bin/bash

# Create the container definition
# TODO:
# Remove API key from script

DOCKER_TAG=${1}
DEPLOY_ENV=${2}

CONTAINER_DEFINITION=$(cat <<EOF

          {
            "name": "location-service",
            "image": "cshr-docker-rpg.bintray.io/location-service:${DOCKER_TAG}",
            "memory": 256,
            "essential": true,
            "hostname": "location-service",
            "portMappings": [
                {
                    "containerPort": 8080,
                    "hostPort": 82
                }
            ],
            "environment": [
              {
                "name": "LOCATION_SERVICE_GOOGLE_SERVICE_API_KEY",
                "value": "AIzaSyDw51wA5CgK-bf0eIyPY_-e8qMh5fu-Vsc"
              }

          }
EOF
)

aws ecs register-task-definition --family ${DEPLOY_ENV}-location-service --container-definitions "${CONTAINER_DEFINITION}"
