<?php
    $logo = dirname(__DIR__, 2) . '/assets/images/bestlink.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Applicant Information</title>
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

        .section-title{
    background:#2d3748;
    color:#fff;
    padding:6px 10px;
    font-weight:bold;
    margin-top:15px;
    font-size:12px;
}

.info-table{
    width:100%;
    border-collapse:collapse;
    margin-bottom:10px;
}

.info-table td{
    border:1px solid #e2e8f0;
    padding:7px;
    font-size:11px;
}

.label{
    width:30%;
    background:#f8fafc;
    font-weight:bold;
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
    Bestlink College of the Philippines — Official Applicant Document | Page <span class="pagenum"></span>
</footer>

<div class="content">

<div class="section-title">APPLICATION INFORMATION</div>

<table class="info-table">
    <tr>
        <td class="label">Application No.</td>
        <td><?= $applicant_number ?></td>

        <td class="label">Submission Date</td>
        <td><?= $applicant_submission_date ?></td>
    </tr>
</table>


<div class="section-title">PERSONAL INFORMATION</div>

<table class="info-table">

<tr>
    <td class="label">Surname</td>
    <td><?= $applicant_surname ?></td>

    <td class="label">First Name</td>
    <td><?= $applicant_first_name ?></td>
</tr>

<tr>
    <td class="label">Middle Name</td>
    <td><?= $applicant_middle_name ?></td>

    <td class="label">Suffix</td>
    <td><?= $applicant_suffix ?: '-' ?></td>
</tr>

<tr>
    <td class="label">Sex</td>
    <td><?= $applicant_sex ?></td>

    <td class="label">Civil Status</td>
    <td><?= $applicant_civil_status ?></td>
</tr>

<tr>
    <td class="label">Date of Birth</td>
    <td><?= $applicant_dob ?></td>

    <td class="label">Place of Birth</td>
    <td><?= $applicant_place_of_birth ?></td>
</tr>

</table>

<div class="section-title">CONTACT INFORMATION</div>

<table class="info-table">

<tr>
    <td class="label">Email Address</td>
    <td><?= $applicant_email ?></td>
</tr>

<tr>
    <td class="label">Contact Number</td>
    <td><?= $applicant_contact_number ?></td>
</tr>

<tr>
    <td class="label">Complete Address</td>
    <td>
        <?= $applicant_address_complete ?>,
        <?= $applicant_barangay ?>,
        <?= $applicant_city ?>,
        <?= $applicant_province ?>
    </td>
</tr>

</table>



<div class="section-title">ACADEMIC INFORMATION</div>

<table class="info-table">

<tr>
    <td class="label">Course</td>
    <td><?= $applicant_course_code ?> - <?= $applicant_course_name ?></td>
</tr>

<tr>
    <td class="label">Last School Attended</td>
    <td><?= $applicant_last_school ?></td>
</tr>

<tr>
    <td class="label">Year Graduated</td>
    <td><?= $applicant_year_graduated ?></td>
</tr>

</table>

<div class="section-title">PARENT / GUARDIAN</div>

<table class="info-table">

<tr>
    <td class="label">Parent Name</td>
    <td><?= $applicant_parent_name ?></td>
</tr>

<tr>
    <td class="label">Contact Number</td>
    <td><?= $applicant_parent_contact ?></td>
</tr>

<tr>
    <td class="label">Address</td>
    <td><?= $appplicant_parent_address ?></td>
</tr>

</table>


<div class="section-title">SUBMITTED REQUIREMENTS</div>

<table class="curriculum-table">

<thead>
<tr>
    <th>Submitted Requirements</th>
     <th>Status</th>
</tr>
</thead>

<tbody>

<?php foreach($enrollee_documents as $doc): ?>

    

<tr>
    <td><?= $doc['requirement_name'] ?></td>
    <td><?= $doc['submission_status'] != 'Missing' ? 'Submitted' : 'Missing' ?></td>
</tr>



<?php endforeach; ?>

</tbody>

</table>

   

   

</div>

</body>
</html>