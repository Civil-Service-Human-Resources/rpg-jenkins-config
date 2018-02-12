pipeline {
    agent any
    environment {
      WORKING_DIR=env.WORKSPACE+'/rpg-location-service'
    }
    parameters {
      string(defaultValue: 'latest', description: '', name: 'dockerTag')
      string(defaultValue: 'dev', description: '', name: 'environment')
    }

    stages {
        stage('Check parameters') {
            steps {
                sh "echo deploy job started "
                sh "echo dockerTag: ${params.dockerTag}"
                sh "echo environmentTag: ${params.environment}"
            }
        }

        stage('Update the task definition') {
            steps {
              script{
                def update_retval = sh script:"${env.WORKING_DIR}/deploy.sh ${params.dockerTag} ${params.environment}", returnStdout: true
              }
            }
        }

        stage('Update the service') {
            steps {
              script {
                def update_output = readJSON text: ${update_retval}
                def arn = update_output['taskDefinition']['taskDefinitionArn']
                sh "${env.WORKING_DIR}/update-service.sh ${arn} ${params.environment}"
              }
            }
        }


    }
}
