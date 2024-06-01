# 資料庫系統-期末作業
## 預覽
[https://db.cangw.eu.org/](https://db.cangw.eu.org/)
## 版本資訊
> 如果SQL或是PHP沒辦法正常用，推薦把Appserv解安裝換XAMPP
Use [XAMPP](https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/8.2.12/xampp-windows-x64-8.2.12-0-VS16-installer.exe)
- Apache 2.4.58
- MariaDB 10.4.32
- PHP 8.2.12
- phpMyAdmin 5.2.1
## 程式相關資訊
- 前端：HTML + Tailwind CSS (flowbite) + JavaScript
- 後端：PHP
- SQL: MariaDB
## 建議
- 一個功能開一個Branch
- [遵循Commit守則](https://heidiliu2020.github.io/git-commit-message/)，可使用Add/Update(Edit)/Del表示檔案新增、刪除、修改
## 說明
**已經做完的**
- login.php + login.html
    登入介面，基本上已經做完了
- logout.php
    登出的接口，也已經做完了
- index.php
    首頁，看之後要放甚麼
- config.php
    存放資料庫登入資訊的地方
- register.php
    註冊帳戶介面，要自己手動進去

**還沒動工的**
- classroom_status.php
    查詢教室當前狀況
- rent_classroom.php
    租借教室
- rental_records.php
    租借紀錄

**管理員帳號** 
admin:12345678

**資料庫**
final_project 為主要的資料庫 另一個已經廢棄了
final_project下有四個資料表：
- user_data 
存放登入系統中的帳戶資訊
- course_table 
目前沒主鍵，主鍵可能為classroom與class_time作為復合主鍵，有主鍵後應該就是1NF了，之後可以刪除一些用不到的欄位作為查詢教室是否被占用的其中一個資料庫
- nkust_course_table
從[高科課程資訊](https://webap.nkust.edu.tw/nkust/ag_pro/ag202.jsp)爬下來的資料表，只有第一校區的資料，同時也是主頁那個Form所查的資料
- nkust_course_table_cleaned
course_table 1NF之前的資料表，基本上沒用處，之後會刪掉

**tools**
基本上不用理這些，通常是拿來處理固定的SQL資料，用完一次就用不到的工具。
