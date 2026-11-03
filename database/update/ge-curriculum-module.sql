ALTER TABLE rgr_subjects
    ADD COLUMN description TEXT NULL AFTER name;

CREATE TABLE IF NOT EXISTS enr_prerequisites (
    id INT NOT NULL AUTO_INCREMENT,
    subject_id INT NOT NULL,
    prerequisite_subject_id INT NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_enr_prerequisites_subject_pair (subject_id, prerequisite_subject_id),
    KEY idx_enr_prerequisites_required_subject (prerequisite_subject_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;