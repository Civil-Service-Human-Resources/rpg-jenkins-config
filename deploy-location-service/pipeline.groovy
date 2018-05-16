def update_retval = 'NA'
def location_user = null
def location_pass = null
def location_key = null
def filebeat_hosts = null

pipeline {
    agent any
    environment {
      WORKING_DIR='${WORKSPACE}'+'/deploy-location-service'
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


        stage('load the credentials in to variables'){
            steps{
                script {
                    withCredentials([usernamePassword(credentialsId: "${params.environment}_location_user", usernameVariable: 'user', passwordVariable: 'pass' )]){
                        location_user = env.user
                        location_pass = env.pass
                    }
                } 
                script {
                    withCredentials([usernamePassword(credentialsId: "${params.environment}_location_key", usernameVariable: 'user', passwordVariable: 'pass' )]){
                        location_key = env.pass
                    }
                } 
                script {
                    withCredentials([usernamePassword(credentialsId: "${params.environment}_filebeat_hosts", usernameVariable: 'user', passwordVariable: 'pass' )]){
                        filebeat_hosts = env.pass
                    }
                } 
            }   
        }

        stage('Update the task definition') {
            steps {
              script{
                update_retval = sh script:"${env.WORKING_DIR}/update-task-def.sh ${params.dockerTag} ${params.environment} ${location_key} ${location_user} ${location_pass} ${filebeat_hosts}", returnStdout: true
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
                sh "${env.COMMON_DIR}/update-service.sh ${arn} ${params.environment} location-service"
              }
            }
        }


    }
}
