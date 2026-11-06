ALTER TABLE gc_events
    ADD COLUMN event_scope VARCHAR(30) NULL AFTER target_audience,
    ADD COLUMN approval_status ENUM('Pending Approval', 'Approved', 'Rejected', 'Completed')
        NOT NULL DEFAULT 'Approved' AFTER status,
    ADD COLUMN submitted_by INT NULL AFTER approval_status,
    ADD COLUMN decided_by INT NULL AFTER submitted_by,
    ADD COLUMN decision_note TEXT NULL AFTER decided_by,
    ADD KEY idx_gc_events_approval_status (approval_status),
    ADD KEY idx_gc_events_date_approval (event_date, approval_status);

UPDATE gc_events
SET approval_status = 'Completed'
WHERE status = 'completed';
