FROM jenkins/jenkins:2.437-jdk17
ENV JAVA_OPTS -Djenkins.install.runSetupWizard=false
ENV CASC_JENKINS_CONFIG /usr/share/jenkins/casc.yaml
ARG DOCKER_GID

# Install docker-compose and add jenkins user to docker group
USER root
RUN groupadd --gid ${DOCKER_GID} docker
RUN apt-get update && apt-get install -y docker-compose=1.29.2-3 && rm -rf /var/lib/apt/lists/*
RUN usermod -aG docker jenkins
USER jenkins

# Install plugins
COPY --chown=jenkins:jenkins plugins.txt /usr/share/jenkins/ref/plugins.txt
RUN jenkins-plugin-cli -f /usr/share/jenkins/ref/plugins.txt

# Set up CASC configuration file
COPY casc.yaml /usr/share/jenkins/casc.yaml
