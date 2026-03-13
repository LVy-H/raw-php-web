DROP TABLE IF EXISTS notes;
DROP TABLE IF EXISTS submissions;
DROP TABLE IF EXISTS practices;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(32) NOT NULL,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    role VARCHAR(32) NOT NULL DEFAULT 'student' CHECK(role IN ('teacher', 'student'))
);

CREATE TABLE practices (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL UNIQUE,
    uploaded_by INTEGER NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

CREATE TABLE submissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    practice_id INTEGER NOT NULL,
    student_id INTEGER NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL UNIQUE,
    submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (practice_id) REFERENCES practices(id),
    FOREIGN KEY (student_id) REFERENCES users(id),
    UNIQUE(practice_id, student_id)
);

CREATE TABLE notes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    profile_user_id INTEGER NOT NULL,
    writer_user_id INTEGER NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (profile_user_id) REFERENCES users(id),
    FOREIGN KEY (writer_user_id) REFERENCES users(id)
);

INSERT INTO users (name, email, phone, username, password, role) VALUES
('Alice Nguyen', 'alice.teacher@class.local', '0900000001', 'alice.teacher', '$2y$12$jfS7ODVvFLmiBB66ZjpcXO04VGB69uaWfDzQKLTZy.V8IF3MEI0k2', 'teacher'),
('Bob Tran', 'bob.teacher@class.local', '0900000002', 'bob.teacher', '$2y$12$0Xk52NhUy3a3UQWOxfLlt.tMXOp6KNBMXpWW2rBK5383P8GqX27Gu', 'teacher'),
('Charlie Student', 'charlie.student@class.local', '0900000101', 'charlie.student', '$2y$12$bAEDQFpCrGCN6nnbEFkjUusU2Ci1X8T3oDiWBAXXGsTjR3V/G9GJG', 'student'),
('Diana Student', 'diana.student@class.local', '0900000102', 'diana.student', '$2y$12$bAEDQFpCrGCN6nnbEFkjUusU2Ci1X8T3oDiWBAXXGsTjR3V/G9GJG', 'student');
