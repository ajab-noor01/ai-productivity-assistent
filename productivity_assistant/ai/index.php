<?php

session_start();

if (!isset($_SESSION["user_id"])) {

    header("Location: ../auth/login.php");
    exit;

}


// =====================================================
// TRIAL / SUBSCRIPTION CHECK
// =====================================================

require_once "../config/trial_check.php";

require_once "../config/db.php";
// =====================================================
// USER INFORMATION
// =====================================================

$user_name =
    $_SESSION["user_name"] ?? "User";

$user_id =
    (int) $_SESSION["user_id"];

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>AI Productivity Assistant</title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            background:
                linear-gradient(
                    135deg,
                    #f5f7fb,
                    #eef2f7
                );

            min-height: 100vh;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

        }


        /* =========================================
           MAIN CONTAINER
        ========================================= */

        .ai-container {

            max-width: 1000px;

            margin: 40px auto;

            padding: 0 15px;

        }


        /* =========================================
           CHAT CARD
        ========================================= */

        .ai-card {

            background: #ffffff;

            border-radius: 20px;

            overflow: hidden;

            box-shadow:
                0 15px 45px
                rgba(0, 0, 0, 0.10);

            border: 1px solid #e9ecef;

        }


        /* =========================================
           HEADER
        ========================================= */

        .ai-header {
    background: linear-gradient(
        135deg,
        #0d6efd,
        #0b5ed7
    );

    color: #ffffff;

    padding: 22px;

    box-shadow:
        0 4px 15px
        rgba(13, 110, 253, 0.15);
}


        .ai-title {

            display: flex;

            align-items: center;

            gap: 12px;

        }


        .ai-icon {

            width: 45px;

            height: 45px;

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #ffffff;

            color: #212529;

            font-size: 20px;

        }


        .online-dot {

            width: 8px;

            height: 8px;

            display: inline-block;

            border-radius: 50%;

            background: #20c997;

            margin-right: 5px;

        }


        /* =========================================
           CHAT AREA
        ========================================= */

        .chat-box {

            height: 500px;

            overflow-y: auto;

            padding: 25px;

            background: #fafbfc;

            scroll-behavior: smooth;

        }


        .chat-box::-webkit-scrollbar {

            width: 7px;

        }


        .chat-box::-webkit-scrollbar-thumb {

            background: #ced4da;

            border-radius: 10px;

        }


        /* =========================================
           MESSAGE
        ========================================= */

        .message {

            display: flex;

            margin-bottom: 20px;

            animation:
                messageAppear
                0.25s ease;

        }


        @keyframes messageAppear {

            from {

                opacity: 0;

                transform:
                    translateY(8px);

            }

            to {

                opacity: 1;

                transform:
                    translateY(0);

            }

        }


        .user-message {

            justify-content: flex-end;

        }


        .ai-message {

            justify-content: flex-start;

        }


        .message-wrapper {

            max-width: 78%;

        }


        .message-label {

            font-size: 12px;

            color: #6c757d;

            margin-bottom: 5px;

        }


        .user-message .message-label {

            text-align: right;

        }


        .message-content {

            padding: 13px 16px;

            border-radius: 16px;

            line-height: 1.6;

            white-space: normal;

            word-wrap: break-word;

        }


       .user-message .message-content {
    background: #0d6efd;

    color: #ffffff;

    display: inline-block;

    padding: 12px 16px;

    border-radius: 15px 15px 0 15px;

    max-width: 75%;

    box-shadow:
        0 3px 10px
        rgba(13, 110, 253, 0.15);
}


       .ai-message .message-content {
    background: #f1f5f9;

    color: #212529;

    display: inline-block;

    padding: 12px 16px;

    border-radius: 15px 15px 15px 0;

    max-width: 75%;
}


        .message-time {

            font-size: 10px;

            color: #adb5bd;

            margin-top: 4px;

        }


        .user-message .message-time {

            text-align: right;

        }


        /* =========================================
           WELCOME MESSAGE
        ========================================= */

        .welcome-title {

            font-weight: 600;

            margin-bottom: 6px;

        }


        .welcome-text {

            color: #6c757d;

            margin: 0;

        }


        /* =========================================
           THINKING
        ========================================= */

        .thinking-dots {

            display: inline-flex;

            gap: 4px;

            align-items: center;

        }


        .thinking-dots span {

            width: 6px;

            height: 6px;

            background: #6c757d;

            border-radius: 50%;

            animation:
                thinking
                1.2s
                infinite;

        }


        .thinking-dots span:nth-child(2) {

            animation-delay: 0.2s;

        }


        .thinking-dots span:nth-child(3) {

            animation-delay: 0.4s;

        }


        @keyframes thinking {

            0%,
            60%,
            100% {

                opacity: 0.3;

                transform:
                    translateY(0);

            }

            30% {

                opacity: 1;

                transform:
                    translateY(-4px);

            }

        }


        /* =========================================
           INPUT AREA
        ========================================= */

        .chat-input {

            padding: 18px;

            background: white;

            border-top:
                1px solid #e9ecef;

        }


        .input-group {

            background: #f8f9fa;

            border-radius: 14px;

            padding: 5px;

            border:
                1px solid #dee2e6;

        }


        #messageInput {

            border: none;

            background: transparent;

            box-shadow: none;

            padding: 12px;

        }


        #messageInput:focus {

            box-shadow: none;

        }


        #sendButton {

            border-radius: 10px;

            min-width: 95px;

        }


