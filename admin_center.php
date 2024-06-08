<?php
if (!isset($_SESSION)) {
    session_start();
}
if (!isset($_SESSION['username'])) {
    $status = "invalid";
} else {
    $status = "valid";
    $is_admin = $_SESSION['is_admin'];
}

$conn = require_once "config.php";


// 從資料庫中選擇所有租借記錄
$sql = "SELECT * FROM rental_table";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Title</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script>
        // On page load or when changing themes, best to add inline in `head` to avoid FOUC
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>
</head>

<body class="bg-white dark:bg-gray-900">
    <!-- navigation -->
    <?php include 'components\navigaion.php'; ?>

    <!-- 在這裡插入你的內容 -->
    <?php if ($result->num_rows > 0): ?>
        <form action="approve.php" method="post">
            <table border="1">
                <tr>
                    <th>Create Time</th>
                    <th>Username</th>
                    <th>Classroom</th>
                    <th>Rent Date</th>
                    <th>Rent Period</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['create_time']; ?></td>
                        <td><?php echo $row['username']; ?></td>
                        <td><?php echo $row['classroom']; ?></td>
                        <td><?php echo $row['rent_date']; ?></td>
                        <td><?php echo $row['rent_period']; ?></td>
                        <td><?php echo $row['reason']; ?></td>
                        <td><?php echo $row['rent_status']; ?></td>
                        <td>
                            <?php if ($row['rent_status'] == 'U'): ?>
                                <select name="status[<?php echo $row['create_time']; ?>]">
                                    <option value="Y">通過</option>
                                    <option value="N">不通過</option>
                                </select>
                            <?php else: ?>
                                已審核
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
            <input type="submit" value="Submit">
        </form>
    <?php else: ?>
        <p>No rentals found.</p>
    <?php endif; ?>
    <?php $conn->close(); ?>


    <script src="./static/js/theme-toggle.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>

</html>


