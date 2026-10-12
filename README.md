# 🎓 Epoka Clubs Database Management System

A relational database for managing the student clubs of Epoka University (clubs, events, students, advisors and Dean of Students reports), with **25 managerial SQL queries** and a **PHP web interface** for browsing tables, adding records, running queries and managing users.

Built for the Database Management Systems course at Epoka University (2025).

## Project structure

- **`database/`**
  - `epoka_clubs_frontend_setup.sql`: creates the whole database for the website (tables, sample data, the users table with 2 demo accounts, indexes and view)
  - `queries.sql`: the 25 managerial queries
- **`EpokaClubsManagementSystem_FrontEnd/`**: the PHP website
- **`documentation/`**: the project report and presentation, each as an editable file and a PDF, covering the ER diagram, relational schema, normalization, every query with its result, and screenshots of the website
  - `Database Project.docx` / `Database Project.pdf`: the report (Word and PDF)
  - `Database Project Presentation.pptx` / `Database Project Presentation.pdf`: the presentation (PowerPoint and PDF)

## How to run

You need **[XAMPP](https://www.apachefriends.org)** (Apache, MySQL/MariaDB and PHP).

1. Open XAMPP and start **Apache** and **MySQL**.
2. Copy the `EpokaClubsManagementSystem_FrontEnd` folder into XAMPP's `htdocs` folder:
   - Windows: `C:\xampp\htdocs\`
   - macOS: `/Applications/XAMPP/xamppfiles/htdocs/`
3. Open **http://localhost/phpmyadmin**, go to the **Import** tab, choose `database/epoka_clubs_frontend_setup.sql` and click **Import**. This creates the `epoka_clubs` database with all the data.
4. Open **http://localhost/EpokaClubsManagementSystem_FrontEnd/** and log in with one of the accounts below, or click **Sign up** to create your own.

The website connects with XAMPP's default settings (user `root`, no password). If yours are different, change them at the top of the PHP files.

To run only the SQL, without the website: import `database/epoka_clubs_frontend_setup.sql` into any MySQL or MariaDB server, then run the queries from `database/queries.sql`.

### Accounts

The database comes with two demo accounts:

| Name         | Username | Password    | Role  |
|--------------|----------|-------------|-------|
| Viola Zeneli | `viola`  | `Viola123!` | Admin |
| Mateo Zeneli | `mateo`  | `Mateo123!` | User  |

- **Admin:** can do everything, including managing users (User Options in the sidebar) and running any SQL on the New query page.
- **User:** can view tables, add records and run the queries, but can't run `DELETE` or access the users table.

You can also create your own account with **Sign up** on the login page. It needs an `@epoka.edu.al` email and a password of at least 8 characters with 1 uppercase letter, 1 number and 1 symbol. Passwords are stored as hashes, never as plain text. New accounts always get the **user** role.

**To make an account an admin:** open phpMyAdmin, select the `epoka_clubs` database, open the **SQL** tab and run this (with that account's username), then log out and back in:

```sql
UPDATE users SET role = 'admin' WHERE username = 'your_username';
```

## Team

| Member          | Work                                                                    |
|-----------------|-------------------------------------------------------------------------|
| Viola Zeneli    | ER diagram, relational schema, indexes, the 25 managerial queries, joining the SQL |
| Sidrit Rrustemi | Web interface (front end) |
| Nikol Dalipi    | SQL for Advisor and Supervises, UPDATE example                          |
| Noel Zani       | SQL for Students, isMemberOf and participatesIn, normalization example  |
| Suada Terolli   | SQL for DOSEmployee and Report, UPDATE example                          |
| Alesia Dardha   | SQL for Club and Event, DELETE example                                  |
