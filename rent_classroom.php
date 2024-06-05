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
if ($conn) {
    $stmt = $conn->prepare("SELECT st.std_name FROM user_data ud JOIN student_account sa ON ud.user_id = sa.user_id JOIN student_table st ON sa.std_id = st.std_id WHERE ud.user_name = ?");
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($result->num_rows == 1) {
        $std_name = $row['std_name'];
    } else {
        $std_name = "Guest";
    }

    $stmt->close();
} else {
    // Handle database connection error
    // For example, display an error message or redirect to an error page
    echo "Failed to connect to the database.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $jsonData = $_POST['data'];
    $decodeData = json_decode($jsonData, true);
    $classroom = $decodeData['classroom'];
    $weekday = $decodeData['weekday'];
    $start_time = $decodeData['start_time'];
    $end_time = $decodeData['end_time'];

} else {
    $classroom = "";
    $weekday = "";
    $rent_date = "";
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
</head>

<body class="bg-white dark:bg-gray-900">
    <!-- navigation -->
    <?php include 'components\navigaion.php'; ?>

    <!-- 在這裡插入你的內容 -->
    <div class="flex h-screen justify-center items-center">
        <div class="w-96 relative bg-white rounded-lg shadow dark:bg-gray-700 ">
            <div class="w-full p-4 md:p-5">
                <input type="hidden" name="classroom" value="">
                <div class="grid gap-4 mb-4 grid-cols-2">
                    <div class="col-span-2">
                        <label for="name"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">姓名</label>
                        <input readonly="readonly" type="text" name="std_name" id="std_name"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            placeholder="" required="" value="<?php echo htmlspecialchars($std_name) ?>">
                    </div>
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] == "POST") {
                        echo '
                            <div class="col-span-2">
                            <label for="classroom"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">教室</label>
                            <label
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                ' . $classroom . '
                            </label>
                             </div>
                            ';
                    } else {
                        echo '
                            <div class="col-span-2">
                            <label for="classroom"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">教室</label>
                            <input type="text" name="classroom" id="classroom"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="" required="" value="">
                             </div>
                            ';
                    }
                    ?>

                    <div class="col-span-2">
                        <label for="price"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">日期</label>
                        <div class="relative max-w-sm">
                            <select id="date"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <?php
                                if ($_SERVER['REQUEST_METHOD'] == "POST") {
                                    $today = date('Y/m/d');
                                    $weekdayMap = [
                                        '一' => 1,
                                        '二' => 2,
                                        '三' => 3,
                                        '四' => 4,
                                        '五' => 5,
                                        '六' => 6,
                                        '日' => 7,
                                    ];

                                    $currentWeekday = date('N', strtotime($today));

                                    // Calculate the difference between current weekday and selected weekday
                                    $difference = $weekdayMap[$weekday] - $currentWeekday;
                                    if ($difference <= 0) {
                                        $difference += 7;
                                    }

                                    // Get the next four weekdays
                                    $nextWeekdays = [];
                                    for ($i = 0; $i < 28; $i += 7) {
                                        $nextWeekdays[] = date('Y/m/d', strtotime("+$difference day", strtotime($today)));
                                        $difference += 7;
                                    }

                                    // Output the options
                                    foreach ($nextWeekdays as $nextWeekday) {
                                        echo '<option value="' . $nextWeekday . '">' . $nextWeekday . '</option>';
                                    }
                                } else {
                                    echo '<option value="" selected>Select date</option>';
                                    foreach ($weekdayMap as $key => $value) {
                                        echo '<option value="' . $key . '">' . $key . '</option>';
                                    }
                                }
                                ?>

                            </select>
                        </div>
                    </div>
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] == "POST") {
                        echo '
                            
                            <div class="col-span-2 sm:col-span-1">
                        <label for="time"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">開始借用時間</label>
                        <label class="relative max-w-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            ' . $start_time . '
                        </label>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label for="time"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">歸還時間</label>
                        <label class="relative max-w-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            ' . $end_time . '
                        </label>
                            
                    </div>
                    ';
                    } else {
                        echo '
                            <div class="col-span-2 sm:col-span-1">
                        <label for="time"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">開始借用時間</label>
                        <select id="start-time"
                            class="relative max-w-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            <option value="" selected>Select start time</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="A">A</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                            <option value="11">11</option>
                            <option value="12">12</option>
                            <option value="13">13</option>
                        </select>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label for="time"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">歸還時間</label>
                        <select id="end-time"
                            class="relative max-w-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            >
                            <option value="" disabled selected>Select end time</option>
                        </select>
                    </div>
                    <script>
                        // 獲取開始借用時間和歸還時間的選擇元素
                        var startTimeSelect = document.getElementById(\'start-time\');
                        var endTimeSelect = document.getElementById(\'end-time\');

                        // 定義對應關係
                        var optionsMap = {
                            \'1\': [\'1\', \'2\', \'3\'],
                            \'2\': [\'2\', \'3\', \'4\'],
                            \'3\': [\'3\', \'4\', \'A\'],
                            \'4\': [\'4\', \'A\', \'5\'],
                            \'5\': [\'5\', \'6\', \'7\'],
                            \'6\': [\'6\', \'7\', \'8\'],
                            \'7\': [\'7\', \'8\', \'9\'],
                            \'8\': [\'8\', \'9\', \'10\'],
                            \'9\': [\'9\', \'10\', \'11\'],
                            \'10\': [\'10\', \'11\', \'12\'],
                            \'11\': [\'11\', \'12\', \'13\'],
                            \'12\': [\'12\', \'13\'],
                            \'13\': [\'13\'],
                            \'A\': [\'A\', \'5\', \'6\']
                        };

                        // 監聽開始借用時間的改變
                        startTimeSelect.addEventListener(\'change\', function () {
                            // 獲取選擇的開始借用時間的值
                            var startTimeValue = startTimeSelect.value;

                            // 清空歸還時間的選項
                            endTimeSelect.innerHTML = \'\';

                            // 根據開始借用時間的值獲取對應的歸還時間選項
                            var options = optionsMap[startTimeValue];

                            // 循環添加選項到歸還時間的選擇元素
                            options.forEach(function (option) {
                                // 創建選項元素
                                var optionElement = document.createElement(\'option\');
                                optionElement.value = option;
                                optionElement.textContent = option;
                                // 添加到歸還時間的選擇元素中
                                endTimeSelect.appendChild(optionElement);
                            });
                        });
                    </script>

                        ';
                    }

                    ?>


                    <div class="col-span-2">
                        <label for="description"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">備註</label>
                        <textarea id="description" rows="4" name="rent_reason" id="rent_reason"
                            class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            placeholder="Write product description here"></textarea>
                    </div>
                </div>
                <button type="submit" onclick="return submitForm()"
                    class="text-white inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                    <svg class="me-1 -ms-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                            clip-rule="evenodd"></path>
                    </svg>
                    送出
                </button>

            </div>
        </div>
    </div>

    <script>
        function submitForm() {
            var classroom = "<?php echo $classroom ?>";
            var weekday = "<?php echo $weekday ?>";
            var rent_date = document.getElementById('date').value;
            var start_period = "<?php echo $start_time ?>";
            var end_period = "<?php echo $end_time ?>";
            var rent_reason = document.getElementById('description').value;

            if (classroom === "" || weekday === "" || rent_date === "" || start_period === "" || end_period === "") {
                alert("Please fill in all the fields.");
                return false;
            }

            var data = {
                classroom: classroom,
                rent_date: rent_date,
                start_period: start_period,
                end_period: end_period,
                rent_reason: rent_reason
            };

            console.log(data);

            $.ajax({
                type: "POST",
                url: "rent.php",
                data: { data: JSON.stringify(data) },
                success: function (response) {
                    console.log(response);
                },
                error: function (response) {
                    console.log(response);
                }
            });
        }
    </script>



    <script src="./static/js/theme-toggle.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>

</html>