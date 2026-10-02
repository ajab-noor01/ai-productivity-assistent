from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel

from database import get_database_connection


# ============================================================
# APPLICATION
# ============================================================

app = FastAPI(
    title="AI Productivity Assistant",
    description="Multilingual AI Productivity Assistant API",
    version="5.0.0"
)


# ============================================================
# CORS
# ============================================================

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


# ============================================================
# REQUEST MODEL
# ============================================================

class ChatRequest(BaseModel):

    message: str
    user_id: int


# ============================================================
# HOME
# ============================================================

@app.get("/")
def home():

    return {
        "status": "success",
        "message": "AI Productivity Assistant is running",
        "version": "5.0.0"
    }


# ============================================================
# LANGUAGE DETECTION
# ============================================================

def detect_language(message: str):

    text = message.lower().strip()

    # --------------------------------------------------------
    # Urdu Script
    # --------------------------------------------------------

    if any(
        "\u0600" <= character <= "\u06FF"
        for character in text
    ):

        return "urdu"


    # --------------------------------------------------------
    # Roman Urdu
    # --------------------------------------------------------

    roman_urdu_words = {

        "mere",
        "meri",
        "mera",
        "mujhe",
        "mujh",
        "aap",
        "ap",
        "batao",
        "bataiye",
        "dikhao",
        "dikhaye",
        "dikha",
        "kya",
        "kaise",
        "kitne",
        "kitni",
        "hain",
        "hai",
        "kaam",
        "kam",
        "yaad",
        "karna",
        "karo",
        "chahiye",
        "wala",
        "wali",
        "wale",
        "abhi",
        "aaj",
        "kal",
        "poore",
        "pura",
        "mukammal",
        "dikhana",
        "bata",
        "banao",
        "bana",
        "add",
        "karo"
    }

    words = set(text.split())

    if len(words.intersection(roman_urdu_words)) >= 1:

        return "roman_urdu"


    return "english"


# ============================================================
# RESPONSE MESSAGES
# ============================================================

