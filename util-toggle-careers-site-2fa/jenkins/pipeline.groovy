pipeline {
    agent any
    environment {
      WORKING_DIR='${WORKSPACE}'+'/util-toggle-careers-site-2fa'
      AWS_DEFAULT_REGION='eu-west-1'
      ANSIBLE_HOST_KEY_CHECKING='False'
      ANSIBLE_FORCE_COLOUR='True'
    }
    parameters {
      choice(choices: 'dev\ntest', description: 'The environment to deploy the changes', name: 'environment')
      choice(choices: 'true\nfalse', description: 'The environment to deploy the changes', name: 'enabled_value')
      
    }

    stages {
        
        stage('package deployment and do basic install') {
            steps {
                        wrap([$class: 'AnsiColorBuildWrapper', 'colorMapName': 'XTerm']) {
                            ansiblePlaybook(
                                playbook: "${env.WORKING_DIR}/ansible/toggle-careers-site-2fa.yml",
                                credentialsId: 'efs_ssh_key',
                                extraVars: [
                                    env: "${env.environment}" ,
                                    enabled_value: "${env.enabled_value}"
                                ]
                            )
                        }
                     
            }
        }

    }
}
