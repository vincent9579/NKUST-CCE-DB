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



$classroom = isset($_GET['classroom']) ? $_GET['classroom'] : '';

$where = '';
$params = [];

if ($classroom) {
    $query = "SELECT * FROM classroom_table WHERE classroom = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $classroom);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
        $stmt->close();
        $conn->close();
        $_SESSION['warn'] = true;
        header("Location: classroom_status.php");
        exit();
    }

    $where .= $where ? " AND classroom = ?" : "WHERE classroom = ?";
    $params[] = $classroom;
}


// 獲取教室狀況
$query = "SELECT * FROM course_table $where ORDER BY weekday, period";
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
        $chinese_weekdays = [
            '一' => 1,
            '二' => 2,
            '三' => 3,
            '四' => 4,
            '五' => 5,
            '六' => 6,
            '日' => 7
        ];
        $weekday = $row['weekday'];
        $weekday = $chinese_weekdays[$weekday];
        $period = $row['period'];

        // 1, 2, 3 ,4 ,A ,5 ,6 ,7 ,8 ,9 ,10, 11, 12, 13
        if ($period < '5') {
            // string to int
            $period = (int) $period;
            $timeTable[$weekday - 1][$period - 1] = $row;
        }
        if ($period == 'A') {
            $timeTable[$weekday - 1][4] = $row;
        }
        if ($period >= '5') {
            $period = (int) $period;
            $timeTable[$weekday - 1][$period] = $row;
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
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script>
        // On page load or when changing themes, best to add inline in `head` to avoid FOUC
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>

    <script>
        function limitCheckbox(checkbox, column, row) {
            const maxChecked = 3;
            const allCheckboxes = document.querySelectorAll('input[type="checkbox"]');
            const checkedCheckboxes = [];

            allCheckboxes.forEach(cb => {
                if (cb.checked) {
                    checkedCheckboxes.push(cb);
                }
            });

            if (checkedCheckboxes.length > maxChecked) {
                checkbox.checked = false;
                return;
            }

            if (checkedCheckboxes.length === maxChecked) {
                allCheckboxes.forEach(cb => {
                    if (!cb.checked) {
                        cb.disabled = true;
                    }
                });
            } else {
                allCheckboxes.forEach(cb => {
                    cb.disabled = false;
                });
            }

            checkContinuity(checkedCheckboxes, column, row);
            checkContinuity2(checkedCheckboxes);
        }

        function checkContinuity(checkedCheckboxes, currentColumn, currentRow) {
            if (checkedCheckboxes.length === 0) {
                return;
            }

            const columns = {};
            let currentColumnRows = [];

            checkedCheckboxes.forEach(cb => {
                const column = cb.dataset.column;
                const row = parseInt(cb.dataset.row);

                if (!columns[column]) {
                    columns[column] = [];
                }

                columns[column].push(row);

                if (column === currentColumn) {
                    currentColumnRows.push(row);
                }
            });

            currentColumnRows.sort((a, b) => a - b);

            // Check continuity within the same column
            for (let i = 0; i < currentColumnRows.length - 1; i++) {
                if (currentColumnRows[i + 1] !== currentColumnRows[i] + 1) {
                    alert("Checkboxes must be consecutive within the same column.");
                    checkedCheckboxes.forEach(cb => cb.checked = false);
                    document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.disabled = false);
                    return;
                }
            }

            // Check if only one column is being checked
            if (Object.keys(columns).length > 1 && checkedCheckboxes.length > 1) {
                alert("Checkboxes must be in the same column.");
                checkedCheckboxes.forEach(cb => cb.checked = false);
                document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.disabled = false);
            }
        }
        function checkContinuity2(checkedCheckboxes) {
            if (checkedCheckboxes.length === 0) {
                return;
            }

            const columns = {};
            checkedCheckboxes.forEach(cb => {
                const column = cb.dataset.column;
                const row = parseInt(cb.dataset.row);

                if (!columns[column]) {
                    columns[column] = [];
                }

                columns[column].push(row);
            });

            for (const column in columns) {
                const rows = columns[column];
                rows.sort((a, b) => a - b);

                for (let i = 0; i < rows.length - 1; i++) {
                    if (rows[i + 1] !== rows[i] + 1) {
                        alert("Checkboxes must be consecutive within the same column.");
                        checkedCheckboxes.forEach(cb => cb.checked = false);
                        document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.disabled = false);
                        return;
                    }
                }
            }
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
                        <a href="rent_classroom.php"
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
    <?php
    if (isset($_SESSION['warn'])) {
        echo '
            <div id="alert-border-2" class="flex items-center p-4 mb-4 text-red-800 border-t-4 border-red-300 bg-red-50 dark:text-red-400 dark:bg-gray-800 dark:border-red-800" role="alert">
    <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
      <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
    </svg>
    <div class="ms-3 text-sm font-medium">
      教室不存在! 請確認輸入的教室編號是否正確。
    </div>
    <button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-red-50 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-gray-700"  data-dismiss-target="#alert-border-2" aria-label="Close">
      <span class="sr-only">Dismiss</span>
      <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
      </svg>
    </button>
</div>
            ';
        unset($_SESSION['warn']);
    }
    ?>
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
        <div class="overflow-x-auto">
        
      
           
        ';
        if ($status == "valid") {

            echo '<button class="w-24 h-10 bg-blue-700 text-white rounded-lg" onclick="getCheckedCheckboxes()">租借教室</button>
            <form id="redirectForm" method="POST" action="rent_classroom.php" style="display: none;">
    <input type="hidden" name="data" id="hiddenData">
</form>

            ';
        }

        echo '
            <!-- 取消查詢 -->
            <a href="classroom_status.php" class="text-blue-700 hover:underline dark:text-blue-500">取消查詢</a>

            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 overflow-auto">
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
                    echo '<input  id="checkbox_' . $j . '_' . $i . '" type="checkbox" name="checkbox[' . $j . '][' . $i . ']" value="' . $j . '_' . $i . '" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 checkbox-column-' . $j . '" data-column="' . $j . '" data-row="' . $i . '" onclick="limitCheckbox(this, ' . $j . ', ' . $i . ')">';
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
        </form>
        </div>
        
        ';
    }
    ?>

    <script>
        function getCheckedCheckboxes() {
            var classroom = "<?php echo ($classroom) ?>";
            var checkboxes = document.querySelectorAll('input[type="checkbox"]:checked');
            var values = [];
            var weekdays = [];
            var times = [];
            var time_range = "";
            var start_time = "";
            var end_time = "";

            checkboxes.forEach(function (checkbox) {
                values.push(checkbox.value);
            });
            
            if (values.length == 0) {
                return false;
            } else {
                var numToWeekday = {
                    0: "一",
                    1: "二",
                    2: "三",
                    3: "四",
                    4: "五",
                    5: "六",
                    6: "日"
                };

                for (var i = 0; i < values.length; i++) {
                    // (4)4 to weekday=4 and time=4
                    var weekday = values[i].split("_")[0];
                    var time = values[i].split("_")[1];
                    weekdays.push(numToWeekday[weekday]);

                    // time = 4 時為A
                    if (time == 4) {
                        time = "A";
                        times.push(time);
                    } else if (time > 4) {
                        times.push(Number(time));
                    } else {
                        times.push(Number(time) + 1);
                    }

                    if (i > 0) {
                        if (weekdays[i] != weekdays[i - 1]) {

                            return false;
                        }
                    }
                }

                times.sort();

                if (times.length == 1) {
                    // list [5] to 5
                    start_time = times[0];
                    end_time = times[0];

                } else {
                    //處理有A的情況
                    if (times.includes("A")) {
                        if (times.length == 2) {
                            // list [5,A] to A-5 [4,A] to 4-A
                            if (times[0] <= 4) {
                                start_time = times[0];
                                end_time = times[1];
            
                            } else {
                                start_time = times[1];
                                end_time = times[0];
            
                            }

                        } else {
                            if (times[1] <= 4) {
                                // list [5,6,A] to A-6
                                start_time = times[0];
                                end_time = times[2];
            
                            } else {
                                // list [4,5,A] to 4-A
                                start_time = times[2];
                                end_time = times[1];
                            }
                        }
                    }
                    // list [5,6,7] to 5-7
                    start_time = times[0];
                    end_time = times[times.length - 1];
                }
            }
            
            var postData = {
                weekday: weekdays[0],
                start_time: start_time,
                end_time: end_time,
                classroom: classroom
            };

            $.ajax({
                url: 'rent_classroom.php', // 後端接收請求的URL
                type: 'POST',
                data: postData,
                success: function (response) {
                    alert('租借成功');
                    // 將需要傳遞的數據設置到隱藏表單
                    $('#hiddenData').val(JSON.stringify(postData));
                    // 提交表單
                    $('#redirectForm').submit();
                },
                error: function (xhr, status, error) {
                    // 處理錯誤響應
                    alert('租借失敗: ' + error);
                }
            });
            return true;
        }
    </script>

    <!-- 必要js -->
    <script src="./static/js/theme-toggle.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>

</html>