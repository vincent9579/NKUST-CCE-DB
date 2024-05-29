import os
import json
import time
import random
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select
from selenium.webdriver.chrome.service import Service as ChromeService
from webdriver_manager.chrome import ChromeDriverManager

driver = webdriver.Chrome(service=ChromeService(ChromeDriverManager().install()))

# Load the HTML file
driver.get('https://webap.nkust.edu.tw/nkust/ag_pro/ag202.jsp')

# Define the range of semesters to scrape
semesters = ["112#2"]

# Define the base URL for saving the data
base_dir = "./course_data"

# Create the base directory if it doesn't exist
os.makedirs(base_dir, exist_ok=True)

def select_option_by_value(select_element, value):
    select = Select(select_element)
    select.select_by_value(value)

def get_select_options(select_element):
    select = Select(select_element)
    return [option.get_attribute('value') for option in select.options if option.get_attribute('value')]

def scrape_data():
    form = driver.find_element(By.NAME, "thisform")
    table = form.find_element(By.XPATH, ".//table[@class='stable']")
    rows = table.find_elements(By.XPATH, ".//tr")[2:]  # Skip the header rows
    courses = []
    for row in rows:
        cells = row.find_elements(By.XPATH, ".//td")
        course_data = {
            "選課代號": cells[0].text,
            "上課校區": cells[1].text,
            "部別": cells[2].text,
            "科系": cells[3].text,
            "班級": cells[4].text,
            "合班班級": cells[5].text,
            "永久課號": cells[6].text,
            "科目名稱": cells[7].text,
            "學分": cells[8].text,
            "授課時數": cells[9].text,
            "實習時數": cells[10].text,
            "必/選": cells[11].text,
            "授課教師": cells[12].text,
            "教室": cells[13].text,
            "修課人數": cells[14].text,
            "人數上限": cells[15].text,
            "上課時間": cells[16].text,
            "全英語授課": cells[17].text,
            "遠距教學": cells[18].text,
            "授課大綱": cells[19].text,
            "備註": cells[20].text
        }
        courses.append(course_data)
    return courses

for semester in semesters:
    # Select semester
    select_option_by_value(driver.find_element(By.NAME, "yms_yms"), semester)
    # Select campus
    select_option_by_value(driver.find_element(By.NAME, "cmp_area_id"), "3")  # 國立高雄科技大學(第一校區)
    
    # Get options for dgr_id and unt_id
    dgr_id_options = get_select_options(driver.find_element(By.NAME, "dgr_id"))
    time.sleep(random.randrange(2,4))  # Wait for the page to load
    
    for dgr_id in dgr_id_options:
        select_option_by_value(driver.find_element(By.NAME, "dgr_id"), dgr_id) 
        unt_id_options = get_select_options(driver.find_element(By.NAME, "unt_id"))
        print(f"Semester {semester} has {len(dgr_id_options)} dgr_id options and {len(unt_id_options)} unt_id options")
        for unt_id in unt_id_options:
            print(f"Scraping data for semester {semester}, dgr_id {dgr_id}, unt_id {unt_id}")
            select_option_by_value(driver.find_element(By.NAME, "unt_id"), unt_id)
            
            # Select all levels, departments, and other options
            select_option_by_value(driver.find_element(By.NAME, "clyear"), "%")
            select_option_by_value(driver.find_element(By.NAME, "week"), "%")
            select_option_by_value(driver.find_element(By.NAME, "period"), "%")

            # Click the search button
            driver.find_element(By.XPATH, "//input[@value='條件查詢']").click()
            time.sleep(random.randrange(2,4))
            # Scrape the data
            course_data = scrape_data()

            # Organize and save the data
            academic_year = semester.split("#")[0]
            year_dir = os.path.join(base_dir, f"{academic_year}學年度")
            os.makedirs(year_dir, exist_ok=True)

            with open(os.path.join(year_dir, f"{semester}_{dgr_id}_{unt_id}.json"), "w" , encoding="utf8") as f:
                json.dump(course_data, f , ensure_ascii=False)

# Close the WebDriver
driver.quit()

