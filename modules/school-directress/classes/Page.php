<?php
class Page {
    private $default = 'dashboard-overview';
    private $pagesDir;
    private $allowed = [];

    private $labels = [
        'dashboard-overview'          => 'Dashboard Overview',
        'user-management'             => 'User & Access Management',
        'department-unit-management'  => 'Department & Unit Management',
        'approval-decision-support'   => 'Approval and Decision Support',
        'communications-announcements'=> 'Communications and Announcements',
        'report-submission-management'=> 'Report Submission & Management',
        'concerns-issue-tracking'     => 'Concerns & Issue Tracking',
        'student-monitoring'            => 'Student Monitoring'
    ];

    
    private $icons = [
        'dashboard-overview'           => 'fa-solid fa-house',
        'user-management'              => 'fa-solid fa-users',
        'department-unit-management'    => 'fa-solid fa-sitemap',
        'approval-decision-support'     => 'fa-solid fa-clipboard-check',
        'communications-announcements' => 'fa-solid fa-bullhorn',
        'report-submission-management' => 'fa-solid fa-file-lines',
        'concerns-issue-tracking'       => 'fa-solid fa-triangle-exclamation',
        'student-monitoring'            => 'fa-solid fa-user-graduate'
    ];


    private $sections = [
        'top'             => ['dashboard-overview'],
        'administration'  => ['user-management', 'department-unit-management'],
        'governance'      => ['approval-decision-support', 'communications-announcements'],
        'reporting'       => ['report-submission-management'],
        'student-oversight' => ['concerns-issue-tracking', 'student-monitoring']
    ];

    public function __construct($pagesDir = null) {
        $this->pagesDir = $pagesDir ?? dirname(__DIR__) . '/pages';
        $this->discoverPages();
    }

    private function discoverPages() {
        if (!is_dir($this->pagesDir)) return;
        foreach (glob($this->pagesDir . '/*.php') as $file) {
            $this->allowed[] = basename($file, '.php');
        }
    }

    public function getPage() {
        if (!empty($_GET['page']) && in_array($_GET['page'], $this->allowed)) {
            return $_GET['page'];
        }
        return $this->default;
    }

    public function render() {
        $page = $this->getPage();
        $file = $this->pagesDir . '/' . $page . '.php';
        if (file_exists($file)) {
            include $file;
        } else {
            include $this->pagesDir . '/' . $this->default . '.php';
        }
    }

    public function isActive($page) {
        return $this->getPage() === $page;
    }

    public function getAllowedPages() {
        return $this->allowed;
    }

    public function renderNav() {
        // Top section (no heading/separator)
        foreach ($this->sections['top'] as $p) {
            $this->renderLink($p);
        }

        // Grouped sections
        $sectionOrder = ['administration', 'governance', 'reporting', 'student-oversight'];
        foreach ($sectionOrder as $section) {
            echo '<div class="separator"></div>';
            echo '<h3>' . ucwords(str_replace('-', ' ', $section)) . '</h3>';
            foreach ($this->sections[$section] as $p) {
                $this->renderLink($p);
            }
        }
    }

    private function renderLink($p) {
        $label = $this->labels[$p] ?? ucwords(str_replace('-', ' ', $p));
        $icon = $this->icons[$p] ?? 'fa-solid fa-circle';

        if (in_array($p, $this->allowed, true)) {
            $class = $this->isActive($p)
                ? 'active-menu-link'
                : 'menu-link';

            echo '<li>';
            echo '<a href="?page=' . htmlspecialchars($p, ENT_QUOTES, 'UTF-8') . '" ';
            echo 'data-page="' . htmlspecialchars($p, ENT_QUOTES, 'UTF-8') . '" ';
            echo 'class="' . $class . '">';

            echo '<i class="' . htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') . ' sidebar-icon" aria-hidden="true"></i>';
            echo '<span>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span>';

            echo '</a>';
            echo '</li>';
        } else {
            echo '<li>';
            echo '<a href="#" class="menu-link">';
            echo '<i class="' . htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') . ' sidebar-icon" aria-hidden="true"></i>';
            echo '<span>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span>';
            echo '</a>';
            echo '</li>';
        }
    }
}