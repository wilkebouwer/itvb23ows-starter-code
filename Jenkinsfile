pipeline {
    agent any

    stages {
	stage('docker-compose build') {
            steps {
        	sh '''
		    cp ./.env-jenkins ./.env
		    echo "test"
		    echo ${EXECUTOR_NUMBER}
		    docker-compose build app database
		'''
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
