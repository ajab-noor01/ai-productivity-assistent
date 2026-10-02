import pymysql


def get_database_connection():

    connection = pymysql.connect(
        host="127.0.0.1",
        port=3306,
        user="root",
        password="",
        database="ai_ats_db",
        charset="utf8mb4",
        connect_timeout=5
    )

    return connection