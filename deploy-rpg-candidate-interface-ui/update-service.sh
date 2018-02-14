#!/bin/bash

TASK_DEF_ARN=${1}
DEPLOY_ENV=${2}

# update the service
aws ecs update-service --cluster ${DEPLOY_ENV}-cshr-ecs-cluster --service ${DEPLOY_ENV}-candidate-interface-ui --task-definition ${TASK_DEF_ARN}
