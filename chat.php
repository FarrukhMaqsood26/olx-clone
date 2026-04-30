<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=login_required");
    exit;
}

include 'includes/header.php'; 
?>

<div class="h-[calc(100vh-200px)] min-h-[500px]">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm h-full flex overflow-hidden relative">
        
        <!-- Sidebar -->
        <div class="w-full md:w-80 border-r border-slate-200 flex flex-col bg-slate-50 absolute md:relative z-20 h-full transition-transform duration-300" id="chatSidebar">
            <div class="p-5 border-b border-slate-200 flex items-center justify-between bg-white">
                <h2 class="text-xl font-bold text-slate-900">Messages</h2>
                <button class="md:hidden text-slate-500 hover:text-slate-800" onclick="toggleSidebar(false)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto" id="convList">
                <!-- Loaded via JS -->
                <div class="p-8 text-center text-slate-500">
                    <i class="fas fa-circle-notch fa-spin text-2xl text-brand mb-2 block"></i>
                    Loading chats...
                </div>
            </div>
        </div>

        <!-- Main Workspace -->
        <div class="flex-1 flex flex-col relative h-full bg-slate-100/50" id="chatMain">
            <!-- Mobile Menu Toggle Overlay -->
            <button class="md:hidden absolute top-4 left-4 z-10 bg-white border border-slate-200 text-slate-700 w-10 h-10 rounded-full shadow-sm flex items-center justify-center hover:text-brand" onclick="toggleSidebar(true)" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Empty State -->
            <div class="flex-1 flex flex-col items-center justify-center text-slate-400 text-center p-8 h-full" id="emptyState">
                <i class="far fa-comments text-7xl mb-4 text-slate-300"></i>
                <h3 class="text-xl font-bold text-slate-700 mb-2">Your Conversations</h3>
                <p>Select a person from the left to start chatting.</p>
            </div>
            
            <!-- Chat Interface -->
            <div id="chatContent" class="hidden flex-col h-full w-full">
                <!-- Chat Header -->
                <div class="bg-white px-6 py-4 border-b border-slate-200 flex items-center gap-4 pl-16 md:pl-6">
                    <div class="w-10 h-10 rounded-full bg-brand/10 text-brand flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <div class="font-bold text-slate-900" id="activePartnerName">...</div>
                        <div class="text-xs font-semibold text-emerald-500 flex items-center gap-1" id="onlineLabel">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span> Online
                        </div>
                        <div class="hidden text-xs text-slate-500 mt-0.5 items-center gap-2" id="typingIndicator">
                           
                            <span>typing...</span>
                        </div>
                    </div>
                </div>

                <!-- Chat Thread -->
                <div class="flex-1 p-3 sm:p-6 overflow-y-auto flex flex-col gap-3 sm:gap-4 relative" id="chatThread" style="background-image: url('https://www.transparenttextures.com/patterns/cubes.png');">
                    <!-- Messages bubbles go here -->
                </div>

                <!-- Input Area -->
                <div class="bg-white p-2.5 sm:p-4 border-t border-slate-200">
                    <div class="flex items-center gap-1 sm:gap-2 relative max-w-4xl mx-auto">
                        
                        <!-- Recording Status Banner -->
                        <div class="absolute inset-0 bg-white z-10 hidden items-center justify-between px-3 sm:px-4 rounded-full border border-red-200 shadow-sm" id="recordingBar">
                            <div class="flex items-center gap-2 sm:gap-3 text-red-500 font-bold text-xs sm:text-sm truncate">
                                <span class="w-2 h-2 sm:w-3 sm:h-3 rounded-full bg-red-500 animate-pulse shrink-0"></span>
                                <span class="truncate">Recording...</span>
                            </div>
                            <button onclick="stopRecording()" class="text-brand font-bold uppercase text-xs sm:text-sm tracking-wider hover:underline shrink-0 pl-2">Done</button>
                        </div>

                        <!-- Add Attachment -->
                        <label for="fileInput" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full text-slate-500 hover:bg-slate-100 hover:text-brand flex items-center justify-center cursor-pointer transition flex-shrink-0">
                            <i class="fas fa-paperclip text-sm sm:text-lg"></i>
                            <input type="file" id="fileInput" class="hidden" accept="image/*,audio/*">
                        </label>
                        
                        <!-- Mic -->
                        <button class="w-8 h-8 sm:w-10 sm:h-10 rounded-full text-slate-500 hover:bg-slate-100 hover:text-brand flex items-center justify-center transition flex-shrink-0" id="micBtn" title="Record Voice">
                            <i class="fas fa-microphone text-sm sm:text-lg"></i>
                        </button>
                        
                        <!-- Text Input -->
                        <input type="text" id="msgInput" placeholder="Message..." class="flex-1 min-w-0 bg-slate-100 border border-slate-200 text-slate-800 text-xs sm:text-sm rounded-full px-3 py-2 sm:px-5 sm:py-3 outline-none focus:ring-2 focus:ring-brand/20 transition focus:bg-white focus:border-brand">
                        
                        <!-- Send Button -->
                        <button class="w-8 h-8 sm:w-10 sm:h-10 md:w-auto md:px-6 rounded-full bg-brand hover:bg-brand-light text-white flex items-center justify-center gap-2 transition flex-shrink-0" id="sendBtn">
                            <i class="fas fa-paper-plane text-[10px] sm:text-sm"></i>
                            <span class="hidden md:inline font-bold">Send</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .typing-dots {
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }
    .typing-dots span {
        width: 6px;
        height: 6px;
        border-radius: 9999px;
        background: #64748b;
        animation: typingBounce 1.2s infinite ease-in-out;
    }
    .typing-dots span:nth-child(2) { animation-delay: 0.15s; }
    .typing-dots span:nth-child(3) { animation-delay: 0.3s; }
    @keyframes typingBounce {
        0%, 80%, 100% { transform: translateY(0); opacity: 0.45; }
        40% { transform: translateY(-4px); opacity: 1; }
    }
    .typing-bubble {
        max-width: 120px;
    }
