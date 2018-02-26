pipeline {
    agent any
    environment {
      WORKING_DIR='${WORKSPACE}'+'/deploy-careers-site'
      AWS_DEFAULT_REGION='eu-west-1'
    }
    parameters {
      string(defaultValue: 'release', description: '', name: 'branch_name')
      string(defaultValue: 'jenkins', description: '', name: 'built_by')
      string(defaultValue: '', description: '', name: 'credentials_id')
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
            sh "mkdir -p ${WORKING_DIR}/ansible/source"
            dir("./deploy-careers-site/ansible/source") {
              git(
                url: 'git@github.com:Civil-Service-Human-Resources/rpg-careers-wp.git',
                credentialsId: "${params.credentials_id}",
                branch: "${params.branch_name}"
                )
            }
            sh "ls -lsR ${WORKING_DIR}"
          }
        }

        stage('Build deployment zip') {
            steps {
                sh "ansible-playbook ./deploy-careers-site/ansible/package.yml --extra-vars 'user=${params.built_by}' --skip-tags 'git_checkout'"
            }
        }
    }
}
