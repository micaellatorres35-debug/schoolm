<?php
$logo = dirname(__DIR__, 2) . '/assets/images/bestlink.png';
$start_hour = 7; 
$end_hour = 18;  
$hour_height = 60; 

function getDayShort($day) {
    return [
        'Monday' => 'Mon', 'Tuesday' => 'Tue', 'Wednesday' => 'Wed',
        'Thursday' => 'Thu', 'Friday' => 'Fri', 'Saturday' => 'Sat'
    ][$day] ?? $day;
}

function getTopOffset($time, $startHour, $pixelsPerHour) {
    $timestamp = strtotime($time);
    $hour = (int)date('H', $timestamp);
    $minute = (int)date('i', $timestamp);
    return (($hour - $startHour) * $pixelsPerHour) + ($minute);
}

function getHeight($start, $end) {
    $durationMinutes = (strtotime($end) - strtotime($start)) / 60;
    return $durationMinutes; 
}

$days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
$total_height = ($end_hour - $start_hour) * $hour_height;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Academic Schedule</title>
    <style>
        
        @page { 
            margin: 140px 40px 60px 40px; 
        }
        
        body { 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            font-size: 11px; 
            color: #333333; 
            line-height: 1.4;
        }

        
        header {
            position: fixed;
            top: -110px;  
            left: 0;
            right: 0;
            height: 100px;
            border-bottom: 2px solid #1a365d; 
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

        .pagenum:before { content: counter(page); }

       
        .schedule-title {
            text-align: center; 
            margin-top: 0; 
            color: #1a365d; 
            font-size: 16px; 
            margin-bottom: 35px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .schedule-container {
            position: relative;
            width: 100%;
            height: <?= $total_height ?>px;
            border: 1px solid #cbd5e1;
            background-color: #fff;
        }

        .day-column {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 15%;
            border-right: 1px solid #e2e8f0;
        }

        
        .day-label {
            position: absolute;
            top: -26px;
            width: 15%;
            text-align: center;
            font-weight: bold;
            color: #1a365d;
            font-size: 12px;
            text-transform: uppercase;
            border-bottom: 2px solid #cbd5e1;
            padding-bottom: 4px;
        }

      
        .grid-line {
            position: absolute;
            left: 0;
            right: 0;
            border-bottom: 1px solid #f1f5f9;
            height: <?= $hour_height ?>px;
            z-index: 1;
        }

        .time-label {
            position: absolute;
            left: 0;
            width: 10%;
            text-align: right;
            padding-right: 8px;
            font-size: 9px;
            font-weight: bold;
            color: #4a5568;
            background: #f8fafc;
            border-right: 2px solid #1a365d;
            box-sizing: border-box;
            line-height: 14px;
        }

        .subject-card {
            position: absolute;
            left: 4px;
            right: 4px;
            background: #2b6cb0; 
            color: white;
            padding: 6px;
            border-radius: 4px;
            font-size: 9px;
            line-height: 1.2;
            overflow: hidden;
            z-index: 10;
            border-left: 3px solid #1a365d; 
            box-sizing: border-box;
        }

        .subject-card strong {
            display: block;
            font-size: 9.5px;
            margin-bottom: 1px;
        }

        .subject-card .room-label {
            color: #ebf8ff;
            font-style: italic;
            display: block;
            margin-top: 2px;
        }

        .subject-card .time-range {
            display: block;
            margin-top: 1px;
            font-weight: 600;
            color: #e2e8f0;
        }

       
        .col-time { left: 0; width: 10%; border-right: none; }
        .col-Mon { left: 10%; }
        .col-Tue { left: 25%; }
        .col-Wed { left: 40%; }
        .col-Thu { left: 55%; }
        .col-Fri { left: 70%; }
        .col-Sat { left: 85%; }
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
    Bestlink College of the Philippines — Official Class Schedule | Page <span class="pagenum"></span>
</footer>

<h3 class="schedule-title">
    Class Schedule: <?= htmlspecialchars($schedules[0]['section_name'] ?? 'N/A') ?>
</h3>

<div class="schedule-container">
    
    <div class="day-column col-time">
        <?php for ($i = 0; $i < ($end_hour - $start_hour); $i++): ?>
            <div class="grid-line" style="top: <?= $i * $hour_height ?>px; width: 1000%;"></div>
            <div class="time-label" style="top: <?= $i * $hour_height ?>px; height: <?= $hour_height ?>px;">
                <?= date('g:i A', strtotime(($start_hour + $i) . ':00')) ?>
            </div>
        <?php endfor; ?>
    </div>

    <?php foreach ($days as $day_key): ?>
        <div class="day-label col-<?= $day_key ?>"><?= $day_key ?></div>
        <div class="day-column col-<?= $day_key ?>">
            
            <?php foreach ($schedules as $sched): ?>
                <?php if (getDayShort($sched['day']) === $day_key): 
                    $top = getTopOffset($sched['start_time'], $start_hour, $hour_height);
                    $height = getHeight($sched['start_time'], $sched['end_time']);
                ?>
                    <div class="subject-card" style="top: <?= $top ?>px; height: <?= $height - 4 ?>px;">
                        <strong><?= htmlspecialchars($sched['subject_code']) ?></strong>
                        <div><?= htmlspecialchars($sched['subject_name']) ?></div>
                        <span class="room-label"><?= htmlspecialchars($sched['room_name']) ?></span>
                        <span class="time-range">
                            <?= date('g:i', strtotime($sched['start_time'])) ?> - <?= date('g:i A', strtotime($sched['end_time'])) ?>
                        </span>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

        </div>
    <?php endforeach; ?>
</div>

</body>
</html>