</style>

<script>
let currentPartnerId = null;
let lastMessageId = 0;
let pollInterval = null;
let typingPollInterval = null;
let mediaRecorder;
let audioChunks = [];
let typingStopTimer = null;
let lastTypingSentAt = 0;
let isTypingActive = false;
let isPartnerTyping = false;
let typingHeartbeatInterval = null;
let typingShowDelayTimer = null;
const TYPING_SHOW_DELAY_MS = 500;

$(document).ready(function() {
    loadConversations();
    
    // Check for partner_id in URL
    const urlParams = new URLSearchParams(window.location.search);
    const urlPartnerId = urlParams.get('partner_id');
    
    $(document).on('click', '.conv-item', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        openChat(id, name);
        if (window.innerWidth < 768) {
            toggleSidebar(false);
        }
    });

    $('#sendBtn').click(sendTextMessage);
    $('#msgInput').keypress(e => { if(e.which == 13) sendTextMessage(); });
    $('#msgInput').on('input', handleTypingInput);
    $('#msgInput').on('blur', () => notifyTyping(false));
    $('#fileInput').change(function() {
        if (this.files && this.files[0]) {
            console.log('File selected:', this.files[0].name);
            sendFile(this.files[0]);
        }
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

function toggleSidebar(show) {
    const sidebar = document.getElementById('chatSidebar');
    if (show) {
        sidebar.classList.remove('-translate-x-full');
    } else {
        sidebar.classList.add('-translate-x-full');
    }
}

// Initially hide sidebar on mobile if not already
if (window.innerWidth < 768) {
    document.getElementById('chatSidebar').classList.add('-translate-x-full');
}

function startRecording() {
    let mimeType = 'audio/webm';
    if (!MediaRecorder.isTypeSupported(mimeType)) {
        mimeType = 'audio/ogg';
        if (!MediaRecorder.isTypeSupported(mimeType)) {
            mimeType = ''; // Let the browser decide
        }
    }

    navigator.mediaDevices.getUserMedia({ audio: true })
        .then(stream => {
            const options = mimeType ? { mimeType } : {};
            mediaRecorder = new MediaRecorder(stream, options);
            mediaRecorder.start();
            audioChunks = [];
            
            $('#recordingBar').css('display', 'flex');
            
            mediaRecorder.addEventListener("dataavailable", event => {
                if (event.data.size > 0) {
                    audioChunks.push(event.data);
                }
            });

            mediaRecorder.addEventListener("stop", () => {
                const finalMime = mediaRecorder.mimeType || 'audio/webm';
                const audioBlob = new Blob(audioChunks, { type: finalMime });
                
                // Extract extension from mime type
                let ext = 'webm';
                if (finalMime.includes('ogg')) ext = 'ogg';
                if (finalMime.includes('mp4')) ext = 'mp4';
                
                const audioFile = new File([audioBlob], `recording.${ext}`, { type: finalMime });
                sendFile(audioFile);
                
                stream.getTracks().forEach(track => track.stop());
            });
        }).catch(err => {
            console.error('Error accessing microphone:', err);
            alert('Could not access microphone. Please ensure you have granted permission and are using a secure connection (HTTPS or localhost).');
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
            const activeClass = currentPartnerId == c.partner_id ? 'bg-brand/5 border-l-4 border-brand' : 'hover:bg-slate-100 hover:border-transparent border-l-4 border-transparent';
            html += `
                <div class="conv-item cursor-pointer p-4 flex items-center gap-4 transition border-b border-slate-100 ${activeClass}" data-id="${c.partner_id}" data-name="${c.partner_name}">
                    <div class="w-12 h-12 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center flex-shrink-0"><i class="fas fa-user text-xl"></i></div>
                    <div class="overflow-hidden flex-1">
                        <div class="font-bold text-slate-900 truncate">${c.partner_name}</div>
                        <div class="text-sm text-slate-500 truncate mt-0.5">${c.file_type !== 'text' ? '<i class="fas fa-paperclip"></i> '+c.file_type.toUpperCase() : c.last_msg}</div>
                    </div>
                </div>
            `;
        });
        $('#convList').html(html || '<div class="p-8 text-center text-slate-500">No conversations yet.<br><small>Message sellers to start chatting!</small></div>');
    });
}

function openChat(partnerId, partnerName) {
    if (currentPartnerId && Number(currentPartnerId) !== Number(partnerId)) {
        notifyTyping(false, currentPartnerId);
    }

    currentPartnerId = partnerId;
    lastMessageId = 0;
    isPartnerTyping = false;
    updateTypingUI(false);
    $('#emptyState').hide();
    $('#chatContent').css('display', 'flex').removeClass('hidden');
    $('#activePartnerName').text(partnerName);
    
    // Update sidebar UI state without full reload
    $('.conv-item').removeClass('bg-brand/5 border-brand').addClass('hover:bg-slate-100 border-transparent');
    $(`.conv-item[data-id="${partnerId}"]`).removeClass('hover:bg-slate-100 border-transparent').addClass('bg-brand/5 border-brand');

    $('#chatThread').html('<div class="p-8 text-center text-slate-500"><i class="fas fa-circle-notch fa-spin text-2xl text-brand mb-2 block"></i> Loading messages...</div>');

    $.get(`api/messages.php?action=get_thread&partner_id=${partnerId}`, function(data) {
        const messages = JSON.parse(data);
        renderMessages(messages, false);
        scrollToBottom();
        
        if (pollInterval) clearInterval(pollInterval);
        pollInterval = setInterval(pollNewMessages, 3000);
        pollTypingStatus();
        if (typingPollInterval) clearInterval(typingPollInterval);
        typingPollInterval = setInterval(pollTypingStatus, 1200);
    });
}

function renderMessages(messages, append = true) {
    let html = '';
    messages.forEach(m => {
        const isMe = m.sender_id == <?= $_SESSION['user_id'] ?>;
        const alignClass = isMe ? 'self-end bg-brand text-white rounded-tr-sm' : 'self-start bg-white text-slate-800 rounded-tl-sm border border-slate-200 shadow-sm';
        
        let contentHtml = '';
        if (m.file_type === 'image') {
            contentHtml = `<img src="${m.file_path}" class="max-w-[150px] sm:max-w-xs rounded-lg mb-2">`;
        } else if (m.file_type === 'audio') {
            contentHtml = `<audio controls class="max-w-[150px] sm:max-w-xs mb-2"><source src="${m.file_path}"></audio>`;
        }
        
        contentHtml += `<span class="break-words">${m.content || ''}</span>`;
        
        let deleteBtnHtml = isMe ? `<button onclick="deleteMessage(${m.id})" class="text-[9px] sm:text-[10px] text-white/60 hover:text-white transition ml-3 opacity-0 group-hover:opacity-100" title="Delete Message"><i class="fas fa-trash-alt"></i></button>` : '';

        html += `
            <div class="max-w-[85%] sm:max-w-[70%] rounded-xl sm:rounded-2xl px-4 py-2.5 sm:px-5 sm:py-3 text-[13px] sm:text-[15px] leading-snug flex flex-col group relative ${alignClass}" data-id="${m.id}">
                ${contentHtml}
                <div class="flex justify-end items-center mt-1.5">
                    <span class="text-[9px] sm:text-[10px] opacity-70 whitespace-nowrap">${m.created_at}</span>
                    ${deleteBtnHtml}
                </div>
            </div>
        `;
        lastMessageId = Math.max(lastMessageId, m.id);
    });
    
    if (append) {
        $('#chatThread').append(html);
        if (messages.length > 0) scrollToBottom();
    } else {
        if(messages.length === 0) {
            html = '<div class="text-center text-slate-400 mt-10 p-4 bg-white/50 rounded-lg max-w-sm mx-auto">This is the beginning of your chat history.</div>';
        }
        $('#chatThread').html(html);
    }
}

function sendTextMessage() {
    const text = $('#msgInput').val().trim();
    if (!text || !currentPartnerId) return;
    $('#msgInput').val('');
    notifyTyping(false);
    $.post('api/messages.php?action=send', { receiver_id: currentPartnerId, content: text, is_ajax: 1 }, function() { pollNewMessages(); loadConversations(); });
}

function sendFile(file) {
    if (!currentPartnerId) {
        alert("Please select a conversation first.");
        return;
    }

    const formData = new FormData();
    formData.append('receiver_id', currentPartnerId);
    formData.append('attachment', file);
    formData.append('is_ajax', 1);

    // Show a temporary sending indicator
    const tempId = Date.now();
    $('#chatThread').append(`
        <div class="self-end bg-slate-200 text-slate-500 rounded-2xl px-4 py-2 text-sm italic animate-pulse" id="temp-${tempId}">
            Sending attachment...
        </div>
    `);
    scrollToBottom();

    $.ajax({
        url: 'api/messages.php?action=send',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            $(`#temp-${tempId}`).remove();
            pollNewMessages();
            $('#fileInput').val('');
            loadConversations(); // Update sidebar last message
        },
        error: function(xhr) {
            $(`#temp-${tempId}`).remove();
            console.error('Upload Error:', xhr.responseText);
            alert('Failed to send file. Please check if the file size is too large or if the folder is writable.');
        }
    });
}

