# Agile-Based Movie Recommendation System
## Project Report

### 1. PROJECT OVERVIEW
**Project Title:** Agile-Based Movie Recommendation System Using Azure DevOps and AWS Cloud

**Project Description:**
The Movie Recommendation System is a web-based application that allows users to search, view, and manage movies while receiving personalized recommendations. The system is developed using Agile methodology and managed through Azure DevOps, ensuring efficient planning, tracking, and collaboration. The application includes user authentication, movie browsing, filtering, reviews, and watchlist features. The system is deployed on AWS cloud and integrated with CI/CD tools for continuous development and deployment.

### 2. WORKING PRINCIPLE OF THE SYSTEM
The system works based on a client-server architecture:
- The frontend (UI) interacts with users.
- User requests are sent to the backend (PHP server).
- The backend processes the request and communicates with the database (MySQL).
- Data is retrieved/stored and sent back to the frontend.
- The frontend displays results to the user.

**Example Flow:**
User searches movie → request → backend → database → results → UI display

### 3. SYSTEM FLOW (END-TO-END)
**User Flow:**
1. User opens website
2. Registers/Login
3. Searches for movies
4. Views movie details
5. Adds to watchlist
6. Writes reviews

**Technical Flow:**
User → Frontend (HTML/CSS/JS)
      ↓
Backend (PHP - Apache Server)
      ↓
Database (MySQL)
      ↓
Response back to UI

### 4. SYSTEM ARCHITECTURE
The system follows a 3-tier architecture:

**1. Presentation Layer**
- HTML, CSS, JavaScript
- Displays UI to users

**2. Application Layer**
- PHP
- Handles logic (login, search, recommendation)

**3. Data Layer**
- MySQL Database
- Stores user and movie data

### 5. DATABASE DESIGN
Tables used:
- **Users** (`id`, `name`, `email`, `password`)
- **Movies** (`id`, `title`, `genre`, `rating`, `description`, `image_url`)
- **Reviews** (`id`, `user_id`, `movie_id`, `comment`)
- **Watchlist** (`id`, `user_id`, `movie_id`)

### 6. FRONTEND DETAILS
Built using HTML, CSS, JavaScript.
Pages include:
- Login/Register
- Home Page
- Movie Search
- Movie Details
- Watchlist

### 7. BACKEND DETAILS
Developed using PHP.
Handles:
- User authentication
- Database operations
- Movie search & filtering
- Review system
- Watchlist logic

### 8. AWS USAGE
The application is deployed using AWS cloud services:
- **EC2 Instance:** Hosts the web application
- **Apache Server:** Runs PHP application
- **Public IP:** Access website from browser

**Deployment Steps:**
1. Launch EC2 instance
2. Install Apache, PHP, MySQL
3. Upload project files
4. Run application on server

### 9. CI/CD (JENKINS)
Jenkins is used for automation.
Steps:
1. Pull code from GitHub
2. Deploy to server
3. Automate updates

### 10. SOURCE CODE MANAGEMENT
GitHub repository used. Includes:
- Frontend folder
- Backend folder
- Database scripts
- README file

### 11. TESTING
Testing performed:
- Functional Testing
- Login validation
- Search functionality
- Watchlist feature
