ALTER TABLE em_certifications
    ADD COLUMN archived_at DATETIME NULL,
    ADD COLUMN certificate_path VARCHAR(255) NULL;

ALTER TABLE cc_certification_engagements
    ADD COLUMN certificate_path VARCHAR(255) NULL;