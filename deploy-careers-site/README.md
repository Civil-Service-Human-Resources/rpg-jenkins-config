# deploy-careers-site - TODO: finish

Ansible scripts to package and deploy careers site to AWS environment

## Prereqs

In order to run these scripts you will need the following:

* aws credentials
* github credentials
* ansible

## Files

### backup.yml

Backs up the entire wordpress installation and database to the host on which you run the script.

#### Steps

* make sure AWS credentials are exported correctly (the script looks up instance details using AWS API)
* run the following command to start the process (change the env variable for env required)

`ansible-playbook backup.yml --extra-vars "env=desired_environment" --extra-vars "db_user=the_db_user" --extra-vars "db_password=the_db_password"`
