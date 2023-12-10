pipeline {
    stages {
	stage('Docker-compose build & run') {
            steps {
        	sh "docker-compose build"
        	sh "docker-compose up -d"
            }
        }
    }

    post {
      always {
          sh "docker-compose down --remove-orphans || true"
      }
   }
}
