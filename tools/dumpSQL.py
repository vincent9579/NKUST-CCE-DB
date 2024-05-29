import subprocess
import os

def export_database(user, password, host, database, output_dir):
    """
    Export the specified MySQL database to the given directory.
    
    :param user: MySQL username
    :param password: MySQL password
    :param host: MySQL host
    :param database: Name of the database to export
    :param output_dir: Directory where the exported file will be saved
    """
    if not os.path.exists(output_dir):
        os.makedirs(output_dir)
    
    output_file = os.path.join(output_dir, f"{database}.sql")
    
    dump_command = [
        "mysqldump",
        f"--no-defaults",
        f"--user={user}",
        f"--password={password}",
        f"--host={host}",
        database,
        "--result-file",
        output_file
    ]
    
    try:
        subprocess.run(dump_command, check=True)
        print(f"Database export successful. File saved to: {output_file}")
    except subprocess.CalledProcessError as e:
        print(f"Error occurred: {e}")

# 使用範例
if __name__ == "__main__":
    user = "root"
    password = "25165312"
    host = "localhost"  # 根據實際情況修改
    database = "CourseData"
    output_dir = "./DB/"
    
    export_database(user, password, host, database, output_dir)
