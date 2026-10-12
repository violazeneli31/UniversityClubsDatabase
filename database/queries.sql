-- =====================================================================
-- Epoka Clubs Database: 25 managerial queries
-- Run after epoka_clubs_frontend_setup.sql (MySQL / MariaDB).
-- =====================================================================
USE epoka_clubs;

-- 1. Students with the role President in any of the clubs
SELECT s.StFName AS FirstName, s.StLName AS LastName, c.CName AS ClubName
FROM Students s, isMemberOf im, Club c
WHERE s.StID = im.StID AND im.CID = c.CID AND im.RoleForClub = 'President';

-- 2. Clubs with more than 500 participants, ordered by name
SELECT CName AS ClubName, CNrOfParticipants AS Participants
FROM Club
WHERE CNrOfParticipants > 500
ORDER BY CName ASC;

-- 3. Events organized by clubs founded before 2020, from the latest event date
SELECT e.EName AS EventName, c.CName AS ClubName, e.EDate AS EventDate
FROM Event e, Club c
WHERE e.CID = c.CID AND c.CFoundDate < '2020-01-01'
ORDER BY e.EDate DESC;

-- 4. Average attendance for each role in clubs
SELECT RoleForClub AS Role, AVG(Attendance) AS AverageAttendance
FROM isMemberOf
GROUP BY RoleForClub;

-- 5. Advisors and the clubs they supervise
SELECT a.AFirstName AS FirstName, a.ALastName AS LastName, c.CName AS ClubName
FROM Advisor a, Supervises s, Club c
WHERE a.AID = s.AID AND s.CID = c.CID
ORDER BY a.AFirstName ASC;

-- 6. Events with more than 6 participants
SELECT e.EName AS EventName, COUNT(pi.StID) AS TotalParticipants
FROM Event e, participatesIn pi
WHERE e.EID = pi.EID
GROUP BY e.EName
HAVING COUNT(pi.StID) > 6;

-- 7. Top 3 clubs with the most participants and their supervisors
SELECT c.CName AS ClubName, c.CNrOfParticipants AS TotalParticipants,
       a.AFirstName AS SupervisorFirstName, a.ALastName AS SupervisorLastName
FROM Club c, Supervises s, Advisor a
WHERE c.CID = s.CID AND s.AID = a.AID
ORDER BY c.CNrOfParticipants DESC
LIMIT 3;

-- 8. Student with the highest attendance in each club (that has members)
SELECT c.CID AS ClubID, c.CName AS ClubName, s.StID AS StudentID,
       s.StFName AS StudentFirstName, s.StLName AS StudentLastName,
       im.Attendance AS HighestAttendance
FROM isMemberOf im
JOIN Students s ON im.StID = s.StID
JOIN Club c ON im.CID = c.CID
WHERE im.Attendance = (SELECT MAX(im2.Attendance)
                       FROM isMemberOf im2
                       WHERE im2.CID = im.CID)
ORDER BY c.CName;

-- 9. Clubs with events sponsored by Google
SELECT c.CName AS ClubName, e.EName AS EventName, e.EDate AS EventDate
FROM Club c, Event e
WHERE c.CID = e.CID AND e.ESponsor LIKE '%Google%'
ORDER BY e.EDate DESC;

-- 10. Students in clubs with an above-average number of participants
SELECT s.StFName AS StudentFirstName, s.StLName AS StudentLastName, c.CName AS ClubName
FROM Students s
JOIN isMemberOf im ON s.StID = im.StID
JOIN Club c ON im.CID = c.CID
WHERE c.CNrOfParticipants > (SELECT AVG(CNrOfParticipants) FROM Club)
ORDER BY s.StFName, s.StLName;

-- 11. Students participating in events organized by the clubs they are members of
SELECT s.StID AS StudentID, s.StFName AS StudentFirstName, s.StLName AS StudentLastName,
       e.EName AS EventName
FROM Students s
JOIN participatesIn pi ON s.StID = pi.StID
JOIN Event e ON pi.EID = e.EID
WHERE e.CID IN (SELECT im.CID FROM isMemberOf im WHERE im.StID = s.StID)
ORDER BY s.StFName;

-- 12. All student departments
SELECT DISTINCT StDepartment AS Department
FROM Students;

-- 13. Students in clubs with more participants than the average club
SELECT s.StID, s.StFName, s.StLName, c.CName AS ClubName
FROM Students s
JOIN isMemberOf im ON s.StID = im.StID
JOIN Club c ON im.CID = c.CID
WHERE c.CNrOfParticipants > (SELECT AVG(CNrOfParticipants) FROM Club);

