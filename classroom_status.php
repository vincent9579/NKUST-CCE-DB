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

$conn = require_once ('config.php');

// 設置每頁顯示的記錄數
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;


$classroom = isset($_GET['classroom']) ? $_GET['classroom'] : '';

$where = '';
$params = [];
if ($classroom) {
    $where .= $where ? " AND classroom = ?" : "WHERE classroom = ?";
    $params[] = $classroom;
}

// 獲取總記錄數
$total_query = "SELECT COUNT(*) FROM course_table $where";
$total_stmt = $conn->prepare($total_query);
if ($total_stmt) {
    if ($params) {
        $types = str_repeat('s', count($params));
        $total_stmt->bind_param($types, ...$params);
    }
    $total_stmt->execute();
    $total_result = $total_stmt->get_result();
    $total_rows = $total_result->fetch_row()[0];
    $total_pages = ceil($total_rows / $limit);
    $total_stmt->close();
}

function parseSchedule($input)
{
    // 匹配不同格式的字符串
    $pattern = '/\((.*?)\)(\d+)-(\d+)|\((.*?)\)(\d+)|\((.*?)\)([A-Za-z])/';
    if (preg_match($pattern, $input, $matches)) {
        // 將中文星期轉換為數字星期
        $chinese_weekdays = [
            '一' => 1,
            '二' => 2,
            '三' => 3,
            '四' => 4,
            '五' => 5,
            '六' => 6,
            '日' => 7
        ];

        // 確定星期
        $weekday_chinese = $matches[1] ?: ($matches[4] ?: $matches[6]);
        $weekday = isset($chinese_weekdays[$weekday_chinese]) ? $chinese_weekdays[$weekday_chinese] : null;

        // 確定時間段或單個時間
        if (!empty($matches[2]) && !empty($matches[3])) {
            // 处理时间段 5-7 这种格式
            $start = intval($matches[2]);
            $end = intval($matches[3]);
            $period = range($start, $end);
        } elseif (!empty($matches[5])) {
            // 处理单个数字 5 这种格式
            $period = [intval($matches[5])];
        } elseif (!empty($matches[7])) {
            // 处理单个字母 A 这种格式
            $period = [$matches[7]];
        } else {
            $period = [];
        }

        return [
            'weekday' => $weekday,
            'period' => $period
        ];
    }

    return null; // 若匹配失敗則返回 null
}


