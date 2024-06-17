<?php
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$is_admin = $_SESSION['is_admin'];
if ($is_admin != 'Y') {
    header("Location: index.php");
    exit();
}

$conn = require_once 'config.php';
$user_id = $user_name = $user_password = $is_admin = "";
$user_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['edit_user'])) {
        // edit user
        $user_id = $_POST['user_id'];
        $user_name = $_POST['user_name'];
        $is_admin = $_POST['is_admin'];
        $user_type = $_POST['user_type'];

        // Update user_data table
        $sql = "UPDATE user_data SET user_name=?, is_admin=? WHERE user_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $user_name, $is_admin, $user_id);
        $stmt->execute();

        if ($user_type == 'student') {
            // Update student_account and student_table table
            $std_id = $_POST['std_id'];
            $std_name = $_POST['std_name'];
            $std_departments = $_POST['std_departments'];

            $sql = "UPDATE student_account SET std_id=? WHERE user_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $std_id, $user_id);
            $stmt->execute();

            $sql = "UPDATE student_table SET std_name=?, std_departments=? WHERE std_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $std_name, $std_departments, $std_id);
            $stmt->execute();
        } elseif ($user_type == 'staff') {
            // Update staff_account and staff_table table
            $staff_id = $_POST['staff_id'];
            $staff_name = $_POST['staff_name'];
            $staff_room = $_POST['staff_room'];
            $staff_department = $_POST['std_departments'];

            $sql = "UPDATE staff_account SET staff_id=? WHERE user_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $staff_id, $user_id);
            $stmt->execute();

            $sql = "UPDATE staff_table SET staff_name=?, staff_room=?, staff_department=? WHERE staff_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $staff_name, $staff_room, $staff_department, $staff_id);
            $stmt->execute();
        }
    } elseif (isset($_POST['delete_user'])) {
        // delete user
        $user_id = $_POST['user_id'];

        // get user type
        $sql = "SELECT * FROM student_account WHERE user_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user_type = 'student';
            $row = $result->fetch_assoc();
            $std_id = $row['std_id'];
        } else {
            $sql = "SELECT * FROM staff_account WHERE user_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $user_type = 'staff';
                $row = $result->fetch_assoc();
                $staff_id = $row['staff_id'];
            }
        }

        // delete user data
        if ($user_type == 'student') {
            $sql = "DELETE FROM student_account WHERE user_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();

            $sql = "DELETE FROM student_table WHERE std_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $std_id);
            $stmt->execute();
        } elseif ($user_type == 'staff') {
            $sql = "DELETE FROM staff_account WHERE user_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();

            $sql = "DELETE FROM staff_table WHERE staff_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $staff_id);
            $stmt->execute();
        }

        // delete user
        $sql = "DELETE FROM user_data WHERE user_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }
}


