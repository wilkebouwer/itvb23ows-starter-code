FROM jenkins/jenkins:latest
ENV JAVA_OPTS -Djenkins.install.runSetupWizard=false
ENV CASC_JENKINS_CONFIG /usr/share/jenkins/casc.yaml

# Install docker-compose
USER root
RUN apt-get update && apt-get install -y docker-compose
USER jenkins

# Install plugins
COPY --chown=jenkins:jenkins plugins.txt /usr/share/jenkins/ref/plugins.txt
RUN jenkins-plugin-cli -f /usr/share/jenkins/ref/plugins.txt

# Set up CASC configuration file
COPY casc.yaml /usr/share/jenkins/casc.yaml
