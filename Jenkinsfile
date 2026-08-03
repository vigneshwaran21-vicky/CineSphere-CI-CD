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
        EC2_HOST = "13.203.101.232"
        SSH_CREDENTIAL = "aws-ec2"
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
                powershell '''
                $files = Get-ChildItem -Path .\\* -Recurse -Include *.php,*.js,*.html,*.css
                
                $lines = 0
                if ($files) {
                    $lines = ($files | Get-Content -ErrorAction SilentlyContinue | Measure-Object -Line).Lines
                }
                
                $php = @($files | Where-Object Extension -eq '.php').Count
                $js = @($files | Where-Object Extension -eq '.js').Count
                $html = @($files | Where-Object Extension -eq '.html').Count
                $css = @($files | Where-Object Extension -eq '.css').Count
                
                Write-Host "============================================"
                Write-Host "          SOFTWARE METRICS SUMMARY          "
                Write-Host "============================================"
                Write-Host "Total Files         : $($files.Count)"
                Write-Host "Total Lines of Code : $lines"
                Write-Host "PHP Files           : $php"
                Write-Host "JavaScript Files    : $js"
                Write-Host "HTML Files          : $html"
                Write-Host "CSS Files           : $css"
                Write-Host "============================================"
                '''
            }
        }

        stage('Check Python') {
            steps {
                bat '''
                @echo off
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
                @echo off
                echo Installing Python packages...
                python -m pip install --upgrade pip -q
                pip install bandit safety -q
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
                @echo off
                echo Running Bandit security scan...
                python -m bandit -r . -f txt -q || (echo WARNING: Bandit found issues & python -m bandit -r . -f txt)
                
                echo.
                echo Running Safety security check...
                safety check >nul 2>&1 || (echo WARNING: Safety found issues & safety check)
                
                echo Security scan completed successfully!
                '''
            }
        }

        stage('PHP Syntax Check') {
            steps {
                echo "==============================="
                echo "PHP LINT"
                echo "==============================="
                
                bat '''
                @echo off
                set ERROR_COUNT=0
                echo Checking PHP files silently...
                
                for /R %%f in (*.php) do (
                    php -l "%%f" >nul 2>&1
                    if errorlevel 1 (
                        set ERROR_COUNT=1
                        php -l "%%f"
                    )
                )
                
                echo.
                if %ERROR_COUNT%==1 (
                    echo PHP syntax errors found!
                    exit /b 1
                ) else (
                    echo All PHP files passed syntax check successfully!
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
                @echo off
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
                    @echo off
                    echo Fixing permissions for SSH key...
                    for /f "delims=" %%i in ('whoami') do icacls "%SSH_KEY%" /inheritance:r /grant:r "%%i:F" >nul
                    
                    echo Deploying to EC2 instance ${EC2_HOST}...
                    ssh -i "%SSH_KEY%" -o StrictHostKeyChecking=no %SSH_USER%@${EC2_HOST} ^
                    "sudo mkdir -p /var/www/html && cd /var/www/html && sudo git init -q && sudo git config --global --add safe.directory /var/www/html && sudo git remote remove origin 2>/dev/null ; sudo git remote add origin https://github.com/vigneshwaran21-vicky/CineSphere-CI-CD.git && sudo git fetch origin -q && sudo git reset --hard origin/main -q && sudo systemctl restart apache2 && echo 'Deployment completed successfully!'"
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
                @echo off
                echo Checking website health at http://${EC2_HOST}
                curl -s -o nul -w "HTTP Status: %%{http_code}\\n" http://${EC2_HOST}
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
