def update_retval = 'NA'

pipeline {
    agent any
    environment {
      WORKING_DIR='${WORKSPACE}'+'/deploy-cshr-ats-adapter'
      AWS_DEFAULT_REGION='eu-west-1'
      COMMON_DIR='${WORKSPACE}'+'/common'
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
                update_retval = sh script:"${env.WORKING_DIR}/update-task-def.sh ${params.dockerTag} ${params.environment}", returnStdout: true
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
                sh "${env.COMMON_DIR}/update-service.sh ${arn} ${params.environment} cshr-ats-adapter"
              }
            }
        }


    }
}
