#!/bin/bash

# Create the container definition
DOCKER_TAG=${1}
DEPLOY_ENV=${2}
datasource_username=${3}
datasource_password=${4}
location_service_username=${5}
location_service_password=${6}
search_username=${7}
search_password=${8}
crud_username=${9}
crud_password=${10}


if [[ "${DEPLOY_ENV}" == "dev" ]]; then
  spring_profiles_active=dev
else
  spring_profiles_active=prod
fi


CONTAINER_DEFINITION=$(cat <<EOF
  {
    "name": "cshr-api",
    "image": "cshrrpg.azurecr.io/cshr-api:${DOCKER_TAG}",
    "memory": 512,
    "essential": true,
    "hostname": "api",
    "portMappings": [
        {
            "containerPort": 8080,
            "hostPort": 8080
        }
    ],
    "environment": [
      {
        "name": "LOCATION_SERVICE_URL",
        "value": "https://location-service.${DEPLOY_ENV}-internal.cshr-gov.uk/findlocation/{searchTerm}"
      },
      {
        "name": "DATASOURCE_URL",
        "value": "jdbc:postgresql://candidate-interface-db.${DEPLOY_ENV}.cshr-gov.uk:5432/cshr"
      },
      {
        "name": "DATASOURCE_USERNAME",
        "value": "${datasource_username}"
      },
      {
        "name": "DATASOURCE_PASSWORD",
        "value": "${datasource_password}"
      },
      {
        "name" : "SPRING_PROFILES_ACTIVE",
        "value": "${spring_profiles_active}"
      },
      {
        "name":"LOCATION_SERVICE_USERNAME",
        "value":"${location_service_username}"
      },
      {
         "name":"LOCATION_SERVICE_PASSWORD",
         "value":"${location_service_password}"
      },
      {
         "name":"SEARCH_USERNAME",
         "value":"${search_username}"
      },
      {
         "name":"SEARCH_PASSWORD",
         "value":"${search_password}"
      },
      {
         "name":"CRUD_USERNAME",
         "value":"${crud_username}"
      },
      {
         "name":"CRUD_PASSWORD",
         "value":"${crud_password}"
      }

    ]
  }

EOF
)

aws ecs register-task-definition --family ${DEPLOY_ENV}-candidate-interface-api --container-definitions "${CONTAINER_DEFINITION}"
