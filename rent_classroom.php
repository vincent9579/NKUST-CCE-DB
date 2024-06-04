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
$conn = require_once('config.php');
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

if ($_SERVER['REQUEST_METHOD']  == "POST"){
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
                            class="block py-2 px-3 text-gray-900 rounded hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 dark:text-white md:dark:hover:text-blue-500 dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent">查詢教室狀況</a>
                    </li>
                    <li>
                        <a href="rent_classroom.php"
                            class="block py-2 px-3 text-white bg-blue-700 rounded md:bg-transparent md:text-blue-700 md:p-0 dark:text-white md:dark:text-blue-500">租借教室</a>
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

    <!-- 在這裡插入你的內容 -->
    <div class="flex h-screen justify-center items-center">
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            <form class="p-4 md:p-5" method="POST" action="rent.php">
                <input type="hidden" name="classroom" value="">
                <div class="grid gap-4 mb-4 grid-cols-2">
                    <div class="col-span-2">
                        <label for="name"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">姓名</label>
                        <input readonly="readonly" type="text" name="std_name" id="std_name"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            placeholder="" required="" value="<?php echo htmlspecialchars($std_name) ?>">
                    </div>
                    <div class="col-span-2">
                        <label for="classroom"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">教室</label>
                        <input type="text" name="classroom" id="classroom"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            placeholder="" required="" value="<?php echo htmlspecialchars($classroom) ?>">
                    </div>
                    <div class="col-span-2">
                        <label for="price"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">日期</label>
                        <div class="relative max-w-sm">
                            <input type="text" name="rent_date" id="rent_date"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="日期" value="">
                        </div>
                    </div>
                    <?php
                        if ($_SERVER['REQUEST_METHOD']  == "POST") {
                            echo '
                            
                            <div class="col-span-2 sm:col-span-1">
                        <label for="time"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">開始借用時間</label>
                        <input type="number" name="start-time" id="start-time"
                            class="relative max-w-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            placeholder="開始借用時間" value="' . $start_time . '">
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label for="time"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">歸還時間</label>
                        <input type="number" name="end-time" id="end-time"
                            class="relative max-w-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            placeholder="歸還時間" value="' . $end_time . '">
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
                <button type="submit" onclick="return validateForm()"
                    class="text-white inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                    <svg class="me-1 -ms-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                            clip-rule="evenodd"></path>
                    </svg>
                    送出
                </button>

            </form>
        </div>
    </div>





    <script src="./static/js/theme-toggle.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>

</html>