-- 14. Clubs with or without events
SELECT c.CID AS ClubID, c.CName AS ClubName, e.EName AS EventName
FROM Club c
LEFT JOIN Event e ON c.CID = e.CID;

-- 15. Students who are both event participants and club members
SELECT s.StID AS StudentID, s.StFName AS FirstName, s.StLName AS LastName
FROM Students s
JOIN participatesIn pi ON s.StID = pi.StID
INTERSECT
SELECT s.StID, s.StFName, s.StLName
FROM Students s
JOIN isMemberOf im ON s.StID = im.StID;

-- 16. Clubs that have members but do not host events
SELECT DISTINCT c.CID AS ClubID, c.CName AS ClubName
FROM Club c
JOIN isMemberOf im ON c.CID = im.CID
LEFT JOIN Event e ON c.CID = e.CID
WHERE e.EID IS NULL;

-- 17. Total members and average attendance for each club (that has members),
--     sorted by average attendance in descending order
SELECT c.CID AS ClubID, c.CName AS ClubName, COUNT(im.StID) AS TotalMembers,
       AVG(im.Attendance) AS AvgAttendance
FROM Club c
JOIN isMemberOf im ON c.CID = im.CID
GROUP BY c.CID, c.CName
ORDER BY AvgAttendance DESC;

-- 18. Total events organized by each club
SELECT c.CID AS ClubID, c.CName AS ClubName, COUNT(e.EID) AS TotalEvents
FROM Club c
LEFT JOIN Event e ON c.CID = e.CID
GROUP BY c.CID, c.CName
ORDER BY TotalEvents DESC;

-- 19. Students in year 1 or year 2, with their club roles and club names
SELECT s.StID AS StudentID, s.StFName AS FirstName, s.StLName AS LastName,
       im.RoleForClub AS Role, c.CName AS ClubName
FROM Students s
JOIN isMemberOf im ON s.StID = im.StID
JOIN Club c ON im.CID = c.CID
WHERE s.StYearOfStudy = 1
UNION
SELECT s.StID, s.StFName, s.StLName, im.RoleForClub, c.CName
FROM Students s
JOIN isMemberOf im ON s.StID = im.StID
JOIN Club c ON im.CID = c.CID
WHERE s.StYearOfStudy = 2
ORDER BY Role;

-- 20. Active clubs (more than 300 participants), using the ActiveClubs view
SELECT *
FROM ActiveClubs;

-- 21. Reports submitted by each Dean of Students employee
SELECT CONCAT(D.DFirstName, ' ', D.DLastName) AS EmployeeName,
       R.RAcademicYear AS AcademicYear, R.RDateSubmitted AS DateSubmitted,
       R.RNrOfEvents AS NumberOfEvents, R.RNrOfClubs AS NumberOfClubs
FROM Report R, DOSEmployee D
WHERE R.DID = D.DID
ORDER BY D.DFirstName;

-- 22. Advisors ordered by the number of clubs they supervise
SELECT CONCAT(A.AFirstName, ' ', A.ALastName) AS AdvisorName,
       COUNT(S.CID) AS NumberOfClubs
FROM Advisor A, Supervises S
WHERE A.AID = S.AID
GROUP BY A.AID, A.AFirstName, A.ALastName
ORDER BY NumberOfClubs DESC;

-- 23. Events that had sponsors, sorted from newest to oldest
SELECT E.EName AS EventName, E.EDate AS EventDate, E.ELocation AS Location,
       E.ESponsor AS Sponsor
FROM Event E
WHERE E.ESponsor IS NOT NULL
ORDER BY E.EDate DESC;

-- 24. Clubs that have no advisor
SELECT C.CName AS ClubName
FROM Club C
LEFT JOIN Supervises S ON C.CID = S.CID
WHERE S.AID IS NULL
ORDER BY C.CID;

-- 25. The advisor(s) supervising the highest number of clubs
SELECT CONCAT(A.AFirstName, ' ', A.ALastName) AS AdvisorName,
       COUNT(S.CID) AS TotalClubsSupervised
FROM Advisor A
JOIN Supervises S ON A.AID = S.AID
GROUP BY A.AID, A.AFirstName, A.ALastName
HAVING COUNT(S.CID) = (SELECT MAX(ClubCount)
                       FROM (SELECT AID, COUNT(CID) AS ClubCount
                             FROM Supervises
                             GROUP BY AID) AS ClubCounts);
