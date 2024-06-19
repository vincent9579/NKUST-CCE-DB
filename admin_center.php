<?php
// 開始 session
if (!isset($_SESSION)) {
    session_start();
}

// 檢查用戶是否登錄及是否為管理員
if (!isset($_SESSION['username'])) {
    $status = "invalid";
    $is_admin = "N";
} else {
    $status = "valid";
    $is_admin = $_SESSION['is_admin'];
}

// 如果不是管理員，重定向到首頁
if ($is_admin != 'Y') {
    header("Location: index.php");
    exit();
}

// 連接到數據庫
$conn = require_once "config.php";

// 查詢所有租借記錄
$sql = "SELECT * FROM rental_table ORDER BY create_time ASC, username ASC, rent_date ASC, rent_period ASC";
$result = $conn->query($sql);

$rental_list = array();

while ($row = $result->fetch_assoc()) {
    // 創建一個唯一鍵
    $unique_key = $row['create_time'] . '_' . $row['username'];
    
    if (isset($rental_list[$unique_key])) {
        if ($row['rent_period'] == 'A') {
            if ($rental_list[$unique_key]['start_period'] <= "4") {
                if ($rental_list[$unique_key]['end_period'] == '5') {
                    continue;
                } else {
                    $rental_list[$unique_key]['end_period'] = 'A';
                }
            } else {
                $rental_list[$unique_key]['start_period'] = 'A';
            }
        } else {
            $rental_list[$unique_key]['end_period'] = $row['rent_period'];
        }
    } else {
        // 新記錄，創建新條目
        $rental_list[$unique_key] = array(
            'create_time' => $row['create_time'],
            'username' => $row['username'],
            'classroom' => $row['classroom'],
            'rent_date' => $row['rent_date'],
            'start_period' => $row['rent_period'],
            'end_period' => $row['rent_period'],
            'reason' => $row['reason'],
            'rent_status' => $row['rent_status']
        );
    }
}

