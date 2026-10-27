<?php

    $info = !empty($enrollments) ? $enrollments[0] : null;

 
    function formatTime($time)
    {
        if (empty($time)) {
            return '';
        }

        return date('h:i A', strtotime($time));
    }

  
    $studentName = '';

    if ($info) {
        $studentName = $info['student_first_name'] . ' ' . $info['student_last_name'];
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Certificate of Registration</title>

    <style>

        @page {
            margin: 140px 40px 60px 40px;
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

        /*
         * HEADER
         */

        header {
            position: fixed;
            top: -110px;
            left: 0;
            right: 0;
            height: 100px;

            border-bottom: 2px solid #1a365d;

            padding-bottom: 10px;
        }

        /*
         * FOOTER
         */

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

        /*
         * PAGE TITLE
         */

        .document-title {
            text-align: center;

            color: #1a365d;

            font-size: 18px;

            font-weight: bold;

            margin-top: 0;

            margin-bottom: 20px;

            text-transform: uppercase;

            letter-spacing: 0.5px;
        }

        /*
         * STUDENT INFORMATION
         */

        .student-info {
            width: 100%;

            border-collapse: collapse;

            margin-bottom: 20px;
        }

        .student-info td {
            padding: 5px 4px;

            vertical-align: top;
        }

        .student-info .label {
            font-weight: bold;

            color: #4a5568;
        }

        /*
         * SECTION TITLE
         */

        .section-title {
            font-size: 14px;

            color: #1a365d;

            font-weight: bold;

            margin-top: 15px;

            margin-bottom: 8px;

            border-bottom: 1px solid #cbd5e1;

            padding-bottom: 4px;

            text-transform: uppercase;
        }

        /*
         * SUBJECT TABLE
         */

        .cor-table {
            width: 100%;

            border-collapse: collapse;

            margin-bottom: 15px;

            background-color: #ffffff;
        }

        .cor-table th {
            background-color: #2d3748;

            color: #ffffff;

            text-align: left;

            font-weight: 600;

            font-size: 10px;

            text-transform: uppercase;

            padding: 7px 8px;

            border: 1px solid #2d3748;
        }

        .cor-table td {
            padding: 7px 8px;

            border: 1px solid #e2e8f0;

            font-size: 10px;

            vertical-align: middle;
        }

        .cor-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        /*
         * TOTAL ROW
         */

        .total-row {
            background-color: #edf2f7 !important;

            font-weight: bold;
        }

        .total-row td {
            border-top: 1px solid #cbd5e1;
        }

        /*
         * STATUS
         */

        .status {
            font-weight: bold;

            color: #1a365d;
        }

        /*
         * SIGNATURE AREA
         */

        .signature-table {
            width: 100%;

            border-collapse: collapse;

            margin-top: 50px;
        }

        .signature-table td {
            width: 50%;

            text-align: center;

            vertical-align: bottom;

            padding: 5px;
        }

        .signature-line {
            border-top: 1px solid #333333;

            width: 80%;

            margin: 35px auto 5px auto;
        }

        .signature-label {
            font-size: 10px;

            color: #4a5568;
        }

    </style>

</head>

<body>

<!-- ========================================================= -->
<!-- HEADER -->
<!-- ========================================================= -->

<header>

    <table width="100%" style="border-collapse: collapse;">

        <tr>

            <!-- SCHOOL LOGO -->

            <td width="12%" valign="middle">

                <?php if (!empty($school_image)): ?>

                    <img
                        src="<?= $school_image ?>"
                        height="65"
                        alt="School Logo"
                    >

                <?php endif; ?>

            </td>


            <!-- SCHOOL INFORMATION -->

            <td
                width="76%"
                align="center"
                valign="middle"
                style="line-height: 1.3;"
            >

                <strong
                    style="
                        font-size: 15px;
                        color: #1a365d;
                    "
                >
                    Bestlink College of the Philippines - Bulacan Inc.
                </strong>

                <br>

                <span
                    style="
                        font-size: 11px;
                        color: #4a5568;
                    "
                >
                    Lot 1 Ipo Road, Minuyan Proper,
                    City of San Jose Del Monte, Bulacan
                </span>

                <br>

                <span
                    style="
                        font-size: 10px;
                        color: #718096;
                    "
                >
                    Tel: (044) 797-2949
                </span>

            </td>


            <!-- CHED LOGO -->

            <td
                width="12%"
                align="right"
                valign="middle"
            >

                <?php if (!empty($ched_image)): ?>

                    <img
                        src="<?= $ched_image ?>"
                        height="65"
                        alt="CHED Logo"
                    >

                <?php endif; ?>

            </td>

        </tr>

    </table>

</header>


<!-- ========================================================= -->
<!-- FOOTER -->
<!-- ========================================================= -->

<footer>

    Bestlink College of the Philippines —
    Certificate of Registration |

    Page <span class="pagenum"></span>

</footer>


<!-- ========================================================= -->
<!-- CONTENT -->
<!-- ========================================================= -->

<div class="content">


    <!-- DOCUMENT TITLE -->

    <div class="document-title">

        Certificate of Registration

    </div>


    <?php if ($info): ?>


        <!-- ================================================= -->
        <!-- STUDENT INFORMATION -->
        <!-- ================================================= -->

        <table class="student-info">

            <tr>

                <td width="15%" class="label">
                    Student Name:
                </td>

                <td width="35%">
                    <?= htmlspecialchars($studentName) ?>
                </td>


              


            </tr>


            <tr>

                <td class="label">
                    Course:
                </td>

                <td>
                    <?= htmlspecialchars($info['course_name']) ?>
                </td>


                <td class="label">
                    Academic Standing:
                </td>

                <td class="status">
                    <?= htmlspecialchars($info['academic_standing']) ?>
                </td>

            </tr>

        </table>


        <!-- ================================================= -->
        <!-- SUBJECTS -->
        <!-- ================================================= -->

        <div class="section-title">

            Enrolled Subjects

        </div>


        <table class="cor-table">

            <thead>

                <tr>

                    <th width="15%">
                        Subject Code
                    </th>

                    <th width="15%">
                        Section
                    </th>

                    <th width="15%">
                        Instructor
                    </th>

                    <th width="35%">
                        Subject Description
                    </th>

                    <th width="15%">
                        Day
                    </th>

                    <th width="20%">
                        Time
                    </th>


                </tr>

            </thead>


            <tbody>


            <?php

                $totalSubjects = 0;

                foreach ($enrollments as $subject):

                    $totalSubjects++;

                    $startTime = formatTime(
                        $subject['start_time']
                    );

                    $endTime = formatTime(
                        $subject['end_time']
                    );

            ?>

                <tr>

                    <!-- SUBJECT CODE -->

                    <td>

                        <strong>
                            <?= htmlspecialchars(
                                $subject['subject_code']
                            ) ?>
                        </strong>

                    </td>


                     <!-- Instructor CODE -->

                    <td>

                    
                            <?= htmlspecialchars(
                                $subject['first_name']
                            ) ?>

                              <?= htmlspecialchars(
                                $subject['last_name']
                            ) ?>
                       

                    </td>


                       <!-- SECTION CODE -->

                    <td>

                            <?= htmlspecialchars(
                                $subject['section_code']
                            ) ?>
                        

                    </td>




                    <!-- SUBJECT NAME -->

                    <td>

                        <?= htmlspecialchars(
                            $subject['subject_name']
                        ) ?>

                    </td>


                    <!-- DAY -->

                    <td>

                        <?= htmlspecialchars(
                            $subject['day_of_week']
                        ) ?>

                    </td>


                    <!-- TIME -->

                    <td>

                        <?= htmlspecialchars($startTime) ?>

                        -

                        <?= htmlspecialchars($endTime) ?>

                    </td>


                   
                </tr>


            <?php endforeach; ?>


                <!-- TOTAL -->

                <tr class="total-row">

                    <td
                        colspan="5"
                        align="right"
                    >

                        Total Enrolled Subjects:

                    </td>

                    <td align="center">

                        <?= $totalSubjects ?>

                    </td>

                </tr>


            </tbody>

        </table>


        <!-- ================================================= -->
        <!-- CERTIFICATION -->
        <!-- ================================================= -->

        <div
            style="
                margin-top: 25px;
                padding: 10px;
                border: 1px solid #e2e8f0;
                background-color: #f8fafc;
                font-size: 10px;
                line-height: 1.5;
            "
        >

            This is to certify that

            <strong>
                <?= htmlspecialchars($studentName) ?>
            </strong>

            is officially enrolled in the above-listed subjects
            for the current academic term.

        </div>


        <!-- ================================================= -->
        <!-- SIGNATURES -->
        <!-- ================================================= -->

        <table class="signature-table">

            <tr>

                <td>

                    <div class="signature-line"></div>

                    <strong>
                        Registrar
                    </strong>

                    <br>

                    <span class="signature-label">
                        Registrar's Office
                    </span>

                </td>


                <td>

                    <div class="signature-line"></div>

                    <strong>
                        Student
                    </strong>

                    <br>

                    <span class="signature-label">
                        Conforme
                    </span>

                </td>

            </tr>

        </table>


    <?php else: ?>


        <!-- NO ENROLLMENT -->

        <div
            style="
                text-align: center;
                margin-top: 50px;
                color: #718096;
            "
        >

            No enrollment record found.

        </div>


    <?php endif; ?>


</div>


</body>

</html>