// get all users
$users = $conn->query("SELECT * FROM user_data");
$students = $conn->query("SELECT u.user_id, u.user_name, u.is_admin, s.std_id, st.std_name, st.std_departments
                        FROM user_data u 
                        JOIN student_account s ON u.user_id = s.user_id
                        JOIN student_table st ON s.std_id = st.std_id");
$staffs = $conn->query("SELECT u.user_id, u.user_name, u.is_admin, s.staff_id, st.staff_name, st.staff_room, st.staff_department
                        FROM user_data u 
                        JOIN staff_account s ON u.user_id = s.user_id
                        JOIN staff_table st ON s.staff_id = st.staff_id");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>資源租借系統 - 用戶管理</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/all.min.js"
        integrity="sha512-u3fPA7V8qQmhBPNT5quvaXVa1mnnLSXUep5PS1qo5NRzHwG19aHmNJnj1Q8hpA/nBWZtZD4r4AX6YOt5ynLN2g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
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
            <!-- ratio -->
            <div class="grid grid-cols-2 pb-4">
                <div class="flex items-center ps-4 border border-gray-200 rounded dark:border-gray-700">
                    <input id="radio-student" type="radio" value="student" name="bordered-radio" checked
                        onclick="toggleTable('student')"
                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                    <label for="radio-student"
                        class="w-full py-4 ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">學生資料</label>
                </div>
                <div class="flex items-center ps-4 border border-gray-200 rounded dark:border-gray-700">
                    <input id="radio-staff" type="radio" value="staff" name="bordered-radio"
                        onclick="toggleTable('staff')"
                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                    <label for="radio-staff"
                        class="w-full py-4 ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">員工資料</label>
                </div>
            </div>

            <!-- List Users -->
            <div id="student-table" class="mb-6">
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                帳號
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                學生編號
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                姓名
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                科系
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                是否為管理員
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                動作
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php while ($row = $students->fetch_assoc()) { ?>
                            <tr id="row-<?php echo $row['user_id']; ?>">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    <span class="cell" data-type="text"
                                        data-name="user_name"><?php echo $row['user_name']; ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <span class="cell" data-type="text"
                                        data-name="std_id"><?php echo $row['std_id']; ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <span class="cell" data-type="text"
                                        data-name="std_name"><?php echo $row['std_name']; ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <span class="cell" data-type="text"
                                        data-name="std_departments"><?php echo $row['std_departments']; ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <span class="cell" data-type="text"
                                        data-name="is_admin"><?php echo $row['is_admin']; ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <button class="px-2 py-1 bg-yellow-500 text-white rounded-md hover:bg-yellow-600"
                                        type="button" onclick="editUser(<?php echo $row['user_id']; ?>)">編輯
                                    </button>
                                    <button class="px-2 py-1 bg-red-500 text-white rounded-md hover:bg-red-600"
                                        onclick="deleteUser(<?php echo $row['user_id']; ?>)">刪除
                                    </button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div id="staff-table" class="mb-6 hidden">
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                帳號
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                員工編號
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                姓名
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                上班地點
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                隸屬部門
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                是否為管理員
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                動作
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php while ($row = $staffs->fetch_assoc()) { ?>
                            <tr id="row-<?php echo $row['user_id']; ?>">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    <span class="cell" data-type="text"
                                        data-name="user_name"><?php echo $row['user_name']; ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <span class="cell" data-type="text"
                                        data-name="staff_id"><?php echo $row['staff_id']; ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <span class="cell" data-type="text"
                                        data-name="staff_name"><?php echo $row['staff_name']; ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <span class="cell" data-type="text"
                                        data-name="staff_room"><?php echo $row['staff_room']; ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <span class="cell" data-type="text"
                                        data-name="staff_department"><?php echo $row['staff_department']; ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <span class="cell" data-type="text"
                                        data-name="is_admin"><?php echo $row['is_admin']; ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <button class="px-2 py-1 bg-yellow-500 text-white rounded-md hover:bg-yellow-600"
                                        type="button" onclick="editUser(<?php echo $row['user_id']; ?>)">編輯
                                    </button>
                                    <button class="px-2 py-1 bg-red-500 text-white rounded-md hover:bg-red-600"
                                        onclick="deleteUser(<?php echo $row['user_id']; ?>)">刪除
                                    </button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <script src="./static/js/theme-toggle.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
    <script>
        function toggleTable(type) {
            var studentTable = document.getElementById('student-table');
            var staffTable = document.getElementById('staff-table');

            if (type === 'student') {
                studentTable.classList.remove('hidden');
                staffTable.classList.add('hidden');
            } else if (type === 'staff') {
                studentTable.classList.add('hidden');
                staffTable.classList.remove('hidden');
            }
        }

        document.getElementById('user_type').addEventListener('change', function () {
            var stdContainer = document.getElementById('std_id_container');
            var staffContainer = document.getElementById('staff_id_container');
            if (this.value === 'student') {
                stdContainer.classList.remove('hidden');
                staffContainer.classList.add('hidden');
            } else if (this.value === 'staff') {
                stdContainer.classList.add('hidden');
                staffContainer.classList.remove('hidden');
            } else {
                stdContainer.classList.add('hidden');
                staffContainer.classList.add('hidden');
            }
        });

        function editUser(userId) {
            // 選擇該行所有可以編輯的單元格
            const row = document.getElementById('row-' + userId);
            const cells = row.querySelectorAll('.cell');

            // 遍歷每個單元格，將其內容替換為輸入框
            cells.forEach(cell => {
                const cellValue = cell.innerText;
                const cellType = cell.getAttribute('data-type');
                const cellName = cell.getAttribute('data-name');

                // 根據需要創建合適的輸入框
                let inputElement;
                if (cellType === 'text') {
                    inputElement = document.createElement('input');
                    inputElement.type = 'text';
                    inputElement.value = cellValue;
                    inputElement.className = 'bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white rounded-md px-2 py-1';
                }
                // 你可以添加更多的條件來處理不同類型的輸入
                // 將輸入框替換到單元格內
                inputElement.setAttribute('data-name', cellName);
                cell.innerHTML = '';
                cell.appendChild(inputElement);
            });

            // 將 "Edit" 按鈕替換為 "Save" 按鈕
            const editButton = row.querySelector('button[onclick^="editUser"]');
            editButton.innerText = 'Save';
            editButton.className = 'px-2 py-1 bg-green-500 text-white rounded-md hover:bg-green-600';
            editButton.setAttribute('onclick', `saveUser(${userId})`);
        }

        function commitEdit(userId, userName, std_name, userType, typeId, isAdmin, userDepartment, staffRoom) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = 'user_management.php';

            var inputUserId = document.createElement('input');
            inputUserId.type = 'hidden';
            inputUserId.name = 'user_id';
            inputUserId.value = userId;
            form.appendChild(inputUserId);

            var inputUserName = document.createElement('input');
            inputUserName.type = 'hidden';
            inputUserName.name = 'user_name';
            inputUserName.value = userName;
            form.appendChild(inputUserName);

            var inputStdName = document.createElement('input');
            inputStdName.type = 'hidden';
            inputStdName.name = userType == 'student' ? 'std_name' : 'staff_name';
            inputStdName.value = std_name;
            form.appendChild(inputStdName);

            var inputUserType = document.createElement('input');
            inputUserType.type = 'hidden';
            inputUserType.name = 'user_type';
            inputUserType.value = userType;
            form.appendChild(inputUserType);

            var inputTypeId = document.createElement('input');
            inputTypeId.type = 'hidden';
            inputTypeId.name = userType == 'student' ? 'std_id' : 'staff_id';
            inputTypeId.value = typeId;
            form.appendChild(inputTypeId);

            var inputIsAdmin = document.createElement('input');
            inputIsAdmin.type = 'hidden';
            inputIsAdmin.name = 'is_admin';
            inputIsAdmin.value = isAdmin;
            form.appendChild(inputIsAdmin);

            var inputUserDepartment = document.createElement('input');
            inputUserDepartment.type = 'hidden';
            inputUserDepartment.name = 'std_departments';
            inputUserDepartment.value = userDepartment;
            form.appendChild(inputUserDepartment);


            var inputStaffRoom = document.createElement('input');
            inputStaffRoom.type = 'hidden';
            inputStaffRoom.name = 'staff_room';
            inputStaffRoom.value = staffRoom;
            form.appendChild(inputStaffRoom);


            var inputEditUser = document.createElement('input');
            inputEditUser.type = 'hidden';
            inputEditUser.name = 'edit_user';
            inputEditUser.value = 'edit_user';
            form.appendChild(inputEditUser);

            var submitButton = document.createElement('button');
            submitButton.type = 'submit';
            submitButton.name = 'edit_user';
            form.appendChild(submitButton);

            document.body.appendChild(form);
            form.submit();
        }

        function saveUser(userId) {
            // 選擇該行所有的輸入框
            const row = document.getElementById('row-' + userId);
            const inputs = row.querySelectorAll('input[data-name]');

            // 創建一個對象來保存新值
            const updatedData = {};

            inputs.forEach(input => {
                const inputName = input.getAttribute('data-name');
                updatedData[inputName] = input.value;

                // 將輸入框的值顯示回到單元格中
                const spanElement = document.createElement('span');
                spanElement.className = 'cell';
                spanElement.setAttribute('data-type', 'text');
                spanElement.setAttribute('data-name', inputName);
                spanElement.innerText = input.value;

                // 替換輸入框為普通文本
                input.parentNode.replaceChild(spanElement, input);
            });

            // get user type
            const userType = row.querySelector('span.cell[data-name="std_id"]') ? 'student' : 'staff';
            const typeId = row.querySelector('span.cell[data-name="' + (userType == 'student' ? 'std_id' : 'staff_id') + '"]').innerText;
            const userName = row.querySelector('span.cell[data-name="user_name"]').innerText;
            const std_name = row.querySelector('span.cell[data-name="' + (userType == 'student' ? 'std_name' : 'staff_name') + '"]').innerText;
            const isAdmin = row.querySelector('span.cell[data-name="is_admin"]').innerText;
            const userDepartment = row.querySelector('span.cell[data-name="std_departments"]') ? row.querySelector('span.cell[data-name="std_departments"]').innerText : row.querySelector('span.cell[data-name="staff_department"]').innerText;
            const staffRoom = row.querySelector('span.cell[data-name="staff_room"]') ? row.querySelector('span.cell[data-name="staff_room"]').innerText : '';
            // 提交編輯
            commitEdit(userId, userName, std_name, userType, typeId, isAdmin, userDepartment, staffRoom);
            // 將 "Save" 按鈕替換回 "Edit" 按鈕
            const saveButton = row.querySelector('button[onclick^="saveUser"]');
            saveButton.innerText = 'Edit';
            saveButton.className = 'px-2 py-1 bg-yellow-500 text-white rounded-md hover:bg-yellow-600';
            saveButton.setAttribute('onclick', `editUser(${userId})`);
        }

        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = 'user_management.php';

                var inputUserId = document.createElement('input');
                inputUserId.type = 'hidden';
                inputUserId.name = 'user_id';
                inputUserId.value = userId;
                form.appendChild(inputUserId);

                var inputDeleteUser = document.createElement('input');
                inputDeleteUser.type = 'hidden';
                inputDeleteUser.name = 'delete_user';
                inputDeleteUser.value = 'delete_user';
                form.appendChild(inputDeleteUser);

                var submitButton = document.createElement('button');
                submitButton.type = 'submit';
                submitButton.name = 'delete_user';
                form.appendChild(submitButton);

                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>

</html>