MESSAGES = {

    "english": {

        "empty":
            "Please enter a message.",

        "no_tasks":
            "You don't have any tasks matching that request.",

        "tasks":
            "Here are the tasks matching your request:",

        "focus":
            "Based on your current tasks, here is what I recommend you focus on today:",

        "no_focus":
            "You don't have any pending tasks that require your attention today.",

        "no_notes":
            "You don't have any notes yet.",

        "notes":
            "Here are your latest notes:",

        "no_reminders":
            "You don't have any reminders yet.",

        "reminders":
            "Here are your latest reminders:",

        "task_created":
            "Task created successfully!",

        "task_title_missing":
            "Please provide a task title.",

        "task_create_error":
            "Sorry, I could not create the task.",

        "greeting":
            "Hello! 👋 I'm your AI Productivity Assistant. "
            "I can help you manage tasks, notes, reminders, "
            "and organize your productivity.",

        "help":
            "I can help you with tasks, notes, reminders, "
            "and daily priorities. You can ask things like:\n\n"
            "• Show my pending tasks\n"
            "• Show my completed tasks\n"
            "• Show my high priority tasks\n"
            "• Show my overdue tasks\n"
            "• What should I focus on today?\n"
            "• Show my tasks for today\n"
            "• Show my tasks for tomorrow\n"
            "• Show my reminders\n"
            "• Show my notes\n"
            "• Create a task called Complete PHP project\n"
            "• Create a high priority task called Submit report",

        "error":
            "Sorry, I couldn't process your request right now."

    },


    "roman_urdu": {

        "empty":
            "Please koi message enter karein.",

        "no_tasks":
            "Aapki request ke mutabiq koi task nahi mila.",

        "tasks":
            "Aapki request ke mutabiq tasks ye hain:",

        "focus":
            "Aapke current tasks ko dekhte hue, aaj aapko in tasks par focus karna chahiye:",

        "no_focus":
            "Aaj aapke paas koi pending task nahi hai jis par focus karna zaroori ho.",

        "no_notes":
            "Aapke paas abhi koi note nahi hai.",

        "notes":
            "Aapke latest notes ye hain:",

        "no_reminders":
            "Aapke paas abhi koi reminder nahi hai.",

        "reminders":
            "Aapke latest reminders ye hain:",

        "task_created":
            "Task successfully create ho gaya!",

        "task_title_missing":
            "Please task ka title provide karein.",

        "task_create_error":
            "Maaf kijiye, task create nahi ho saka.",

        "greeting":
            "Assalam-o-Alaikum! 👋 Main aapka AI Productivity "
            "Assistant hoon. Main tasks, notes aur reminders "
            "manage karne mein aapki help kar sakta hoon.",

        "help":
            "Main tasks, notes, reminders aur daily priorities "
            "mein aapki help kar sakta hoon. Aap pooch sakte hain:\n\n"
            "• Mere pending tasks dikhao\n"
            "• Mere completed tasks dikhao\n"
            "• Mere high priority tasks batao\n"
            "• Mere overdue tasks dikhao\n"
            "• Aaj mujhe kin tasks par focus karna chahiye?\n"
            "• Aaj ke tasks dikhao\n"
            "• Kal ke tasks dikhao\n"
            "• Mere reminders dikhao\n"
            "• Mere notes dikhao\n"
            "• Task banao Complete PHP project\n"
            "• High priority task banao Submit report",

        "error":
            "Maaf kijiye, request process karte waqt problem aa gayi."

    },


    "urdu": {

        "empty":
            "براہ کرم اپنا سوال درج کریں۔",

        "no_tasks":
            "آپ کی درخواست کے مطابق کوئی ٹاسک نہیں ملا۔",

        "tasks":
            "آپ کی درخواست کے مطابق ٹاسکس یہ ہیں:",

        "focus":
            "آپ کے موجودہ ٹاسکس کو دیکھتے ہوئے، آج آپ کو ان ٹاسکس پر توجہ دینی چاہیے:",

        "no_focus":
            "آج آپ کے پاس کوئی زیر التوا ٹاسک نہیں ہے جس پر توجہ دینے کی ضرورت ہو۔",

        "no_notes":
            "آپ کے پاس ابھی کوئی نوٹ موجود نہیں ہے۔",

        "notes":
            "آپ کے تازہ ترین نوٹس یہ ہیں:",

        "no_reminders":
            "آپ کے پاس ابھی کوئی ریمائنڈر نہیں ہے۔",

        "reminders":
            "آپ کے تازہ ترین ریمائنڈرز یہ ہیں:",

        "task_created":
            "ٹاسک کامیابی سے بنا دیا گیا ہے۔",

        "task_title_missing":
            "براہ کرم ٹاسک کا عنوان درج کریں۔",

        "task_create_error":
            "معذرت، ٹاسک بنایا نہیں جا سکا۔",

        "greeting":
            "السلام علیکم! 👋 میں آپ کا AI Productivity Assistant ہوں۔ "
            "میں آپ کے ٹاسکس، نوٹس اور ریمائنڈرز کو منظم کرنے میں مدد کر سکتا ہوں۔",

        "help":
            "میں آپ کے ٹاسکس، نوٹس، ریمائنڈرز اور روزانہ کی ترجیحات "
            "میں مدد کر سکتا ہوں۔ آپ مثال کے طور پر پوچھ سکتے ہیں:\n\n"
            "• میرے زیر التوا ٹاسکس دکھائیں\n"
            "• میرے مکمل شدہ ٹاسکس دکھائیں\n"
            "• میرے اہم ٹاسکس دکھائیں\n"
            "• میرے تاخیر شدہ ٹاسکس دکھائیں\n"
            "• آج مجھے کن ٹاسکس پر توجہ دینی چاہیے؟\n"
            "• آج کے ٹاسکس دکھائیں\n"
            "• کل کے ٹاسکس دکھائیں\n"
            "• میرے ریمائنڈرز دکھائیں\n"
            "• میرے نوٹس دکھائیں\n"
            "• ٹاسک بنائیں Complete PHP project",

        "error":
            "معذرت، اس وقت آپ کی درخواست پر کارروائی نہیں ہو سکی۔"

    }
}


