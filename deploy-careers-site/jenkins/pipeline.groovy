def zip_file_name = 'NA'

pipeline {
    agent any
    environment {
      WORKING_DIR='${WORKSPACE}'+'/deploy-careers-site'
      AWS_DEFAULT_REGION='eu-west-1'
      ANSIBLE_HOST_KEY_CHECKING='False'
      ANSIBLE_FORCE_COLOUR='True'
    }
    parameters {
      string(defaultValue: 'release', description: '', name: 'branch_name')
      string(defaultValue: 'jenkins', description: '', name: 'built_by')
      choice(choices: 'demo\ntest', description: 'The environment to deploy the changes', name: 'environment')
      
    }

    stages {
        stage('Check parameters') {
            steps {
                sh "echo deploy job started "
                sh "echo dockerTag: ${params.branch_name}"
                sh "echo environmentTag: ${params.built_by}"

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
                        sh "ansible-playbook ./deploy-careers-site/ansible/package.yml --extra-vars \"user=${params.built_by}\" --extra-vars \"base_dir=${env.WORKING_DIR}/ansible\""
                        withCredentials([usernamePassword(credentialsId: "${params.environment}_db_root", usernameVariable: 'user', passwordVariable: 'pass' )]){
                          wrap([$class: 'AnsiColorBuildWrapper', 'colorMapName': 'XTerm']) {
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
                        // Wait until the site is available before moving on.
                        sh "${env.WORKING_DIR}/jenkins/checkEnv.sh ${params.environment}"
                    }
            }
        }

        stage('deploy the zip file') {
            steps {
              withCredentials([usernamePassword(credentialsId: "${params.environment}_db_root", usernameVariable: 'user', passwordVariable: 'pass' )]){
                wrap([$class: 'AnsiColorBuildWrapper', 'colorMapName': 'XTerm']) {
                  ansiblePlaybook(
                      playbook: "${env.WORKING_DIR}/ansible/deploy.yml",
                      credentialsId: 'efs_ssh_key',
                      extraVars: [
                          base_dir: "${env.WORKING_DIR}/ansible",
                          env: "${env.environment}" ,
                          db_user: "${user}",
                          db_password: [ value: "${pass}", hidden: true ],
                          zip_location: "${env.WORKING_DIR}/ansible/zip/latest_deployment.zip"
                      ]
                  )
                }
              }
              sh "${env.WORKING_DIR}/jenkins/checkEnv.sh ${params.environment}"
            }
        }

        stage('run the post deploy script') {
            steps {
              withCredentials([usernamePassword(credentialsId: "${params.environment}_db_root", usernameVariable: 'user', passwordVariable: 'pass' )]){
                wrap([$class: 'AnsiColorBuildWrapper', 'colorMapName': 'XTerm']) {
                  ansiblePlaybook(
                      playbook: "${env.WORKING_DIR}/ansible/post-deploy.yml",
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
            }
            sh "${env.WORKING_DIR}/jenkins/checkEnv.sh ${params.environment}"
        }


    }
}
