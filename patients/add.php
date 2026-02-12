<?php
session_start();
require_once '../config/db.php';

// Security: only doctors should register patients
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../login.php");
    exit();
}

$message = '';
$message_type = 'success';

if (isset($_POST['reg_patient_btn'])) {
    $full_name = trim(mysqli_real_escape_string($conn, $_POST['full_name'] ?? ''));
    $national_id = trim(mysqli_real_escape_string($conn, $_POST['national_id'] ?? ''));
    $dob = trim(mysqli_real_escape_string($conn, $_POST['dob'] ?? ''));
    $phone = trim(mysqli_real_escape_string($conn, $_POST['phone'] ?? ''));
    $county = trim(mysqli_real_escape_string($conn, $_POST['county'] ?? ''));
    $blood_group = trim(mysqli_real_escape_string($conn, $_POST['blood_group'] ?? ''));
    $allergies = trim(mysqli_real_escape_string($conn, $_POST['allergies'] ?? ''));
    $chronic_conditions = trim(mysqli_real_escape_string($conn, $_POST['chronic_conditions'] ?? ''));
    $nok_name = trim(mysqli_real_escape_string($conn, $_POST['nok_name'] ?? ''));
    $nok_phone = trim(mysqli_real_escape_string($conn, $_POST['nok_phone'] ?? ''));

    if ($full_name === '' || $national_id === '' || $dob === '' || $phone === '' || $county === '' || $blood_group === '' || $nok_name === '' || $nok_phone === '') {
        $message_type = 'error';
        $message = "Please fill in all required fields.";
    } else {
        $exists_q = mysqli_query($conn, "SELECT patient_id FROM patients WHERE national_id = '$national_id' LIMIT 1");

        if ($exists_q && mysqli_num_rows($exists_q) > 0) {
            $message_type = 'error';
            $message = "A patient with National ID $national_id already exists.";
        } else {
            $has_username_col = false;
            $username_col_q = mysqli_query($conn, "SHOW COLUMNS FROM patients LIKE 'username'");
            if ($username_col_q && mysqli_num_rows($username_col_q) > 0) {
                $has_username_col = true;
            }

            if ($has_username_col) {
                $sql = "INSERT INTO patients (full_name, national_id, username, dob, phone, county, blood_group, allergies, chronic_conditions, next_of_kin_name, next_of_kin_phone)
                        VALUES ('$full_name', '$national_id', '$national_id', '$dob', '$phone', '$county', '$blood_group', '$allergies', '$chronic_conditions', '$nok_name', '$nok_phone')";
            } else {
                $sql = "INSERT INTO patients (full_name, national_id, dob, phone, county, blood_group, allergies, chronic_conditions, next_of_kin_name, next_of_kin_phone)
                        VALUES ('$full_name', '$national_id', '$dob', '$phone', '$county', '$blood_group', '$allergies', '$chronic_conditions', '$nok_name', '$nok_phone')";
            }

            if (mysqli_query($conn, $sql)) {
                $message_type = 'success';
                $message = "Patient registered successfully for National ID $national_id.";
                $_POST = [];
            } else {
                $message_type = 'error';
                $message = "Failed to register patient. " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>National Patient Registry | Afya Bora</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        :root {
            --brand-green: #004d40;
            --paper-bg: #ffffff;
            --border-color: #cbd5e1;
        }

        body {
            background: #e2e8f0; /* Darker background to make the "paper" pop */
            padding: 40px 0;
            font-family: 'Inter', sans-serif;
        }

        /* The A4 Container */
        .a4-container {
            max-width: 850px; /* Standard A4 proportion on screens */
            margin: auto;
            background: var(--paper-bg);
            padding: 50px 70px; /* Generous margins like a real document */
            border-radius: 4px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            position: relative;
            border-top: 8px solid var(--brand-green);
        }

        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .section-title {
            background: #f1f5f9;
            padding: 8px 15px;
            font-size: 0.85rem;
            font-weight: 800;
            color: var(--brand-green);
            text-transform: uppercase;
            letter-spacing: 1px;
            display: block;
            margin: 25px 0 15px 0;
            border-left: 4px solid var(--brand-green);
        }

        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 15px; }
        
        label { display: block; font-weight: 600; font-size: 0.9rem; color: #334155; margin-bottom: 6px; }
        
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 1rem;
            box-sizing: border-box;
        }

        input:focus { border-color: var(--brand-green); outline: none; box-shadow: 0 0 0 3px rgba(0,77,64,0.1); }

        .btn-submit {
            background: var(--brand-green);
            color: white;
            padding: 18px;
            border: none;
            width: 100%;
            font-size: 1.1rem;
            font-weight: bold;
            text-transform: uppercase;
            cursor: pointer;
            margin-top: 30px;
            transition: 0.3s;
        }

        .btn-submit:hover { background: #00332a; }

        @media print {
            body { background: white; padding: 0; }
            .a4-container { box-shadow: none; width: 100%; max-width: 100%; padding: 0; }
            .btn-submit, .back-link { display: none; }
        }
    </style>
</head>
<body>

    <div class="a4-container">
        <div class="form-header">
            <div>
                <h1 style="margin:0; font-size: 1.8rem; color: #1e293b;">MINISTRY OF HEALTH</h1>
                <p style="margin:5px 0; color: #64748b; font-weight: bold;">National Patient Electronic Health Record</p>
            </div>
            <div style="text-align: right;">
                <p style="margin:0; font-size: 0.8rem;">FORM ID: NEHR-<?php echo date('Y'); ?>-K</p>
                <p style="margin:5px 0; font-size: 0.8rem; color: green;">● SECURE REGISTRY</p>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div style="padding: 15px; background: <?php echo $message_type === 'success' ? '#dcfce7' : '#fee2e2'; ?>; color: <?php echo $message_type === 'success' ? '#166534' : '#991b1b'; ?>; border-radius: 4px; margin-bottom: 20px;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <span class="section-title">1. Patient Identification</span>
            <div class="grid">
                <div>
                    <label>Full Legal Name</label>
                    <input type="text" name="full_name" required placeholder="Surname, First Name Middle Name">
                </div>
                <div>
                    <label>National ID / Passport Number</label>
                    <input type="text" name="national_id" required>
                </div>
            </div>

            <div class="grid">
                <div>
                    <label>Date of Birth</label>
                    <input type="date" name="dob" required>
                </div>
                <div>
                    <label>Contact Phone Number</label>
                    <input type="tel" name="phone" placeholder="e.g., 0712345678" required>
                </div>
            </div>

            <span class="section-title">2. Geographic & Medical Information</span>
            <div class="grid">
                <div>
                    <label>County of Residence</label>
                    <select name="county" required>
                        <option value="" disabled selected>Select County</option>
                        <option value="Bomet">036 - Bomet</option><option value="Bungoma">039 - Bungoma</option>
                        <option value="Busia">040 - Busia</option><option value="Elgeyo Marakwet">028 - Elgeyo Marakwet</option>
                        <option value="Embu">014 - Embu</option><option value="Garissa">007 - Garissa</option>
                        <option value="Homa Bay">043 - Homa Bay</option><option value="Isiolo">011 - Isiolo</option>
                        <option value="Kajiado">034 - Kajiado</option><option value="Kakamega">037 - Kakamega</option>
                        <option value="Kericho">035 - Kericho</option><option value="Kiambu">022 - Kiambu</option>
                        <option value="Kilifi">003 - Kilifi</option><option value="Kirinyaga">020 - Kirinyaga</option>
                        <option value="Kisii">045 - Kisii</option><option value="Kisumu">042 - Kisumu</option>
                        <option value="Kitui">015 - Kitui</option><option value="Kwale">002 - Kwale</option>
                        <option value="Laikipia">031 - Laikipia</option><option value="Lamu">005 - Lamu</option>
                        <option value="Machakos">016 - Machakos</option><option value="Makueni">017 - Makueni</option>
                        <option value="Mandera">009 - Mandera</option><option value="Marsabit">010 - Marsabit</option>
                        <option value="Meru">012 - Meru</option><option value="Migori">044 - Migori</option>
                        <option value="Mombasa">001 - Mombasa</option><option value="Murang'a">021 - Murang'a</option>
                        <option value="Nairobi">047 - Nairobi</option><option value="Nakuru">032 - Nakuru</option>
                        <option value="Nandi">029 - Nandi</option><option value="Narok">033 - Narok</option>
                        <option value="Nyamira">046 - Nyamira</option><option value="Nyandarua">018 - Nyandarua</option>
                        <option value="Nyeri">019 - Nyeri</option><option value="Samburu">025 - Samburu</option>
                        <option value="Siaya">041 - Siaya</option><option value="Taita Taveta">006 - Taita Taveta</option>
                        <option value="Tana River">004 - Tana River</option><option value="Tharaka Nithi">013 - Tharaka Nithi</option>
                        <option value="Trans Nzoia">026 - Trans Nzoia</option><option value="Turkana">023 - Turkana</option>
                        <option value="Uasin Gishu">027 - Uasin Gishu</option><option value="Vihiga">038 - Vihiga</option>
                        <option value="Wajir">008 - Wajir</option><option value="West Pokot">024 - West Pokot</option>
                    </select>
                </div>
                <div>
                    <label>Blood Group</label>
                    <select name="blood_group" required>
                        <option value="Unknown">Unknown / Not Tested</option>
                        <option value="A+">A Positive (A+)</option>
                        <option value="O+">O Positive (O+)</option>
                        <option value="B+">B Positive (B+)</option>
                        <option value="AB+">AB Positive (AB+)</option>
                        <option value="A-">A Negative (A-)</option>
                        <option value="O-">O Negative (O-)</option>
                    </select>
                </div>
            </div>

            <div class="grid">
                <div>
                    <label>Known Allergies</label>
                    <textarea name="allergies" rows="2" placeholder="List drug or food allergies..."></textarea>
                </div>
                <div>
                    <label>Chronic Conditions</label>
                    <textarea name="chronic_conditions" rows="2" placeholder="e.g., Hypertension, Diabetes..."></textarea>
                </div>
            </div>

            <span class="section-title">3. Next of Kin (Emergency Contact)</span>
            <div class="grid">
                <div>
                    <label>Full Name of Next of Kin</label>
                    <input type="text" name="nok_name" required>
                </div>
                <div>
                    <label>NOK Phone Number</label>
                    <input type="tel" name="nok_phone" required>
                </div>
            </div>

            <button type="submit" name="reg_patient_btn" class="btn-submit">Register Record to National Node</button>
            <a href="../doctor_dashboard.php" class="back-link" style="display:block; text-align:center; margin-top:20px; color:#64748b; text-decoration:none;">Return to Dashboard</a>
        </form>
    </div>

</body>
</html>

