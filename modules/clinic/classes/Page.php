<?php
class Page {
    // Name of the default page file in /pages (without .php). Case doesn't matter.
    private $default = 'ClinicDashboard';
    private $pagesDir;
    private $allowed = [];

    /* If the main file is missing, these alternative file names are tried. */
    private $aliases = [
        'clinicdashboard' => ['dashboard-overview', 'dashboard']
    ];

    /* Sidebar labels. Keys are LOWERCASE page names. */
    private $labels = [
        'clinicdashboard'                => 'Clinic Dashboard',
        'patientinformationmanagement'   => 'Patient Information Management',
        'clinicvisitandconsultation'     => 'Clinic Visit & Consultation',
        'medicationinventory'            => 'Medication Inventory',
        'incidentandemergencymanagement' => 'Incident & Emergency Management',
        'report-submission'              => 'Report Submission',
        'concern-submission'             => 'Concern Submission',
        'approval-submission'            => 'Approval Submission'
    ];

    /* Sidebar sections. Page names here can be any letter case. */
    private $sections = [
        'top'                  => ['ClinicDashboard'],
        'patient-management'   => ['PatientInformationManagement'],
        'clinic-operations'    => ['ClinicVisitAndConsultation'],
        'inventory-management' => ['MedicationInventory'],
        'incident-management'  => ['IncidentAndEmergencyManagement'],
        'requests-and-reports' => ['ReportSubmission', 'ConcernSubmission', 'ApprovalSubmission']
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

    /** Finds the real file name for a page, ignoring letter case. */
    private function resolve($name) {
        $candidates = array_merge([(string) $name], $this->aliases[strtolower((string) $name)] ?? []);
        foreach ($candidates as $candidate) {
            foreach ($this->allowed as $actual) {
                if (strcasecmp($actual, $candidate) === 0) return $actual;
            }
        }
        return null;
    }

    public function getPage() {
        if (!empty($_GET['page'])) {
            $match = $this->resolve($_GET['page']);
            if ($match !== null) return $match;
        }
        return $this->resolve($this->default) ?? $this->default;
    }

    public function render() {
        $page = $this->getPage();
        $file = $this->pagesDir . '/' . $page . '.php';
        if (file_exists($file)) {
            include $file;
        } else {
            echo '<div class="module-content"><p>Page not found: '
               . htmlspecialchars($page) . '.php is missing from the pages folder.</p></div>';
        }
    }

    public function isActive($page) {
        return strcasecmp($this->getPage(), (string) $page) === 0;
    }

    public function getAllowedPages() {
        return $this->allowed;
    }

    public function renderNav() {
        foreach ($this->sections['top'] as $p) {
            $this->renderLink($p);
        }

        $sectionOrder = ['patient-management', 'clinic-operations', 'inventory-management', 'incident-management', 'requests-and-reports'];
        foreach ($sectionOrder as $section) {
            echo '<div class="separator"></div>';
            echo '<h3>' . ucwords(str_replace('-', ' ', $section)) . '</h3>';
            foreach ($this->sections[$section] as $p) {
                $this->renderLink($p);
            }
        }
    }

    private function renderLink($p) {
        $label  = $this->labels[strtolower($p)] ?? ucwords(str_replace('-', ' ', $p));
        $actual = $this->resolve($p);
        if ($actual !== null) {
            $class = $this->isActive($actual) ? 'active-menu-link' : 'menu-link';
            echo "<li><a href=\"?page={$actual}\" data-page=\"{$actual}\" class=\"{$class}\">{$label}</a></li>";
        } else {
            echo "<li><a href=\"#\" class=\"menu-link\">{$label}</a></li>";
        }
    }
}