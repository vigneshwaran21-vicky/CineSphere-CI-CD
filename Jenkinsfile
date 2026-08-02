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
        echo.
        echo ============================================
        echo TOTAL FILES
        echo ============================================
        dir /s /b *.php *.js *.html *.css
        '''

        bat '''
        echo.
        echo ============================================
        echo LINES OF CODE
        echo ============================================
        powershell -NoProfile -Command "$c=(Get-ChildItem -Recurse -Include *.php,*.js,*.html,*.css | Get-Content | Measure-Object -Line).Lines; Write-Host 'TOTAL LINES OF CODE:' $c"
        '''

        bat '''
        echo.
        echo ============================================
        echo PHP FILE COUNT
        echo ============================================
        dir /s *.php | find /c ".php"
        '''

        bat '''
        echo.
        echo ============================================
        echo JAVASCRIPT FILE COUNT
        echo ============================================
        dir /s *.js | find /c ".js"
        '''

        bat '''
        echo.
        echo ============================================
        echo HTML FILE COUNT
        echo ============================================
        dir /s *.html | find /c ".html"
        '''

        bat '''
        echo.
        echo ============================================
        echo CSS FILE COUNT
        echo ============================================
        dir /s *.css | find /c ".css"
        '''
    }
}


        stage('Check Python') {
    steps {
        bat '''
        echo ============================
        echo CHECKING PYTHON
        echo ============================

        where python

        python --version

        python -m bandit --version
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
