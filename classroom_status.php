<?php
if (!isset($_SESSION)) {
    session_start();
}
if (!isset($_SESSION['username'])) {
    $status = "invalid";
    $is_admin = "N";
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
$timeTable = [[], [], [], [], [], [], []];
if ($stmt) {
    if ($params) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

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
        if ($period == 'A') {
            $timeTable[$weekday - 1][4] = $row;
        } else {
            if ($period < '5') {
                // string to int
                $period = (int) $period;
                $timeTable[$weekday - 1][$period - 1] = $row;
            } else {
                $period = (int) $period;
                $timeTable[$weekday - 1][$period] = $row;
            }

        }
    }
}

$query = "SELECT * FROM rental_table WHERE classroom = ? AND rent_status = 'Y'";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $classroom);
$stmt->execute();

$rental = $stmt->get_result();
while ($row = $rental->fetch_assoc()) {
    // 確認rent_date是否為未來日期
    $today = date('Y-m-d');
    if ($row['rent_date'] < $today) {
        continue;
    }
    $rent_date = $row['rent_date'];
    $period = $row['rent_period'];
    $weekday = date('N', strtotime($row['rent_date']));

    if ($period == 'A') {
        // 判斷timeTable是否已經有值
        if (isset($timeTable[$weekday - 1][4])) {
            // 判斷instructor與username是否相同
            if ($timeTable[$weekday - 1][4]['instructor'] != $row['username']) {
                $timeTable[$weekday - 1][4]['instructor'] .= '、' . $row['username'];
            }
            $timeTable[$weekday - 1][4]['major'] .= ' ' . $rent_date;
            continue;
        }
        $timeTable[$weekday - 1][4] = $row;
        $timeTable[$weekday - 1][4]['course_name'] = "租借";
        $timeTable[$weekday - 1][4]['instructor'] = $row['username'];
        $timeTable[$weekday - 1][4]['major'] = $rent_date;
    } else {
        if ($period < '5') {
            // string to int
            $period = (int) $period;
            if (isset($timeTable[$weekday - 1][$period - 1])) {
                if ($timeTable[$weekday - 1][$period - 1]['instructor'] != $row['username']) {
                    $timeTable[$weekday - 1][$period - 1]['instructor'] .= '、' . $row['username'];
                }
                $timeTable[$weekday - 1][$period - 1]['major'] .= ' ' . $rent_date;
                continue;
            }
            $timeTable[$weekday - 1][$period - 1] = $row;
            $timeTable[$weekday - 1][$period - 1]['course_name'] = "租借";
            $timeTable[$weekday - 1][$period - 1]['instructor'] = $row['username'];
            $timeTable[$weekday - 1][$period - 1]['major'] = $rent_date;
        } else {
            $period = (int) $period;
            if (isset($timeTable[$weekday - 1][$period])) {
                if ($timeTable[$weekday - 1][$period]['instructor'] != $row['username']) {
                    $timeTable[$weekday - 1][$period]['instructor'] .= '、' . $row['username'];
                }
                $timeTable[$weekday - 1][$period]['major'] .= ' ' . $rent_date;
                continue;
            }
            $timeTable[$weekday - 1][$period] = $row;
            $timeTable[$weekday - 1][$period]['course_name'] = "租借";
            $timeTable[$weekday - 1][$period]['instructor'] = $row['username'];
            $timeTable[$weekday - 1][$period]['major'] = $rent_date;
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
    <?php include './components/navigaion.php'; ?>

    <!-- 選擇教室 -->
    <?php if (isset($_SESSION['warn'])): ?>
        <div id="alert-border-2"
            class="flex items-center p-4 mb-4 text-red-800 border-t-4 border-red-300 bg-red-50 dark:text-red-400 dark:bg-gray-800 dark:border-red-800"
            role="alert">
            <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                viewBox="0 0 20 20">
                <path
                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
            </svg>
            <div class="ms-3 text-sm font-medium">
                教室不存在! 請確認輸入的教室編號是否正確。
            </div>
            <button type="button"
                class="ms-auto -mx-1.5 -my-1.5 bg-red-50 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-gray-700"
                data-dismiss-target="#alert-border-2" aria-label="Close">
                <span class="sr-only">Dismiss</span>
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                </svg>
            </button>
        </div>
        <?php unset($_SESSION['warn']); ?>
    <?php endif; ?>

    <?php if ($classroom == ""): ?>
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
    <?php else: ?>
        <?php
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
        ?>
        <div class="max-w-screen-xl mx-auto p-4 sm:p-6 lg:p-8">
            <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                <div class="overflow-x-auto">
                    <?php if ($status == "valid"): ?>
                        <button class="w-24 h-10 bg-blue-700 text-white rounded-lg"
                            onclick="getCheckedCheckboxes()">租借教室</button>
                        <form id="redirectForm" method="POST" action="rent_classroom.php" style="display: none;">
                            <input type="hidden" name="data" id="hiddenData">
                        </form>
                    <?php endif; ?>
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
                            <?php for ($i = 0; $i < 14; $i++): ?>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <td class="px-6 py-4  text-sm font-medium text-gray-900 dark:text-white">
                                        <?php if ($i < 4): ?>
                                            <?= $i + 1 ?><br>
                                        <?php elseif ($i == 4): ?>
                                            A<br>
                                        <?php else: ?>
                                            <?= $i ?><br>
                                        <?php endif; ?>
                                        <?= $periodToTime[$i] ?>
                                    </td>
                                    <?php for ($j = 0; $j < 7; $j++): ?>
                                        <td class="px-6 py-4  text-sm font-medium text-gray-900 dark:text-white">
                                            <?php if (isset($timeTable[$j][$i])): ?>
                                                <?php $row = $timeTable[$j][$i]; ?>
                                                <?php if ($row['course_name'] == "租借"): ?>
                                                    <input id="checkbox_<?= $j ?>_<?= $i ?>" type="checkbox"
                                                        name="checkbox[<?= $j ?>][<?= $i ?>]" value="<?= $j ?>_<?= $i ?>"
                                                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 checkbox-column-<?= $j ?>"
                                                        data-column="<?= $j ?>" data-row="<?= $i ?>"
                                                        onclick="limitCheckbox(this, <?= $j ?>, <?= $i ?>)">
                                                <?php endif; ?>
                                                <?= $row['course_name'] ?><br>
                                                <?= $row['instructor'] ?><br>
                                                <?= $row['major'] ?>
                                            <?php else: ?>
                                                <?php if ($status != "invalid"): ?>
                                                    <?php if ($is_admin == "N"): ?>
                                                        <input id="checkbox_<?= $j ?>_<?= $i ?>" type="checkbox"
                                                            name="checkbox[<?= $j ?>][<?= $i ?>]" value="<?= $j ?>_<?= $i ?>"
                                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 checkbox-column-<?= $j ?>"
                                                            data-column="<?= $j ?>" data-row="<?= $i ?>"
                                                            onclick="limitCheckbox(this, <?= $j ?>, <?= $i ?>)">
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    <?php endfor; ?>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <?php include './components/footer.php'; ?>
    <script>
        function getCheckedCheckboxes() {
            var classroom = "<?= $classroom ?>";
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
                    var weekday = values[i].split("_")[0];
                    var time = values[i].split("_")[1];
                    weekdays.push(numToWeekday[weekday]);

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
                    start_time = times[0];
                    end_time = times[0];
                } else {
                    if (times.includes("A")) {
                        if (times.length == 2) {
                            if (times[0] <= 4) {
                                start_time = times[0];
                                end_time = times[1];
                            } else {
                                start_time = times[1];
                                end_time = times[0];
                            }
                        }
                        if (times.length == 3) {
                            if (times[1] == 6) {
                                start_time = times[2];
                                end_time = times[1];
                            } else if (times[1] == 5) {
                                start_time = times[0];
                                end_time = times[1];
                            } else {
                                start_time = times[0];
                                end_time = times[2];
                            }
                        }
                    } else {
                        start_time = times[0];
                        end_time = times[times.length - 1];
                    }
                }
            }

            var postData = {
                weekday: weekdays[0],
                start_time: start_time,
                end_time: end_time,
                classroom: classroom
            };

            $.ajax({
                url: 'rent_classroom.php',
                type: 'POST',
                data: postData,
                success: function (response) {
                    $('#hiddenData').val(JSON.stringify(postData));
                    $('#redirectForm').submit();
                },
                error: function (xhr, status, error) {
                    alert('跳轉失敗: ' + error);
                }
            });
            return true;
        }
    </script>

    <script>
        // JavaScript to populate the date select field
        document.addEventListener("DOMContentLoaded", function () {
            var dateSelect = document.getElementById('date');
            var today = new Date();

            function formatDate(date) {
                var d = new Date(date);
                var month = '' + (d.getMonth() + 1);
                var day = '' + d.getDate();
                var year = d.getFullYear();

                if (month.length < 2) month = '0' + month;
                if (day.length < 2) day = '0' + day;

                return [year, month, day].join('/');
            }

            // Fetch unavailable dates from the server
            fetch('unavailable_dates.php')
                .then(response => response.json())
                .then(data => {
                    let unavailableDates = data; // Dates where rent_status = 'Y'
                    for (let i = 0; i < 28; i++) {
                        let date = new Date();
                        date.setDate(today.getDate() + i);
                        let formattedDate = formatDate(date);
                        if (!unavailableDates.includes(formattedDate)) {
                            let option = document.createElement('option');
                            option.value = formattedDate;
                            option.textContent = formattedDate;
                            dateSelect.appendChild(option);
                        }
                    }
                })
                .catch(error => console.error('Error fetching unavailable dates:', error));
        });
    </script>

    <script src="./static/js/theme-toggle.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>

</html>