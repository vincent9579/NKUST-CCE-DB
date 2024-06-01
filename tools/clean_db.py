import mysql.connector
import re
import csv

# 連接到 MySQL 資料庫
conn = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="final_project"
)

cursor = conn.cursor()

# 選擇 nkust_course_table 表格
select_query = "SELECT * FROM nkust_course_table"
cursor.execute(select_query)

# 取得所有紀錄
records = cursor.fetchall()

# 取得欄位名稱
field_names = [i[0] for i in cursor.description]

# 新的紀錄列表
new_records = []

# 正則表達式匹配 classroom 欄位中的多個值
classroom_pattern = re.compile(r'\(([^)]+)\)([^,]+)')

for record in records:
    classroom = record[field_names.index('classroom')]
    class_time = record[field_names.index('class_time')]
    
    # 匹配 classroom 欄位中的多個值
    matches = classroom_pattern.findall(classroom)
    
    if matches:
        for match in matches:
            time_slot, room = match
            # 去除 classroom 欄位中的中文字符
            room = re.sub(r'[^\w\s]', '', room)
            try:
                time_slots = time_slot.split(',')
                time_slot = "(" + time_slots[0] + ")"+time_slots[1]
            except:
                time_slot = class_time
            # 構造新的紀錄
            new_record = list(record)
            new_record[field_names.index('classroom')] = room
            new_record[field_names.index('class_time')] = time_slot
            new_records.append(new_record)
    else:
        # 如果沒有匹配到，保留原始紀錄
        new_records.append(record)

# 刪除 classroom 欄位值為 '教室未定' 及空值的紀錄
new_records = [record for record in new_records if record[field_names.index('classroom')] not in ('教室未定', '')]

# 創建新的資料表 nkust_course_table_cleaned
create_table_query = """
CREATE TABLE IF NOT EXISTS nkust_course_table_cleaned (
    {} VARCHAR(255)
)
""".format(" VARCHAR(255), ".join(field_names))
cursor.execute(create_table_query)

# 插入清理後的數據到新的資料表
insert_query = "INSERT INTO nkust_course_table_cleaned ({}) VALUES ({})".format(
    ", ".join(field_names),
    ", ".join(["%s"] * len(field_names))
)

for record in new_records:
    cursor.execute(insert_query, record)

conn.commit()

# 關閉資料庫連接
cursor.close()
conn.close()

print("處理完成，結果已存至新的資料表 nkust_course_table_cleaned")