/* =================================================
   QUICK ACTIONS
================================================= */

.quick-actions {

    margin-top: 14px;

}


.quick-title {

    font-size: 12px;

    font-weight: 600;

    color: #6c757d;

    margin-bottom: 9px;

}


.quick-title i {

    color: #0d6efd;

    margin-right: 5px;

}


.quick-buttons {

    display: flex;

    flex-wrap: wrap;

    gap: 8px;

}


.quick-btn {

    border: 1px solid #dee2e6;

    background: #ffffff;

    color: #495057;

    border-radius: 9px;

    padding: 8px 12px;

    font-size: 13px;

    font-weight: 500;

    display: inline-flex;

    align-items: center;

    gap: 7px;

    cursor: pointer;

    transition:
        background 0.2s ease,
        border-color 0.2s ease,
        color 0.2s ease,
        transform 0.2s ease;

}


.quick-btn i {

    color: #0d6efd;

    font-size: 12px;

}


.quick-btn:hover {

    background: #f0f6ff;

    border-color: #86b7fe;

    color: #0d6efd;

    transform: translateY(-1px);

}


.quick-btn:active {

    transform: translateY(0);

}


@media (max-width: 576px) {

    .quick-btn {

        flex: 1 1 calc(50% - 8px);

        justify-content: center;

    }

}



        /* =========================================
           RESPONSIVE
        ========================================= */

        @media (max-width: 768px) {

            .ai-container {

                margin: 15px auto;

            }


            .chat-box {

                height: 60vh;

                padding: 15px;

            }


            .message-wrapper {

                max-width: 90%;

            }


            .ai-header {

                padding: 18px;

            }


            .dashboard-btn {

                display: none;

            }

        }

/* =================================================
   THINKING INDICATOR
================================================= */

.thinking-dots {

    display: inline-flex;

    align-items: center;

    gap: 4px;

    margin-left: 5px;

}


.thinking-dots span {

    width: 5px;

    height: 5px;

    background: #6c757d;

    border-radius: 50%;

    display: inline-block;

    animation: thinkingAnimation 1.4s infinite ease-in-out;

}


.thinking-dots span:nth-child(1) {

    animation-delay: 0s;

}


.thinking-dots span:nth-child(2) {

    animation-delay: 0.2s;

}


.thinking-dots span:nth-child(3) {

    animation-delay: 0.4s;

}


@keyframes thinkingAnimation {

    0%,
    60%,
    100% {

        opacity: 0.3;

        transform: translateY(0);

    }

    30% {

        opacity: 1;

        transform: translateY(-3px);

    }

}


/* =================================================
   SEND BUTTON
================================================= */

#sendButton:disabled {

    opacity: 0.65;

    cursor: not-allowed;

}


