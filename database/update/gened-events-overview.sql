ALTER TABLE gc_events
    ADD COLUMN event_category VARCHAR(64) NULL AFTER event_type,
    ADD COLUMN archived_at DATETIME NULL DEFAULT NULL AFTER decision_note,
    MODIFY COLUMN approval_status ENUM('Draft', 'Pending Approval', 'Approved', 'Rejected', 'Completed')
        NOT NULL DEFAULT 'Approved',
    ADD KEY idx_gc_events_archived_at (archived_at);
