def zip_file_name = 'NA'

pipeline {
    agent any
    environment {
      WORKING_DIR='${WORKSPACE}'+'/deploy-careers-site'
      AWS_DEFAULT_REGION='eu-west-1'
      ANSIBLE_HOST_KEY_CHECKING='False'
    }
    parameters {
      string(defaultValue: 'release', description: '', name: 'branch_name')
      string(defaultValue: 'jenkins', description: '', name: 'built_by')
      string(defaultValue: 'demo', description: 'The environment to deploy the changes', name: 'environment')
    }

    stages {
        stage('Check parameters') {
            steps {
                sh "echo deploy job started "
                sh "echo dockerTag: ${params.branch_name}"
                sh "echo environmentTag: ${params.built_by}"
                sh "ls -lsR ${WORKING_DIR}"
            }
        }

        stage('Git clone the branch') {
          steps {
            sh "mkdir -p ${WORKING_DIR}/source"
            dir("./deploy-careers-site/source") {
              git(
                url: 'git@github.com:Civil-Service-Human-Resources/rpg-careers-wp.git',
                branch: "${params.branch_name}"
                )
            }
          }
        }

        stage('package deployment and do basic install') {
            steps {
                parallel(
                    package: {
                        sh "ansible-playbook ./deploy-careers-site/ansible/package.yml --extra-vars \"user=${params.built_by}\" --extra-vars \"base_dir=${env.WORKING_DIR}/ansible\""
                    },
                    basic_install: {
                        withCredentials([usernamePassword(credentialsId: "${params.environment}_db_root", usernameVariable: 'user', passwordVariable: 'pass' )]){
                            ansiblePlaybook(
                                playbook: "${env.WORKING_DIR}/ansible/basic-install.yml",
                                credentialsId: 'efs_ssh_key',
                                extraVars: [
                                    base_dir: "${env.WORKING_DIR}/ansible",
                                    env: "${env.environment}" ,
                                    db_user: "${user}",
                                    db_password: [ value: "${pass}", hidden: true ]
                                ]

                            )
                        }
                    }
                )
            }
        }

        //stage('Build deployment zip') {
        //    steps {
        //        sh "ansible-playbook ./deploy-careers-site/ansible/package.yml --extra-vars \"user=${params.built_by}\" --extra-vars \"base_dir=${WORKING_DIR}\""
        //    }
        //}

        stage('get the zip file name') {
            steps {
                script{
                  zip_file_name = sh script:"ls -1rt ./deploy-careers-site/zip | tail -1", returnStdout: true
                }
            }
        }

        stage('deploy the zip file') {
            steps {
            withCredentials([usernamePassword(credentialsId: "${params.environment}_db_root", usernameVariable: 'user', passwordVariable: 'pass' )]){
                script{
                  update_retval = sh script:"ansible-playbook ${env.WORKING_DIR}/ansible/deploy.yml --extra-vars \"env=${params.environment}\" --extra-vars \"db_user=${user}\" --extra-vars \"db_password=${pass}\" --extra-vars \"zip_location=${WORKING_DIR}/zip/${zip_file_name}\"", returnStdout: true
                }
              }
            }
        }


    }
}

//stage('deploy') {
//    steps {
//        withCredentials([[$class: 'UsernamePasswordMultiBinding', credentialsId: '<CREDENTIAL_ID>',
//                    usernameVariable: 'USERNAME', passwordVariable: 'PASSWORD']]) {
//            sh 'echo hello'
//        }
//    }
//}
