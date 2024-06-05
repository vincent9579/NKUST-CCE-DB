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
// 設置每頁顯示的記錄數
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// 獲取過濾條件
$department = isset($_GET['department']) ? $_GET['department'] : '';
$major = isset($_GET['major']) ? $_GET['major'] : '';
$class = isset($_GET['class']) ? $_GET['class'] : '';

// 構建過濾條件的SQL查詢
$conditions = [];
if ($department) {
    $conditions[] = "department = '" . $conn->real_escape_string($department) . "'";
}
if ($major) {
    $conditions[] = "major = '" . $conn->real_escape_string($major) . "'";
}
if ($class) {
    $conditions[] = "class = '" . $conn->real_escape_string($class) . "'";
}
$where = '';
if (count($conditions) > 0) {
    $where = 'WHERE ' . implode(' AND ', $conditions);
}

// 獲取總記錄數
$total_query = "SELECT COUNT(*) FROM nkust_course_table $where";
$total_result = $conn->query($total_query);
$total_rows = $total_result->fetch_row()[0];
$total_pages = ceil($total_rows / $limit);

// 獲取當前頁的記錄
$query = "SELECT * FROM nkust_course_table $where LIMIT $limit OFFSET $offset";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>

<head>
    <title>資源租借系統</title>
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
    <?php include 'components\navigaion.php'; ?>
    <div class="max-w-screen-xl mx-auto p-4 sm:p-6 lg:p-8">
        <form method="GET" action="" class="flex flex-wrap space-x-4">
            <div class="mb-4 flex-1">
                <label for="limit" class="block text-sm font-medium text-gray-900 dark:text-white">Records per
                    page:</label>
                <select name="limit" id="limit" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                    <option value="20" <?php echo $limit == 20 ? 'selected' : ''; ?>>20</option>
                    <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100</option>
                </select>
            </div>
            <div class="mb-4 flex-1">
                <label for="department"
                    class="block text-sm font-medium text-gray-900 dark:text-white">Department:</label>
                <input type="text" name="department" id="department"
                    value="<?php echo htmlspecialchars($department); ?>"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
            </div>
            <div class="mb-4 flex-1">
                <label for="major" class="block text-sm font-medium text-gray-900 dark:text-white">Major:</label>
                <input type="text" name="major" id="major" value="<?php echo htmlspecialchars($major); ?>"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
            </div>
            <div class="mb-4 flex-1">
                <label for="class" class="block text-sm font-medium text-gray-900 dark:text-white">Class:</label>
                <input type="text" name="class" id="class" value="<?php echo htmlspecialchars($class); ?>"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
            </div>
            <div class="flex items-end mb-4">
                <button type="submit"
                    class="px-4 py-2 font-semibold text-white bg-blue-500 rounded-md hover:bg-blue-700">Filter</button>
            </div>
        </form>
    </div>

    <div class="max-w-screen-xl mx-auto p-4 sm:p-6 lg:p-8">
        <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between pt-4"
            aria-label="Table navigation">
            <span
                class="text-sm font-normal text-gray-500 dark:text-gray-400 mb-4 md:mb-0 block w-full md:inline md:w-auto">
                Showing <span
                    class="font-semibold text-gray-900 dark:text-white"><?php echo $offset + 1; ?>-<?php echo min($offset + $limit, $total_rows); ?></span>
                of <span class="font-semibold text-gray-900 dark:text-white"><?php echo $total_rows; ?></span>
            </span>
            <ul class="inline-flex -space-x-px rtl:space-x-reverse text-sm h-8">
                <?php if ($page > 1): ?>
                    <li>
                        <a href="?page=1&limit=<?php echo $limit; ?>"
                            class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 hover:text-gray-900 dark:text-white dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">First</a>
                    </li>
                    <li>
                        <a href="?page=<?php echo $page - 1; ?>&limit=<?php echo $limit; ?>"
                            class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-900 dark:text-white dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">Previous</a>
                    </li>
                <?php endif; ?>

                <?php
                // 顯示頁碼
                $displayed_pages = [];
                if ($total_pages <= 10) {
                    $displayed_pages = range(1, $total_pages);
                } else {
                    $displayed_pages = array_merge(
                        range(1, 2),
                        ($page > 5 ? ['...'] : []),
                        range(max(3, $page - 2), min($total_pages - 2, $page + 2)),
                        ($page < $total_pages - 4 ? ['...'] : []),
                        range($total_pages - 1, $total_pages)
                    );
                }

                foreach ($displayed_pages as $p) {
                    if ($p === '...') {
                        echo '<li><span class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400">...</span></li>';
                    } else {
                        echo '<li><a href="?page=' . $p . '&limit=' . $limit . '" class="flex items-center justify-center px-3 h-8 leading-tight ' . ($p == $page ? 'text-blue-600 border border-gray-300 bg-blue-50 hover:bg-blue-100 hover:text-blue-700 dark:border-gray-700 dark:bg-gray-700 dark:text-white' : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-900 dark:text-white dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white') . '">' . $p . '</a></li>';
                    }
                }
                ?>

                <?php if ($page < $total_pages): ?>
                    <li>
                        <a href="?page=<?php echo $page + 1; ?>&limit=<?php echo $limit; ?>"
                            class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 hover:text-gray-900 dark:text-white dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">Next</a>
                    </li>
                    <li>
                        <a href="?page=<?php echo $total_pages; ?>&limit=<?php echo $limit; ?>"
                            class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-900 dark:text-white dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">Last</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 overflow-auto">
                <thead
                    class="text-xs text-gray-900 dark:text-white uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 whitespace-nowrap">
                    <tr>
                        <th scope="col" class="px-6 py-3">ID</th>
                        <th scope="col" class="px-6 py-3">選課代號</th>
                        <th scope="col" class="px-6 py-3">上課校區</th>
                        <th scope="col" class="px-6 py-3">部別</th>
                        <th scope="col" class="px-6 py-3">科系</th>
                        <th scope="col" class="px-6 py-3">班級</th>
                        <th scope="col" class="px-6 py-3">合班班級</th>
                        <th scope="col" class="px-6 py-3">永久課號</th>
                        <th scope="col" class="px-6 py-3">科目名稱</th>
                        <th scope="col" class="px-6 py-3">學分</th>
                        <th scope="col" class="px-6 py-3">授課時數</th>
                        <th scope="col" class="px-6 py-3">實習時數</th>
                        <th scope="col" class="px-6 py-3">必/選</th>
                        <th scope="col" class="px-6 py-3">授課教師</th>
                        <th scope="col" class="px-6 py-3">教室</th>
                        <th scope="col" class="px-6 py-3">修課人數</th>
                        <th scope="col" class="px-6 py-3">人數上限</th>
                        <th scope="col" class="px-6 py-3">上課時間</th>
                        <th scope="col" class="px-6 py-3">全英授課</th>
                        <th scope="col" class="px-6 py-3">遠距教學</th>
                        <th scope="col" class="px-6 py-3">授課大綱</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr
                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 whitespace-nowrap">
                            <td class="px-6 py-4"><?php echo $row['id']; ?></td>
                            <td class="px-6 py-4"><?php echo $row['course_code']; ?></td>
                            <td class="px-6 py-4"><?php echo $row['campus']; ?></td>
                            <td class="px-6 py-4"><?php echo $row['department']; ?></td>
                            <td class="px-6 py-4"><?php echo $row['major']; ?></td>
                            <td class="px-6 py-4"><?php echo $row['class']; ?></td>
                            <td class="px-6 py-4"><?php echo $row['combined_class']; ?></td>
                            <td class="px-6 py-4"><?php echo $row['permanent_course_code']; ?></td>
                            <td class="px-6 py-4"><?php echo $row['course_name']; ?></td>
                            <td class="px-6 py-4"><?php echo $row['credits']; ?></td>
                            <td class="px-6 py-4"><?php echo $row['teaching_hours']; ?></td>
                            <td class="px-6 py-4"><?php echo $row['practice_hours']; ?></td>
                            <td class="px-6 py-4"><?php echo $row['required_or_elective']; ?></td>
                            <td class="px-6 py-4"><?php echo $row['instructor']; ?></td>
                            <td class="px-6 py-4"><?php echo $row['classroom']; ?></td>
                            <td class="px-6 py-4"><?php echo $row['enrolled_students']; ?></td>
                            <td class="px-6 py-4"><?php echo $row['max_students']; ?></td>
                            <td class="px-6 py-4"><?php echo $row['class_time']; ?></td>
                            <td class="px-6 py-4"><?php echo $row['full_english_teaching']; ?></td>
                            <td class="px-6 py-4"><?php echo $row['distance_learning']; ?></td>
                            <td class="px-6 py-4"><?php echo $row['remarks']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between pt-4"
            aria-label="Table navigation">
            <span
                class="text-sm font-normal text-gray-500 dark:text-gray-400 mb-4 md:mb-0 block w-full md:inline md:w-auto">
                Showing <span
                    class="font-semibold text-gray-900 dark:text-white"><?php echo $offset + 1; ?>-<?php echo min($offset + $limit, $total_rows); ?></span>
                of <span class="font-semibold text-gray-900 dark:text-white"><?php echo $total_rows; ?></span>
            </span>
            <ul class="inline-flex -space-x-px rtl:space-x-reverse text-sm h-8">
                <?php if ($page > 1): ?>
                    <li>
                        <a href="?page=1&limit=<?php echo $limit; ?>"
                            class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 hover:text-gray-900 dark:text-white dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">First</a>
                    </li>
                    <li>
                        <a href="?page=<?php echo $page - 1; ?>&limit=<?php echo $limit; ?>"
                            class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-900 dark:text-white dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">Previous</a>
                    </li>
                <?php endif; ?>

                <?php
                // 顯示頁碼
                $displayed_pages = [];
                if ($total_pages <= 10) {
                    $displayed_pages = range(1, $total_pages);
                } else {
                    $displayed_pages = array_merge(
                        range(1, 2),
                        ($page > 5 ? ['...'] : []),
                        range(max(3, $page - 2), min($total_pages - 2, $page + 2)),
                        ($page < $total_pages - 4 ? ['...'] : []),
                        range($total_pages - 1, $total_pages)
                    );
                }

                foreach ($displayed_pages as $p) {
                    if ($p === '...') {
                        echo '<li><span class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400">...</span></li>';
                    } else {
                        echo '<li><a href="?page=' . $p . '&limit=' . $limit . '" class="flex items-center justify-center px-3 h-8 leading-tight ' . ($p == $page ? 'text-blue-600 border border-gray-300 bg-blue-50 hover:bg-blue-100 hover:text-blue-700 dark:border-gray-700 dark:bg-gray-700 dark:text-white' : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-900 dark:text-white dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white') . '">' . $p . '</a></li>';
                    }
                }
                ?>

                <?php if ($page < $total_pages): ?>
                    <li>
                        <a href="?page=<?php echo $page + 1; ?>&limit=<?php echo $limit; ?>"
                            class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 hover:text-gray-900 dark:text-white dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">Next</a>
                    </li>
                    <li>
                        <a href="?page=<?php echo $total_pages; ?>&limit=<?php echo $limit; ?>"
                            class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-900 dark:text-white dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">Last</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    </div>
    <script src="./static/js/theme-toggle.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>

</html>

<?php
$conn->close();
?>