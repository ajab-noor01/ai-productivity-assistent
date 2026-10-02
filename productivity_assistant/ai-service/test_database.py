from database import get_database_connection


print("Connecting to database...")

try:

    connection = get_database_connection()

    cursor = connection.cursor()

    cursor.execute("""
        SELECT
            id,
            title,
            priority,
            status,
            due_date
        FROM tasks
        ORDER BY id DESC
        LIMIT 10
    """)

    tasks = cursor.fetchall()

    print("\n========== TASKS ==========\n")

    if not tasks:

        print("No tasks found.")

    else:

        for task in tasks:

            print(
                f"ID: {task[0]} | "
                f"Title: {task[1]} | "
                f"Priority: {task[2]} | "
                f"Status: {task[3]} | "
                f"Due: {task[4]}"
            )

    cursor.close()

    connection.close()

    print("\nDatabase test completed successfully.")

except Exception as error:

    print("Database Error:")
    print(error)