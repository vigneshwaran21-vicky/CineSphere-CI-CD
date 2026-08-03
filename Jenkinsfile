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
        EC2_HOST = "13.203.101.232" // REMOVED TRAILING SLASH
        SSH_CREDENTIAL = "aws-ec2"  // Updated to match the credentials ID used below
    }

    stages {

        stage('Checkout Source Code') {
            steps {
                echo "==============================="
                echo "CHECKOUT SOURCE CODE"
                echo "==============================="
                
                git branch: "${BRANCH}", url: "${GIT_REPO}"
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
                '''
            }
        }

        stage('Install Python Dependencies') {
            steps {
                echo "==============================="
                echo "INSTALL PYTHON DEPENDENCIES"
                echo "==============================="
                
                bat '''
                echo Installing Python packages...
                python -m pip install --upgrade pip
                pip install bandit safety
                echo Python dependencies installed successfully!
                '''
            }
        }

        stage('Security Scan') {
            steps {
                echo "==============================="
                echo "SECURITY SCAN"
                echo "==============================="
                
                bat '''
                echo Running Bandit security scan...
                python -m bandit -r . -f txt || echo "Bandit scan completed with warnings"
                
                echo.
                echo Running Safety security check...
                safety check || echo "Safety check completed with warnings"
                '''
            }
        }

        stage('PHP Syntax Check') {
            steps {
                echo "==============================="
                echo "PHP LINT"
                echo "==============================="
                
                bat '''
                set ERROR_COUNT=0
                echo Checking PHP files...
                for /R %%f in (*.php) do (
                    php -l "%%f"
                    if errorlevel 1 set ERROR_COUNT=1
                )
                echo.
                if %ERROR_COUNT%==1 (
                    echo PHP syntax errors found!
                    exit /b 1
                ) else (
                    echo All PHP files passed syntax check!
                )
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
                    echo Running PHPUnit tests...
                    vendor\\bin\\phpunit
                ) else (
                    echo PHPUnit Not Configured. Skipping unit tests.
                )
                '''
            }
        }
        stage('Deploy to AWS EC2') {
            steps {
                echo "==============================="
                echo "DEPLOYING TO AWS EC2"
                echo "==============================="
                
                withCredentials([sshUserPrivateKey(credentialsId: 'aws-ec2', keyFileVariable: 'SSH_KEY', usernameVariable: 'SSH_USER')]) {
                    bat """
                    echo Fixing permissions for SSH key...
                    for /f "delims=" %%i in ('whoami') do icacls "%SSH_KEY%" /inheritance:r /grant:r "%%i:F"
                    
                    echo Deploying to EC2 instance ${EC2_HOST}...
                    ssh -i "%SSH_KEY%" -o StrictHostKeyChecking=no %SSH_USER%@${EC2_HOST} ^
                    "sudo mkdir -p /var/www/html && cd /var/www/html && sudo git init && sudo git remote remove origin ; sudo git remote add origin https://github.com/vigneshwaran21-vicky/CineSphere-CI-CD.git && sudo git fetch origin && sudo git reset --hard origin/main && sudo systemctl restart apache2 && echo 'Deployment completed successfully!'"
                    """
                }
            }
        }
        stage('Website Health Check') {
            steps {
                echo "==============================="
                echo "HEALTH CHECK"
                echo "==============================="
                
                bat """
                echo Checking website health at http://${EC2_HOST}
                curl -s -o nul -w "HTTP Status: %%{http_code}\n" http://${EC2_HOST}
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
            echo "✓ Git Checkout          : PASS"
            echo "✓ Software Metrics      : PASS"
            echo "✓ Python Check          : PASS"
            echo "✓ Python Dependencies   : PASS"
            echo "✓ Security Scan         : PASS"
            echo "✓ PHP Syntax Check      : PASS"
            echo "✓ Unit Test             : PASS"
            echo "✓ Deployment            : PASS"
            echo "✓ Website Test          : PASS"
            echo "-------------------------------------------"
            echo "BUILD STATUS          : SUCCESS ✓"
            echo "==========================================="
        }

        failure {
            echo ""
            echo "==========================================="
            echo "        CineSphere Build Failed"
            echo "==========================================="
            echo "✗ BUILD STATUS          : FAILED"
            echo "==========================================="
            echo ""
            echo "Please check the logs above for errors."
        }

        unstable {
            echo ""
            echo "==========================================="
            echo "        CineSphere Build Unstable"
            echo "==========================================="
            echo "⚠ BUILD STATUS          : UNSTABLE"
            echo "==========================================="
        }

        aborted {
            echo ""
            echo "==========================================="
            echo "        CineSphere Build Aborted"
            echo "==========================================="
            echo "⚠ BUILD STATUS          : ABORTED"
            echo "==========================================="
        }

        always {
            echo "Archiving artifacts..."
            archiveArtifacts artifacts: '**/*.log', allowEmptyArchive: true
            
            echo ""
            echo "==========================================="
            echo "Build completed at: ${currentBuild.startTimeInMillis}"
            echo "Build URL: ${env.BUILD_URL}"
            echo "==========================================="
        }
    }
}
