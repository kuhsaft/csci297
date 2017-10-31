-- File: HW07.sql
-- Peter Nguyen

-- Add one new warehouse
INSERT IGNORE INTO warehouse (WhNumb, City, Floors)
VALUES ('WH3', 'Denver', 20);

-- Add five new employees
INSERT IGNORE INTO employee (EmpNumb, WhNumb, Salary, YearHired)
VALUES
  ('E9', 'WH3', 20000, 2001),
  ('E10', 'WH3', 21000, 2002),
  ('E11', 'WH3', 22000, 2003),
  ('E12', 'WH3', 26000, 2004),
  ('E13', 'WH3', 27000, 2005);

-- Show contents of warehouse table
SELECT * FROM warehouse;

-- Show contents of employee table
SELECT * FROM employee;

-- List employee number of each employee making $20,000
SELECT employee.EmpNumb FROM employee
WHERE employee.Salary = 20000;

-- Which cities have employees making over $25000
SELECT DISTINCT warehouse.City FROM employee
INNER JOIN warehouse ON warehouse.WhNumb = employee.WhNumb
WHERE employee.Salary > 25000;

-- What is the total payroll of the Charlotte warehouse
SELECT SUM(employee.Salary) AS TotalPayroll FROM employee
INNER JOIN warehouse ON warehouse.WhNumb = employee.WhNumb
WHERE warehouse.City = 'CHARLOTTE';
