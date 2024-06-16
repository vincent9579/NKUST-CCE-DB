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
    if ($is_admin == 'Y') {
        header("Location: admin_center.php");
        exit();
    }
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

}
if (is_array($_GET) && count($_GET) > 0) {
    $rent_date = $_GET['rent_date'];
    // date() function returns the current date like 2024/05/27
    if ($rent_date <= date("Y/m/d")) {
        $rent_date = "";
    }
    $start_time = $_GET['start_time'];
    $end_time = $_GET['end_time'];
    $rent_reason = $_GET['rent_reason'];
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/datepicker.min.js"></script>
    <script>
        // On page load or when changing themes, best to add inline in `head` to avoid FOUC
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark')
        }
        function getTable() {
            data = {
                rent_date: document.getElementById('date').value,
                start_time: document.getElementById('start_time').value,
                end_time: document.getElementById('end_time').value,
                rent_reason: document.getElementById('rent_reason').value
            };
            $.ajax({
                type: "GET",
                url: "rent_classroom.php",
                data: { data: JSON.stringify(data) },
                success: function (response) {
                    alert("Rent successfully!");
                    window.location.href = "rental_record.php";
                },
                error: function (response) {
                    alert("Failed to rent the classroom.");
                }
            });
        }
    </script>
</head>

<body class="bg-white dark:bg-gray-900">
    <!-- navigation -->
    <?php include './components/navigaion.php'; ?>
    <?php if ($status == "valid"): ?>
        <!-- 在這裡插入你的內容 -->
        <?php
        if (is_array($_GET) && count($_GET) > 0) {
            if ($rent_date != "" && $start_time != "" && $end_time != "") {
                include './components/rent_table.php';
            } else {
                ?>
                <div id="alert-additional-content-2"
                    class="p-4 mb-4 text-red-800 border border-red-300 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 dark:border-red-800"
                    role="alert">
                    <div class="flex items-center">
                        <svg class="flex-shrink-0 w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                        </svg>
                        <span class="sr-only">Info</span>
                        <h3 class="text-lg font-medium">錯誤</h3>
                    </div>
                    <div class="mt-2 mb-4 text-sm">
                        <p>請選擇正確的日期和時間。</p>
                        <p>日期必須大於今天。</p>
                        <p>開始時間必須小於結束時間。</p>
                        <p>請重新確認後再進行查詢。</p>
                    </div>
                    <div class="flex">
                        <button type="button"
                            class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                            onclick="window.location.href='rent_classroom.php'">
                            返回
                        </button>
                    </div>
                </div>
                <?php
            }
        } else {
            include './components/rent_form.php';
        }
        ?>
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
                        alert("Rent successfully!");
                        window.location.href = "rental_record.php";
                    },
                    error: function (response) {
                        alert("Failed to rent the classroom.");
                    }
                });
            }
        </script>
    <?php else: ?>
        <div id="alert-additional-content-1"
            class="p-4 mb-4 text-blue-800 border border-blue-300 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400 dark:border-blue-800"
            role="alert">
            <div class="flex items-center">
                <svg class="flex-shrink-0 w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                    fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
                <span class="sr-only">Info</span>
                <h3 class="text-lg font-medium">您現在是訪客</h3>
            </div>
            <div class="mt-2 mb-4 text-sm">
                訪客無法使用此功能，請先登入。
            </div>
            <div class="flex">
                <button type="button" onclick="location.href='login.html'"
                    class="text-white bg-blue-800 hover:bg-blue-900 focus:ring-4 focus:outline-none focus:ring-blue-200 font-medium rounded-lg text-xs px-3 py-1.5 me-2 text-center inline-flex items-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                    <svg class="me-2 h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                        viewBox="0 0 20 14">
                        <path
                            d="M10 0C4.612 0 0 5.336 0 7c0 1.742 3.546 7 10 7 6.454 0 10-5.258 10-7 0-1.664-4.612-7-10-7Zm0 10a3 3 0 1 1 0-6 3 3 0 0 1 0 6Z" />
                    </svg>
                    登入
                </button>
                <button type="button" onclick="location.href='index.php'"
                    class="text-blue-800 bg-transparent border border-blue-800 hover:bg-blue-900 hover:text-white focus:ring-4 focus:outline-none focus:ring-blue-200 font-medium rounded-lg text-xs px-3 py-1.5 text-center dark:hover:bg-blue-600 dark:border-blue-600 dark:text-blue-400 dark:hover:text-white dark:focus:ring-blue-800"
                    data-dismiss-target="#alert-additional-content-1" aria-label="Close">
                    關閉
                </button>
            </div>
        </div>
    <?php endif; ?>

    <script src="./static/js/theme-toggle.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>

</html>