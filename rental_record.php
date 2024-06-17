<?php
if (!isset($_SESSION)) {
    session_start();
}
if (!isset($_SESSION['username'])) {
    $status = "invalid";
    $is_admin = "N";
    header("Location: index.php");
} else {
    $status = "valid";
    $is_admin = $_SESSION['is_admin'];
    if ($is_admin == 'Y') {
        header("Location: admin_center.php");
        exit();
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>資源租借系統 - 查詢租借狀況</title>
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
    <div class="flex items-center justify-center min-h-screen">
        <h3
            class="mb-4 text-4xl font-extrabold leading tracking-tight text-gray-900 md:text-3xl lg:text-4xl dark:text-white">
            租借紀錄</h3>
    </div>

    <div class="max-w-screen-xl mx-auto p-4 sm:p-6 lg:p-8">
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">
                            次序
                        </th>
                        <th scope="col" class="px-6 py-3">
                            送出日期
                        </th>
                        <th scope="col" class="px-6 py-3">
                            教室
                        </th>
                        <th scope="col" class="px-6 py-3">
                            時間
                        </th>
                        <th scope="col" class="px-6 py-3">
                            節次
                        </th>
                        <th scope="col" class="px-6 py-3">
                            審核結果
                        </th>
                        <th scope="col" class="px-6 py-3">
                            <span class="sr-only">Edit</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $conn = require_once "config.php";
                    $stmt = $conn->prepare("SELECT * FROM rental_table ORDER BY rent_date ASC, rent_period ASC");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $rental_list = array();
                    for ($i = 1; $i <= $result->num_rows; $i++) {

                        $row = $result->fetch_assoc();
                        if ($row['username'] != $_SESSION['username']) {
                            continue;
                        }
                        $temp[] = $row['rent_period'];
                        // 同create_time的資料合併
                        if (isset($rental_list[$row['create_time']])) {
                            if ($row['rent_period'] == 'A') {
                                if ($rental_list[$row['create_time']]['start_period'] <= "4") {
                                    if ($rental_list[$row['create_time']]['end_period'] == '5') {
                                        continue;
                                    } else {
                                        $rental_list[$row['create_time']]['end_period'] = 'A';
                                    }
                                } else {
                                    $rental_list[$row['create_time']]['start_period'] = 'A';
                                }
                            } else {
                                $rental_list[$row['create_time']]['end_period'] = $row['rent_period'];
                            }
                        } else {
                            $rental_list[$row['create_time']] = array(
                                'create_time' => $row['create_time'],
                                'classroom' => $row['classroom'],
                                'rent_date' => $row['rent_date'],
                                'start_period' => $row['rent_period'],
                                'end_period' => $row['rent_period'],
                                'rent_status' => $row['rent_status']
                            );
                        }
                    }
                    if (count($rental_list) == 0) {
                        ?>
                        <tr class='bg-white border-b dark:bg-gray-800 dark:border-gray-70'>
                            <td class='px-6 py-4 whitespace-nowrap' colspan='7'>無租借紀錄</td>
                        </tr>
                        <?php
                    } else {
                        sort($rental_list);
                        $j = 1;
                        foreach ($rental_list as $rental) {
                            ?>
                            <tr class='bg-white border-b dark:bg-gray-800 dark:border-gray-70'>
                                <td class='px-6 py-4 whitespace-nowrap'><?php echo $j; ?></td>
                                <td class='px-6 py-4 whitespace-nowrap'><?php echo $rental['create_time']; ?></td>
                                <td class='px-6 py-4 whitespace-nowrap'><?php echo $rental['classroom']; ?></td>
                                <td class='px-6 py-4 whitespace-nowrap'><?php echo $rental['rent_date']; ?></td>
                                <?php if ($rental['start_period'] == $rental['end_period']) { ?>
                                    <td class='px-6 py-4 whitespace-nowrap'><?php echo $rental['start_period']; ?></td>
                                <?php } else { ?>
                                    <td class='px-6 py-4 whitespace-nowrap'>
                                        <?php echo $rental['start_period'] . "-" . $rental['end_period']; ?>
                                    </td>
                                <?php } ?>
                                <td class='px-6 py-4 whitespace-nowrap'>
                                    <?php
                                    if ($rental['rent_status'] == 'U') {
                                        echo "審核中";
                                    } else if ($rental['rent_status'] == 'Y') {
                                        echo "已審核";
                                    } else {
                                        echo "未通過";
                                    }
                                    ?>
                                </td>
                                <!-- post delete -->
                                <?php
                                if ($rental['rent_status'] == 'U') {
                                    ?>
                                    <td class='px-6 py-4 whitespace-nowrap'>
                                        <form action='rent.php' method='DELETE'>
                                            <input type='hidden' name='create_time' value='<?php echo $rental['create_time']; ?>'>
                                            <button type='submit' class='text-red-600 hover:text-red-900'>刪除</button>
                                        </form>
                                    </td>
                                    <?php
                                } else {
                                    ?>
                                    <td class='px-6 py-4 whitespace-nowrap'></td>
                                    <?php
                                }
                                ?>

                            </tr>
                            <?php
                            $j++;
                        }
                    }
                    $stmt->close();
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include './components/footer.php'; ?>

    <script src="./static/js/theme-toggle.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>

</html>