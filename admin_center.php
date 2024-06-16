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

if ($is_admin != 'Y') {
    header("Location: index.php");
    exit();
}

$conn = require_once "config.php";

$sql = "SELECT * FROM rental_table ORDER BY rent_date ASC, rent_period ASC";
$result = $conn->query($sql);

$rental_list = array();
while ($row = $result->fetch_assoc()) {
    $temp[] = $row['rent_period'];
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

    <?php if (count($rental_list) > 0): ?>
        <form action="approve.php" method="post">
            <div class="relative overflow-x-auto shadow-md sm:p-6 lg:p-8">
                <table
                    class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 border-collapse border border-gray-200 dark:border-gray-700">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th class="px-6 py-3 border border-gray-200 dark:border-gray-700">申請時間</th>
                            <th class="px-6 py-3 border border-gray-200 dark:border-gray-700">使用者名字</th>
                            <th class="px-6 py-3 border border-gray-200 dark:border-gray-700">租借教室</th>
                            <th class="px-6 py-3 border border-gray-200 dark:border-gray-700">租借日期</th>
                            <th class="px-6 py-3 border border-gray-200 dark:border-gray-700">租借時段</th>
                            <th class="px-6 py-3 border border-gray-200 dark:border-gray-700">租借原因</th>
                            <th class="px-6 py-3 border border-gray-200 dark:border-gray-700">租借狀態</th>
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
                        <tr class="bg-white dark:bg-gray-800 px-6 py-3">
                            <td class="bg-white dark:bg-gray-800 px-6 py-3"><?php echo $rental['create_time']; ?></td>
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
                            <td class="bg-white dark:bg-gray-800 px-6 py-3"><?php echo $rental['rent_status']; ?></td>
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
            <?php if (count($rental_list) > 0): ?>
                <div class="flex justify-center mt-4">
                    <button type="submit"
                        class="text-white inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">送出</button>
                </div>
            <?php endif; ?>
        </div>
    </form>

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