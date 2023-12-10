pipeline {
    agent any

    stages {
	stage('docker-compose build') {
            steps {
        	sh 'docker-compose build app database'
            }
        }

	stage('test') {
            steps {
	    	sh '''
		    echo "Debug"
		'''
            }
        }

	stage('docker-compose up') {
            steps {
	    	sh '''
		    echo "Debug"
		    cp .env-jenkins .env
		    docker-compose up -d app database
		'''
            }
        }


    }

    post {
      always {
          sh 'docker-compose down --remove-orphans'
      }
   }
}
