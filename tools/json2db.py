import os
import json
import mysql.connector
from mysql.connector import Error



# 目錄路徑
directory_path = "./course_data/112學年度/"

def get_json_files(directory):
    """取得指定目錄下的所有JSON檔案"""
    return [os.path.join(directory, file) for file in os.listdir(directory) if file.endswith('.json')]

def read_json(file_path):
    """讀取JSON檔案並回傳其內容"""
    with open(file_path, 'r', encoding='utf-8') as file:
        return json.load(file)

def insert_into_db(connection, data):
    """將JSON資料插入資料庫"""
    try:
        cursor = connection.cursor()

        # 假設JSON資料結構如下: [{'選課代號': '...', '上課校區': '...', ..., '備註': '...'}, ...]
        for item in data:
            sql = """
            INSERT INTO CourseTable (
                course_code, campus, department, major, class, combined_class,
                permanent_course_code, course_name, credits, teaching_hours,
                practice_hours, required_or_elective, instructor, classroom,
                enrolled_students, max_students, class_time, full_english_teaching,
                distance_learning, syllabus, remarks
            ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
            """
            values = (
                item['選課代號'].strip(), item['上課校區'].strip(), item['部別'].strip(), item['科系'].strip(),
                item['班級'].strip(), item['合班班級'].strip(), item['永久課號'].strip(), item['科目名稱'].strip(),
                float(item['學分'].strip()), float(item['授課時數'].strip()), float(item['實習時數'].strip()), item['必/選'].strip(),
                item['授課教師'].strip(), item['教室'].strip(), int(float(item['修課人數'].strip())), int(float(item['人數上限'].strip())),
                item['上課時間'].strip(), item['全英語授課'].strip(), item['遠距教學'].strip(),
                item['授課大綱'].strip(), item['備註'].strip()
            )
            cursor.execute(sql, values)
        
        connection.commit()
    except Error as e:
        print(f"Error: {e}")
        connection.rollback()



def main():
    # 連接資料庫
    try:
        connection = mysql.connector.connect(
            host="localhost",
            user="root",
            passwd="25165312",
            database="CourseData"
            )
        if connection.is_connected():
            print("Connected to MariaDB database")

            # 取得所有JSON檔案
            json_files = get_json_files(directory_path)

            for file_path in json_files:
                print(f"Processing file: {file_path}")
                data = read_json(file_path)
                if isinstance(data, list):  # 確保讀取的JSON資料是一個列表
                    insert_into_db(connection, data)
                else:
                    print(f"Error: JSON data in {file_path} is not a list")
                    
    except Error as e:
        print(f"Error: {e}")
    finally:
        if connection.is_connected():
            connection.close()
            print("Database connection closed")

if __name__ == "__main__":
    main()
