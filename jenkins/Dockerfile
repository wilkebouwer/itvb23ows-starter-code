FROM jenkins/jenkins:latest
ENV JAVA_OPTS -Djenkins.install.runSetupWizard=false
ENV CASC_JENKINS_CONFIG /usr/share/jenkins/casc.yaml
ARG DOCKERGID

# Install docker-compose, change docker socket permissions and add jenkins user to docker group
USER root
RUN groupadd --gid ${DOCKERGID} docker
RUN apt-get update && apt-get install -y docker-compose
RUN usermod -aG docker jenkins
USER jenkins

# Install plugins
COPY --chown=jenkins:jenkins plugins.txt /usr/share/jenkins/ref/plugins.txt
RUN jenkins-plugin-cli -f /usr/share/jenkins/ref/plugins.txt

# Set up CASC configuration file
COPY casc.yaml /usr/share/jenkins/casc.yaml
