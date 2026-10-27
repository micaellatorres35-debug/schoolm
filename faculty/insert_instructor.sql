CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    role VARCHAR(50) DEFAULT 'instructor',
    is_admin TINYINT(1) DEFAULT 0,
    theme VARCHAR(20) DEFAULT 'light',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (
    username,
    password,
    full_name,
    email,
    role,
    is_admin,
    theme
) VALUES (
    'instructor',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Instructor',
    NULL,
    'instructor',
    0,
    'light'
);

-- =====================================================
-- DUMMY INSTRUCTOR USERS
-- Password for all accounts: instructor123
-- =====================================================

INSERT INTO users (
    username,
    password,
    full_name,
    email,
    role,
    is_admin,
    theme,
    created_at
) VALUES

(
    'instructor01',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Juan Dela Cruz',
    'juan.delacruz@example.com',
    'instructor',
    0,
    'light',
    NOW()
),

(
    'instructor02',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Maria Santos',
    'maria.santos@example.com',
    'instructor',
    0,
    'light',
    NOW()
),

(
    'instructor03',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Pedro Reyes',
    'pedro.reyes@example.com',
    'instructor',
    0,
    'light',
    NOW()
),

(
    'instructor04',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Ana Garcia',
    'ana.garcia@example.com',
    'instructor',
    0,
    'light',
    NOW()
),

(
    'instructor05',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Carlos Mendoza',
    'carlos.mendoza@example.com',
    'instructor',
    0,
    'light',
    NOW()
),

(
    'instructor06',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Sofia Ramirez',
    'sofia.ramirez@example.com',
    'instructor',
    0,
    'light',
    NOW()
),

(
    'instructor07',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Michael Torres',
    'michael.torres@example.com',
    'instructor',
    0,
    'light',
    NOW()
),

(
    'instructor08',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Elizabeth Cruz',
    'elizabeth.cruz@example.com',
    'instructor',
    0,
    'light',
    NOW()
),

(
    'instructor09',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Daniel Flores',
    'daniel.flores@example.com',
    'instructor',
    0,
    'light',
    NOW()
),

(
    'instructor10',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Patricia Aquino',
    'patricia.aquino@example.com',
    'instructor',
    0,
    'light',
    NOW()
);
-- =====================================================
-- DUMMY INSTRUCTOR EMPLOYEES
-- Password for all accounts: instructor123
-- =====================================================
INSERT INTO employees (
    user_id,
    employee_no,
    full_name,
    category_id,
    position_id,
    employment_type_id,
    employment_status,
    department,
    date_hired
) VALUES

(
    1,
    'T011',
    'Juan Dela Cruz',
    1,
    1,
    1,
    'active',
    'College Department',
    '2021-06-01'
),

(
    2,
    'T012',
    'Maria Santos',
    1,
    1,
    1,
    'active',
    'College Department',
    '2021-08-15'
),

(
    3,
    'T013',
    'Pedro Reyes',
    1,
    1,
    1,
    'active',
    'College Department',
    '2022-01-10'
),

(
    4,
    'T014',
    'Ana Garcia',
    1,
    1,
    1,
    'active',
    'College Department',
    '2022-06-01'
),

(
    5,
    'T005',
    'Carlos Mendoza',
    1,
    1,
    1,
    'active',
    'College Department',
    '2022-08-15'
),

(
    6,
    'T006',
    'Sofia Ramirez',
    1,
    1,
    1,
    'active',
    'College Department',
    '2023-01-10'
),

(
    7,
    'T007',
    'Michael Torres',
    1,
    1,
    1,
    'active',
    'College Department',
    '2023-06-01'
),

(
    8,
    'T008',
    'Elizabeth Cruz',
    1,
    1,
    1,
    'active',
    'College Department',
    '2023-08-15'
),

(
    9,
    'T009',
    'Daniel Flores',
    1,
    1,
    1,
    'active',
    'College Department',
    '2024-01-10'
),

(
    10,
    'T010',
    'Patricia Aquino',
    1,
    1,
    1,
    'active',
    'College Department',
    '2024-06-01'
);