// 現在$rental_list包含合併後的記錄
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>資源租借系統 - 管理介面</title>
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
    <?php include './components/sidebar.php'; ?>
    <div class="p-4 sm:ml-64">
        <div class="p-4 rounded-lg dark:border-gray-700 mt-14">
            <div class="relative overflow-x-auto shadow-md sm:rounded-lg min-h-60">
                <div
                    class="flex flex-column sm:flex-row flex-wrap space-y-4 sm:space-y-0 items-center justify-between pb-4">
                    <div>
                        <button id="dropdownRadioButton" data-dropdown-toggle="dropdownRadio"
                            class="inline-flex items-center text-gray-500 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-3 py-1.5 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700"
                            type="button">
                            <svg class="w-3 h-3 text-gray-500 dark:text-gray-400 me-3" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M10 0a10 10 0 1 0 10 10A10.011 10.011 0 0 0 10 0Zm3.982 13.982a1 1 0 0 1-1.414 0l-3.274-3.274A1.012 1.012 0 0 1 9 10V6a1 1 0 0 1 2 0v3.586l2.982 2.982a1 1 0 0 1 0 1.414Z" />
                            </svg>
                            全部
                            <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 10 6">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 4 4 4-4" />
                            </svg>
                        </button>
                        <!-- Dropdown menu -->
                        <div id="dropdownRadio"
                            class="z-10 hidden w-48 bg-white divide-y divide-gray-100 rounded-lg shadow dark:bg-gray-700 dark:divide-gray-600"
                            data-popper-reference-hidden="" data-popper-escaped="" data-popper-placement="top">
                            <ul class="p-3 space-y-1 text-sm text-gray-700 dark:text-gray-200"
                                aria-labelledby="dropdownRadioButton">
                                <li>
                                    <div class="flex items-center p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                                        <input id="filter-radio-example-1" type="radio" value="all" name="filter-radio"
                                            checked
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                        <label for="filter-radio-example-1"
                                            class="w-full ms-2 text-sm font-medium text-gray-900 rounded dark:text-gray-300">全部</label>
                                    </div>
                                </li>
                                <li>
                                    <div class="flex items-center p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                                        <input id="filter-radio-example-2" type="radio" value="reviewed"
                                            name="filter-radio"
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                        <label for="filter-radio-example-2"
                                            class="w-full ms-2 text-sm font-medium text-gray-900 rounded dark:text-gray-300">已審核</label>
                                    </div>
                                </li>
                                <li>
                                    <div class="flex items-center p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-600">
                                        <input id="filter-radio-example-3" type="radio" value="unreviewed"
                                            name="filter-radio"
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                        <label for="filter-radio-example-3"
                                            class="w-full ms-2 text-sm font-medium text-gray-900 rounded dark:text-gray-300">未審核</label>
                                    </div>
                                </li>

                            </ul>
                        </div>
                    </div>
                    <label for="table-search" class="sr-only">Search</label>
                    <div class="relative">
                        <div
                            class="absolute inset-y-0 left-0 rtl:inset-r-0 rtl:right-0 flex items-center ps-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" aria-hidden="true" fill="currentColor"
                                viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <input type="text" id="table-search"
                            class="block p-2 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg w-80 bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            placeholder="Search for items">
                    </div>
                </div>
                <?php if (count($rental_list) > 0): ?>
                    <form action="approve.php" method="post">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-6 py-3 border border-gray-200 dark:border-gray-700">
                                        <div class="flex items-center cursor-pointer" onclick="sortTable(0)">
                                            申請時間
                                            <svg class="w-3 h-3 ms-1.5" aria-hidden="true"
                                                xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    d="M8.574 11.024h6.852a2.075 2.075 0 0 0 1.847-1.086 1.9 1.9 0 0 0-.11-1.986L13.736 2.9a2.122 2.122 0 0 0-3.472 0L6.837 7.952a1.9 1.9 0 0 0-.11 1.986 2.074 2.074 0 0 0 1.847 1.086Zm6.852 1.952H8.574a2.072 2.072 0 0 0-1.847 1.087 1.9 1.9 0 0 0 .11 1.985l3.426 5.05a2.123 2.123 0 0 0 3.472 0l3.427-5.05a1.9 1.9 0 0 0 .11-1.985 2.074 2.074 0 0 0-1.846-1.087Z" />
                                            </svg>
                                        </div>
                                    </th>

                                    <th class="px-6 py-3 border border-gray-200 dark:border-gray-700">
                                        <div class="flex items-center cursor-pointer" onclick="sortTable(1)">
                                            使用者名字
                                            <a href="#"><svg class="w-3 h-3 ms-1.5" aria-hidden="true"
                                                    xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path
                                                        d="M8.574 11.024h6.852a2.075 2.075 0 0 0 1.847-1.086 1.9 1.9 0 0 0-.11-1.986L13.736 2.9a2.122 2.122 0 0 0-3.472 0L6.837 7.952a1.9 1.9 0 0 0-.11 1.986 2.074 2.074 0 0 0 1.847 1.086Zm6.852 1.952H8.574a2.072 2.072 0 0 0-1.847 1.087 1.9 1.9 0 0 0 .11 1.985l3.426 5.05a2.123 2.123 0 0 0 3.472 0l3.427-5.05a1.9 1.9 0 0 0 .11-1.985 2.074 2.074 0 0 0-1.846-1.087Z" />
                                                </svg></a>
                                        </div>
                                    </th>
                                    <th class="px-6 py-3 border border-gray-200 dark:border-gray-700">
                                        <div class="flex items-center cursor-pointer" onclick="sortTable(2)">
                                            租借教室
                                            <a href="#"><svg class="w-3 h-3 ms-1.5" aria-hidden="true"
                                                    xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path
                                                        d="M8.574 11.024h6.852a2.075 2.075 0 0 0 1.847-1.086 1.9 1.9 0 0 0-.11-1.986L13.736 2.9a2.122 2.122 0 0 0-3.472 0L6.837 7.952a1.9 1.9 0 0 0-.11 1.986 2.074 2.074 0 0 0 1.847 1.086Zm6.852 1.952H8.574a2.072 2.072 0 0 0-1.847 1.087 1.9 1.9 0 0 0 .11 1.985l3.426 5.05a2.123 2.123 0 0 0 3.472 0l3.427-5.05a1.9 1.9 0 0 0 .11-1.985 2.074 2.074 0 0 0-1.846-1.087Z" />
                                                </svg></a>
                                        </div>
                                    </th>
                                    <th class="px-6 py-3 border border-gray-200 dark:border-gray-700">
                                        <div class="flex items-center cursor-pointer" onclick="sortTable(3)">
                                            租借日期
                                            <a href="#"><svg class="w-3 h-3 ms-1.5" aria-hidden="true"
                                                    xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path
                                                        d="M8.574 11.024h6.852a2.075 2.075 0 0 0 1.847-1.086 1.9 1.9 0 0 0-.11-1.986L13.736 2.9a2.122 2.122 0 0 0-3.472 0L6.837 7.952a1.9 1.9 0 0 0-.11 1.986 2.074 2.074 0 0 0 1.847 1.086Zm6.852 1.952H8.574a2.072 2.072 0 0 0-1.847 1.087 1.9 1.9 0 0 0 .11 1.985l3.426 5.05a2.123 2.123 0 0 0 3.472 0l3.427-5.05a1.9 1.9 0 0 0 .11-1.985 2.074 2.074 0 0 0-1.846-1.087Z" />
                                                </svg></a>
                                        </div>
                                    </th>
                                    <th class="px-6 py-3 border border-gray-200 dark:border-gray-700">
                                        <div class="flex items-center cursor-pointer" onclick="sortTable(4)">
                                            租借時段
                                            <a href="#"><svg class="w-3 h-3 ms-1.5" aria-hidden="true"
                                                    xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path
                                                        d="M8.574 11.024h6.852a2.075 2.075 0 0 0 1.847-1.086 1.9 1.9 0 0 0-.11-1.986L13.736 2.9a2.122 2.122 0 0 0-3.472 0L6.837 7.952a1.9 1.9 0 0 0-.11 1.986 2.074 2.074 0 0 0 1.847 1.086Zm6.852 1.952H8.574a2.072 2.072 0 0 0-1.847 1.087 1.9 1.9 0 0 0 .11 1.985l3.426 5.05a2.123 2.123 0 0 0 3.472 0l3.427-5.05a1.9 1.9 0 0 0 .11-1.985 2.074 2.074 0 0 0-1.846-1.087Z" />
                                                </svg></a>
                                        </div>
                                    </th>
                                    <th class="px-6 py-3 border border-gray-200 dark:border-gray-700">
                                        <div class="flex items-center cursor-pointer" onclick="sortTable(5)">
                                            租借原因
                                            <a href="#"><svg class="w-3 h-3 ms-1.5" aria-hidden="true"
                                                    xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path
                                                        d="M8.574 11.024h6.852a2.075 2.075 0 0 0 1.847-1.086 1.9 1.9 0 0 0-.11-1.986L13.736 2.9a2.122 2.122 0 0 0-3.472 0L6.837 7.952a1.9 1.9 0 0 0-.11 1.986 2.074 2.074 0 0 0 1.847 1.086Zm6.852 1.952H8.574a2.072 2.072 0 0 0-1.847 1.087 1.9 1.9 0 0 0 .11 1.985l3.426 5.05a2.123 2.123 0 0 0 3.472 0l3.427-5.05a1.9 1.9 0 0 0 .11-1.985 2.074 2.074 0 0 0-1.846-1.087Z" />
                                                </svg></a>
                                        </div>
                                    </th>
                                    <th class="px-6 py-3 border border-gray-200 dark:border-gray-700">審核</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php else: ?>
                                <div class="flex items-baseline justify-center min-h-screen">
                                    <h3
                                        class="mb-4 text-4xl font-extrabold leading tracking-tight text-gray-900 md:text-3xl lg:text-4xl dark:text-white">
                                        No rental records</h3>
                                </div>
                            <?php endif; ?>
                            <?php $conn->close(); ?>
                            <?php foreach ($rental_list as $rental): ?>
                                <tr
                                    class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <td class="bg-white dark:bg-gray-800 px-6 py-3"><?php echo $rental['create_time']; ?>
                                    </td>
                                    <td class="bg-white dark:bg-gray-800 px-6 py-3"><?php echo $rental['username']; ?></td>
                                    <td class="bg-white dark:bg-gray-800 px-6 py-3"><?php echo $rental['classroom']; ?></td>
                                    <td class="bg-white dark:bg-gray-800 px-6 py-3"><?php echo $rental['rent_date']; ?></td>
                                    <td class="bg-white dark:bg-gray-800 px-6 py-3">
                                        <?php if ($rental['start_period'] == $rental['end_period']) {
                                            echo $rental['start_period'];
                                        } else {
                                            echo $rental['start_period'] . "-" . $rental['end_period'];
                                        } ?>
                                    </td>
                                    <td class="bg-white dark:bg-gray-800 px-6 py-3"><?php echo $rental['reason']; ?></td>
                                    <td class="bg-white dark:bg-gray-800 px-6 py-3">
                                        <?php if ($rental['rent_status'] == 'U'): ?>
                                            <select
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                name="status[<?php echo $rental['create_time']; ?>]">
                                                <option value="Y">通過</option>
                                                <option value="N">不通過</option>
                                            </select>
                                        <?php else: ?>
                                            <?php echo $rental['rent_status'] == "Y" ? "已審核(通過)" : "已審核(不通過)"; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                        </tbody>
                    </table>
                    <!-- 靠右 -->
                    <div class="flex justify-end mt-4">
                        <button type="submit"
                            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">送出</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
    <script>
        // Filter and search table
        document.addEventListener('DOMContentLoaded', function () {
            const filterRadios = document.getElementsByName('filter-radio');
            const searchInput = document.getElementById('table-search');
            const table = document.querySelector('table tbody');

            filterRadios.forEach(radio => {
                radio.addEventListener('change', filterTable);
            });

            searchInput.addEventListener('input', filterTable);

            // Function to filter table rows based on filter and search input
            function filterTable() {
                const filterValue = Array.from(filterRadios).find(radio => radio.checked).value;
                const searchValue = searchInput.value.toLowerCase();
                const rows = table.querySelectorAll('tr');

                rows.forEach(row => {
                    const status = row.cells[6].textContent.trim();
                    const text = row.textContent.toLowerCase();
                    const matchesFilter = (filterValue === 'all') ||
                        (filterValue === 'reviewed' && (status === '已審核(通過)' || status === '已審核(不通過)')) ||
                        (filterValue === 'unreviewed' && status === 'U');
                    const matchesSearch = text.includes(searchValue);

                    if (matchesFilter && matchesSearch) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            window.sortTable = function (n) {
                const rows = Array.from(table.rows);
                const isAsc = table.getAttribute('data-sort-order') === 'asc';
                const multiplier = isAsc ? 1 : -1;

                rows.sort((a, b) => {
                    const aText = a.cells[n].textContent.trim();
                    const bText = b.cells[n].textContent.trim();

                    return aText.localeCompare(bText) * multiplier;
                });

                rows.forEach(row => table.appendChild(row));
                table.setAttribute('data-sort-order', isAsc ? 'desc' : 'asc');
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form');
            const statusSelectors = document.querySelectorAll('select[name^="status"]');

            // Function to initialize statuses to handle conflicts on page load
            function initializeStatuses() {
                const rentalData = {};

                // Group rental requests by classroom, date, and period
                statusSelectors.forEach(selector => {
                    const createTime = selector.name.match(/\[(.*?)\]/)[1];
                    const status = selector.value;
                    const row = selector.closest('tr');
                    const classroom = row.querySelector('td:nth-child(3)').innerText;
                    const rentDate = row.querySelector('td:nth-child(4)').innerText;
                    const rentPeriod = row.querySelector('td:nth-child(5)').innerText;

                    const key = `${classroom}-${rentDate}-${rentPeriod}`;

                    if (!rentalData[key]) {
                        rentalData[key] = [];
                    }

                    rentalData[key].push({
                        createTime,
                        selector,
                        row,
                    });
                });

                // For each group of conflicting rentals, approve the first one, and disapprove the rest
                for (let key in rentalData) {
                    const rentals = rentalData[key];
                    let hasApproved = false;

                    rentals.forEach(rental => {
                        if (!hasApproved) {
                            rental.selector.value = 'Y';
                            hasApproved = true;
                        } else {
                            rental.selector.value = 'N';
                        }
                    });
                }
            }

            // Function to handle status change dynamically
            function handleStatusChange(event) {
                const currentSelector = event.target;
                const currentCreateTime = currentSelector.name.match(/\[(.*?)\]/)[1];
                const currentRow = currentSelector.closest('tr');
                const currentClassroom = currentRow.querySelector('td:nth-child(3)').innerText;
                const currentRentDate = currentRow.querySelector('td:nth-child(4)').innerText;
                const currentRentPeriod = currentRow.querySelector('td:nth-child(5)').innerText;
                const currentStatus = currentSelector.value;

                // Check and update other conflicting rentals
                statusSelectors.forEach(sel => {
                    if (sel !== currentSelector) {
                        const createTime = sel.name.match(/\[(.*?)\]/)[1];
                        const row = sel.closest('tr');
                        const classroom = row.querySelector('td:nth-child(3)').innerText;
                        const rentDate = row.querySelector('td:nth-child(4)').innerText;
                        const rentPeriod = row.querySelector('td:nth-child(5)').innerText;

                        if (
                            currentClassroom === classroom &&
                            currentRentDate === rentDate &&
                            currentRentPeriod === rentPeriod
                        ) {
                            if (currentStatus === 'Y') {
                                sel.value = 'N'; // Set conflicting rentals to 'N'
                            }
                        }
                    }
                });
            }

            // Initialize statuses on page load
            initializeStatuses();

            // Add event listener for status changes to handle dynamic conflicts
            statusSelectors.forEach(selector => {
                selector.addEventListener('change', handleStatusChange);
            });
        });
    </script>
    <script src="./static/js/theme-toggle.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>

</html>