# ============================================================
# TASK RESPONSE FORMATTER
# ============================================================

def format_tasks(tasks, language):

    if not tasks:

        return MESSAGES[language]["no_tasks"]


    reply = MESSAGES[language]["tasks"] + "\n\n"


    for task in tasks:

        title = task[0]
        priority = task[1]
        status = task[2]
        due_date = task[3]


        if language == "urdu":

            reply += (
                f"• {title}\n"
                f"  ترجیح: {priority}\n"
                f"  حالت: {status}\n"
                f"  آخری تاریخ: {due_date}\n\n"
            )

        else:

            reply += (
                f"• {title}\n"
                f"  Priority: {priority}\n"
                f"  Status: {status}\n"
                f"  Due: {due_date}\n\n"
            )


    return reply


# ============================================================
# TODAY'S FOCUS FORMATTER
# ============================================================

def format_focus_tasks(tasks, language):

    if not tasks:

        return MESSAGES[language]["no_focus"]


    reply = MESSAGES[language]["focus"] + "\n\n"


    for task in tasks:

        title = task[0]
        priority = task[1]
        status = task[2]
        due_date = task[3]


        if language == "urdu":

            reply += (
                f"• {title}\n"
                f"  ترجیح: {priority}\n"
                f"  حالت: {status}\n"
                f"  آخری تاریخ: {due_date}\n\n"
            )

        else:

            reply += (
                f"• {title}\n"
                f"  Priority: {priority}\n"
                f"  Status: {status}\n"
                f"  Due: {due_date}\n\n"
            )


    return reply


# ============================================================
# NOTES FORMATTER
# ============================================================

def format_notes(notes, language):

    if not notes:

        return MESSAGES[language]["no_notes"]


    reply = MESSAGES[language]["notes"] + "\n\n"


    for note in notes:

        title = note[0]
        content = note[1]
        category = note[2]
        created_at = note[3]


        if language == "urdu":

            reply += (
                f"• {title}\n"
                f"  زمرہ: {category}\n"
                f"  مواد: {content}\n"
                f"  تاریخ و وقت: {created_at}\n\n"
            )

        else:

            reply += (
                f"• {title}\n"
                f"  Category: {category}\n"
                f"  Content: {content}\n"
                f"  Created: {created_at}\n\n"
            )


    return reply


# ============================================================
# REMINDER FORMATTER
# ============================================================

def format_reminders(reminders, language):

    if not reminders:

        return MESSAGES[language]["no_reminders"]


    reply = MESSAGES[language]["reminders"] + "\n\n"


    for reminder in reminders:

        description = reminder[0]
        reminder_date = reminder[1]
        reminder_time = reminder[2]
        status = reminder[3]


        if language == "urdu":

            reply += (
                f"• {description}\n"
                f"  تاریخ: {reminder_date}\n"
                f"  وقت: {reminder_time}\n"
                f"  حالت: {status}\n\n"
            )

        else:

            reply += (
                f"• {description}\n"
                f"  Date: {reminder_date}\n"
                f"  Time: {reminder_time}\n"
                f"  Status: {status}\n\n"
            )


    return reply


# ============================================================
# CREATE TASK REQUEST DETECTION
# ============================================================

def is_create_task_request(message):

    create_phrases = [

        # English
        "create a task",
        "create task",
        "create a high priority task",
        "create a low priority task",
        "create a medium priority task",

        "add a task",
        "add task",
        "add a high priority task",
        "add a low priority task",
        "add a medium priority task",

        "make a task",
        "make task",
        "make a high priority task",
        "make a low priority task",
        "make a medium priority task",

        "new task",
        "create my task",
        "add my task",

        # Roman Urdu
        "task bana",
        "task banao",
        "task add karo",
        "kaam add karo",
        "kaam banao",

        # Urdu
        "ٹاسک بنائیں",
        "ٹاسک بناؤ",
        "ٹاسک شامل کریں"
    ]


    return any(
        phrase in message
        for phrase in create_phrases
    )


