-- ============================================
-- QApp Smart University Profile
-- Full Database Setup
-- Run this entire file in phpMyAdmin
-- ============================================

CREATE DATABASE IF NOT EXISTS qapp;
USE qapp;

-- ============================================
-- TABLE 1: students (users)
-- ============================================
CREATE TABLE students (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    name               VARCHAR(255) NOT NULL,
    email              VARCHAR(255) NOT NULL UNIQUE,
    password           VARCHAR(255) NOT NULL,
    avatar             VARCHAR(500),
    grade              INT,
    country            VARCHAR(100),
    city               VARCHAR(100),
    gpa                DECIMAL(3,1),
    gpa_max            DECIMAL(3,1) DEFAULT 5.0,
    ielts              DECIMAL(3,1),
    sat                INT,
    interests          VARCHAR(255),
    preferred_language VARCHAR(50),
    needs_scholarship  TINYINT(1) DEFAULT 0,
    preferred_cities   VARCHAR(255),
    goal               VARCHAR(100),
    created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- TABLE 2: universities
-- ============================================
CREATE TABLE universities (
    id                   INT AUTO_INCREMENT PRIMARY KEY,
    name                 VARCHAR(255) NOT NULL,
    name_ru              VARCHAR(255),
    city                 VARCHAR(100) NOT NULL,
    country              VARCHAR(100) NOT NULL DEFAULT 'Kazakhstan',
    founded              YEAR,
    type                 VARCHAR(50),
    languages            VARCHAR(100),
    total_programs       INT,
    min_gpa              DECIMAL(3,1),
    min_ielts            DECIMAL(3,1),
    min_sat              INT,
    application_deadline DATE,
    website              VARCHAR(255),
    email                VARCHAR(255),
    campus_photo         VARCHAR(500),
    description          TEXT,
    created_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- TABLE 3: programs
-- ============================================
CREATE TABLE programs (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    university_id INT NOT NULL,
    name          VARCHAR(255) NOT NULL,
    field         VARCHAR(100),
    degree        VARCHAR(50),
    duration      INT,
    language      VARCHAR(50),
    tuition_per_year INT,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (university_id) REFERENCES universities(id) ON DELETE CASCADE
);

-- ============================================
-- TABLE 4: scholarships
-- ============================================
CREATE TABLE scholarships (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    university_id INT NOT NULL,
    name          VARCHAR(255) NOT NULL,
    description   TEXT,
    covers        VARCHAR(100),
    eligibility   VARCHAR(255),
    min_gpa       DECIMAL(3,1),
    min_ielts     DECIMAL(3,1),
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (university_id) REFERENCES universities(id) ON DELETE CASCADE
);

-- ============================================
-- TABLE 5: deadlines
-- ============================================
CREATE TABLE deadlines (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    university_id INT NOT NULL,
    label         VARCHAR(100),
    date          DATE NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (university_id) REFERENCES universities(id) ON DELETE CASCADE
);

-- ============================================
-- TABLE 6: documents
-- ============================================
CREATE TABLE documents (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    name       VARCHAR(255),
    status     VARCHAR(20),
    file_path  VARCHAR(500),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- ============================================
-- TABLE 7: shortlist
-- ============================================
CREATE TABLE shortlist (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    program_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
);

-- ============================================
-- SAMPLE DATA: 3 Universities
-- ============================================

INSERT INTO universities (name, name_ru, city, country, founded, type, languages, total_programs, min_gpa, min_ielts, min_sat, application_deadline, website, email, campus_photo, description) VALUES
(
    'Nazarbayev University', 'Назарбаев Университет',
    'Astana', 'Kazakhstan', 2010, 'Research', 'English', 40,
    4.0, 6.5, 1200, '2026-05-01',
    'https://nu.edu.kz', 'admissions@nu.edu.kz',
    'https://upload.wikimedia.org/wikipedia/commons/thumb/5/5a/Nazarbayev_University_Main_Building.jpg/1280px-Nazarbayev_University_Main_Building.jpg',
    'Kazakhstan leading research university, offering world-class education fully in English with international faculty.'
),
(
    'Kazakh-British Technical University', 'Казахстанско-Британский Технический Университет',
    'Almaty', 'Kazakhstan', 2001, 'Technical', 'English, Russian', 28,
    3.5, 6.0, 1100, '2026-06-01',
    'https://kbtu.kz', 'admission@kbtu.kz',
    'https://kbtu.kz/images/campus.jpg',
    'Top technical university in Kazakhstan, focused on IT, engineering, and business with strong industry connections.'
),
(
    'Astana IT University', 'Астана АйТи Университет',
    'Astana', 'Kazakhstan', 2019, 'Technical', 'English, Kazakh', 15,
    3.5, 6.0, 1100, '2026-06-15',
    'https://aitu.edu.kz', 'info@aitu.edu.kz',
    'https://aitu.edu.kz/images/campus.jpg',
    'Modern IT-focused university in Astana, built around digital technologies, startups, and innovation.'
);

-- Programs for NU
INSERT INTO programs (university_id, name, field, degree, duration, language) VALUES
(1, 'Computer Science', 'Computer Science', 'Bachelor', 4, 'English'),
(1, 'Software Engineering', 'Engineering', 'Bachelor', 4, 'English'),
(1, 'Business Administration', 'Business', 'Bachelor', 4, 'English'),
(1, 'Data Science', 'Computer Science', 'Bachelor', 4, 'English'),
(1, 'Electrical and Computer Engineering', 'Engineering', 'Bachelor', 4, 'English'),
(1, 'Finance', 'Business', 'Bachelor', 4, 'English'),
(1, 'Civil Engineering', 'Engineering', 'Bachelor', 4, 'English');

-- Programs for KBTU
INSERT INTO programs (university_id, name, field, degree, duration, language) VALUES
(2, 'Information Systems', 'Computer Science', 'Bachelor', 4, 'English'),
(2, 'Cybersecurity', 'Computer Science', 'Bachelor', 4, 'English'),
(2, 'Business and Management', 'Business', 'Bachelor', 4, 'English'),
(2, 'Oil and Gas Engineering', 'Engineering', 'Bachelor', 4, 'Russian'),
(2, 'Automation and Control', 'Engineering', 'Bachelor', 4, 'Russian');

-- Programs for AITU
INSERT INTO programs (university_id, name, field, degree, duration, language) VALUES
(3, 'Artificial Intelligence', 'Computer Science', 'Bachelor', 4, 'English'),
(3, 'Software Development', 'Computer Science', 'Bachelor', 4, 'English'),
(3, 'Digital Business', 'Business', 'Bachelor', 3, 'English'),
(3, 'Cybersecurity and Networks', 'Computer Science', 'Bachelor', 4, 'English'),
(3, 'Game Development', 'Computer Science', 'Bachelor', 3, 'English');

-- Scholarships for NU
INSERT INTO scholarships (university_id, name, description, covers, eligibility, min_gpa, min_ielts) VALUES
(1, 'NU Merit Scholarship', 'Full tuition waiver for top academic achievers', '100% tuition', 'GPA 4.5+ and IELTS 7.0+', 4.5, 7.0),
(1, 'Need-Based Financial Aid', 'Partial support for students with financial need', 'Up to 50% tuition', 'Demonstrated financial need', 3.5, 6.0),
(1, 'Government Grant Bolashak', 'State scholarship for top students', 'Full tuition + stipend', 'Kazakh citizenship, GPA 4.0+', 4.0, 6.5);

-- Scholarships for KBTU
INSERT INTO scholarships (university_id, name, description, covers, eligibility, min_gpa, min_ielts) VALUES
(2, 'KBTU Excellence Award', 'Merit-based scholarship for high-achieving applicants', '75% tuition', 'GPA 4.0+ or SAT 1300+', 4.0, 6.0),
(2, 'IT Industry Scholarship', 'Sponsored by tech companies for CS students', 'Full tuition + internship', 'CS applicants with strong academic record', 4.0, 6.5);

-- Scholarships for AITU
INSERT INTO scholarships (university_id, name, description, covers, eligibility, min_gpa, min_ielts) VALUES
(3, 'Digital Future Grant', 'Full scholarship for students passionate about technology', '100% tuition', 'Strong interest in IT, GPA 4.0+', 4.0, 6.0);

-- Deadlines for NU
INSERT INTO deadlines (university_id, label, date) VALUES
(1, 'Application Opens', '2026-01-15'),
(1, 'Document Submission', '2026-03-01'),
(1, 'Scholarship Deadline', '2026-04-01'),
(1, 'Final Deadline', '2026-05-01'),
(1, 'Admission Decision', '2026-06-15');

-- Deadlines for KBTU
INSERT INTO deadlines (university_id, label, date) VALUES
(2, 'Application Opens', '2026-02-01'),
(2, 'Document Submission', '2026-04-15'),
(2, 'Final Deadline', '2026-06-01'),
(2, 'Admission Decision', '2026-07-01');

-- Deadlines for AITU
INSERT INTO deadlines (university_id, label, date) VALUES
(3, 'Application Opens', '2026-03-01'),
(3, 'Final Deadline', '2026-06-15'),
(3, 'Admission Decision', '2026-07-15');
