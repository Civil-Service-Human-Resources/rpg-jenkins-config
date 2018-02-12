pipeline {
    agent any
    parameters {
      stringParam(defaultValue: 'latest', description: '', name: 'dockerTag')
    }

    stages {
        stage('Deploy') {
            steps {
                sh 'echo deploy job started '
                sh 'echo ${params.dockerTag}'
            }
        }
    }
}
