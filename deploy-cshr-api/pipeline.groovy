def update_retval = 'NA'
def location_user = null
def location_pass = null
def api_user = null
def api_pass = null
def crud_user = null
def crud_pass = null

pipeline {
    agent any
    environment {
      WORKING_DIR='${WORKSPACE}'+'/deploy-cshr-api'
      COMMON_DIR='${WORKSPACE}'+'/common'
      AWS_DEFAULT_REGION='eu-west-1'
    }
    parameters {
      string(defaultValue: 'develop', description: 'The circle CI tag', name: 'dockerTag')
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
                    withCredentials([usernamePassword(credentialsId: "${params.environment}_api_search", usernameVariable: 'user', passwordVariable: 'pass' )]){
                        api_user = env.user
                        api_pass = env.pass
                    }
                }
                script {
                    withCredentials([usernamePassword(credentialsId: "${params.environment}_api_crud", usernameVariable: 'user', passwordVariable: 'pass' )]){
                        crud_user = env.user
                        crud_pass = env.pass
                    }
                }
                script {
                    withCredentials([usernamePassword(credentialsId: "${params.environment}_filebeat_hosts", usernameVariable: 'user', passwordVariable: 'pass' )]){
                        filebeat_hosts = env.pass
                    }
                } 
                script {
                    withCredentials([usernamePassword(credentialsId: "${params.environment}_notify_service", usernameVariable: 'user', passwordVariable: 'pass' )]){
                        notify_template_id = env.user
                        notify_api_key = env.pass
                    }
                } 
              
            }   
        }

        stage('Update the task definition') {
            steps {
              withCredentials([usernamePassword(credentialsId: "${params.environment}_db_root", usernameVariable: 'user', passwordVariable: 'pass' )]){
                script{
                  update_retval = sh script:"${env.WORKING_DIR}/update-task-def.sh ${params.dockerTag} ${params.environment} ${user} ${pass} ${location_user} ${location_pass} ${api_user} ${api_pass} ${crud_user} ${crud_pass} ${filebeat_hosts} ${notify_template_id} ${notify_api_key}", returnStdout: true
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