#sendButton {

    min-width: 85px;

    transition:
        opacity 0.2s ease,
        transform 0.2s ease;

}


/* =================================================
   MESSAGE TRANSITION
================================================= */

.message {

    animation: messageAppear 0.2s ease;

}


@keyframes messageAppear {

    from {

        opacity: 0;

        transform: translateY(5px);

    }

    to {

        opacity: 1;

        transform: translateY(0);

    }

}


    </style>

</head>


<body>


<div class="ai-container">


    <div class="ai-card">


        <!-- ======================================
             HEADER
        ======================================= -->

        <div class="ai-header">

            <div
                class="d-flex
                       justify-content-between
                       align-items-center"
            >

                <div class="ai-title">

                    <div class="ai-icon">

                        <i class="fa-solid fa-robot"></i>

                    </div>


                    <div>

                        <h4 class="mb-1">

                            AI Assistant

                        </h4>

                        <small>

                            <span
                                class="online-dot"
                            ></span>

                            Online ·
                            <?= htmlspecialchars($user_name) ?>

                        </small>

                    </div>

                </div>


                <div
                    class="d-flex gap-2"
                >

                    <button
                        type="button"
                        id="clearChatButton"
                        class="btn btn-outline-light btn-sm"
                    >

                        <i
                            class="fa-solid
                                   fa-trash-can
                                   me-1"
                        ></i>

                        Clear

                    </button>


                    <a
                        href="../dashboard/index.php"
                        class="btn btn-light btn-sm dashboard-btn"
                    >

                        <i
                            class="fa-solid
                                   fa-arrow-left
                                   me-1"
                        ></i>

                        Dashboard

                    </a>

                </div>

            </div>

        </div>


        <!-- ======================================
             CHAT BOX
        ======================================= -->

        <div
            id="chatBox"
            class="chat-box"
        >


            <!-- AI WELCOME -->

            <div
                class="message ai-message"
            >

                <div
                    class="message-wrapper"
                >

                    <div
                        class="message-label"
                    >

                        AI Assistant

                    </div>


                    <div
                        class="message-content"
                    >

                        <div class="welcome-title">

    AI Productivity Assistant

</div>

<p class="welcome-text">

    How can I help you today?
    Ask me about your tasks, notes, reminders,
    or daily productivity.

</p>

                    </div>


                    <div
                        class="message-time"
                    >

                        <?= date("h:i A") ?>

                    </div>

                </div>

            </div>


        </div>


        <!-- ======================================
             INPUT
        ======================================= -->

        <div class="chat-input">


            <form
                id="chatForm"
            >

                <div
                    class="input-group"
                >

                    <input
                        type="text"
                        id="messageInput"
                        class="form-control"
                        placeholder="Ask about your tasks, notes or reminders..."
                        autocomplete="off"
                        maxlength="1000"
                        required
                    >


                    <button
                        type="submit"
                        id="sendButton"
                        class="btn btn-dark"
                    >

                        <i
                            class="fa-solid
                                   fa-paper-plane
                                   me-1"
                        ></i>

                        Send

                    </button>

                </div>


        
<!-- ======================================
     QUICK QUESTIONS
======================================= -->

<div class="quick-actions">

    <div class="quick-title">

        <i class="fa-solid fa-wand-magic-sparkles"></i>

        Quick actions

    </div>


    <div class="quick-buttons">

        <button
            type="button"
            class="quick-btn"
            data-message="Show my pending tasks"
        >

            <i class="fa-solid fa-clock"></i>

            <span>Pending tasks</span>

        </button>


        <button
            type="button"
            class="quick-btn"
            data-message="Show my completed tasks"
        >

            <i class="fa-solid fa-circle-check"></i>

            <span>Completed tasks</span>

        </button>


        <button
            type="button"
            class="quick-btn"
            data-message="Show my high priority tasks"
        >

            <i class="fa-solid fa-flag"></i>

            <span>High priority</span>

        </button>


        <button
            type="button"
            class="quick-btn"
            data-message="Show my notes"
        >

            <i class="fa-solid fa-note-sticky"></i>

            <span>My notes</span>

        </button>


        <button
            type="button"
            class="quick-btn"
            data-message="Show my reminders"
        >

            <i class="fa-solid fa-bell"></i>

            <span>My reminders</span>

        </button>


        <button
            type="button"
            class="quick-btn"
            data-message="Which tasks should I focus on today?"
        >

            <i class="fa-solid fa-bullseye"></i>

            <span>Today's focus</span>

        </button>

    </div>

