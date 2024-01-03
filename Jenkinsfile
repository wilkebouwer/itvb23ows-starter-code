pipeline {
    agent any

    stages {
    	stage('SonarQube Analysis') {
  	    environment {
    	        SCANNER_HOME = tool 'SonarScanner'
    		PROJECT_NAME = 'wilkebouwer-itvb23ows-starter-code-sonarqube'
  	    }
  	    steps {
    		withSonarQubeEnv('wilkebouwer-itvb23ows-starter-code-sonarqube') {
        	    sh '''
		    	$SCANNER_HOME/bin/sonar-scanner \
			    -Dsonar.projectKey=$PROJECT_NAME
		    '''
    		}
  	    }
	}

	stage('docker-compose build') {
            steps {
        	sh '''
		    i=0; while IFS= read -r line; do
		    	echo "$line" | grep -q '^.*=:port:' && echo "$line" | sed "s/\\(.*=\\):port:/\\1$(( 50000 + ( 100 * i ) + EXECUTOR_NUMBER ))/" >> ./.env && i=$((i+1)) || echo "$line" >> ./.env
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

	stage('PHPUnit Tests') {
            steps {
	    	sh 'docker exec -it tvb23ows-starter-code_${BRANCH_NAME}_app-1 ./vendor/bin/phpunit'
            }
        }
    }

    post {
      always {
          sh 'docker-compose down --remove-orphans'
      }
   }
}
