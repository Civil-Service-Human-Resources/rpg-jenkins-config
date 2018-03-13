#!/bin/bash

# Create the container definition
DOCKER_TAG=${1}
DEPLOY_ENV=${2}

CONTAINER_DEFINITION=$(cat <<EOF

    {
      "name": "cshr-ui",
      "image": "cshr-docker-rpg.bintray.io/rpg-candidate-interface-ui:${DOCKER_TAG}",
      "memory": 128,
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
          "name": "API_PORT",
          "value": "443"
        }
      ]
    }
EOF
)

aws ecs register-task-definition --family ${DEPLOY_ENV}-candidate-interface-ui --container-definitions "${CONTAINER_DEFINITION}"
