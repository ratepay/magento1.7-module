pipeline {
    agent {
        label "php"
    }

    stages {

        stage('Build') {
            steps {
                echo "[STAGE] Build"
                sh "if [ -d build ]; then rm -rf build ; fi && mkdir -p build/logs"
            }
        }

        stage('Test') {
            steps {
                echo "[STAGE] Test"
                sh (
                    returnStatus: true,
                    script: "phpcs --report=checkstyle --report-file=./build/logs/checkstyle.xml --standard=PSR2 --extensions=php ./"
                )

                sh (
                    returnStatus: true,
                    script: "phpmd ./ xml codesize,cleancode,design,naming,unusedcode,controversial --reportfile build/logs/pmd.xml"
                )

                // sh '''vendor/bin/phpunit --log-junit=build/junit.xml \
                //     --coverage-html=build/coverage \
                //     --coverage-clover=build/clover.xml'''
                // junit 'build/junit.xml'
            }
        }

        stage('Report') {
            steps {
                echo "[STAGE] Report"
                // checkstyle canComputeNew: false, defaultEncoding: '', healthy: '', pattern: 'build/logs/checkstyle.xml', unHealthy: ''
                // pmd canComputeNew: false, defaultEncoding: '', healthy: '', pattern: 'build/logs/pmd.xml', unHealthy: ''
                // junit 'build/junit.xml'
            }
        }

        stage('Sonarqube') {
            agent { label "master" }
            steps {
                script {
                    def scannerHome = tool name: 'SonarQube Scanner 3.0.1', type: 'hudson.plugins.sonar.SonarRunnerInstallation';
                    withSonarQubeEnv() {
                        sh "$scannerHome/bin/sonar-scanner -Dsonar.host.url=$SONAR_HOST_URL -Dsonar.login=$SONAR_AUTH_TOKEN"
                    }
                }
            }
        }

        stage('Deploy') {
            steps {
                echo "[STAGE] Deploy"
            }
        }

    }
}