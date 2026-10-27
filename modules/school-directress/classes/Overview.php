<?php

include_once __DIR__ . "/../../../database/db.php";

$database = new Database();
$conn     = $database->getConnection();     


$totalStudents     = $conn->query("SELECT COUNT(*) FROM enr_students WHERE enrollment_status = 'enrolled'")->fetchColumn();
$pendingApplicants = $conn->query("SELECT COUNT(*) FROM enr_applicants WHERE status = 'pending'")->fetchColumn();
$activeEmployees   = $conn->query("SELECT COUNT(*) FROM em_employees WHERE employment_status = 'Active'")->fetchColumn();
$pendingReports    = $conn->query("SELECT COUNT(*) FROM sd_reports WHERE status = 'submitted'")->fetchColumn();
$openIssues        = $conn->query("SELECT COUNT(*) FROM sd_issues WHERE status = 'submitted'")->fetchColumn();
$pendingApprovals  = $conn->query("SELECT COUNT(*) FROM sd_approvals WHERE status = 'submitted'")->fetchColumn();
$overdueBooks      = $conn->query("SELECT COUNT(*) FROM lbr_transactions WHERE status = 'overdue'")->fetchColumn();
$todayClinic       = $conn->query("SELECT COUNT(*) FROM cln_clinic_visits WHERE DATE(visit_date) = CURDATE()")->fetchColumn();