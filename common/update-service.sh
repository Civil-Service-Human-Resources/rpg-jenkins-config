#!/bin/bash
#
# usage ./update-service.sh arn dev candidate-interface-api

TASK_DEF_ARN=${1}
DEPLOY_ENV=${2}
COMPONENT=${3}

# update the service
aws ecs update-service --cluster ${DEPLOY_ENV}-cshr-ecs-cluster --service ${DEPLOY_ENV}-${COMPONENT} --task-definition ${TASK_DEF_ARN}
