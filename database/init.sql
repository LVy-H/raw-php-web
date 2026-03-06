DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(32) NOT NULL,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(32) NOT NULL DEFAULT 'student' CHECK(role IN ('teacher', 'student'))
);

INSERT INTO users (name, email, phone, username, password, role) VALUES
('Alice Nguyen', 'alice.teacher@class.local', '0900000001', 'alice.teacher', '$2y$12$jfS7ODVvFLmiBB66ZjpcXO04VGB69uaWfDzQKLTZy.V8IF3MEI0k2', 'teacher'),
('Bob Tran', 'bob.teacher@class.local', '0900000002', 'bob.teacher', '$2y$12$0Xk52NhUy3a3UQWOxfLlt.tMXOp6KNBMXpWW2rBK5383P8GqX27Gu', 'teacher'),
('Charlie Student', 'charlie.student@class.local', '0900000101', 'charlie.student', '$2y$12$bAEDQFpCrGCN6nnbEFkjUusU2Ci1X8T3oDiWBAXXGsTjR3V/G9GJG', 'student'),
('Diana Student', 'diana.student@class.local', '0900000102', 'diana.student', '$2y$12$bAEDQFpCrGCN6nnbEFkjUusU2Ci1X8T3oDiWBAXXGsTjR3V/G9GJG', 'student');
