CREATE TABLE IF NOT EXISTS gc_item_analysis_settings (
    exam_id INT NOT NULL,
    subject_id INT NOT NULL,
    total_items INT UNSIGNED NOT NULL,
    passing_score DECIMAL(7,2) UNSIGNED NOT NULL,
    updated_by INT NOT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (exam_id, subject_id),
    KEY idx_gc_item_analysis_settings_subject (subject_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS gc_item_analysis_scores (
    exam_schedule_id INT NOT NULL,
    student_id INT NOT NULL,
    score DECIMAL(7,2) UNSIGNED NOT NULL,
    entered_by INT NOT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (exam_schedule_id, student_id),
    KEY idx_gc_item_analysis_scores_student (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
