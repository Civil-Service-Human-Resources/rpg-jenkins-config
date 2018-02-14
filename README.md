# rpg-jenkins-config

A place to store Jenkins config and pipelines.

## Config

Ideally we have a single entry point in Jenkins (or AN Other) that will take requests for deployment.  

Format of folders should be as follows:

deploy-<name of github>

so

deploy-rpg-candidate-interface
deploy-cshr-api

#TODO
* Pull all update-service.sh and update-task-def.sh scripts in to a single common folder and parameterise
* Change location service deploy folder and scripts to match naming convention
