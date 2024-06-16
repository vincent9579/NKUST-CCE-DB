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

$conn = require_once "config.php";

// 找出有哪些學院
$colleages = [];
$query = "SELECT DISTINCT colleage FROM classroom_table";
$result = $conn->query($query);
while ($row = $result->fetch_row()) {
    $colleages[] = $row[0];
}

// 設置每頁顯示的記錄數
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// 獲取過濾條件
$colleage = isset($_GET['colleage']) ? $_GET['colleage'] : '';
// 構建過濾條件的SQL查詢
$conditions = [];
if ($colleage) {
    $conditions[] = "colleage = '" . $conn->real_escape_string($colleage) . "'";
}
$where = '';
if (count($conditions) > 0) {
    $where = 'WHERE ' . implode(' AND ', $conditions);
}

// 獲取總記錄數
$total_query = "SELECT COUNT(*) FROM classroom_table $where";
$total_result = $conn->query($total_query);
$total_rows = $total_result->fetch_row()[0];
$total_pages = ceil($total_rows / $limit);

// 獲取當前頁的記錄
$query = "SELECT * FROM classroom_table $where LIMIT $limit OFFSET $offset";
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
    <?php include './components/navigaion.php'; ?>
    <div class="max-w-screen-xl mx-auto p-4 sm:p-6 lg:p-8">
        <form method="GET" action="" class="flex flex-wrap space-x-4">
            <div class="mb-4 flex-1">
                <label for="limit" class="block text-sm font-medium text-gray-900 dark:text-white">Records per
                    page:</label>
                <select name="limit" id="limit"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option value="10" <?= $limit == 10 ? 'selected' : ''; ?>>10</option>
                    <option value="20" <?= $limit == 20 ? 'selected' : ''; ?>>20</option>
                    <option value="50" <?= $limit == 50 ? 'selected' : ''; ?>>50</option>
                </select>
            </div>
            <div class="mb-4 flex-1">
                <label for="colleage" class="block text-sm font-medium text-gray-900 dark:text-white">colleage:</label>
                <select name="colleage" id="colleage"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option selected value="">All</option>
                    <?php foreach ($colleages as $c): ?>
                        <option value="<?= $c ?>" <?= $colleage == $c ? 'selected' : '' ?>><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
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
                    class="font-semibold text-gray-900 dark:text-white"><?= $offset + 1 ?>-<?= min($offset + $limit, $total_rows) ?></span>
                of <span class="font-semibold text-gray-900 dark:text-white"><?= $total_rows ?></span>
            </span>
            <ul class="inline-flex -space-x-px rtl:space-x-reverse text-sm h-8">
                <?php if ($page > 1): ?>
                    <li>
                        <a href="?page=1&limit=<?= $limit ?>"
                            class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 hover:text-gray-900 dark:text-white dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">First</a>
                    </li>
                    <li>
                        <a href="?page=<?= $page - 1 ?>&limit=<?= $limit ?>"
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

                foreach ($displayed_pages as $p):
                    if ($p === '...'):
                        ?>
                        <li>
                            <span
                                class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400">...</span>
                        </li>
                        <?php
                    else:
                        ?>
                        <li>
                            <a href="?page=<?= $p ?>&limit=<?= $limit ?>"
                                class="flex items-center justify-center px-3 h-8 leading-tight <?= $p == $page ? 'text-blue-600 border border-gray-300 bg-blue-50 hover:bg-blue-100 hover:text-blue-700 dark:border-gray-700 dark:bg-gray-700 dark:text-white' : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-900 dark:text-white dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white' ?>"><?= $p ?></a>
                        </li>
                        <?php
                    endif;
                endforeach;
                ?>

                <?php if ($page < $total_pages): ?>
                    <li>
                        <a href="?page=<?= $page + 1 ?>&limit=<?= $limit ?>"
                            class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 hover:text-gray-900 dark:text-white dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">Next</a>
                    </li>
                    <li>
                        <a href="?page=<?= $total_pages ?>&limit=<?= $limit ?>"
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
                        <th scope="col" class="px-6 py-3">教室編號</th>
                        <th scope="col" class="px-6 py-3">學院/大樓</th>
                        <th scope="col" class="px-6 py-3">最大容納人數</th>
                        <th scope="col" class="px-6 py-3">資料正確性</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr
                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 whitespace-nowrap">
                            <td class="px-6 py-4"><?= $row['classroom'] ?? '' ?></td>
                            <td class="px-6 py-4"><?= $row['colleage'] ?? '' ?></td>
                            <td class="px-6 py-4"><?= $row['max_capacity'] ?? '' ?></td>
                            <td class="px-6 py-4"><?= $row['is_realdata'] == 'N' ? '❓' : '✅' ?></td>
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
                    class="font-semibold text-gray-900 dark:text-white"><?= $offset + 1 ?>-<?= min($offset + $limit, $total_rows) ?></span>
                of <span class="font-semibold text-gray-900 dark:text-white"><?= $total_rows ?></span>
            </span>
            <ul class="inline-flex -space-x-px rtl:space-x-reverse text-sm h-8">
                <?php if ($page > 1): ?>
                    <li>
                        <a href="?page=1&limit=<?= $limit ?>"
                            class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 hover:text-gray-900 dark:text-white dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">First</a>
                    </li>
                    <li>
                        <a href="?page=<?= $page - 1 ?>&limit=<?= $limit ?>"
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

                foreach ($displayed_pages as $p):
                    if ($p === '...'):
                        ?>
                        <li>
                            <span
                                class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400">...</span>
                        </li>
                        <?php
                    else:
                        ?>
                        <li>
                            <a href="?page=<?= $p ?>&limit=<?= $limit ?>"
                                class="flex items-center justify-center px-3 h-8 leading-tight <?= $p == $page ? 'text-blue-600 border border-gray-300 bg-blue-50 hover:bg-blue-100 hover:text-blue-700 dark:border-gray-700 dark:bg-gray-700 dark:text-white' : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-900 dark:text-white dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white' ?>"><?= $p ?></a>
                        </li>
                        <?php
                    endif;
                endforeach;
                ?>

                <?php if ($page < $total_pages): ?>
                    <li>
                        <a href="?page=<?= $page + 1 ?>&limit=<?= $limit ?>"
                            class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 hover:text-gray-900 dark:text-white dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">Next</a>
                    </li>
                    <li>
                        <a href="?page=<?= $total_pages ?>&limit=<?= $limit ?>"
                            class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-900 dark:text-white dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">Last</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <script src="./static/js/theme-toggle.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>

</html>

<?php
$conn->close();
?>