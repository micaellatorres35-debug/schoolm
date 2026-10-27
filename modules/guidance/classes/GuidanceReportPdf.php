<?php
// dompdf is Composer-installed at the project root.
require_once __DIR__ . '/../../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * GuidanceReportPdf
 * Turns a GuidanceReport::build() result (+ Chart.js PNG images from the
 * browser) into PDF bytes. No remote resources are loaded.
 */
class GuidanceReportPdf
{
    public static function render(array $report, array $chartImages, string $generatedBy): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(self::html($report, $chartImages, $generatedBy), 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        try {
            $fontMetrics = $dompdf->getFontMetrics();
            $font = method_exists($fontMetrics, 'getFont')
                ? $fontMetrics->getFont('DejaVu Sans', 'normal')
                : $fontMetrics->get_font('DejaVu Sans', 'normal');

            $dompdf->getCanvas()->page_text(720, 570, 'Page {PAGE_NUM} of {PAGE_COUNT}', $font, 8, [0.42, 0.45, 0.50]);
        } catch (\Throwable $e) {
            // Page numbers are cosmetic; never fail the report over them.
        }

        return $dompdf->output();
    }

    private static function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    private static function formatDate(string $date, bool $withTime): string
    {
        $ts = strtotime($date);

        return $ts ? date($withTime ? 'M j, Y g:i A' : 'M j, Y', $ts) : $date;
    }

    private static function formatCell(string $key, $value): string
    {
        if ($value === null || $value === '') {
            return '&mdash;';
        }

        if ($key === 'incident_date' || $key === 'appointment_date') {
            return self::e(self::formatDate((string) $value, true));
        }

        if (substr($key, -3) === '_at') {
            return self::e(self::formatDate((string) $value, false));
        }

        return self::e($value);
    }

    /** Lays blocks out in a fixed-column table (dompdf has no flex/grid). */
    private static function grid(array $blocks, int $cols): string
    {
        if (!$blocks) {
            return '';
        }

        $width = floor(100 / $cols);
        $html  = '<table class="layout">';

        foreach (array_chunk($blocks, $cols) as $row) {
            $html .= '<tr>';
            for ($i = 0; $i < $cols; $i++) {
                $html .= '<td style="width:' . $width . '%;">' . ($row[$i] ?? '') . '</td>';
            }
            $html .= '</tr>';
        }

        return $html . '</table>';
    }

    private static function html(array $r, array $chartImages, string $generatedBy): string
    {
        $period = self::formatDate($r['period']['from'], false) . ' &ndash; ' . self::formatDate($r['period']['to'], false);

        // Chart images (already validated PNGs, base64).
        $chartBlocks = [];
        foreach ($chartImages as $b64) {
            $chartBlocks[] = '<img src="data:image/png;base64,' . $b64 . '" style="width:100%;">';
        }

        // Summary tables.
        $summaryBlocks = [];
        foreach ($r['sections'] as $section) {
            $rows = '';
            foreach ($section['rows'] as $row) {
                $rows .= '<tr><td>' . self::e($row['label']) . '</td><td class="num">' . (int) $row['count'] . '</td></tr>';
            }
            if ($rows === '') {
                $rows = '<tr><td colspan="2">No data</td></tr>';
            }
            $summaryBlocks[] = '<table class="grid"><thead><tr><th colspan="2">' . self::e($section['title']) . '</th></tr></thead><tbody>' . $rows . '</tbody></table>';
        }

        // Detail table.
        $head = '';
        foreach ($r['columns'] as $col) {
            $head .= '<th>' . self::e($col['label']) . '</th>';
        }

        $body = '';
        foreach ($r['rows'] as $row) {
            $body .= '<tr>';
            foreach ($r['columns'] as $col) {
                $body .= '<td>' . self::formatCell($col['key'], $row[$col['key']] ?? null) . '</td>';
            }
            $body .= '</tr>';
        }

        $note = $r['truncated']
            ? '<p class="note">Showing the most recent ' . count($r['rows']) . ' of ' . (int) $r['total'] . ' records.</p>'
            : '';

        $css = "
            body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #1f2937; }
            h1 { font-size: 17px; margin: 0 0 3px; }
            h2 { font-size: 11px; margin: 12px 0 5px; }
            .meta { color: #6b7280; margin: 0 0 10px; }
            .confidential { color: #6b7280; font-size: 8px; margin: 0 0 8px; }
            table.layout { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin-bottom: 8px; }
            table.layout td { vertical-align: top; }
            table.grid { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
            table.grid th, table.grid td { border: 1px solid #d1d5db; padding: 3px 5px; text-align: left; }
            table.grid th { background: #f3f4f6; }
            td.num { text-align: right; width: 40px; }
            .detail { page-break-before: always; }
            .note { color: #6b7280; margin-top: 4px; }
        ";

        return '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>' . $css . '</style></head><body>'
            . '<h1>' . self::e($r['title']) . '</h1>'
            . '<p class="meta">Period: ' . $period
            . ' &nbsp;|&nbsp; Total records: ' . (int) $r['total']
            . ' &nbsp;|&nbsp; Generated by ' . self::e($generatedBy)
            . ' on ' . self::e(date('M j, Y g:i A')) . '</p>'
            . '<p class="confidential">Confidential &mdash; Guidance &amp; Counseling records. Authorized personnel only.</p>'
            . ($chartBlocks ? '<h2>Charts</h2>' . self::grid($chartBlocks, 2) : '')
            . '<h2>Summary</h2>' . self::grid($summaryBlocks, 2)
            . '<div class="detail"><h2>Records</h2>'
            . '<table class="grid"><thead><tr>' . $head . '</tr></thead><tbody>' . $body . '</tbody></table>' . $note . '</div>'
            . '</body></html>';
    }
}