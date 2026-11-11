<?php
class Page {
    private $default = 'dashboard-overview';
    private $pagesDir;
    private $allowed = [];

    /*Sidebar Content*/ 
    private $labels = [
        'dashboard-overview' => 'Dashboard',
        'research-proposal' => 'Research Proposals',
        'research-project'  => 'Research Projects',
        'evaluation-assessment' => 'Evaluation & Assessment',
        'research-documentation' => 'Research Documentation',
        'ethics-compliance' => 'Ethics & Compliance',
        'adviser-management' => 'Adviser Management',
        'defense-scheduling' => 'Defense Scheduling',
        'events-management' => 'Research Events',
        'report-submission' => 'Report Submissions',
        'concern-submission' => 'Concern Submissions',
        'approval-submission' => 'Approval Requests'
    ];

    /*Content Sections*/ 
    private $sections = [
        'Dashboard Overview' => ['dashboard-overview'],
        'Research Management' => ['research-project', 'research-proposal', 'research-documentation', 'evaluation-assessment', 'ethics-compliance'],
        'Academic Management' => ['adviser-management', 'defense-scheduling', 'events-management'],
        'Submissions & Approvals' => ['report-submission', 'concern-submission', 'approval-submission']
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
        $isFirstSection = true;
        foreach ($this->sections as $heading => $pages) {
            if (!$isFirstSection) {
                echo '<div class="separator"></div>';
            }
            echo '<h3>' . htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') . '</h3>';
            foreach ($pages as $p) {
                $this->renderLink($p);
            }
            $isFirstSection = false;
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