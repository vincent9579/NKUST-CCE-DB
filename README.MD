# 資料庫系統-期末作業
## 預覽
[https://db.cangw.eu.org/](https://db.cangw.eu.org/)
## 版本資訊
> 如果SQL或是PHP沒辦法正常用，請把Appserv解安裝換[XAMPP](https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/8.2.12/xampp-windows-x64-8.2.12-0-VS16-installer.exe)
- Apache 2.4.58
- MariaDB 10.4.32
- PHP 8.2.12
- phpMyAdmin 5.2.1
## 程式相關資訊
- 前端：HTML + Tailwind CSS (flowbite) + JavaScript
- 後端：PHP
- SQL: MariaDB

## 帳號
**管理員帳號** 
admin:12345678
admin1:12345678 (電資學院管理帳號)
**普通測試帳號**
test1:12345678
## 說明
請先更改config.php中的SQL設定

**檔案目錄 含各檔案大概功能**
```
NKUST-CCE-DB
├── components (分離後的元件資料夾)
│   ├── footer.php (底欄)
│   ├── navigaion.php (導航欄)
│   ├── rent_form.php (從classroom_status跳轉到rent_classroom顯示的租借單)
│   ├── rent_table.php (依照時間下去查會顯示的可租借教室清單)
│   ├── sidebar.php (管理中心側邊導航欄)
├── DB
│   └── final_project.sql (所需的MySQL資料庫)
├── static
│   └── js
│       └── theme-toggle.js (主題切換所需的JavaScript)
├── admin_center.php (管理中心)
├── approve.php (審核的api)
├── classroom_status.php (查詢教室課表及租借狀態)
├── config.php (php的設定檔)
├── index.php (主頁)
├── login_api.php (登入的api)
├── login.php (登入頁面)
├── logout.php (登出的api)
├── reg.php (註冊的api)
├── register.php (註冊頁面)
├── rent_classroom.php (租借教室)
├── rent.php (租借的api)
├── rental_record.php (租借紀錄頁面)
├── template.php (網頁模板)
└── user_management.php (管理中心的用戶管理)
```

**資料庫**
資料一部分是從[高科課程資訊](https://webap.nkust.edu.tw/nkust/ag_pro/ag202.jsp)爬下來的資料，以及一些零星學校網頁資料及我們自身經驗
借鑰匙的地點都是假資料

