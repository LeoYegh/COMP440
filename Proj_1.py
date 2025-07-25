import mysql.connector
from mysql.connector import Error

# connection
def create_connection():
    try:
        connection = mysql.connector.connect(
            host='localhost', # default
            user='root', # default
            password= input("MySQL Password: "), # your MySQL password
        )
        print("Connection successful")
        return connection
    except Error as e:
        print(f"The error '{e}' occurred")
        return None

# executes query expecting no return value
def execute_query(connection, query):
    cursor = connection.cursor()
    try:
        cursor.execute(query)
        connection.commit()
    except Error as e:
        print(f"The error '{e}' occured")
    
# executes query expecting a return value    
def execute_read_query(connection, query): 
    cursor = connection.cursor()
    result = None
    try:
        cursor.execute(query)
        result = cursor.fetchall()
        return result
    except Error as e:
        print(f"The error '{e}' occurred")
        return None

# connection
connection = create_connection()

# creates schema if not exists
execute_query(connection, "CREATE SCHEMA IF NOT EXISTS proj_1") 

# uses schema "proj_1"
cursor = connection.cursor()
cursor.execute("USE proj_1")

# creates table if not exists
execute_query(connection, """
              CREATE TABLE IF NOT EXISTS user(
                username VARCHAR(50) PRIMARY KEY,
                password VARCHAR(50) NOT NULL,
                firstName VARCHAR(50) NOT NULL,
                lastName VARCHAR(50) NOT NULL,
                email VARCHAR(50) UNIQUE
              )""")

