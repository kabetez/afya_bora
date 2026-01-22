<?php
include("../config/db.php");
$result = mysqli_query($conn, "SELECT * FROM patients");
?>

<h2>Registered Patients</h2>

<table border="1">
<tr>
    <th>Patient No</th>
    <th>Name</th>
    <th>Gender</th>
    <th>Phone</th>
    <th>Action</th>
</tr>

<?php while ($row = mysqli_fetch_assoc($result)) { ?>
<tr>
    <td><?= $row['patient_number']; ?></td>
    <td><?= $row['first_name']." ".$row['last_name']; ?></td>
    <td><?= $row['gender']; ?></td>
    <td><?= $row['phone']; ?></td>
    <td>
        <a href="view.php?id=<?= $row['id']; ?>">View</a>
    </td>
</tr>
<?php } ?>
</table>
