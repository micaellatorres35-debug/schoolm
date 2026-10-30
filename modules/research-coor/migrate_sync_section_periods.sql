-- Migration: synchronize cc_section_faculty and cc_faculty_load academic period values with cc_sections

-- Preview records that would be updated
SELECT
    'cc_section_faculty' AS target_table,
    sf.id AS record_id,
    sf.section_id,
    sf.school_year_id AS current_school_year_id,
    s.school_year_id AS expected_school_year_id,
    sf.semester_id AS current_semester_id,
    s.semester_id AS expected_semester_id
FROM cc_section_faculty sf
JOIN cc_sections s ON sf.section_id = s.id
WHERE sf.school_year_id <> s.school_year_id
   OR sf.semester_id <> s.semester_id
UNION ALL
SELECT
    'cc_faculty_load' AS target_table,
    fl.id AS record_id,
    fl.section_id,
    fl.school_year_id AS current_school_year_id,
    s.school_year_id AS expected_school_year_id,
    fl.semester_id AS current_semester_id,
    s.semester_id AS expected_semester_id
FROM cc_faculty_load fl
JOIN cc_sections s ON fl.section_id = s.id
WHERE fl.school_year_id <> s.school_year_id
   OR fl.semester_id <> s.semester_id
ORDER BY target_table, record_id;

-- Apply updates
UPDATE cc_section_faculty sf
JOIN cc_sections s ON sf.section_id = s.id
SET
    sf.school_year_id = s.school_year_id,
    sf.semester_id = s.semester_id
WHERE sf.school_year_id <> s.school_year_id
   OR sf.semester_id <> s.semester_id;

UPDATE cc_faculty_load fl
JOIN cc_sections s ON fl.section_id = s.id
SET
    fl.school_year_id = s.school_year_id,
    fl.semester_id = s.semester_id
WHERE fl.school_year_id <> s.school_year_id
   OR fl.semester_id <> s.semester_id;
