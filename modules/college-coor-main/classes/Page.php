<?php
class Page {
    private $default = 'dashboard-overview';
    private $pagesDir;
    private $allowed = [];

    /*Sidebar Content*/
    private $labels = [
        'dashboard-overview' => 'Dashboard Overview',
        'faculty-management' => 'Faculty Management',
        'user-management' => 'Coordinator Management',
        'communications-announcements' => 'Communications and Announcements',
        'academics-management' => 'Academics Management',   // dati: 'academics' (hindi tugma sa page slug)
        'class-scheduling' => 'Class Scheduling',
        'events-management' => 'Events Management',
        'report-submission-management' => 'Report Submission & Management',
        'concerns-issues-tracking' => 'Concern & Issue Tracking',
        'approval-decision-support' => 'Approval and Decision Support',
    ];

    /*Section headings (ayon sa pagkakasunod sa sidebar)*/
    private $sectionTitles = [
        'administration' => 'Administration',
        'academic-affairs' => 'Academic Affairs',
        'requests-and-reports' => 'Requests And Reports',
    ];

    /*Content Sections*/
    private $sections = [
        'top' => ['dashboard-overview'],
        'administration' => [
            'faculty-management',
            'user-management',
            'communications-announcements',
        ],
        'academic-affairs' => [
            'academics-management',
            'class-scheduling',
            'events-management',
        ],
        'requests-and-reports' => [
            'report-submission-management',
            'concerns-issues-tracking',
            'approval-decision-support',
        ],
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
        // Top section (walang heading/separator)
        foreach ($this->sections['top'] as $p) {
            $this->renderLink($p);
        }

        // Grouped sections
        foreach ($this->sectionTitles as $key => $title) {
            echo '<div class="separator"></div>';
            echo '<h3>' . htmlspecialchars($title) . '</h3>';
            foreach ($this->sections[$key] as $p) {
                $this->renderLink($p);
            }
        }
    }

    private function renderLink($p) {
        $label = $this->labels[$p] ?? ucwords(str_replace('-', ' ', $p));
        if (in_array($p, $this->allowed)) {
            $class = $this->isActive($p) ? 'active-menu-link' : 'menu-link';
            echo "<li><a href=\"?page={$p}\" data-page=\"{$p}\" class=\"{$class}\">{$label}</a></li>";
        } else {
            echo "<li><a href=\"#\" class=\"menu-link\">{$label}</a></li>";
        }
    }
}