</div>


            </form>

        </div>


    </div>


</div>


<script>

/* =====================================================
   ELEMENTS
===================================================== */

const chatForm =
    document.getElementById("chatForm");

const messageInput =
    document.getElementById("messageInput");

const chatBox =
    document.getElementById("chatBox");

const sendButton =
    document.getElementById("sendButton");

const clearChatButton =
    document.getElementById("clearChatButton");


/* =====================================================
   USER ID
===================================================== */

const currentUserId = <?= json_encode($user_id) ?>;

console.log("CURRENT USER ID:", currentUserId);
/* =====================================================
   CURRENT TIME
===================================================== */

function getCurrentTime() {

    return new Date().toLocaleTimeString(
        [],
        {
            hour: "2-digit",
            minute: "2-digit"
        }
    );

}


/* =====================================================
   CHAT STORAGE
===================================================== */

const CHAT_STORAGE_KEY =
    "ai_productivity_chat_<?= $user_id ?>";


/* =====================================================
   SAVE CHAT MESSAGE
===================================================== */

function saveChatMessage(
    sender,
    message,
    time
) {

    const history =
        JSON.parse(
            localStorage.getItem(
                CHAT_STORAGE_KEY
            )
        ) || [];

    history.push({

        sender: sender,

        message: message,

        time: time

    });

    localStorage.setItem(
        CHAT_STORAGE_KEY,
        JSON.stringify(history)
    );

}


/* =====================================================
   LOAD SAVED CHAT
===================================================== */

function loadChatHistory() {

    const history =
        JSON.parse(
            localStorage.getItem(
                CHAT_STORAGE_KEY
            )
        ) || [];

    if (history.length === 0) {

        return;

    }

    chatBox.innerHTML = "";

    history.forEach(function(item) {

        if (item.sender === "user") {

            addUserMessage(
                item.message,
                item.time,
                false
            );

        } else {

            addAIMessage(
                item.message,
                item.time,
                false
            );

        }

    });

    scrollChat();

}


/* =====================================================
   ESCAPE HTML
===================================================== */

function escapeHtml(text) {

    const div =
        document.createElement("div");

    div.textContent = text;

    return div.innerHTML;

}


/* =====================================================
   SCROLL CHAT
===================================================== */

function scrollChat() {

    chatBox.scrollTop =
        chatBox.scrollHeight;

}


/* =====================================================
   ADD USER MESSAGE
===================================================== */

function addUserMessage(
    message,
    savedTime = null,
    saveMessage = true
) {

    const wrapper =
        document.createElement("div");

    wrapper.className =
        "message user-message";

    const messageTime =
        savedTime ||
        getCurrentTime();

    wrapper.innerHTML = `

        <div class="message-wrapper">

            <div class="message-label">

                You

            </div>

            <div class="message-content">

                ${escapeHtml(message)}

            </div>

            <div class="message-time">

                ${messageTime}

            </div>

        </div>

    `;

    chatBox.appendChild(wrapper);

    if (saveMessage) {

        saveChatMessage(
            "user",
            message,
            messageTime
        );

    }

    scrollChat();

}


/* =====================================================
   ADD AI MESSAGE
===================================================== */

function addAIMessage(
    reply,
    savedTime = null,
    saveMessage = true
) {

    const wrapper =
        document.createElement("div");

    wrapper.className =
        "message ai-message";

    const messageTime =
        savedTime ||
        getCurrentTime();

    const safeReply =
        escapeHtml(reply)
            .replace(/\n/g, "<br>");

    wrapper.innerHTML = `

        <div class="message-wrapper">

            <div class="message-label">

                AI Assistant

            </div>

            <div class="message-content">

                ${safeReply}

            </div>

            <div class="message-time">

                ${messageTime}

            </div>

        </div>

    `;

    chatBox.appendChild(wrapper);

    if (saveMessage) {

        saveChatMessage(
            "ai",
            reply,
            messageTime
        );

    }

    scrollChat();

}


