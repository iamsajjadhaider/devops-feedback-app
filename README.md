DevOps Feedback Application
A modern, containerized three-tier web application built with Docker to demonstrate full-stack development and DevOps principles.

Project Overview
This project implements a complete feedback management system with separate frontend, API, and database tiers, all orchestrated using Docker containers. The application allows users to submit feedback and provides an admin interface to manage and filter submissions.

Architecture
devops-feedback-app/
├── Dockerfile.frontend        # Frontend container definition
├── Dockerfile.api             # API container definition  
├── docker-compose.yml         # Multi-container orchestration
├── README.md                  # Project documentation
│
├── frontend/                  # User Interface Layer
│   ├── index.php              # Feedback submission form
│   └── admin.php              # Admin dashboard
│
├── api/                       # Business Logic Layer
│   └── index.php              # REST API endpoints
│
└── db/                        # Data Layer
    └── init.sql               # Database schema & seed data

Technology Stack
Frontend: PHP, HTML, Tailwind CSS, JavaScript
Backend: PHP, Nginx/PHP-FPM
Database: MySQL 8.0
Containerization: Docker, Docker Compose
Networking: Custom bridge network

Quick Start
Prerequisites
- Docker Engine 20.10+
- Docker Compose 2.0+

Installation & Deployment
Clone and navigate to the project:

bash
git clone <repository-url>
cd devops-feedback-app

Start the application:

bash
docker-compose up -d

Verify services are running:

bash
docker-compose ps

Access the application:
Frontend: http://localhost:8080
API: http://localhost:8081
Admin Panel: http://localhost:8080/admin.php

Development
Building Individual Services
Build frontend only:

bash
docker-compose build frontend

Build API only:

bash
docker-compose build api

Viewing Logs
All services:

bash
docker-compose logs

Specific service:

bash
docker-compose logs api
docker-compose logs frontend
docker-compose logs db

Database Operations
Access MySQL shell:

bash
docker-compose exec db mysql -u app_user -papp_password_secure feedback_db

View feedback data:

bash
docker-compose exec db mysql -u app_user -papp_password_secure feedback_db -e "SELECT * FROM feedback;"

API Endpoints
Submit Feedback
POST /index.php
Content-Type: application/json
Body: {"feedback_text": "Your feedback here"}

List Feedback
GET /index.php?action=list&status=new&sort=newest

Query Parameters:
action=list (required)
status (optional): new, in-progress, done
sort (optional): newest, oldest

Configuration
Environment Variables

Frontend Container:
API_URL: Internal API endpoint (http://api)

API Container:
DB_HOST: Database hostname (db)
DB_NAME: Database name (feedback_db)
DB_USER: Database user (app_user)
DB_PASS: Database password

Database Container:
MYSQL_ROOT_PASSWORD: Root password
MYSQL_DATABASE: Default database
MYSQL_USER: Application user
MYSQL_PASSWORD: Application user password

Port Mapping
Frontend: 8080 -> 80
API: 8081 -> 80
Database: 3306 -> 3306

Database Schema
sql
CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    feedback_text TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

Docker Images
Frontend Image (Dockerfile.frontend)
Base: php:8.2-fpm-alpine
Web Server: Nginx
Features: PHP MySQL extensions, optimized Nginx config

API Image (Dockerfile.api)
Base: php:8.2-fpm-alpine
Web Server: Nginx
Features: PHP MySQL extensions, CORS support

Database Image
Base: mysql:8.0
Features: Automatic schema initialization, persistent storage

Operations
Stopping the Application
bash
docker-compose down

Restarting Services
bash
docker-compose restart

Rebuilding with Changes
bash
docker-compose down
docker-compose up -d --build

Cleaning Up
bash
docker-compose down -v

Testing
API Testing

bash
# Test feedback submission
curl -X POST http://localhost:8081/index.php \
  -H "Content-Type: application/json" \
  -d '{"feedback_text":"Test feedback"}'

# Test feedback retrieval
curl "http://localhost:8081/index.php?action=list&status=new"

Container Connectivity

bash
# Test frontend to API connection
docker-compose exec frontend curl http://api/index.php

# Test API to database connection
docker-compose exec api ping db

Production Considerations
Security Enhancements
- Use secrets management for credentials
- Implement HTTPS/TLS encryption
- Configure proper firewall rules
- Regular security updates

Monitoring & Logging
- Centralized logging
- Health check endpoints
- Container monitoring
- Alerting system

Scaling
- Database connection pooling
- Load balancer for frontend
- API rate limiting
- Caching strategies

Contributing
- Fork the repository
- Create a feature branch
- Make your changes
- Test with docker-compose up -d --build
- Submit a pull request

Troubleshooting
Common Issues
Connection refused errors:
- Verify containers: docker-compose ps
- Check logs: docker-compose logs [service]

Database issues:
- Ensure database initialized
- Verify environment variables

API not responding:
- Test API directly: curl http://localhost:8081/test.php
- Check API logs

Debugging Commands

bash
# Check container status
docker-compose ps

# View real-time logs
docker-compose logs -f

# Inspect container network
docker-compose exec frontend ping api

# Check database state
docker-compose exec db mysql -u app_user -papp_password_secure -e "SHOW TABLES;"

License
This project is for educational purposes as part of DevOps learning.

Acknowledgments
Built as a learning exercise for Docker containerization, multi-tier architecture, and full-stack development principles.

Note: This application is designed for educational purposes. For production use, implement additional security measures, monitoring, and backup strategies.
