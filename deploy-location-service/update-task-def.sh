#!/bin/bash

# Create the container definition
# TODO:
# Remove API key from script

DOCKER_TAG=${1}
DEPLOY_ENV=${2}
LOCATION_SERVICE_GOOGLE_SERVICE_API_KEY=${3}
LOCATION_SERVICE_USERNAME=${4}
LOCATION_SERVICE_PASSWORD=${5}
FILEBEAT_HOSTS=${6}

CONTAINER_DEFINITION=$(cat <<EOF
{
   "name":"location-service",
   "image":"cshrrpg.azurecr.io/location-service:${DOCKER_TAG}",
   "memory":768,
   "essential":true,
   "hostname":"location-service",
   "portMappings":[
      {
         "containerPort":8989,
         "hostPort":82
      }
   ],
   "environment":[
      {
         "name":"LOCATION_SERVICE_GOOGLE_SERVICE_API_KEY",
         "value":"${LOCATION_SERVICE_GOOGLE_SERVICE_API_KEY}"
      },
      {
         "name":"LOCATION_SERVICE_USERNAME",
         "value":"${LOCATION_SERVICE_USERNAME}"
      },
      {
         "name":"LOCATION_SERVICE_PASSWORD",
         "value":"${LOCATION_SERVICE_PASSWORD}"
      },
      {
          "name": "FILEBEAT_PLATFORM",
          "value": "aws"
      },
      {
          "name": "FILEBEAT_ENVIRONMENT",
          "value": "${DEPLOY_ENV}"
      },
      {
          "name": "FILEBEAT_COMPONENT",
          "value": "location-service"
      },
      {
          "name": "FILEBEAT_HOSTS",
          "value": "${FILEBEAT_HOSTS}"
      }
   ]
}
EOF
)

aws ecs register-task-definition --family ${DEPLOY_ENV}-location-service --container-definitions "${CONTAINER_DEFINITION}"
