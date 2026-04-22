<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=login_required");
    exit;
}

include 'includes/header.php'; 
?>

<style>
.chat-app-container {
    max-width: 1200px;
    margin: 20px auto;
    height: 80vh;
    display: flex;
    gap: 0;
    overflow: hidden;
    border-radius: 20px;
}

/* Sidebar Styling */
.chat-sidebar {
    width: 350px;
    background: rgba(255, 255, 255, 0.4);
    border-right: 1px solid var(--glass-border);
    display: flex;
    flex-direction: column;
}

.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid var(--glass-border);
    font-weight: 700;
    color: var(--primary-teal);
    font-size: 20px;
}

.conversation-list {
    flex: 1;
    overflow-y: auto;
}

.conv-item {
    padding: 15px 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    cursor: pointer;
    transition: background 0.2s;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.conv-item:hover { background: rgba(255, 255, 255, 0.3); }
.conv-item.active { background: rgba(0, 47, 52, 0.1); border-left: 4px solid var(--primary-teal); }

.conv-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #ddd;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
}

.conv-info { flex: 1; overflow: hidden; }
.conv-name { font-weight: 600; color: var(--text-primary); }
.conv-snippet { font-size: 13px; color: var(--text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* Main Chat Area */
.chat-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: rgba(255, 255, 255, 0.2);
}

.chat-header {
    padding: 15px 25px;
    background: rgba(255, 255, 255, 0.5);
    border-bottom: 1px solid var(--glass-border);
    display: flex;
    align-items: center;
    gap: 15px;
}

.chat-thread {
    flex: 1;
    padding: 25px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 15px;
    background-image: url('https://www.transparenttextures.com/patterns/cubes.png');
}

/* Bubbles */
.bubble {
    max-width: 70%;
    padding: 12px 18px;
    border-radius: 18px;
    position: relative;
    font-size: 15px;
    line-height: 1.4;
    word-wrap: break-word;
}

.bubble.sent {
    align-self: flex-end;
    background: var(--primary-teal);
    color: white;
    border-bottom-right-radius: 4px;
}

.bubble.received {
    align-self: flex-start;
    background: white;
    color: #333;
    border-bottom-left-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.bubble-time {
    display: block;
    font-size: 10px;
    margin-top: 5px;
    opacity: 0.7;
    text-align: right;
}

.chat-attachment {
    max-width: 100%;
    border-radius: 8px;
    margin-bottom: 8px;
    display: block;
}

/* Input Area */
.chat-input-bar {
    padding: 20px;
    background: rgba(255, 255, 255, 0.6);
    display: flex;
    align-items: center;
    gap: 10px;
    position: relative;
}

.chat-input-bar input[type="text"] {
    flex: 1;
    padding: 12px 20px;
    border-radius: 25px;
    border: 1px solid var(--glass-border);
    background: white;
    outline: none;
}

.attach-btn, .mic-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    background: none;
    cursor: pointer;
    color: var(--primary-teal);
    font-size: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: 0.2s;
}
.attach-btn:hover, .mic-btn:hover { background: rgba(0,0,0,0.05); }

.recording-status {
    position: absolute;
    left: 80px;
    right: 80px;
    background: #fff;
    padding: 12px 20px;
    border-radius: 25px;
    display: none;
    align-items: center;
    justify-content: space-between;
    color: #ff4c4c;
    font-weight: 600;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.pulse {
    width: 12px;
    height: 12px;
    background: #ff4c4c;
    border-radius: 50%;
    animation: flash 1s infinite;
}

@keyframes flash {
    0% { opacity: 1; }
    50% { opacity: 0.3; }
    100% { opacity: 1; }
}

.empty-state {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: var(--text-secondary);
    text-align: center;
}
</style>

<main>
    <div class="chat-app-container glass-panel">
        <!-- Sidebar -->
        <div class="chat-sidebar">
            <div class="sidebar-header">Messages</div>
            <div class="conversation-list" id="convList">
                <!-- Loaded via JS -->
            </div>
        </div>

        <!-- Main Workspace -->
        <div class="chat-main" id="chatMain">
            <div class="empty-state" id="emptyState">
                <i class="far fa-comments" style="font-size: 64px; margin-bottom: 20px; opacity: 0.5;"></i>
                <h3>Your Conversations</h3>
                <p>Select a person from the left to start chatting.</p>
            </div>
            
            <div id="chatContent" style="display: none; flex-direction: column; height: 100%;">
                <div class="chat-header">
                    <div class="conv-avatar" id="activePartnerAvatar"><i class="fas fa-user"></i></div>
                    <div>
                        <div class="conv-name" id="activePartnerName">...</div>
                        <div style="font-size: 12px; color: var(--text-secondary);">Online</div>
                    </div>
                </div>

                <div class="chat-thread" id="chatThread">
                    <!-- Messages bubbles go here -->
                </div>

                <div class="chat-input-bar">
                    <div class="recording-status" id="recordingBar">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div class="pulse"></div>
                            <span>Recording Voice...</span>
                        </div>
                        <button onclick="stopRecording()" style="background:none; border:none; color:var(--primary-teal); cursor:pointer; font-weight:700;">DONE</button>
                    </div>

                    <label class="attach-btn" title="Send Image/Audio">
                        <i class="fas fa-paperclip"></i>
                        <input type="file" id="fileInput" style="display:none" accept="image/*,audio/*">
                    </label>
                    <button class="mic-btn" id="micBtn" title="Record Voice">
                        <i class="fas fa-microphone"></i>
                    </button>
                    <input type="text" id="msgInput" placeholder="Type a message...">
                    <button class="btn-sell" style="margin: 0; padding: 10px 25px; border-radius: 25px;" id="sendBtn">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
let currentPartnerId = null;
let lastMessageId = 0;
let pollInterval = null;
let mediaRecorder;
let audioChunks = [];

$(document).ready(function() {
    loadConversations();
    
    // Check for partner_id in URL
    const urlParams = new URLSearchParams(window.location.search);
    const urlPartnerId = urlParams.get('partner_id');
    
    $(document).on('click', '.conv-item', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        openChat(id, name);
    });

    $('#sendBtn').click(sendTextMessage);
    $('#msgInput').keypress(e => { if(e.which == 13) sendTextMessage(); });
    $('#fileInput').change(function() {
        if (this.files && this.files[0]) sendFile(this.files[0]);
    });

    // Mic Button Logic
    $('#micBtn').click(startRecording);
    
    // Select partner if in URL
    if(urlPartnerId) {
        // Wait for list to load
        setTimeout(() => {
            const item = $(`.conv-item[data-id="${urlPartnerId}"]`);
            if(item.length) {
                item.click();
            } else {
                // Fetch partner name if not in list
                openChat(urlPartnerId, "Seller");
            }
        }, 500);
    }
});

function startRecording() {
    navigator.mediaDevices.getUserMedia({ audio: true })
        .then(stream => {
            mediaRecorder = new MediaRecorder(stream);
            mediaRecorder.start();
            audioChunks = [];
            
            $('#recordingBar').css('display', 'flex');
            
            mediaRecorder.addEventListener("dataavailable", event => {
                audioChunks.push(event.data);
            });

            mediaRecorder.addEventListener("stop", () => {
                const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                const audioFile = new File([audioBlob], "voice_message.webm", { type: 'audio/webm' });
                sendFile(audioFile);
                stream.getTracks().forEach(track => track.stop());
            });
        }).catch(err => {
            alert('Could not access microphone: ' + err);
        });
}

function stopRecording() {
    if (mediaRecorder) {
        mediaRecorder.stop();
        $('#recordingBar').hide();
    }
}

function loadConversations() {
    $.get('api/messages.php?action=list_conversations', function(data) {
        const conversations = JSON.parse(data);
        let html = '';
        conversations.forEach(c => {
            html += `
                <div class="conv-item ${currentPartnerId == c.partner_id ? 'active' : ''}" data-id="${c.partner_id}" data-name="${c.partner_name}">
                    <div class="conv-avatar"><i class="fas fa-user"></i></div>
                    <div class="conv-info">
                        <div class="conv-name">${c.partner_name}</div>
                        <div class="conv-snippet">${c.file_type !== 'text' ? '['+c.file_type.toUpperCase()+']' : c.last_msg}</div>
                    </div>
                </div>
            `;
        });
        $('#convList').html(html || '<div style="padding:20px; text-align:center; color:#999;">No conversations yet</div>');
    });
}

function openChat(partnerId, partnerName) {
    currentPartnerId = partnerId;
    $('#emptyState').hide();
    $('#chatContent').css('display', 'flex');
    $('#activePartnerName').text(partnerName);
    $('.conv-item').removeClass('active');
    $(`.conv-item[data-id="${partnerId}"]`).addClass('active');

    $('#chatThread').html('<div style="text-align:center; padding:20px;">Loading chat...</div>');

    $.get(`api/messages.php?action=get_thread&partner_id=${partnerId}`, function(data) {
        const messages = JSON.parse(data);
        renderMessages(messages, false);
        scrollToBottom();
        
        if (pollInterval) clearInterval(pollInterval);
        pollInterval = setInterval(pollNewMessages, 3000);
    });
}

function renderMessages(messages, append = true) {
    let html = '';
    messages.forEach(m => {
        const isMe = m.sender_id == <?= $_SESSION['user_id'] ?>;
        let contentHtml = '';
        
        if (m.file_type === 'image') {
            contentHtml = `<img src="${m.file_path}" class="chat-attachment">`;
        } else if (m.file_type === 'audio') {
            contentHtml = `<audio controls class="chat-attachment" style="max-width:200px;"><source src="${m.file_path}"></audio>`;
        }
        
        contentHtml += `<span>${m.content || ''}</span>`;

        html += `
            <div class="bubble ${isMe ? 'sent' : 'received'}" data-id="${m.id}">
                ${contentHtml}
                <span class="bubble-time">${m.created_at}</span>
            </div>
        `;
        lastMessageId = Math.max(lastMessageId, m.id);
    });
    
    if (append) {
        $('#chatThread').append(html);
        if (messages.length > 0) scrollToBottom();
    } else {
        $('#chatThread').html(html);
    }
}

function sendTextMessage() {
    const text = $('#msgInput').val().trim();
    if (!text || !currentPartnerId) return;
    $('#msgInput').val('');
    $.post('api/messages.php?action=send', { receiver_id: currentPartnerId, content: text, is_ajax: 1 }, function() { pollNewMessages(); });
}

function sendFile(file) {
    const formData = new FormData();
    formData.append('receiver_id', currentPartnerId);
    formData.append('attachment', file);
    formData.append('is_ajax', 1);

    $.ajax({
        url: 'api/messages.php?action=send',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function() {
            pollNewMessages();
            $('#fileInput').val('');
        }
    });
}

function pollNewMessages() {
    if (!currentPartnerId) return;
    $.get(`api/messages.php?action=poll&partner_id=${currentPartnerId}&last_id=${lastMessageId}`, function(data) {
        const newMsgs = JSON.parse(data);
        if (newMsgs.length > 0) renderMessages(newMsgs, true);
    });
}

function scrollToBottom() {
    const thread = document.getElementById('chatThread');
    thread.scrollTop = thread.scrollHeight;
}
</script>

<?php include 'includes/footer.php'; ?>
