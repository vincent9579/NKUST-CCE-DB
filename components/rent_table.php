<div class="max-w-screen-xl mx-auto p-4 sm:p-6 lg:p-8">
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">
                        教室編號
                    </th>
                    <th scope="col" class="px-6 py-3">
                        學院/大樓
                    </th>
                    <th scope="col" class="px-6 py-3">
                        最大容納人數
                    </th>
                    <th scope="col" class="px-6 py-3">
                        真/假資料
                    </th>
                    <th scope="col" class="px-6 py-3">
                        <span class="sr-only">選擇</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php
                $weekday_to_chinese = ['日', '一', '二', '三', '四', '五', '六'];
                $all_periods = ['1', '2', '3', '4', 'A', '5', '6', '7', '8', '9', '10', '11', '12', '13'];
                $weekday = date('w', strtotime($rent_date));
                $weekday = $weekday_to_chinese[$weekday];

                function getPeriodsInRange($start, $end, $all_periods)
                {
                    $start_index = array_search($start, $all_periods);
                    $end_index = array_search($end, $all_periods);
                    return array_slice($all_periods, $start_index, $end_index - $start_index + 1);
                }

                $periods_to_check = getPeriodsInRange($start_time, $end_time, $all_periods);

                // 將 periods_to_check 轉換為逗號分隔的字串格式，以便用於 SQL 中的 IN 子句
                $periods_to_check_sql = "'" . implode("','", $periods_to_check) . "'";

                // 建立 SQL 查詢
                $sql = "
                    SELECT 
                        c.classroom, c.colleage, c.max_capacity, c.is_realdata
                    FROM 
                        classroom_table c
                    LEFT JOIN 
                        course_table ct
                    ON 
                        c.classroom = ct.classroom 
                        AND ct.weekday = '$weekday'
                        AND ct.period IN ($periods_to_check_sql)
                    WHERE 
                        ct.course_code IS NULL
                    ";

                // 假設你已經連接了 MySQL 數據庫
                $result = mysqli_query($conn, $sql);

                // 處理查詢結果
                if ($result):
                    while ($row = mysqli_fetch_assoc($result)):
                ?>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                <?= htmlspecialchars($row['classroom'], ENT_QUOTES, 'UTF-8') ?>
                            </th>
                            <td class="px-6 py-4">
                                <?= htmlspecialchars($row['colleage'], ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td class="px-6 py-4">
                                <?= htmlspecialchars($row['max_capacity'], ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td class="px-6 py-4">
                                <?= $row['is_realdata'] == 'N' ? '❓' : '✅' ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button onclick="submitForm2('<?= htmlspecialchars($row['classroom'], ENT_QUOTES, 'UTF-8') ?>')" class="block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800" type="button">
                                    租借
                                </button>
                            </td>
                        </tr>
                <?php
                    endwhile;
                else:
                ?>
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-red-500">
                            查詢失敗: <?= htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8') ?>
                        </td>
                    </tr>
                <?php
                endif;
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function submitForm2(classroom) {
        var classroom = classroom;

        var weekday = "<?= $weekday ?>";
        var rent_date = '<?= $rent_date ?>';
        var start_period = "<?= $start_time ?>";
        var end_period = "<?= $end_time ?>";
        var rent_reason = <?= json_encode($rent_reason) ?>;

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
