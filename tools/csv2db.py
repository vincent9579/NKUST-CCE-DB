import pandas as pd
import mysql.connector

# 讀取CSV文件
csv_file_path = './cleaned_data.csv'
data = pd.read_csv(csv_file_path)

# 第一列是欄位名稱，將其去除
data = data.iloc[1:]

# 連接到MySQL數據庫
db_config = {
    'user': 'root',
    'password': '',
    'host': '127.0.0.1',
    'database': 'final_project'
}

conn = mysql.connector.connect(**db_config)
cursor = conn.cursor()

# 插入數據到course_table
insert_query = """
            INSERT INTO course_table (
                id, course_code, campus, department, major, class, combined_class,
                permanent_course_code, course_name, credits, teaching_hours,
                practice_hours, required_or_elective, instructor, classroom,
                enrolled_students, max_students, class_time, full_english_teaching,
                distance_learning, syllabus, remarks
            ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
            """

for index, row in data.iterrows():
    cursor.execute(insert_query, tuple(row))

# 提交更改
conn.commit()

# 關閉連接
cursor.close()
conn.close()