# ============================================================
# EXTRACT TASK DETAILS
# ============================================================

def extract_task_details(message):

    message = message.strip()


    prefixes = [

        "create a high priority task called ",
        "create a low priority task called ",
        "create a medium priority task called ",

        "create a high priority task ",
        "create a low priority task ",
        "create a medium priority task ",

        "create a task called ",
        "create task called ",

        "add a high priority task called ",
        "add a low priority task called ",
        "add a medium priority task called ",

        "add a high priority task ",
        "add a low priority task ",
        "add a medium priority task ",

        "add a task called ",
        "add task called ",

        "make a high priority task called ",
        "make a low priority task called ",
        "make a medium priority task called ",

        "make a high priority task ",
        "make a low priority task ",
        "make a medium priority task ",

        "make a task called ",
        "make task called ",

        "new task called ",
        "new task ",

        "create a task ",
        "create task ",

        "add a task ",
        "add task ",

        "make a task ",
        "make task ",

        "task bana ",
        "task banao ",
        "task add karo ",
        "kaam add karo ",
        "kaam banao "
    ]


    title = message


    for prefix in prefixes:

        if message.startswith(prefix):

            title = message[len(prefix):].strip()

            break


    return title


# ============================================================
# TASK REQUEST DETECTION
# ============================================================

def is_task_request(message):

    task_words = [

        # English
        "task",
        "tasks",
        "to do",
        "todo",
        "work",
        "focus",
        "productivity",
        "priority",
        "priorities",
        "pending",
        "overdue",
        "unfinished",
        "incomplete",
        "completed",
        "complete",
        "finished",
        "done",
        "today",
        "tomorrow",

        # Roman Urdu
        "kaam",
        "kam",
        "focus",
        "priority",
        "pending",
        "mukammal",
        "poore",
        "pura",
        "aaj",
        "kal",

        # Urdu
        "ٹاسک",
        "کام",
        "توجہ",
        "اہم",
        "زیر التوا",
        "نامکمل",
        "مکمل",
        "آج",
        "کل"
    ]


    return any(
        word in message
        for word in task_words
    )


# ============================================================
# NOTES REQUEST DETECTION
# ============================================================

def is_note_request(message):

    note_words = [

        "note",
        "notes",
        "notebook",
        "my note",
        "my notes",

        "نوٹ",
        "نوٹس"
    ]


    return any(
        word in message
        for word in note_words
    )


# ============================================================
# REMINDER REQUEST DETECTION
# ============================================================

def is_reminder_request(message):

    reminder_words = [

        "reminder",
        "reminders",
        "remind me",
        "reminder list",

        "yaad",
        "yad",
        "yaad dilao",
        "yaad dila",

        "یاد",
        "ریماینڈر",
        "ریمائنڈر"
    ]


    return any(
        word in message
        for word in reminder_words
    )


# ============================================================
# TASK FILTER
# ============================================================

