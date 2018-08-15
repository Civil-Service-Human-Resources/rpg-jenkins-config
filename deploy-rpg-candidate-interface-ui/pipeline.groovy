def update_retval = 'NA'

def ci_user = null
def ci_pass = null
def api_user = null
def api_pass = null

pipeline {
    agent any
    environment {
      WORKING_DIR='${WORKSPACE}'+'/deploy-rpg-candidate-interface-ui'
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
                    withCredentials([usernamePassword(credentialsId: "${params.environment}_api_search", usernameVariable: 'user', passwordVariable: 'pass' )]){
                        
                        api_user = env.user
                        api_pass = env.pass
                            
                    }
                }    
                //withCredentials([usernamePassword(credentialsId: "${params.environment}_ci_auth", usernameVariable: 'user', passwordVariable: 'pass' )]){
                 //    script {
                   //      ci_user = ${user}
                 //       ci_pass = ${pass}   
                  //   }
                }
            }
        }

        stage('Update the task definition') {
            steps {
              script{
                update_retval = sh script:"${env.WORKING_DIR}/update-task-def.sh ${params.dockerTag} ${params.environment} ${ci_user} ${ci_pass} ${api_user} ${api_pass}", returnStdout: true
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
                sh "${env.COMMON_DIR}/update-service.sh ${arn} ${params.environment} candidate-interface-ui"
              }
            }
        }

    }
}
