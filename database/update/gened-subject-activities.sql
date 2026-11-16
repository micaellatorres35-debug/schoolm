CREATE TABLE IF NOT EXISTS gc_subject_activities (
    id INT NOT NULL AUTO_INCREMENT,
    subject_id INT NOT NULL,
    section_id INT NOT NULL,
    school_year_id INT NOT NULL,
    semester_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    category VARCHAR(100) NOT NULL,
    exam_period VARCHAR(30) NULL,
    activity_date DATE NOT NULL,
    status ENUM('Pending Approval', 'Approved', 'Rejected', 'Completed') NOT NULL DEFAULT 'Pending Approval',
    submitted_by INT NOT NULL,
    decided_by INT NULL,
    decision_note TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_gc_subject_activities_period (school_year_id, semester_id),
    KEY idx_gc_subject_activities_subject (subject_id, section_id),
    KEY idx_gc_subject_activities_status (status),
    KEY idx_gc_subject_activities_date (activity_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS gc_subject_activity_faculty (
    activity_id INT NOT NULL,
    faculty_id INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (activity_id, faculty_id),
    KEY idx_gc_subject_activity_faculty_faculty (faculty_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
