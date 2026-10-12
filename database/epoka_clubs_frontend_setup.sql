-- =====================================================================
-- Epoka Clubs Database: full setup
-- Creates the epoka_clubs database with all tables, sample data,
-- the website's users table (with 2 demo accounts), indexes and a view.
-- Import this file in phpMyAdmin, or run:  mysql -u root < epoka_clubs_frontend_setup.sql
-- =====================================================================

DROP DATABASE IF EXISTS epoka_clubs;
CREATE DATABASE epoka_clubs;
USE epoka_clubs;

-- ---------------------------------------------------------------------
-- Tables and sample data (including the assignment's UPDATE and DELETE examples)
-- ---------------------------------------------------------------------

CREATE TABLE Advisor(
AID CHAR PRIMARY KEY,
AFirstName VARCHAR(20) NOT NULL,
ALastName VARCHAR(20) NOT NULL,
AEmail VARCHAR(50) NOT NULL,
ADepartment VARCHAR(100) NOT NULL,
AOffice CHAR(4) NOT NULL,
AOfficePhone INT NOT NULL );

INSERT INTO Advisor (AID, AFirstName, ALastName, AEmail, ADepartment, AOffice, AOfficePhone) VALUES
('A', 'Sabrina', 'Begaj', 'sbegaj@epoka.edu.al', 'Computer Engineering', 'E007', 1533),
('B', 'Redjola', 'Manaj', 'manaj@epoka.edu.al', 'Computer Engineering', 'A106', 1551),
('C', 'Andia', 'Tafa', 'atafa@epoka.edu.al', 'Computer Engineering', 'A120', 1578),
('D', 'Valmir', 'Bame', 'vbame@epoka.edu.al', 'Computer Engineering', 'E202', 1670),
('E', 'Ari', 'Gjerazi', 'agjerazi@epoka.edu.al', 'Computer Engineering', 'E108', 1542);

SET SQL_SAFE_UPDATES = 0;
UPDATE Advisor
SET AOfficePhone = 1654
WHERE AOfficePhone = 1551;

CREATE TABLE Club(
CID INT PRIMARY KEY,
CName VARCHAR(100) NOT NULL,
CFoundDate DATE NOT NULL,
CNrOfParticipants INT NOT NULL );

INSERT INTO Club(CID, CName, CFoundDate, CNrOfParticipants) VALUES
(1, 'Film Club', '2018-09-15', 480), 
(2, 'Young Talent Music Club', '2020-10-09', 340),
(3, 'Sports Club', '2015-09-25', 675),
(4, 'Google Developer Club', '2019-10-12', 1198),
(5, 'Future Engineers Club', '2018-11-23', 956),
(6, 'Volunteer Club', '2016-09-28', 1043),
(7, 'Diplomacy Club', '2023-10-17', 180),
(8, 'Young Economists Network', '2022-09-29', 215),
(9, 'Finance Club', '2017-11-16', 940),
(10, 'Programming Club', '2016-10-21', 1062),
(11, 'Electronics Club', '2018-11-13', 867),
(12, 'Innovators Club', '2022-11-29', 657),
(13, 'Marketing Zone Club', '2019-10-12', 1035),
(14, 'Sensitive Society Club', '2023-10-05', 726),
(15, 'Iustitia Club', '2018-11-23', 740),
(16, 'Ncep Club', '2022-10-16', 320),
(17, 'Multimedia Club', '2017-10-21', 527),
(18, 'Book Lovers Club', '2021-09-15', 614),
(19, 'Theater Club', '2021-09-15', 462),
(20, 'Podcast And Journal Club', '2024-10-24', 315),
(21, 'Human Rights Defender Club', '2024-10-19', 214),
(22, 'Art Club', '2017-10-20', 1010),
(23, 'Womens Leadership Club', '2021-10-27', 826),
(24, 'Robotic Club', '2024-11-02', 769),
(25, 'Outdoors Club', '2018-10-31', 942);

CREATE TABLE Event(
EID CHAR PRIMARY KEY,
EName VARCHAR(100) NOT NULL,
EDATE DATE NOT NULL,
ELocation VARCHAR(100) NOT NULL,
EDescription VARCHAR(250) NOT NULL,
ESponsor VARCHAR(100),
CID INT,
FOREIGN KEY (CID) REFERENCES Club(CID) ON DELETE CASCADE ON UPDATE RESTRICT
);

INSERT INTO Event(EID, EName, EDate, ELocation, EDescription, ESponsor, CID) VALUES
('a', 'Movie Screening: The Truman Show', '2023-01-19', 'Conference Hall', 'Movie Screening next week', NULL, 1),
('b', 'Art Week', '2023-03-23', 'Epoka Campus', 'Showcasing local artists', NULL, 22),
('c', 'Spring Fest', '2023-05-24', 'Epoka Campus', 'Annual University Festival', 'Epoka University', 2),
('d', 'Club Fest', '2024-11-04', 'Epoka Campus', 'Get to know all the clubs offered at Epoka', 'x bank', 2),
('e', 'DevFest', '2024-12-22', 'Pyramid of Tirana', 'A meetup for tech enthusiasts', 'Google', 4),
('f', 'Football League', '2024-10-15', 'Soccer Field', 'All interested should apply for the teams', NULL, 3),
('g', 'Poetry Competition', '2023-03-15', 'A101', 'Call for all interested', NULL, 22);


DELETE FROM Event
WHERE EID='g';

CREATE TABLE DOSEmployee (
DID VARCHAR(4) PRIMARY KEY,
DFirstName VARCHAR(25) NOT NULL,
DLastName VARCHAR(25)NOT NULL,
DRole VARCHAR(50) NOT NULL,
DEmail VARCHAR(77) NOT NULL,
DOffice CHAR(4)NOT NULL,
DOfficePhone INT
);

INSERT INTO DOSEmployee (DID, DFirstName, DLastName, Drole, DEmail, DOffice, DOfficePhone) VALUES
('AA01','Sadullah', 'Yilmaz', 'Dean of Students', 'syilmaz@epoka.edu.al', 'A001', 111222333),
('AB02', 'Naida', 'Ertekin', 'Vice Dean of Students', 'nertekin@epoka.edu.al', 'E005', 123345567),
('AC03', 'Jonida', 'Sota', 'Coordinator', 'jsota@epoka.edu.al', 'E008', 432578975),
('AD04', 'Kelima', 'Hima', 'Career Planning and Alumni Specialist', 'khima@epoka.edu.al', 'D104', 754231821),
('AE05', 'Jona', 'Kaso', 'Student Clubs and Activities Specialist', 'jkaso@epoka.edu.al', 'E002', 512721910);

CREATE TABLE Report(
RAcademicYear CHAR(9) PRIMARY KEY,
RDateSubmitted DATE NOT NULL,
RNrOfEvents INT NOT NULL,
RNrOfClubs INT NOT NULL,
DID VARCHAR(4) NOT NULL,
FOREIGN KEY (DID) REFERENCES DOSEmployee (DID) ON DELETE CASCADE ON UPDATE RESTRICT );

INSERT INTO Report(RAcademicYear, RDateSubmitted, RNrOfEvents, RNrOfClubs, DID) VALUES
('2017-2018', '2018-07-27', 34, 10, 'AB02'),
('2018-2019', '2019-08-26', 25, 13, 'AA01'),
('2019-2020', '2020-08-06', 33, 20, 'AC03'),
('2021-2022', '2022-07-21', 26, 22, 'AE05'),
('2022-2023', '2023-01-20', 45, 24, 'AD04');

UPDATE Report
SET RNrOfEvents=10
WHERE RNrOfEvents=26;

CREATE TABLE Supervises(
AID CHAR NOT NULL,
CID INT NOT NULL,
StartYear INT NOT NULL,
EndYear INT,
 PRIMARY KEY (AID, CID),
 FOREIGN KEY (AID) REFERENCES Advisor (AID) ON DELETE CASCADE ON UPDATE RESTRICT,
 FOREIGN KEY (CID) REFERENCES Club (CID) ON DELETE CASCADE ON UPDATE RESTRICT );

INSERT INTO Supervises (AID, CID, StartYear, EndYear) VALUES
('A', 5, 2021, NULL),
('C', 18, 2018, 2022),
('E', 9, 2023, NULL),
('B', 11, 2019, 2023),
('C', 7, 2024, NULL);

CREATE TABLE Students
(StID CHAR(5) NOT NULL,
StFName VARCHAR (25) NOT NULL,
StLName VARCHAR (25) NOT NULL,
StDepartment VARCHAR (5) NOT NULL,
StEmail VARCHAR (40) NOT NULL,
StPhoneNumber VARCHAR(10) NOT NULL,
StYearOfStudy INT (1) NOT NULL, 
PRIMARY KEY (StID) );

INSERT INTO Students(StID, StFName, StLName, StDepartment, StEmail, StPhoneNumber, StYearOfStudy) VALUES
('12345','Noel','Zani','SWE','nzani23@epoka.edu.al','0695436572','2'),
('11223','Viola','Zeneli','SWE','vzeneli23@epoka.edu.al','0693453496','2'),
('22453','Teddy','Daniels','BINF','tdaniels22@epoka.edu.al','0671253675','3'),
('25341','Nikol','Dalipi','SWE','ndalipi23@epoka.edu.al','0673916235','1'),
('12546','James','Conway','CEN','jconway24@epoka.edu.al','0691890744','1'),
('32546','Sidrit','Rrustemi','SWE','srrustemi23@epoka.edu.al','0695722508','2'),
('26743','Suada','Terolli','SWE','sterolli23@epoka.edu.al','0675647890','1'),
('19364','Joe','Pesci','IML','jpesci22@epoka.edu.al','0682501370','3'),
('37628','Alesia','Dardha','SWE','adardha23@epoka.edu.al','0691096341','2'),
('46378','Henry','Hill','CEN','hhill22@epoka.edu.al','0690749216','3');

CREATE TABLE isMemberOf
(StID CHAR (5) NOT NULL,
CID INT NOT NULL,
RoleForClub VARCHAR (15) NOT NULL,
YearOfEnrollment INT (4) NOT NULL,
Attendance   INT (3) NOT NULL,                    
PRIMARY KEY (StID, CID),
FOREIGN KEY (StId) REFERENCES Students (StId) ON DELETE CASCADE ON UPDATE RESTRICT,
FOREIGN KEY (CID) REFERENCES Club (CID) ON DELETE CASCADE ON UPDATE RESTRICT);

INSERT INTO isMemberOf(StID, CID, RoleForClub, YearOfEnrollment, Attendance) VALUES
('25341','2','President','2024','100'),
('12546','2','VicePresident','2024','96'),
('37628','2','Member','2023','87'),
('26743','2','Member','2024','50'),
('11223','2','Secretary','2024','98'),
('12345','2','Treasurer','2023','90'),
('32546','2','Member','2024','95'),
('22453','2','Member','2022','68'),
('46378','5','Member','2022','78'),
('12345','5','President','2023','100'),
('19364','5','Member','2022','67'),
('11223','5','Treasurer','2023','70'),
('22453','5','VicePresident','2022','94'),
('26743','5','Member','2024','88'),
('32546','5','Secretary','2023','90');

CREATE TABLE participatesIn(
StID CHAR (5) NOT NULL,
EID CHAR NOT NULL,
RoleForEvent VARCHAR (15) NOT NULL,
PRIMARY KEY (StID,EID),
FOREIGN KEY (StID) REFERENCES Students (StID) ON DELETE CASCADE ON UPDATE RESTRICT,
FOREIGN KEY (EID) REFERENCES Event (EID) ON DELETE CASCADE ON UPDATE RESTRICT);

INSERT INTO participatesIn(StID, EID, RoleForEvent)VALUES
('32546','a','Organizator'),
('26743','a','Participant'),
('19364','a','Participant'),
('12345','a','Participant'),
('25341','a','Participant'),
('46378','a','Participant'),
('11223','a','Participant'),
('46378','c','Participant'),
('26743','c','Participant'),
('11223','c','Organizator'),
('22453','c','Participant'),
('37628','c','Participant'),
('19364','c','Participant');


-- ---------------------------------------------------------------------
-- Website login accounts (used by the PHP front end)
-- Two demo accounts are included (passwords are stored as bcrypt hashes):
--   viola / Viola123!   (role: admin, can manage users and run any SQL)
--   mateo / Mateo123!   (role: user)
-- More accounts can be created on the website's Sign Up page. New accounts
-- get the "user" role; to make an account an admin, run:
--   UPDATE users SET role = 'admin' WHERE username = 'your_username';
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(30) NOT NULL,
    surname VARCHAR(30) NOT NULL,
    email VARCHAR(40) NOT NULL,
    gender VARCHAR(6) NOT NULL,
    username VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(10) NOT NULL
);

