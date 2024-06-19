<div class="flex h-screen justify-center items-center">
    <div class="w-96 relative bg-white rounded-lg shadow dark:bg-gray-700">
        <?php if ($_SERVER['REQUEST_METHOD'] == "POST"): ?>
            <div class="w-full p-4 md:p-5">
            <?php else: ?>
                <form class="w-full p-4 md:p-5" method="get" action="rent_classroom.php">
                <?php endif; ?>

                <h3
                    class="mb-4 text-4xl font-extrabold leading tracking-tight text-gray-900 md:text-3xl lg:text-4xl dark:text-white">
                    租借教室
                </h3>
                <div class="grid gap-4 mb-4 grid-cols-2">
                    <div class="col-span-2">
                        <label for="name"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">姓名</label>
                        <input readonly="readonly" type="text"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            placeholder="" required="" value="<?= htmlspecialchars($std_name) ?>">
                    </div>

                    <?php if ($_SERVER['REQUEST_METHOD'] == "POST"): ?>
                        <div class="col-span-2">
                            <label for="classroom"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">教室</label>
                            <label
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                <?= htmlspecialchars($classroom) ?>
                            </label>
                        </div>
                    <?php endif; ?>

                    <div class="col-span-2">
                        <label for="price"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">日期</label>
                        <div class="relative max-w-sm">
                            <?php if ($_SERVER['REQUEST_METHOD'] == "POST"): ?>
                                <select id="date"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                    <?php
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
                                    $difference = $weekdayMap[$weekday] - $currentWeekday;
                                    if ($difference <= 0) {
                                        $difference += 7;
                                    }

                                    $nextWeekdays = [];
                                    for ($i = 0; $i < 28; $i += 7) {
                                        $nextWeekdays[] = date('Y/m/d', strtotime("+$difference day", strtotime($today)));
                                        $difference += 7;
                                    }

                                    foreach ($nextWeekdays as $nextWeekday): ?>
                                        <option value="<?= htmlspecialchars($nextWeekday) ?>">
                                            <?= htmlspecialchars($nextWeekday) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <div class="relative max-w-sm">
                                    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                            xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                                        </svg>
                                    </div>
                                    <input datepicker datepicker-autohide datepicker-format="yyyy/mm/dd" type="text"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                        name="rent_date" id="rent_date" placeholder="選擇日期">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($_SERVER['REQUEST_METHOD'] == "POST"): ?>
                        <div class="col-span-2 sm:col-span-1">
                            <label for="time"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">開始借用時間</label>
                            <label
                                class="relative max-w-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <?= htmlspecialchars($start_time) ?>
                            </label>
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label for="time"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">歸還時間</label>
                            <label
                                class="relative max-w-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <?= htmlspecialchars($end_time) ?>
                            </label>
                        </div>
                        <div class="col-span-2">
                            <label for="description"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">備註</label>
                            <textarea id="description" rows="4" name="rent_reason" id="rent_reason"
                                class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                placeholder="Write product description here"></textarea>
                        </div>
                    <?php else: ?>
                        <div class="col-span-2 sm:col-span-1">
                            <label for="time"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">開始借用時間</label>
                            <select id="start_time" name="start_time" required
                                class="relative max-w-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option value="" selected>選擇時段</option>
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
                            <select id="end_time" name="end_time" required
                                class="relative max-w-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option value="" disabled selected>選擇時段</option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label for="description"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">備註</label>
                            <textarea id="description" rows="4" name="rent_reason" id="rent_reason"
                                class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                placeholder="請填寫租借緣由"></textarea>
                        </div>

                        <script>
                            var startTimeSelect = document.getElementById('start_time');
                            var endTimeSelect = document.getElementById('end_time');

                            var optionsMap = {
                                '1': ['1', '2', '3'],
                                '2': ['2', '3', '4'],
                                '3': ['3', '4', 'A'],
                                '4': ['4', 'A', '5'],
                                '5': ['5', '6', '7'],
                                '6': ['6', '7', '8'],
                                '7': ['7', '8', '9'],
                                '8': ['8', '9', '10'],
                                '9': ['9', '10', '11'],
                                '10': ['10', '11', '12'],
                                '11': ['11', '12', '13'],
                                '12': ['12', '13'],
                                '13': ['13'],
                                'A': ['A', '5', '6']
                            };

                            startTimeSelect.addEventListener('change', function () {
                                var startTimeValue = startTimeSelect.value;
                                endTimeSelect.innerHTML = '';

                                var options = optionsMap[startTimeValue];
                                options.forEach(function (option) {
                                    var optionElement = document.createElement('option');
                                    optionElement.value = option;
                                    optionElement.textContent = option;
                                    endTimeSelect.appendChild(optionElement);
                                });
                            });
                        </script>
                    <?php endif; ?>
                </div>

                <?php if ($_SERVER['REQUEST_METHOD'] == "POST"): ?>
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
                <?php else: ?>
                    <button type="submit" onclick="return getTable()"
                        class="text-white inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                        <svg class="me-1 -ms-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                clip-rule="evenodd"></path>
                        </svg>
                        查詢
                    </button>
                <?php endif; ?>

                <?php if ($_SERVER['REQUEST_METHOD'] == "POST"): ?>
            </div>
        <?php else: ?>
            </form>
        <?php endif; ?>
    </div>
</div>

