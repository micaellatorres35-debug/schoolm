<?php
    $logo = dirname(__DIR__, 2) . '/assets/images/bestlink.png';

    $grouped = [];
    foreach ($school_years as $row) {
        $grouped[$row['year_level']][$row['semester']][] = $row;
    }

    $info = !empty($school_years) ? $school_years[0] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Curriculum Report</title>
    <style>
        
        @page {
            margin: 140px 40px 60px 40px; /* Increased top margin to clear header room safely */
        }

       
        .pagenum:before { 
            content: counter(page); 
        }

        body { 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            color: #333333;
            font-size: 12px;
            line-height: 1.4;
        }

       
        header {
            position: fixed;
            top: -110px;  
            left: 0;
            right: 0;
            height: 100px;
            border-bottom: 2px solid #1a365d; /* Institutional Deep Blue accent line */
            padding-bottom: 10px;
        }

     
        footer {
            position: fixed;
            bottom: -35px;
            left: 0;
            right: 0;
            height: 30px;
            text-align: center;
            font-size: 10px;
            color: #718096;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
        }

       
        .meta-table {
            width: 100%;
            margin-bottom: 20px;
            border: none;
        }
        .meta-table td {
            padding: 4px 0;
            vertical-align: top;
        }

        
        .year-title {
            font-size: 16px;
            color: #1a365d;
            margin-top: 25px;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 3px;
        }

        .semester-title {
            font-size: 13px;
            color: #4a5568;
            margin-top: 12px;
            margin-bottom: 8px;
            font-weight: bold;
        }

     
        .curriculum-table { 
            width: 100%; 
            border-collapse: collapse;
            margin-bottom: 15px;
            background-color: #ffffff;
        }
        
        .curriculum-table th { 
            background-color: #2d3748; 
            color: #ffffff; 
            text-align: left;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            padding: 7px 10px;
            border: 1px solid #2d3748;
        }

        .curriculum-table td { 
            padding: 7px 10px; 
            border: 1px solid #e2e8f0;
            font-size: 11px;
        }

    
        .curriculum-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

      
        .total-row {
            background-color: #edf2f7 !important;
            font-weight: bold;
        }
        .total-row td {
            border-top: 1px solid #cbd5e1;
        }
    </style>
</head>
<body>

<header>
    <table width="100%" style="border-collapse: collapse;">
        <tr>
            <td width="12%" valign="middle">
                <?php if(!empty($school_image)): ?>
                    <img src="<?= $school_image ?>" height="65" alt="School Logo">
                <?php endif; ?>
            </td>
            <td width="76%" align="center" valign="middle" style="line-height: 1.3;">
                <strong style="font-size: 15px; color: #1a365d;">Bestlink College of the Philippines - Bulacan Inc.</strong><br>
                <span style="font-size: 11px; color: #4a5568;">
                    Lot 1 Ipo Road, Minuyan Proper, City of San Jose Del Monte, Bulacan
                </span><br>
                <span style="font-size: 10px; color: #718096;">Tel: (044) 797-2949</span>
            </td>
            <td width="12%" align="right" valign="middle">
                <?php if(!empty($ched_image)): ?>
                    <img src="<?= $ched_image ?>" height="65" alt="CHED Logo">
                <?php endif; ?>
            </td>
        </tr>
    </table>
</header>

<footer>
    Bestlink College of the Philippines — Official Curriculum Document | Page <span class="pagenum"></span>
</footer>

<div class="content">

    <?php if ($info): ?>
        <h2 style="text-align: center; margin-top: 0; color: #1a365d; font-size: 18px; margin-bottom: 20px;">
            <?= htmlspecialchars($info['curriculum_name']) ?>
        </h2>

        <table class="meta-table">
            <tr>
                <td width="12%"><strong>Course:</strong></td>
                <td width="58%"><?= htmlspecialchars($info['course_name']) ?></td>
                <td width="15%"><strong>Effective Year:</strong></td>
                <td width="15%" align="right"><?= htmlspecialchars($info['effective_year']) ?></td>
            </tr>
        </table>
    <?php endif; ?>

    <?php foreach($grouped as $year => $semesters): ?>
        
        <div class="year-title">
            <?php
                switch($year){
                    case 1: echo "First Year"; break;
                    case 2: echo "Second Year"; break;
                    case 3: echo "Third Year"; break;
                    case 4: echo "Fourth Year"; break;
                    default: echo "Year " . htmlspecialchars($year); break;
                }
            ?>
        </div>

        <?php foreach($semesters as $semester => $subjects): ?>

            <div class="semester-title">
                <?= htmlspecialchars($semester) ?>
            </div>

            <table class="curriculum-table">
                <thead>
                    <tr>
                        <th width="20%">Subject Code</th>
                        <th width="65%">Subject Description</th>
                        <th width="15%" style="text-align: center;">Units</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                    $total_semester_units = 0; 
                    foreach($subjects as $subject): 
                        $total_semester_units += (float)$subject['subject_units'];
                ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($subject['subject_code']) ?></strong></td>
                        <td><?= htmlspecialchars($subject['subject_name']) ?></td>
                        <td align="center"><?= htmlspecialchars($subject['subject_units']) ?></td>
                    </tr>
                <?php endforeach; ?>
                
                <tr class="total-row">
                    <td colspan="2" align="right">Total Units:</td>
                    <td align="center"><?= $total_semester_units ?></td>
                </tr>
                </tbody>
            </table>

        <?php endforeach; ?>

    <?php endforeach; ?>

</div>

</body>
</html>