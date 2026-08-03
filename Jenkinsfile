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
                pip install bandit safety lizard -q
                echo Python dependencies installed successfully!
                '''
            }
        }

        stage('Cyclomatic Complexity (Lizard)') {
            steps {
                echo "==============================="
                echo "LIZARD METRICS"
                echo "==============================="
                
                bat '''
                @echo off
                echo Running Lizard Code Complexity Analyzer...
                python -m lizard backend/ frontend/ || true
                '''
            }
        }

        stage('Security Scan (SAST)') {
            steps {
                echo "==============================="
                echo "STATIC SECURITY SCAN"
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

        stage('Advanced PHP Code Quality') {
            steps {
                echo "==============================="
                echo "PHPCPD, PHPCS, PHPStan, PhpMetrics"
                echo "==============================="
                
                bat '''
                @echo off
                if not exist tools mkdir tools
                
                echo Downloading PHPCPD (Copy/Paste Detector)...
                if not exist tools\\phpcpd.phar curl -sL https://phar.phpunit.de/phpcpd.phar -o tools\\phpcpd.phar
                
                echo Downloading PHP_CodeSniffer (PHPCS)...
                if not exist tools\\phpcs.phar curl -sL https://github.com/squizlabs/PHP_CodeSniffer/releases/download/3.7.2/phpcs.phar -o tools\\phpcs.phar
                
                echo Downloading PHPStan (Static Analysis)...
                if not exist tools\\phpstan.phar curl -sL https://github.com/phpstan/phpstan/releases/latest/download/phpstan.phar -o tools\\phpstan.phar
                
                echo Downloading PhpMetrics (Maintainability Index)...
                if not exist tools\\phpmetrics.phar curl -sL https://github.com/phpmetrics/PhpMetrics/releases/latest/download/phpmetrics.phar -o tools\\phpmetrics.phar
                
                echo.
                echo --- Running PHPCPD (Code Duplication) ---
                php tools\\phpcpd.phar backend\\ || true
                
                echo.
                echo --- Running PHPCS (Coding Standards PSR-12) ---
                php tools\\phpcs.phar backend\\ || true
                
                echo.
                echo --- Running PHPStan (Static Code Analysis) ---
                php tools\\phpstan.phar analyse backend\\ --level=1 || true
                
                echo.
                echo --- Running PhpMetrics (Maintainability Index) ---
                php tools\\phpmetrics.phar --report-html=metrics backend\\ || true
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

        stage('Unit Testing (PHPUnit + Xdebug)') {
            steps {
                echo "==============================="
                echo "PHPUNIT CODE COVERAGE"
                echo "==============================="
                
                bat '''
                @echo off
                echo Downloading PHPUnit...
                if not exist tools mkdir tools
                if not exist tools\\phpunit.phar curl -sL https://phar.phpunit.de/phpunit-10.5.phar -o tools\\phpunit.phar
                
                echo.
                echo Running Unit Tests and generating Xdebug Coverage Report...
                php tools\\phpunit.phar tests\\CineSphereTest.php --coverage-text --coverage-filter backend/
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

        stage('OWASP ZAP Dynamic Scan') {
            steps {
                echo "==============================="
                echo "OWASP ZAP DAST"
                echo "==============================="
                
                bat """
                @echo off
                echo Running OWASP ZAP Baseline Scan against live server http://${EC2_HOST}...
                docker run -t owasp/zap2docker-stable zap-baseline.py -t http://${EC2_HOST} || true
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

        always {
            echo "Archiving artifacts..."
            archiveArtifacts artifacts: '**/*.log', allowEmptyArchive: true
            
            echo ""
            echo "==========================================="
            echo "Build URL: ${env.BUILD_URL}"
            echo "==========================================="
        }
    }
}