function pollNewMessages() {
    if (!currentPartnerId) return;
    $.get(`api/messages.php?action=poll&partner_id=${currentPartnerId}&last_id=${lastMessageId}`, function(data) {
        const newMsgs = JSON.parse(data);
        if (newMsgs.length > 0) {
            renderMessages(newMsgs, true);
            loadConversations();
            const hasPartnerMessage = newMsgs.some(m => Number(m.sender_id) === Number(currentPartnerId));
            if (hasPartnerMessage) {
                updateTypingUI(false);
            }
        }
    });
}

function handleTypingInput() {
    if (!currentPartnerId) return;
    const hasText = $('#msgInput').val().trim().length > 0;

    if (hasText) {
        const now = Date.now();
        if (!isTypingActive || (now - lastTypingSentAt) > 2000) {
            notifyTyping(true);
            lastTypingSentAt = now;
        }
        if (typingStopTimer) clearTimeout(typingStopTimer);
        typingStopTimer = setTimeout(() => notifyTyping(false), 2800);
    } else {
        notifyTyping(false);
    }
}

function notifyTyping(isTyping, partnerOverride = null) {
    const targetPartner = partnerOverride || currentPartnerId;
    if (!targetPartner) return;

    if (!isTypingActive && !isTyping) return;
    if (
        isTypingActive === isTyping &&
        Number(targetPartner) === Number(currentPartnerId) &&
        ((isTyping && typingHeartbeatInterval) || !isTyping)
    ) {
        return;
    }

    isTypingActive = !!isTyping;
    if (!isTyping && typingStopTimer) {
        clearTimeout(typingStopTimer);
        typingStopTimer = null;
    }

    if (isTyping) {
        if (!typingHeartbeatInterval) {
            typingHeartbeatInterval = setInterval(() => {
                if (!currentPartnerId || !isTypingActive) return;
                $.post('api/messages.php?action=set_typing', {
                    partner_id: currentPartnerId,
                    is_typing: 1
                });
            }, 2000);
        }
    } else if (typingHeartbeatInterval) {
        clearInterval(typingHeartbeatInterval);
        typingHeartbeatInterval = null;
    }

    $.post('api/messages.php?action=set_typing', {
        partner_id: targetPartner,
        is_typing: isTyping ? 1 : 0
    });
}