def get_task_filter(message):

    # --------------------------------------------------------
    # FOCUS
    # --------------------------------------------------------

    focus_phrases = [

        "focus on today",
        "focus today",
        "focus on",
        "should i focus",
        "what should i focus",
        "which tasks should i focus",
        "what tasks should i focus",
        "today's focus",
        "todays focus",
        "daily focus",
        "today focus",

        "aaj kis",
        "aaj kin",
        "kis task par focus",
        "kin tasks par focus",
        "focus karna",
        "focus karun",
        "aaj focus",

        "آج کس",
        "آج کن",
        "توجہ دینی",
        "توجہ دوں",
        "آج کی ترجیح"
    ]


    if any(
        phrase in message
        for phrase in focus_phrases
    ):

        return "focus"


    # --------------------------------------------------------
    # PENDING
    # --------------------------------------------------------

    pending_words = [

        "pending",
        "incomplete",
        "unfinished",
        "not completed",
        "underway",

        "pending tasks",
        "pending task",

        "زیر التوا",
        "نامکمل"
    ]


    if any(
        word in message
        for word in pending_words
    ):

        return "pending"


    # --------------------------------------------------------
    # COMPLETED
    # --------------------------------------------------------

    completed_words = [

        "completed",
        "complete",
        "finished",
        "done",
        "completed tasks",

        "mukammal",
        "poore",
        "pura",

        "مکمل",
        "مکمل شدہ"
    ]


    if any(
        word in message
        for word in completed_words
    ):

        return "completed"


    # --------------------------------------------------------
    # HIGH PRIORITY
    # --------------------------------------------------------

    high_priority_phrases = [

        "high priority",
        "high-priority",
        "important task",
        "important tasks",
        "top priority",

        "high priority tasks",
        "important work",

        "اہم ٹاسک",
        "اہم کام"
    ]


    if any(
        phrase in message
        for phrase in high_priority_phrases
    ):

        return "high_priority"


    # --------------------------------------------------------
    # OVERDUE
    # --------------------------------------------------------

    overdue_phrases = [

        "overdue",
        "overdue tasks",
        "late tasks",
        "past due",
        "past deadline",

        "taakhir",
        "taakhir shuda",

        "تاخیر",
        "تاخیر شدہ"
    ]


    if any(
        phrase in message
        for phrase in overdue_phrases
    ):

        return "overdue"


    # --------------------------------------------------------
    # TODAY
    # --------------------------------------------------------

    today_phrases = [

        "today",
        "due today",
        "today's tasks",
        "todays tasks",
        "tasks for today",

        "aaj",
        "aaj ke tasks",

        "آج"
    ]


    if any(
        phrase in message
        for phrase in today_phrases
    ):

        return "today"


    # --------------------------------------------------------
    # TOMORROW
    # --------------------------------------------------------

    tomorrow_phrases = [

        "tomorrow",
        "due tomorrow",
        "tomorrow's tasks",
        "tomorrows tasks",
        "tasks for tomorrow",

        "kal",
        "kal ke tasks",

        "کل"
    ]


    if any(
        phrase in message
        for phrase in tomorrow_phrases
    ):

        return "tomorrow"


    return "all"


# ============================================================
# CHAT API
# ============================================================