/* =====================================================
   ADD THINKING MESSAGE
===================================================== */

function addThinkingMessage() {

    const wrapper =
        document.createElement("div");

    wrapper.className =
        "message ai-message";

    wrapper.id =
        "thinkingMessage";

    wrapper.innerHTML = `

        <div class="message-wrapper">

            <div class="message-label">

                AI Assistant

            </div>

            <div class="message-content">

                <span>

                    Thinking

                </span>

                <span class="thinking-dots">

                    <span></span>
                    <span></span>
                    <span></span>

                </span>

            </div>

        </div>

    `;

    chatBox.appendChild(wrapper);

    scrollChat();

}


/* =====================================================
   REMOVE THINKING MESSAGE
===================================================== */

function removeThinkingMessage() {

    const thinking =
        document.getElementById(
            "thinkingMessage"
        );

    if (thinking) {

        thinking.remove();

    }

}


/* =====================================================
   SEND MESSAGE
===================================================== */

async function sendMessage(message) {

    if (!message) {

        return;

    }

    /*
     * IMPORTANT:
     * Do NOT call loadChatHistory() here.
     * The old version was loading the old localStorage
     * history again after adding a new message.
     */

    addUserMessage(message);

    messageInput.value = "";

    messageInput.focus();

    sendButton.disabled = true;

    addThinkingMessage();

    try {

        const response =
            await fetch(
                "chat.php",
                {

                    method: "POST",

                    headers: {

                        "Content-Type":
                            "application/json"

                    },

                    body: JSON.stringify({
                     message: message,
                     user_id: currentUserId
                 })

                }
            );

        const data =
            await response.json();

        removeThinkingMessage();

        if (
            !response.ok ||
            data.status === "error"
        ) {

            addAIMessage(
                data.reply ||
                data.message ||
                "Sorry, something went wrong."
            );

            return;

        }

        addAIMessage(
            data.reply ||
            "I couldn't generate a response."
        );

    }

    catch (error) {

        removeThinkingMessage();

        addAIMessage(
            "I'm unable to connect to the AI service. " +
            "Please make sure the FastAPI server is running."
        );

        console.error(
            "AI Service Error:",
            error
        );

    }

    finally {

        sendButton.disabled = false;

        messageInput.focus();

    }

}


/* =====================================================
   FORM SUBMIT
===================================================== */

chatForm.addEventListener(
    "submit",
    function(event) {

        event.preventDefault();

        const message =
            messageInput.value.trim();

        if (!message) {

            return;

        }

        sendMessage(message);

    }
);


/* =====================================================
   QUICK QUESTIONS
===================================================== */

document
    .querySelectorAll(".quick-btn")
    .forEach(
        function(button) {

            button.addEventListener(
                "click",
                function() {

                    const message =
                        this.dataset.message;

                    sendMessage(message);

                }
            );

        }
    );


/* =====================================================
   CLEAR CHAT
===================================================== */

clearChatButton.addEventListener(
    "click",
    function() {

        /*
         * Remove saved chat from browser storage.
         */
        localStorage.removeItem(
            CHAT_STORAGE_KEY
        );

        /*
         * Clear chat window.
         */
        chatBox.innerHTML = "";

        /*
         * Show fresh welcome message.
         * saveMessage = false means this message
         * will NOT be saved into localStorage.
         */
        addAIMessage(
            "Chat cleared. How can I help you?",
            null,
            false
        );

        messageInput.focus();

    }
);


/* =====================================================
   ENTER KEY
===================================================== */

messageInput.addEventListener(
    "keydown",
    function(event) {

        if (
            event.key === "Enter" &&
            !event.shiftKey
        ) {

            event.preventDefault();

            chatForm.requestSubmit();

        }

    }
);


/* =====================================================
   INITIAL LOAD
===================================================== */

loadChatHistory();

messageInput.focus();


</script>


</body>

</html>

