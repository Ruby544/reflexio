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
## Installation and Run Instructions:
### Prerequisites:
To host and run the "Reflexio" project locally, you need to install the **XAMPP** virtual server on a Windows machine.
### Steps:
To successfully run the web application locally, follow these steps:
### Download and Install XAMPP: 
1. Visit the official XAMPP website (https://www.apachefriends.org/).
2. Download the version compatible with your operating system.
3. Open the downloaded .exe file and follow the installation instructions.
4. Select all components during setup to ensure Apache, MySQL, PHP, and any other necessary tools are installed.
5. When prompted for the installation folder, use the default folder: C:\xampp.
### **Set Up the Project Files**: 
1. Download the Reflexio project folder from GitHub.
2. Unzip the folder and move the folder to the following directory on your computer: C:/xampp/htdocs. This will allow the project to be hosted on XAMPP virtual server.
3. You can also save the folder in a subdirectory but it must be subdirectory of C:/xampp/htdocs
### Start Required Services: 
1. Open the XAMPP Control Panel. You can click on the searchbar "xampp control panel"
2. Start both the Apache and MySQL modules by clicking the "Start" buttons next to each service. This will start the web server and the datanase.
### Import the Database: 
 1. Open a web browser.
 2. Enter "http://localhost/phpmyadmin" in the address bar.
 3. Create a new database (e.g., reflexio).
 4. Import the SQL file provided in the GitHub repository to set up the necessary tables and data. This can be done using the "Import" tab in phpMyAdmin.
### Launch the Web Application: 
1. Open a new tab in the same web browser or a new web browser.
2. enter the following URL: http://localhost/reflexio-main/html/index.html if you saved the folder in htdocs.
3. If you saved the file in some other folder enter the URL: http://localhost/path to your folder/reflexio-main/html/index.html. 
4. The home page of the application should now be displayed.