// 獲取教室狀況
$query = "SELECT * FROM course_table $where LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($query);
if ($stmt) {
    if ($params) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    $timeTable = [[], [], [], [], [], [], []];

    // 將result
    while ($row = $result->fetch_assoc()) {
        $parseTime = parseSchedule($row['class_time']);
        if ($parseTime) {
            $weekday = $parseTime['weekday'];
            $period = $parseTime['period'];
            foreach ($period as $p) {
                // 1, 2, 3 ,4 ,A ,5 ,6 ,7 ,8 ,9 ,10, 11, 12, 13
                if ($p < 5) {
                    $timeTable[$weekday - 1][$p - 1] = $row;
                }
                if ($p == 'A') {
                    $timeTable[$weekday - 1][4] = $row;
                }
                if ($p >= 5) {
                    $timeTable[$weekday - 1][$p] = $row;
                }

            }
        }

    }
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
    <!-- <script src="https://cdn.tailwindcss.com"></script> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/datepicker.min.js"></script>
</head>

<body class="bg-white dark:bg-gray-900">
    <!-- navigation -->
    <nav class="bg-white border-gray-200 dark:bg-gray-900">
        <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
            <a href="index.html" class="flex items-center space-x-3 rtl:space-x-reverse">
                <span class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white">資源租借系統</span>
            </a>
            <button data-collapse-toggle="navbar-default" type="button"
                class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600"
                aria-controls="navbar-default" aria-expanded="false">
                <span class="sr-only">開啟菜單</span>
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 17 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M1 1h15M1 7h15M1 13h15" />
                </svg>
            </button>
            <div class="hidden w-full md:block md:w-auto" id="navbar-default">
                <ul
                    class="font-medium flex flex-col p-4 md:p-0 mt-4 border border-gray-100 rounded-lg bg-gray-50 md:flex-row md:space-x-8 rtl:space-x-reverse md:mt-0 md:border-0 md:bg-white dark:bg-gray-800 md:dark:bg-gray-900 dark:border-gray-700">
                    <li>
                        <a href="index.php"
                            class="block py-2 px-3 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent"
                            aria-current="page">主頁</a>
                    </li>
                    <li>
                        <a href="classroom_status.php"
                            class="block py-2 px-3 text-white bg-blue-700 rounded md:bg-transparent md:text-blue-700 md:p-0 dark:text-white md:dark:text-blue-500">查詢教室狀況</a>
                    </li>
                    <li>
                        <a href="#"
                            class="block py-2 px-3 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent">租借教室</a>
                    </li>
                    <li>
                        <a href="#"
                            class="block py-2 px-3 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent">租借紀錄</a>
                    </li>
                    <?php
                    if ($status == "valid" && $is_admin == 'Y') {
                        echo '<li>
                        <a href="admin.php"
                            class="block py-2 px-3 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent">管理介面</a>
                    </li>';
                    }
                    ?>
                    <li>
                        <?php
                        if ($status == "valid") {
                            echo '<a href="logout.php" class="block py-2 px-3 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent">登出</a>';
                        } else {
                            echo '<a href="login.html" class="block py-2 px-3 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent">登入</a>';
                        }
                        ?>
                    </li>
                </ul>
            </div>
            <button id="theme-toggle" type="button"
                class="text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 rounded-lg text-sm p-2.5">
                <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                </svg>
                <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                        fill-rule="evenodd" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
    </nav>

    <!-- 選擇教室 -->
    <?php if ($classroom == "") {
        echo '
        <div class="flex h-screen justify-center items-center">
        <div
            class="w-full max-w-sm p-4 bg-white border border-gray-200 rounded-lg shadow sm:p-6 dark:bg-gray-800 dark:border-gray-700 mx-auto">
            <h5 class="mb-3 text-base font-semibold text-gray-900 md:text-xl dark:text-white">
                選擇教室
            </h5>

            <p class="text-sm font-normal text-gray-500 dark:text-gray-400">選擇一個教室即可查詢該教室的課表及租用情況</p>
            <ul class="my-4 space-y-3">
                <form action="" method="GET">
                    <div class="relative">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                            </svg>
                        </div>
                        <input type="search" id="default-search" value=""
                            class="block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            placeholder="教室編號" name="classroom" />

                    </div>
                    
                    <button type="submit"
                        class="w-full mt-4 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Search</button>
                </form>

            </ul>
            
        </div>
    </div>
        ';
    } else {
        $periodToTime = [
            "0" => "08:10-09:00",
            "1" => "09:10-10:00",
            "2" => "10:10-11:00",
            "3" => "11:10-12:00",
            "4" => "12:10-13:00",
            "5" => "13:30-14:20",
            "6" => "14:30-15:20",
            "7" => "15:30-16:20",
            "8" => "16:30-17:20",
            "9" => "17:30-18:20",
            "10" => "18:30-19:20",
            "11" => "19:25-20:15",
            "12" => "20:20-21:10",
            "13" => "21:15-22:05"
        ];
        echo '
        <div class="max-w-screen-xl mx-auto p-4 sm:p-6 lg:p-8">
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
      </div>  <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 overflow-auto">
            <thead
                class="text-xs text-gray-900 dark:text-white uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 whitespace-nowrap">
                <tr>
                    <th scope="col" class="px-6 py-3">節次</th>
                    <th scope="col" class="px-6 py-3">星期一</th>
                    <th scope="col" class="px-6 py-3">星期二</th>
                    <th scope="col" class="px-6 py-3">星期三</th>
                    <th scope="col" class="px-6 py-3">星期四</th>
                    <th scope="col" class="px-6 py-3">星期五</th>
                    <th scope="col" class="px-6 py-3">星期六</th>
                    <th scope="col" class="px-6 py-3">星期日</th>

                </tr>
            </thead>
            <tbody>

                ';
        for ($i = 0; $i < 14; $i++) {
            echo '<tr class=\'border-b border-gray-200 dark:border-gray-700\'>';
            echo '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">';
            if ($i < 4) {
                echo $i + 1;
            } elseif ($i == 4) {
                echo 'A';
            } else {
                echo $i;
            }
            echo '<br>';
            echo $periodToTime[$i];
            echo '</td>';
            for ($j = 0; $j < 7; $j++) {
                echo '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">';
                if (isset($timeTable[$j][$i])) {
                    $row = $timeTable[$j][$i];
                    echo $row['course_name'];
                    echo '<br>';
                    echo $row['instructor'];
                    echo '<br>';
                    echo $row['major'];

                } else {
                    echo '<input  id="checked-checkbox" type="checkbox" value="" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">';
                }
                echo '</td>';
            }
            echo '</tr>';
        }
        echo '
            </tbody>
        </table>
    </div>
    </div>
        ';
    }
    ?>

    <!-- 必要js -->
    <script src="./static/js/theme-toggle.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>

</html>