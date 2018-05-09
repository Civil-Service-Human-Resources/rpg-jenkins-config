#!/bin/bash

# Create the container definition
DOCKER_TAG=${1}
DEPLOY_ENV=${2}
auth_username=${3}
auth_password=${4}
ui_auth_username=${5}
ui_auth_password=${6}

CONTAINER_DEFINITION=$(cat <<EOF

    {
      "name": "cshr-ui",
      "image": "cshrrpg.azurecr.io/rpg-candidate-interface-ui:${DOCKER_TAG}",
      "memory": 256,
      "essential": true,
      "hostname": "ui",
      "portMappings": [
          {
              "containerPort": 3000,
              "hostPort": 81
          }
      ],
      "environment": [
        {
          "name": "API_URL",
          "value": "https://candidate-interface-api.${DEPLOY_ENV}-internal.cshr-gov.uk"
        },
        {
          "name": "AUTH_USERNAME",
          "value": "${auth_username}"
        },
        {
          "name": "AUTH_PASSWORD",
          "value": "${auth_password}"
        },
        {
          "name": "UI_AUTH_USERNAME",
          "value": "${ui_auth_username}"
        },
        {
          "name": "UI_AUTH_PASSWORD",
          "value": "${ui_auth_password}"
        }
      ]
    }
EOF
)

aws ecs register-task-definition --family ${DEPLOY_ENV}-candidate-interface-ui --container-definitions "${CONTAINER_DEFINITION}"
