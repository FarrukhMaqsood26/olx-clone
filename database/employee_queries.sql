DROP TABLE IF EXISTS Employees;
DROP TABLE IF EXISTS Departments;

CREATE TABLE Departments (
    id INT PRIMARY KEY,
    dept_name VARCHAR(100) NOT NULL
);

CREATE TABLE Employees (
    id INT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    salary INT,
    age INT,
    department_id INT,
    status VARCHAR(50) DEFAULT 'Active',
    manager_id INT,
    FOREIGN KEY (department_id) REFERENCES Departments(id),
    FOREIGN KEY (manager_id) REFERENCES Employees(id) ON DELETE SET NULL
);

INSERT INTO Departments (id, dept_name) VALUES 
(1, 'IT'), 
(2, 'HR'), 
(3, 'Finance'), 
(4, 'Sales'), 
(5, 'Marketing');

INSERT INTO Employees (id, name, salary, age, department_id, status, manager_id) VALUES
(1, 'Ali Ahmed', 65000, 30, 1, 'Active', NULL),
(2, 'Ayesha Khan', 55000, 28, 2, 'Active', 1),
(3, 'Bilal Sheikh', 45000, 35, 1, 'Active', 1),
(4, 'Fatima Zahra', 72000, 42, 3, 'Active', NULL),
(5, 'Hamza Malik', 38000, 24, 4, 'Active', 4),
(6, 'Sana Javed', 52000, 31, 2, 'Active', 2),
(7, 'Zainab Bibi', 60000, 27, 1, 'Active', 1),
(8, 'Usman Ghani', 48000, 39, 5, 'Resigned', 4),
(9, 'Amna Pervez', 58000, 33, 3, 'Active', 4),
(10, 'Omer Farooq', 90000, 45, 1, 'Active', NULL),
(11, 'Hina Rabbani', 32000, 26, 4, 'Active', 5),
(12, 'Asad Hussain', 41000, 29, 5, 'Active', 8),
(13, 'Mariam Nawaz', 53000, 34, 2, 'Active', 2),
(14, 'Kamran Akmal', 47000, 37, 4, 'Resigned', 5),
(15, 'Noman Ali', 62000, 32, 1, 'Active', 10),
(16, 'Saad Rafique', 51000, 40, 3, 'Active', 4);

SELECT * FROM Employees;

SELECT DISTINCT d.dept_name FROM Departments d 
JOIN Employees e ON d.id = e.department_id;

SELECT * FROM Employees WHERE salary > 50000;

SELECT * FROM Employees WHERE age BETWEEN 25 AND 40;

SELECT * FROM Employees WHERE name LIKE 'A%';

INSERT INTO Employees (id, name, salary, age, department_id, status, manager_id) 
VALUES (17, 'Faisal Iqbal', 58000, 29, 1, 'Active', 1);

UPDATE Employees SET salary = 60000 WHERE name LIKE 'Ali%';

DELETE FROM Employees WHERE status = 'Resigned';

SELECT * FROM Employees ORDER BY salary DESC LIMIT 5;

SELECT * FROM Employees WHERE manager_id IS NULL;

SELECT COUNT(*) AS total_employees FROM Employees;

SELECT AVG(salary) AS average_salary FROM Employees;

SELECT MAX(salary) AS max_salary, MIN(salary) AS min_salary FROM Employees;

SELECT d.dept_name, SUM(e.salary) AS total_dept_salary
FROM Employees e JOIN Departments d ON e.department_id = d.id
GROUP BY d.dept_name;

SELECT d.dept_name, AVG(e.salary) AS avg_salary
FROM Employees e JOIN Departments d ON e.department_id = d.id
GROUP BY d.dept_name
HAVING AVG(e.salary) > 50000;

SELECT e.* FROM Employees e 
JOIN Departments d ON e.department_id = d.id 
WHERE d.dept_name IN ('IT', 'HR');

SELECT * FROM Employees WHERE name LIKE '%an%';

SELECT * FROM Employees WHERE salary NOT BETWEEN 30000 AND 60000;

SELECT e.name AS employee_name, d.dept_name AS department_name
FROM Employees e
INNER JOIN Departments d ON e.department_id = d.id;

SELECT e1.name AS employee, e2.name AS manager
FROM Employees e1
LEFT JOIN Employees e2 ON e1.manager_id = e2.id;
