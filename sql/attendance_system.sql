CREATE DATABASE IF NOT EXISTS attendance_system;
USE attendance_system;

------------------------------
-- STUDENTS TABLE
------------------------------
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    rfid_code VARCHAR(50) UNIQUE NOT NULL,
    absences INT DEFAULT 0
);

------------------------------
-- ATTENDANCE TABLE
------------------------------
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    date DATE NOT NULL,
    time_in TIME DEFAULT NULL,
    status ENUM('present','absent') DEFAULT 'absent',
    late INT DEFAULT 0,
    FOREIGN KEY (student_id) REFERENCES students(id)
);

------------------------------
-- Example Students
------------------------------
INSERT INTO students (name, rfid_code) VALUES
('Noel', 'A1B2C3D4'),
('Luke', 'E5F6G7H8');