@app.post("/chat")
def chat(request: ChatRequest):

    original_message = request.message.strip()

    # ========================================================
    # CURRENT USER
    # ========================================================

    user_id = request.user_id


    # ========================================================
    # EMPTY MESSAGE
    # ========================================================

    if not original_message:

        return {
            "status": "error",
            "reply": MESSAGES["english"]["empty"]
        }


    message = original_message.lower()

    language = detect_language(message)


    connection = None
    cursor = None


    try:

        connection = get_database_connection()
        cursor = connection.cursor()


        # ====================================================
        # GREETING
        # ====================================================

        greeting_phrases = [

            "hello",
            "hi",
            "hey",
            "salam",
            "assalam",
            "assalam o alaikum",
            "السلام علیکم",
            "ہیلو"
        ]


        is_greeting = (

            message in greeting_phrases

            or "hello ai" in message
            or "hi ai" in message
            or "hey ai" in message
            or "salam ai" in message
            or "assalam o alaikum" in message
            or "السلام علیکم" in message
            or "ہیلو" in message
        )


        if is_greeting:

            return {
                "status": "success",
                "reply": MESSAGES[language]["greeting"]
            }


        # ====================================================
        # HELP
        # ====================================================

        help_phrases = [

            "help",
            "what can you do",
            "how can you help",
            "what do you do",

            "madad",
            "help karo",
            "kya kar sakte ho",

            "مدد",
            "آپ کیا کر سکتے ہیں"
        ]


        if any(
            phrase in message
            for phrase in help_phrases
        ):

            return {
                "status": "success",
                "reply": MESSAGES[language]["help"]
            }


        # ====================================================
        # CREATE TASK
        # ====================================================

        if is_create_task_request(message):

            try:

                # ------------------------------------------------
                # Extract task title
                # ------------------------------------------------

                task_title = extract_task_details(message)


                # ------------------------------------------------
                # Check empty title
                # ------------------------------------------------

                if not task_title:

                    return {
                        "status": "error",
                        "reply": MESSAGES[language]["task_title_missing"]
                    }


                # ------------------------------------------------
                # Default values
                # ------------------------------------------------

                priority = "Medium"
                status = "Pending"
                description = ""
                due_date = None


                # ------------------------------------------------
                # Detect priority
                # ------------------------------------------------

                if "high priority" in message:

                    priority = "High"

                elif "low priority" in message:

                    priority = "Low"

                elif "medium priority" in message:

                    priority = "Medium"


                # ------------------------------------------------
                # Remove priority from title
                # ------------------------------------------------

                task_title = task_title.replace(
                    "high priority",
                    ""
                )

                task_title = task_title.replace(
                    "medium priority",
                    ""
                )

                task_title = task_title.replace(
                    "low priority",
                    ""
                )

                task_title = task_title.strip()


                # ------------------------------------------------
                # Check title
                # ------------------------------------------------

                if not task_title:

                    return {
                        "status": "error",
                        "reply": MESSAGES[language]["task_title_missing"]
                    }


                # ------------------------------------------------
                # Insert task
                # ------------------------------------------------

                cursor.execute(
                    """
                    INSERT INTO tasks
                    (
                        user_id,
                        title,
                        description,
                        priority,
                        status,
                        due_date
                    )
                    VALUES
                    (
                        %s,
                        %s,
                        %s,
                        %s,
                        %s,
                        %s
                    )
                    """,
                    (
                        user_id,
                        task_title,
                        description,
                        priority,
                        status,
                        due_date
                    )
                )


                connection.commit()


                # ------------------------------------------------
                # Success response
                # ------------------------------------------------

                if language == "urdu":

                    reply = (
                        f"{MESSAGES[language]['task_created']}\n\n"
                        f"عنوان: {task_title}\n"
                        f"ترجیح: {priority}\n"
                        f"حالت: {status}"
                    )

                else:

                    reply = (
                        f"{MESSAGES[language]['task_created']}\n\n"
                        f"Title: {task_title}\n"
                        f"Priority: {priority}\n"
                        f"Status: {status}"
                    )


                return {
                    "status": "success",
                    "reply": reply
                }


            except Exception as error:

                connection.rollback()

                print(
                    "CREATE TASK ERROR:",
                    str(error)
                )

                return {
                    "status": "error",
                    "reply": MESSAGES[language]["task_create_error"]
                }


        # ====================================================
        # TASKS
        # ====================================================

        if is_task_request(message):

            task_filter = get_task_filter(message)


            # ------------------------------------------------
            # TODAY'S FOCUS
            # ------------------------------------------------

            if task_filter == "focus":

                cursor.execute(
                    """
                    SELECT
                        title,
                        priority,
                        status,
                        due_date
                    FROM tasks
                    WHERE user_id = %s
                    AND status != 'Completed'
                    ORDER BY
                        CASE
                            WHEN due_date < CURDATE()
                            THEN 0
                            WHEN due_date = CURDATE()
                            THEN 1
                            WHEN priority = 'High'
                            THEN 2
                            WHEN priority = 'Medium'
                            THEN 3
                            ELSE 4
                        END,
                        due_date ASC,
                        id DESC
                    LIMIT 10
                    """,
                    (user_id,)
                )


                tasks = cursor.fetchall()


                return {
                    "status": "success",
                    "reply": format_focus_tasks(
                        tasks,
                        language
                    )
                }


            # ------------------------------------------------
            # PENDING
            # ------------------------------------------------

            elif task_filter == "pending":

                cursor.execute(
                    """
                    SELECT
                        title,
                        priority,
                        status,
                        due_date
                    FROM tasks
                    WHERE user_id = %s
                    AND status = 'Pending'
                    ORDER BY
                        CASE
                            WHEN priority = 'High'
                            THEN 0
                            WHEN priority = 'Medium'
                            THEN 1
                            ELSE 2
                        END,
                        due_date ASC,
                        id DESC
                    """,
                    (user_id,)
                )


            # ------------------------------------------------
            # COMPLETED
            # ------------------------------------------------

            elif task_filter == "completed":

                cursor.execute(
                    """
                    SELECT
                        title,
                        priority,
                        status,
                        due_date
                    FROM tasks
                    WHERE user_id = %s
                    AND status = 'Completed'
                    ORDER BY id DESC
                    LIMIT 20
                    """,
                    (user_id,)
                )


            # ------------------------------------------------
            # HIGH PRIORITY
            # ------------------------------------------------

            elif task_filter == "high_priority":

                cursor.execute(
                    """
                    SELECT
                        title,
                        priority,
                        status,
                        due_date
                    FROM tasks
                    WHERE user_id = %s
                    AND priority = 'High'
                    AND status != 'Completed'
                    ORDER BY
                        due_date ASC,
                        id DESC
                    """,
                    (user_id,)
                )


            # ------------------------------------------------
            # OVERDUE
            # ------------------------------------------------

            elif task_filter == "overdue":

                cursor.execute(
                    """
                    SELECT
                        title,
                        priority,
                        status,
                        due_date
                    FROM tasks
                    WHERE user_id = %s
                    AND due_date IS NOT NULL
                    AND due_date < CURDATE()
                    AND status != 'Completed'
                    ORDER BY
                        due_date ASC,
                        id DESC
                    """,
                    (user_id,)
                )


            # ------------------------------------------------
            # TODAY
            # ------------------------------------------------

            elif task_filter == "today":

                cursor.execute(
                    """
                    SELECT
                        title,
                        priority,
                        status,
                        due_date
                    FROM tasks
                    WHERE user_id = %s
                    AND due_date = CURDATE()
                    AND status != 'Completed'
                    ORDER BY
                        CASE
                            WHEN priority = 'High'
                            THEN 0
                            WHEN priority = 'Medium'
                            THEN 1
                            ELSE 2
                        END,
                        id DESC
                    """,
                    (user_id,)
                )


            # ------------------------------------------------
            # TOMORROW
            # ------------------------------------------------

            elif task_filter == "tomorrow":

                cursor.execute(
                    """
                    SELECT
                        title,
                        priority,
                        status,
                        due_date
                    FROM tasks
                    WHERE user_id = %s
                    AND due_date = DATE_ADD(
                        CURDATE(),
                        INTERVAL 1 DAY
                    )
                    AND status != 'Completed'
                    ORDER BY
                        CASE
                            WHEN priority = 'High'
                            THEN 0
                            WHEN priority = 'Medium'
                            THEN 1
                            ELSE 2
                        END,
                        id DESC
                    """,
                    (user_id,)
                )


            # ------------------------------------------------
            # ALL TASKS
            # ------------------------------------------------

            else:

                cursor.execute(
                    """
                    SELECT
                        title,
                        priority,
                        status,
                        due_date
                    FROM tasks
                    WHERE user_id = %s
                    ORDER BY id DESC
                    LIMIT 20
                    """,
                    (user_id,)
                )


            tasks = cursor.fetchall()


            return {
                "status": "success",
                "reply": format_tasks(
                    tasks,
                    language
                )
            }


        # ====================================================
        # NOTES
        # ====================================================

        if is_note_request(message):

            cursor.execute(
                """
                SELECT
                    title,
                    content,
                    category,
                    created_at
                FROM notes
                WHERE user_id = %s
                ORDER BY id DESC
                LIMIT 20
                """,
                (user_id,)
            )


            notes = cursor.fetchall()


            return {
                "status": "success",
                "reply": format_notes(
                    notes,
                    language
                )
            }


        # ====================================================
        # REMINDERS
        # ====================================================

        if is_reminder_request(message):

            cursor.execute(
                """
                SELECT
                    description,
                    reminder_date,
                    reminder_time,
                    status
                FROM reminders
                WHERE user_id = %s
                ORDER BY id DESC
                LIMIT 20
                """,
                (user_id,)
            )


            reminders = cursor.fetchall()


            return {
                "status": "success",
                "reply": format_reminders(
                    reminders,
                    language
                )
            }


        # ====================================================
        # GENERAL FALLBACK
        # ====================================================

        return {
            "status": "success",
            "reply": MESSAGES[language]["help"]
        }


    except Exception as error:

        print(
            "CHAT ERROR:",
            str(error)
        )

        return {
            "status": "error",
            "reply": MESSAGES[language]["error"]
        }


    finally:

        if cursor:

            cursor.close()

        if connection:

            connection.close()


