pipeline {
    agent any

    stages {
	stage('Set environment variables for docker-compose') {
            steps {
	    	sh 'cp ./.env-jenkins ./.env'
            }
        }

	stage('docker-compose build') {
            steps {
        	sh 'docker-compose build app database'
            }
        }

	stage('docker-compose up') {
            steps {
	    	sh 'docker-compose up -d app database'
            }
        }
    }

    post {
      always {
          sh 'docker-compose down --remove-orphans'
      }
   }
}
