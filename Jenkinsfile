pipeline {
    agent any

    environment {
        DB_NAME = 'cinesphere_db'
        DB_USER = 'root'
        DB_PASS = credentials('db-password')
        EC2_USER = 'ubuntu'
        EC2_IP = 'your-ec2-public-ip'
        SSH_KEY_ID = 'aws-ssh-key'
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
                sshagent([SSH_KEY_ID]) {
                    echo "Deploying to %EC2_IP%..."

                    bat """
                    ssh -o StrictHostKeyChecking=no %EC2_USER%@%EC2_IP% "sudo mkdir -p /var/www/html/cinesphere"
                    """

                    bat """
                    scp -o StrictHostKeyChecking=no -r * %EC2_USER%@%EC2_IP%:/var/www/html/cinesphere
                    """

                    bat """
                    ssh -o StrictHostKeyChecking=no %EC2_USER%@%EC2_IP% "sudo chown -R www-data:www-data /var/www/html/cinesphere"
                    """
                }
            }
        }

        stage('Database Migration') {
            steps {
                sshagent([SSH_KEY_ID]) {
                    echo 'Applying Database Schema...'

                    bat """
                    ssh -o StrictHostKeyChecking=no %EC2_USER%@%EC2_IP% "mysql -u%DB_USER% -p%DB_PASS% -e \\"CREATE DATABASE IF NOT EXISTS %DB_NAME%\\""
                    """

                    bat """
                    ssh -o StrictHostKeyChecking=no %EC2_USER%@%EC2_IP% "mysql -u%DB_USER% -p%DB_PASS% %DB_NAME% < /var/www/html/cinesphere/database.sql"
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