function pollTypingStatus() {
    if (!currentPartnerId) return;
    $.get(`api/messages.php?action=get_typing&partner_id=${currentPartnerId}`, function(data) {
        const res = JSON.parse(data);
        const shouldShow = !!(res && Number(res.is_typing) === 1);
        if (shouldShow) {
            if (typingShowDelayTimer) return;
            typingShowDelayTimer = setTimeout(() => {
                typingShowDelayTimer = null;
                updateTypingUI(true);
            }, TYPING_SHOW_DELAY_MS);
        } else {
            if (typingShowDelayTimer) {
                clearTimeout(typingShowDelayTimer);
                typingShowDelayTimer = null;
            }
            updateTypingUI(false);
        }
    });
}

function updateTypingUI(showTyping) {
    if (isPartnerTyping === showTyping) return;
    isPartnerTyping = showTyping;
    if (showTyping) {
        $('#onlineLabel').addClass('hidden');
        $('#typingIndicator').removeClass('hidden').addClass('flex');
        showTypingBubble();
    } else {
        $('#typingIndicator').addClass('hidden').removeClass('flex');
        $('#onlineLabel').removeClass('hidden');
        removeTypingBubble();
    }
}

function showTypingBubble() {
    if ($('#typingBubble').length) return;
    $('#chatThread').append(`
        <div id="typingBubble" class="self-start typing-bubble bg-white text-slate-700 rounded-2xl rounded-tl-sm border border-slate-200 shadow-sm px-4 py-2.5 sm:px-5 sm:py-3">
            <div class="typing-dots">
                <span></span><span></span><span></span>
            </div>
        </div>
    `);
    scrollToBottom();
}

function removeTypingBubble() {
    $('#typingBubble').remove();
}

function deleteMessage(id) {
    if(confirm("Are you sure you want to delete this message?")) {
        $.post('api/messages.php?action=delete', { message_id: id }, function(res) {
            let config = JSON.parse(res);
            if (config.status === 'success') {
                $(`.max-w-\\[85\\%\\][data-id="${id}"]`).fadeOut(300, function() { $(this).remove(); });
                loadConversations(); // Update the sidebar last message
            } else {
                alert("Could not delete message.");
            }
        });
    }
}

function scrollToBottom() {
    const thread = document.getElementById('chatThread');
    thread.scrollTop = thread.scrollHeight;
}

window.addEventListener('beforeunload', () => {
    if (currentPartnerId) {
        notifyTyping(false);
    }
    if (typingHeartbeatInterval) {
        clearInterval(typingHeartbeatInterval);
        typingHeartbeatInterval = null;
    }
    if (typingShowDelayTimer) {
        clearTimeout(typingShowDelayTimer);
        typingShowDelayTimer = null;
    }
});
</script>

<?php include 'includes/footer.php'; ?>