# ============================================================
# GET TASKS API
# ============================================================

@app.get("/tasks")
def get_tasks(user_id: int):

    connection = None
    cursor = None


    try:

        connection = get_database_connection()
        cursor = connection.cursor()


        cursor.execute(
            """
            SELECT
                id,
                title,
                priority,
                status,
                due_date
            FROM tasks
            WHERE user_id = %s
            ORDER BY id DESC
            LIMIT 20
            """,
            (user_id,)
        )


        tasks = cursor.fetchall()


        task_list = []


        for task in tasks:

            task_list.append({

                "id":
                    task[0],

                "title":
                    task[1],

                "priority":
                    task[2],

                "status":
                    task[3],

                "due_date":
                    str(task[4])
                    if task[4]
                    else None
            })


        return {
            "status": "success",
            "tasks": task_list
        }


    except Exception as error:

        return {
            "status": "error",
            "message": str(error)
        }


    finally:

        if cursor:

            cursor.close()

        if connection:

            connection.close()


# ============================================================
# GET NOTES API
# ============================================================

@app.get("/notes")
def get_notes(user_id: int):

    connection = None
    cursor = None


    try:

        connection = get_database_connection()
        cursor = connection.cursor()


        cursor.execute(
            """
            SELECT
                id,
                title,
                content,
                category,
                created_at
            FROM notes
            WHERE user_id = %s
            ORDER BY id DESC
            LIMIT 20
            """,
            (user_id,)
        )


        notes = cursor.fetchall()


        note_list = []


        for note in notes:

            note_list.append({

                "id":
                    note[0],

                "title":
                    note[1],

                "content":
                    note[2],

                "category":
                    note[3],

                "created_at":
                    str(note[4])
                    if note[4]
                    else None
            })


        return {
            "status": "success",
            "notes": note_list
        }


    except Exception as error:

        return {
            "status": "error",
            "message": str(error)
        }


    finally:

        if cursor:

            cursor.close()

        if connection:

            connection.close()


# ============================================================
# GET REMINDERS API
# ============================================================

@app.get("/reminders")
def get_reminders(user_id: int):

    connection = None
    cursor = None


    try:

        connection = get_database_connection()
        cursor = connection.cursor()


        cursor.execute(
            """
            SELECT
                id,
                description,
                reminder_date,
                reminder_time,
                status,
                created_at
            FROM reminders
            WHERE user_id = %s
            ORDER BY id DESC
            LIMIT 20
            """,
            (user_id,)
        )


        reminders = cursor.fetchall()


        reminder_list = []


        for reminder in reminders:

            reminder_list.append({

                "id":
                    reminder[0],

                "description":
                    reminder[1],

                "reminder_date":
                    str(reminder[2])
                    if reminder[2]
                    else None,

                "reminder_time":
                    str(reminder[3])
                    if reminder[3]
                    else None,

                "status":
                    reminder[4],

                "created_at":
                    str(reminder[5])
                    if reminder[5]
                    else None
            })


        return {
            "status": "success",
            "reminders": reminder_list
        }


    except Exception as error:

        return {
            "status": "error",
            "message": str(error)
        }


    finally:

        if cursor:

            cursor.close()

        if connection:

            connection.close()