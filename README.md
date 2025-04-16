# REFLEXIO
This web-based application enhances user productivity by integrating goal setting, habit tracking, and journaling into a unified platform. It enables users to efficiently monitor 
personal growth and daily activities.
## Features
- **User Registration & Authentication**: Users create secure personal accounts with hashed passwords, ensuring privacy. Each user can log in/out freely and manage only their own goals, habits, and journal entries.
- **Goal page**: Users can create and manage goals by adding a title, description, priority, and deadline. A progress bar visually tracks completion. Goals can be updated or deleted as needed.
- **Journal Page**: Users can document their thoughts, reflections, or daily summaries. Each entry has a title and description and can be edited or deleted, providing flexibility in personal expression.
- **Habit Page**: Users set habits with a title and a monthly frequency target. They can mark completed days, and track their consistency. Habits can also be edited or deleted, as their routines change.
## Technologies Choices
### FRONT-END TECHNOLOGIES:
- **HTML**: For structuring the web pages.
- **CSS and Tailwind CSS**: For styling and ensuring a responsive and modern UI.
- **JavaScript**: For adding interactivity, handling UI logic, and making API requests (e.g., form submission, fetching data, requesting and sending data from and to back-end).
- **Google Fonts**: For customized typography to enhance aesthetics.
### BACK-END TECHNOLOGIES:
- **PHP**: Handles server-side logic, processes form data, performs CRUD operations, and manages user sessions.
- **MySQL**: The relational database used to store user data, including goals, journals entries, habits, and authentication information.
- **XAMPP**: A local development environment using Apache, MySQL, and PHP for testing and running the web app. 
## How to Successfully initialize the server:
To successfully run the web application locally, follow these steps:
 1. **Download and Install XAMPP**: Visit the official XAMPP website (https://www.apachefriends.org/). Download the version compatible with your operating system, Open the downloaded .exe file and follow the installation instructions. Select all components during setup to ensure Apache, MySQL, PHP, and other necessary tools are installed. When prompted for the installation folder, use the default folder: C:\xampp.
 2. **Set Up the Project Files**: Download the Reflexio project folder from GitHub. Unzip the folder and move the folder to the following directory on your computer: C:/xampp/htdocs. This will allow the project to be hosted on XAMPP virtual server.
 3. **Start Required Services**: Open the XAMPP Control Panel and start both the Apache and MySQL modules by clicking the "Start" buttons next to each service.
 4. **Import the Database**: Open a web browser and go to http://localhost/phpmyadmin. Create a new database (e.g., reflexio) and import the SQL file provided in the GitHub repository to set up the necessary tables and data. This can be done using the Import tab in phpMyAdmin.
5. **Launch the Web Application**: Open a web browser and enter the following URL: http://localhost/reflexio-main/html/index.html if you saved the folder in htdocs. The home page of the application should now be displayed.
