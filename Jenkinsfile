pipeline {
    agent any

    environment {
        DB_NAME = 'cinesphere_db'
        DB_USER = 'root'
        DB_PASS = credentials('db-password')
        EC2_USER = 'ubuntu'
        EC2_IP = '65.0.193.223'
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Build & Lint') {
            steps {
                echo 'Running PHP Lint...'
                bat 'for /r %%i in (*.php) do php -l "%%i"'
            }
        }

        stage('Deploy to AWS EC2') {
    steps {
        withCredentials([sshUserPrivateKey(credentialsId: 'aws-ssh-key', keyFileVariable: 'SSH_KEY')]) {

            echo "Deploying to %EC2_IP%..."

            // Fix key permissions
            bat """
            icacls "%SSH_KEY%" /inheritance:r
            icacls "%SSH_KEY%" /grant:r SYSTEM:F
            icacls "%SSH_KEY%" /grant:r Administrators:F
            """

            // 🔥 FIX: Give ubuntu access BEFORE copying
            bat """
            ssh -i "%SSH_KEY%" -o StrictHostKeyChecking=no %EC2_USER%@%EC2_IP% "sudo mkdir -p /var/www/html/cinesphere && sudo chown -R ubuntu:ubuntu /var/www/html"
            """

            // Now copy works ✅
            bat """
            scp -i "%SSH_KEY%" -o StrictHostKeyChecking=no -r * %EC2_USER%@%EC2_IP%:/var/www/html/cinesphere
            """

            // Restore correct ownership
            bat """
            ssh -i "%SSH_KEY%" -o StrictHostKeyChecking=no %EC2_USER%@%EC2_IP% "sudo chown -R www-data:www-data /var/www/html/cinesphere"
            """
        }
    }
}

        stage('Database Migration') {
            steps {
                withCredentials([sshUserPrivateKey(credentialsId: 'aws-ssh-key', keyFileVariable: 'SSH_KEY')]) {
                    echo 'Applying Database Schema...'

                    bat 'icacls "%SSH_KEY%" /inheritance:r /grant "%USERNAME%:F"'

                    bat """
                    ssh -i "%SSH_KEY%" -o StrictHostKeyChecking=no %EC2_USER%@%EC2_IP% "mysql -u%DB_USER% -p%DB_PASS% -e 'CREATE DATABASE IF NOT EXISTS %DB_NAME%'"
                    """

                    bat """
                    ssh -i "%SSH_KEY%" -o StrictHostKeyChecking=no %EC2_USER%@%EC2_IP% "mysql -u%DB_USER% -p%DB_PASS% %DB_NAME% < /var/www/html/cinesphere/database.sql"
                    """
                }
            }
        }

        stage('Smoke Test') {
            steps {
                echo 'Verifying deployment...'
                bat 'curl -I http://%EC2_IP%/cinesphere'
            }
        }
    }

    post {
        success {
            echo 'Deployment successful!'
        }
        failure {
            echo 'Deployment failed. Check logs and SSH connectivity.'
        }
    }
}
