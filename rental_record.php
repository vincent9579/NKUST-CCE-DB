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
                                'end_period' => $row['rent_period']
                            );
                        }
                    }


                    if (count($rental_list) == 0) {
                        echo "<tr class='bg-white border-b dark:bg-gray-800 dark:border-gray-70'>";
                        echo "<td class='px-6 py-4 whitespace-nowrap' colspan='6'>無租借紀錄</td>";
                        echo "</tr>";
                    } else {
                        sort($rental_list);
                        $j = 1;
                        foreach ($rental_list as $rental) {
                            echo "<tr class='bg-white border-b dark:bg-gray-800 dark:border-gray-70'>";
                            echo "<td class='px-6 py-4 whitespace-nowrap'>" . $j . "</td>";
                            echo "<td class='px-6 py-4 whitespace-nowrap'>" . $rental['create_time'] . "</td>";
                            echo "<td class='px-6 py-4 whitespace-nowrap'>" . $rental['classroom'] . "</td>";
                            echo "<td class='px-6 py-4 whitespace-nowrap'>" . $rental['rent_date'] . "</td>";
                            echo "<td class='px-6 py-4 whitespace-nowrap'>" . $rental['start_period'] . "-" . $rental['end_period'] . "</td>";
                            echo "<td class='px-6 py-4 whitespace-nowrap'><a href='edit_rental.php?classroom=" . $rental['classroom'] . "&rent_date=" . $rental['rent_date'] . "&start_period=" . $rental['start_period'] . "&end_period=" . $rental['end_period'] . "' class='text-blue-600 hover:text-blue-900'>編輯</a></td>";
                            echo "</tr>";
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



    <script src="./static/js/theme-toggle.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>

</html>