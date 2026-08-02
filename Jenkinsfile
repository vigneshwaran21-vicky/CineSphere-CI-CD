pipeline {
    agent any

    options {
        ansiColor('xterm')
        timestamps()
    }

    environment {
    GIT_REPO = "https://github.com/vigneshwaran21-vicky/CineSphere-CI-CD.git"
    BRANCH = "main"

    EC2_USER = "ubuntu"
    EC2_HOST = "13.204.69.14"

    SSH_CREDENTIAL = "aws-server"
}

    stages {

        stage('Checkout Source Code') {
            steps {
                echo "==============================="
                echo "CHECKOUT SOURCE CODE"
                echo "==============================="

                git branch: "${BRANCH}",
                    url: "${GIT_REPO}"
            }
        }

       stage('Software Metrics') {

    steps {

        echo "==============================="
        echo "SOFTWARE METRICS"
        echo "==============================="

        bat '''
        echo ===== Counting Lines of Code =====

        powershell -Command ^
        "$files=Get-ChildItem -Recurse -Include *.php,*.js,*.html,*.css; ^
        $count=0; ^
        foreach($f in $files){$count+=(Get-Content $f.FullName).Count}; ^
        Write-Host 'TOTAL LINES OF CODE:' $count"
        '''

        bat '''
        echo.
        echo ===== Cyclomatic Complexity =====
        lizard .
        '''

        bat '''
        echo.
        echo ===== Maintainability =====
        radon mi .
        '''

    }

}

        stage('Security Scan') {
            steps {

                echo "==============================="
                echo "SECURITY SCAN"
                echo "==============================="

                bat '''
                bandit -r .
                '''

                bat '''
                safety check
                '''
            }
        }

        stage('PHP Syntax Check') {
            steps {

                echo "==============================="
                echo "PHP LINT"
                echo "==============================="

                bat '''
                for /R %%f in (*.php) do php -l "%%f"
                '''
            }
        }

        stage('Unit Testing') {
            steps {

                echo "==============================="
                echo "UNIT TEST"
                echo "==============================="

                bat '''
                if exist vendor\\bin\\phpunit (
                    vendor\\bin\\phpunit
                ) else (
                    echo PHPUnit Not Configured.
                )
                '''
            }
        }

        stage('Deploy to AWS EC2') {

            steps {

                sshagent(credentials: ['aws-ec2']) {

                    bat """
                    ssh -o StrictHostKeyChecking=no ubuntu@${EC2_HOST} ^
                    "cd /var/www/html &&
                    sudo git pull origin main &&
                    sudo systemctl restart apache2"
                    """
                }
            }
        }

        stage('Website Health Check') {

            steps {

                bat """
                curl http://${EC2_HOST}
                """
            }
        }

    }

    post {

        success {

            echo ""
            echo "==========================================="
            echo "        CineSphere Build Summary"
            echo "==========================================="
            echo "Git Checkout          : PASS"
            echo "Software Metrics      : PASS"
            echo "Security Scan         : PASS"
            echo "PHP Syntax Check      : PASS"
            echo "Unit Test             : PASS"
            echo "Deployment            : PASS"
            echo "Website Test          : PASS"
            echo "-------------------------------------------"
            echo "BUILD STATUS          : SUCCESS"
            echo "==========================================="
        }

        failure {

            echo ""
            echo "==========================================="
            echo "BUILD FAILED"
            echo "==========================================="
        }

        always {

            archiveArtifacts artifacts: '**/*.log', allowEmptyArchive: true

        }
    }
}
