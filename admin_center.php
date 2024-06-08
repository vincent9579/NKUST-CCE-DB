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

if ($is_admin != 'Y'){
    header("Location: index.php");
    exit();
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
    <?php include './components/navigaion.php'; ?>

    <!-- 在這裡插入你的內容 -->
    <?php if ($result->num_rows > 0): ?>
    <form action="approve.php" method="post">
        <div class="relative overflow-x-auto shadow-md  sm:p-6 lg:p-8 ">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 border-collapse border border-gray-200 dark:border-gray-700">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3 border border-gray-200 dark:border-gray-700">
                            申請時間
                        </th>
                        <th class="px-6 py-3 border border-gray-200 dark:border-gray-700">
                            使用者名字
                        </th>
                        <th class="px-6 py-3 border border-gray-200 dark:border-gray-700">
                            租借教室
                        </th>
                        <th class="px-6 py-3 border border-gray-200 dark:border-gray-700">
                            租借日期
                        </th>
                        <th class="px-6 py-3 border border-gray-200 dark:border-gray-700">
                            租借時段
                        </th>
                        <th class="px-6 py-3 border border-gray-200 dark:border-gray-700">
                            租借原因
                        </th>
                        <th class="px-6 py-3 border border-gray-200 dark:border-gray-700">
                            租借狀態
                        </th>
                        <th class="px-6 py-3 border border-gray-200 dark:border-gray-700">
                            審核
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php else: ?>
                        <p>No rentals found.</p>
                    <?php endif; ?>
                        <?php $conn->close(); ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="bg-white dark:bg-gray-800 px-6 py-3">
                                <td class="bg-white dark:bg-gray-800 px-6 py-3"><?php echo $row['create_time']; ?></td>
                                <td class="bg-white dark:bg-gray-800 px-6 py-3"><?php echo $row['username']; ?></td>
                                <td class="bg-white dark:bg-gray-800 px-6 py-3"><?php echo $row['classroom']; ?></td>
                                <td class="bg-white dark:bg-gray-800 px-6 py-3"><?php echo $row['rent_date']; ?></td>
                                <td class="bg-white dark:bg-gray-800 px-6 py-3"><?php echo $row['rent_period']; ?></td>
                                <td class="bg-white dark:bg-gray-800 px-6 py-3"><?php echo $row['reason']; ?></td>
                                <td class="bg-white dark:bg-gray-800 px-6 py-3"><?php echo $row['rent_status']; ?></td>
                                <td>
                                    <?php if ($row['rent_status'] == 'U'): ?>
                                        <select class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block  p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" name="status[<?php echo $row['create_time']; ?>]">
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
                <div class="flex justify-center mt-4">
                    <button type="submit" 
                    class="text-white inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">送出</button>
                </div>
                
                </tbody>
            </table>
        </div>
    </form>

    <script src="./static/js/theme-toggle.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>

</html>






