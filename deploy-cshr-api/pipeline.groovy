def update_retval = 'NA'

pipeline {
    agent any
    environment {
      WORKING_DIR='${WORKSPACE}'+'/deploy-cshr-api'
      COMMON_DIR='${WORKSPACE}'+'/common'
      AWS_DEFAULT_REGION='eu-west-1'
    }
    parameters {
      string(defaultValue: 'latest', description: 'The circle CI tag', name: 'dockerTag')
      string(defaultValue: 'dev', description: '', name: 'environment')
      //string(description: 'CSHR database user name for the API', name: 'datasource_username')
      //string(description: 'CSHR database password for the API', name: 'datasource_password')
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
              withCredentials([usernamePassword(credentialsId: '${params.environment}-db_root', usernameVariable: 'user', passwordVariable: 'pass' )]){
                script{
                  update_retval = sh script:"${env.WORKING_DIR}/update-task-def.sh ${params.dockerTag} ${params.environment} ${user} ${pass}", returnStdout: true
                }
              }
            }
        }

        stage('Show results.') {
            steps {
                echo "${update_retval}"
            }
        }

        stage('Update the service') {
            steps {
              script {
                def update_output = readJSON text: "${update_retval}"
                def arn = update_output['taskDefinition']['taskDefinitionArn']
                sh "${env.COMMON_DIR}/update-service.sh ${arn} ${params.environment} candidate-interface-api"
              }
            }
        }


    }


}
