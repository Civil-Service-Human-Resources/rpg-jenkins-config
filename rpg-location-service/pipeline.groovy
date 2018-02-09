pipeline {
    agent any
    stages {
        stage('Deploy') {
            steps {
                sh 'echo deploy job started'
            }
        }
    }
}
