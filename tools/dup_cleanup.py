import pandas as pd

# Read the data from the CSV file
data = pd.read_csv('course_table.csv')

# remove duplicate rows
data.drop_duplicates(inplace=True)

# write the cleaned data to a new CSV file
data.to_csv('course_table.csv', index=False)
