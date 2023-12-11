pipeline {
    agent any

    stages {
	stage('docker-compose build') {
            steps {
        	sh '''
		    i=0; while IFS= read -r line; do
		    	echo "$line" | grep -q '^.*=:port:' && echo "$line" | sed "s/\\(.*=\\):port:/\\1$(( 50000 + EXECUTOR_NUMBER + ( 100 * i ) ))/" >> ./.env && i=$((i+1)) || echo "$line" >> ./.env
		    done < ./.env-jenkins
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
