#!/bin/bash
# Lookup the connector IP.

set -eo pipefail

search_env=${1}

env_ip=$(aws ec2 describe-instances --filters "Name=tag:Name,Values=${search_env}-efs-connector-stack" | jq -r '.Reservations[].Instances[0].NetworkInterfaces[0].Association.PublicIp')

echo "ENVIRONMENT: ${search_env}, IP: ${env_ip}"