INSERT INTO users (name, surname, email, gender, username, password, role) VALUES
('Viola', 'Zeneli', 'vzeneli23@epoka.edu.al', 'Female', 'viola', '$2y$10$ErJBHSx/pn0AIb0zwS3YJORvb7DaOKV2qTJD0AUEU1U9LPW0m3cPO', 'admin'),
('Mateo', 'Zeneli', 'mzeneli25@epoka.edu.al', 'Male', 'mateo', '$2y$10$YQuCL4aDzAw40bbiE8CyCuvrqxHrIM/DHOUwicGaDPAeR9WSaFdq.', 'user');

-- ---------------------------------------------------------------------
-- Indexes (to speed up SELECT queries)
-- ---------------------------------------------------------------------
-- Indexes for Advisor
CREATE INDEX idx_advisor_aid ON Advisor(AID);
CREATE INDEX idx_advisor_afirstname ON Advisor(AFirstName);
CREATE INDEX idx_advisor_alastname ON Advisor(ALastName);
CREATE INDEX idx_advisor_aemail ON Advisor(AEmail);
CREATE INDEX idx_advisor_adepartment ON Advisor(ADepartment);
CREATE INDEX idx_advisor_aoffice ON Advisor(AOffice);
CREATE INDEX idx_advisor_aofficephone ON Advisor(AOfficePhone);
-- Indexes for Supervises
CREATE INDEX idx_supervises_aid ON Supervises(AID);
CREATE INDEX idx_supervises_cid ON Supervises(CID);
CREATE INDEX idx_supervises_startyear ON Supervises(StartYear);
CREATE INDEX idx_supervises_endyear ON Supervises(EndYear);
-- Indexes for Students
CREATE INDEX idx_students_stid ON Students(StID);
CREATE INDEX idx_students_stfname ON Students(StFName);
CREATE INDEX idx_students_stlname ON Students(StLName);
CREATE INDEX idx_students_stdepartment ON Students(StDepartment);
CREATE INDEX idx_students_stemail ON Students(StEmail);
CREATE INDEX idx_students_stphonenumber ON Students(StPhoneNumber);
CREATE INDEX idx_students_styearofstudy ON Students(StYearOfStudy);
-- Indexes for isMemberOf
CREATE INDEX idx_ismemberof_stid ON isMemberOf(StID);
CREATE INDEX idx_ismemberof_cid ON isMemberOf(CID);
CREATE INDEX idx_ismemberof_roleforclub ON isMemberOf(RoleForClub);
CREATE INDEX idx_ismemberof_yearofenrollment ON isMemberOf(YearOfEnrollment);
CREATE INDEX idx_ismemberof_attendance ON isMemberOf(Attendance);
-- Indexes for participatesIn
CREATE INDEX idx_participatesin_stid ON participatesIn(StID);
CREATE INDEX idx_participatesin_eid ON participatesIn(EID);
CREATE INDEX idx_participatesin_roleforevent ON participatesIn(RoleForEvent);
-- Indexes for Club
CREATE INDEX idx_club_cid ON Club(CID);
CREATE INDEX idx_club_cname ON Club(CName);
CREATE INDEX idx_club_cfounddate ON Club(CFoundDate);
CREATE INDEX idx_club_cnrofparticipants ON Club(CNrOfParticipants);
-- Indexes for Event
CREATE INDEX idx_event_eid ON Event(EID);
CREATE INDEX idx_event_ename ON Event(EName);
CREATE INDEX idx_event_edate ON Event(EDate);
CREATE INDEX idx_event_elocation ON Event(ELocation);
CREATE INDEX idx_event_edescription ON Event(EDescription);
CREATE INDEX idx_event_esponsor ON Event(ESponsor);
CREATE INDEX idx_event_cid ON Event(CID);
-- Indexes for DOSEmployee
CREATE INDEX idx_dosemployee_did ON DOSEmployee(DID);
CREATE INDEX idx_dosemployee_dfirstname ON DOSEmployee(DFirstName);
CREATE INDEX idx_dosemployee_dlastname ON DOSEmployee(DLastName);
CREATE INDEX idx_dosemployee_drole ON DOSEmployee(DRole);
CREATE INDEX idx_dosemployee_demail ON DOSEmployee(DEmail);
CREATE INDEX idx_dosemployee_doffice ON DOSEmployee(DOffice);
CREATE INDEX idx_dosemployee_dofficephone ON DOSEmployee(DOfficePhone);
-- Indexes for Report
CREATE INDEX idx_report_racademicyear ON Report(RAcademicYear);
CREATE INDEX idx_report_rdatesubmitted ON Report(RDateSubmitted);
CREATE INDEX idx_report_rnrofevents ON Report(RNrOfEvents);
CREATE INDEX idx_report_rnrofclubs ON Report(RNrOfClubs);
CREATE INDEX idx_report_did ON Report(DID);

-- ---------------------------------------------------------------------
-- View: active clubs (more than 300 participants), used by query 20
-- ---------------------------------------------------------------------
CREATE OR REPLACE VIEW ActiveClubs AS
SELECT CID, CName, CNrOfParticipants
FROM Club
WHERE CNrOfParticipants > 300;
