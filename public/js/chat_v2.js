/* public/js/chat_v2.js — Modern Premium Chat Logic */
(function () {
    'use strict';

    /* ── State ────────────────────────────────────────────────── */
    let currentConvId = window.CURRENT_CONV_ID || null;
    let lastTimestamp = null;
    let pollTimer = null;
    let isOpen = false;
    let convList = [];
    let isStartingNewChat = false; // Flag to prevent auto-select during specific chat start
    let isListMode = false;        // Flag to prevent auto-select when showing conversation list in widget

    /* ── Elements ─────────────────────────────────────────────── */
    const isFullPage = document.querySelector('.chat-page-wrapper') !== null;
    
    // Widget Specific
    const bubble = document.getElementById('wgtBubble');
    const badge = document.getElementById('wgtBadge');
    const widgetWindow = document.getElementById('wgtWindow');
    const widgetCloseBtn = document.getElementById('wgtCloseBtn');
    
    // Common Elements
    const convListEl = document.getElementById(isFullPage ? 'sidebar-conv-list' : 'wgtConvList');
    const convEmpty = document.getElementById(isFullPage ? 'chat-pane-empty' : 'wgtConvEmpty');
    const activePane = document.getElementById(isFullPage ? 'chat-pane-active' : 'wgtChatPane');
    const messagesEl = document.getElementById(isFullPage ? 'chat-messages-body' : 'wgtMessages');
    const textarea = document.getElementById(isFullPage ? 'full-chat-input' : 'wgtTextarea');
    const sendBtn = document.getElementById(isFullPage ? 'full-chat-send' : 'wgtSendBtn');
    const fileInput = document.getElementById(isFullPage ? 'full-chat-file' : 'wgtFileInput');
    const searchInput = document.getElementById(isFullPage ? 'sidebar-search' : 'wgtSearchInput');
    const paneAvatar = document.getElementById(isFullPage ? 'active-conv-avatar' : 'wgtPaneAvatar');
    const paneName = document.getElementById(isFullPage ? 'active-conv-name' : 'wgtPaneName');
    
    // Mobile/Widget Back Button
    const mobileBackBtn = document.getElementById('mobile-back-btn');
    const wgtBackBtn = document.getElementById('wgtBackBtn');
    const sidebar = document.getElementById('full-chat-sidebar');
    const wgtInputBar = document.querySelector('.chat-input-bar');
    const wgtStatusText = document.querySelector('.wgt-header-info span');

    /* ── Initialization ──────────────────────────────────────── */
    if (isFullPage) {
        loadConversations();
        if (currentConvId) {
            // Find conv in list and select it after loading
            setTimeout(() => {
                const conv = convList.find(c => c.id == currentConvId);
                if (conv) selectConv(conv);
            }, 500);
        }
    } else {
        const minimizeBtn = document.getElementById('wgtMinimizeBtn');
        if (bubble) bubble.addEventListener('click', toggleWidget);
        if (widgetCloseBtn) widgetCloseBtn.addEventListener('click', closeWidget);
        if (minimizeBtn) minimizeBtn.addEventListener('click', closeWidget);
        if (wgtBackBtn) wgtBackBtn.addEventListener('click', showConvList);
    }

    if (mobileBackBtn) {
        mobileBackBtn.onclick = () => {
            if (sidebar) sidebar.classList.remove('hidden');
        };
    }

    /* ── Actions ─────────────────────────────────────────────── */
    window.chatAction = {
        selectById: function(id) {
            const conv = convList.find(c => c.id == id);
            if (conv) selectConv(conv);
        },
        open: function() {
            openWidget();
        },
        close: function() {
            closeWidget();
        },
        showList: function() {
            showConvList();
        },
        showConvOptions: function(e, id) {
            e.stopPropagation();
            showOptionsMenu(e.target, id);
        },
        deleteConversation: function(id) {
            if (confirm('Bạn có chắc chắn muốn xóa cuộc hội thoại này? Tất cả tin nhắn sẽ bị xóa vĩnh viễn.')) {
                deleteConv(id);
            }
        }
    };

    // Sidebar Back to Top
    const socialBackToTop = document.getElementById('socialBackToTop');
    if (socialBackToTop) {
        socialBackToTop.onclick = () => window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    /* ── Toggle Widget ────────────────────────────────────────── */
    function toggleWidget() {
        if (isOpen) closeWidget(); else openWidget();
    }

    function openWidget() {
        isOpen = true;
        if (widgetWindow) widgetWindow.classList.add('open');
        showConvList();
    }

    function closeWidget() {
        isOpen = false;
        if (widgetWindow) widgetWindow.classList.remove('open');
        stopPolling();
    }

    /* ── Conversations ────────────────────────────────────────── */
    function loadConversations() {
        fetch(BASE_URL + 'index.php?url=Chat/conversations')
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                convList = data.conversations || [];
                renderConvList(convList);
                
                const totalUnread = convList.reduce((s, c) => s + (c.unread || 0), 0);
                updateBubbleBadge(totalUnread);

                // Auto-select first conversation for Widget (Customer)
                if (!isFullPage && convList.length > 0 && !currentConvId && !isStartingNewChat && !isListMode) {
                    selectConv(convList[0]);
                }
                isStartingNewChat = false; // Reset after first load
                // isListMode stays true until a conversation is selected manually
            })
            .catch(err => console.error('Load conv error:', err));
    }

    function renderConvList(list) {
        if (!convListEl) return;
        
        if (list.length === 0) {
            convListEl.innerHTML = '<div class="text-center p-5 text-muted">Chưa có hội thoại nào</div>';
            return;
        }

        const html = list.map(c => {
            const preview = c.last_message ? escHtml(c.last_message.substring(0, 35)) + (c.last_message.length > 35 ? '...' : '') : 'Bắt đầu trò chuyện...';
            const unread = c.unread > 0 ? `<span class="chat-unread-badge">${c.unread}</span>` : '';
            const active = c.id == currentConvId ? 'active' : '';
            const avatar = c.display_avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(c.display_name)}&background=random`;
            const time = c.last_message_at ? c.last_message_at.substring(11, 16) : '';

            return `
                <div class="chat-conv-item ${active}" data-id="${c.id}" onclick="window.chatAction.selectById(${c.id})">
                    <div class="chat-conv-avatar">
                        <img src="${escAttr(avatar)}" alt="">
                        <span class="chat-status-dot"></span>
                    </div>
                    <div class="chat-conv-info">
                        <div class="chat-conv-name">${escHtml(c.display_name)}</div>
                        <div class="chat-conv-preview">${preview}</div>
                    </div>
                    <div class="chat-conv-meta">
                        <div class="chat-conv-time">${time}</div>
                        <div class="chat-conv-actions">
                            <i class="fas fa-ellipsis-h btn-conv-more" data-id="${c.id}" onclick="event.stopPropagation(); window.chatAction.showConvOptions(event, ${c.id})"></i>
                        </div>
                        ${unread}
                    </div>
                </div>`;
        }).join('');

        convListEl.innerHTML = html;
    }

    /* ── Selection ────────────────────────────────────────────── */
    function selectConv(conv) {
        if (!conv) return;
        currentConvId = conv.id;
        lastTimestamp = null;
        isListMode = false; // Exit list mode when a conversation is selected

        // UI Updates
        if (convEmpty) convEmpty.style.display = 'none';
        
        // Widget logic: Hide list, show pane
        if (!isFullPage) {
            if (convListEl) convListEl.style.display = 'none';
            if (activePane) activePane.style.display = 'flex';
            if (wgtInputBar) wgtInputBar.style.display = 'block';
            if (wgtBackBtn) wgtBackBtn.style.display = 'block';
            if (paneAvatar) paneAvatar.style.display = 'block';
            if (wgtStatusText) wgtStatusText.style.display = 'block';
        }

        if (activePane) activePane.style.display = 'flex';
        
        // Mobile Sidebar handling
        if (isFullPage && window.innerWidth <= 768) {
            if (sidebar) sidebar.classList.add('hidden');
        }

        // Highlight selected
        document.querySelectorAll('.chat-conv-item').forEach(el => {
            el.classList.toggle('active', el.dataset.id == conv.id);
        });

        // Header
        if (paneAvatar) paneAvatar.src = conv.display_avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(conv.display_name)}&background=random`;
        if (paneName) paneName.textContent = conv.display_name;

        // Load Messages
        loadMessages(true);
        startPolling();

        // Update seller ID for product selector
        currentSellerId = conv.seller_id || 0;
    }

    function showConvList() {
        if (isFullPage) return;
        
        currentConvId = null;
        isListMode = true; // Enter list mode
        stopPolling();

        if (convListEl) convListEl.style.display = 'block';
        if (activePane) activePane.style.display = 'none';
        if (wgtInputBar) wgtInputBar.style.display = 'none';
        if (wgtBackBtn) wgtBackBtn.style.display = 'none';
        if (paneAvatar) paneAvatar.style.display = 'none';
        if (wgtStatusText) wgtStatusText.style.display = 'none';
        if (paneName) paneName.textContent = 'Đoạn chat';
        
        loadConversations();
    }

    /* ── Messages ─────────────────────────────────────────────── */
    function loadMessages(fullReload = false) {
        if (!currentConvId) return;

        let url = `${BASE_URL}index.php?url=Chat/history&conv_id=${currentConvId}`;
        if (!fullReload && lastTimestamp) {
            url += `&since=${encodeURIComponent(lastTimestamp)}`;
        }

        fetch(url)
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;

                if (data.messages && data.messages.length > 0) {
                    lastTimestamp = data.messages[data.messages.length - 1].created_at;
                }

                if (fullReload) {
                    messagesEl.innerHTML = buildMessagesHTML(data.messages, true);
                    scrollToBottom();
                } else if (data.messages && data.messages.length > 0) {
                    const html = buildMessagesHTML(data.messages, false);
                    messagesEl.insertAdjacentHTML('beforeend', html);
                    scrollToBottom();
                }
            })
            .catch(err => console.error('Load msg error:', err));
    }

    let lastShownTs = null;

    function buildMessagesHTML(messages, isFullReload = false) {
        let html = '';
        if (isFullReload) lastShownTs = null;
        
        messages.forEach(msg => {
            const ts = new Date(msg.created_at.replace(' ', 'T'));
            const dateKey = ts.toLocaleDateString('vi-VN');
            
            const diffInMinutes = lastShownTs ? (ts - lastShownTs) / (1000 * 60) : 99999;
            const isNewDay = lastShownTs ? (dateKey !== lastShownTs.toLocaleDateString('vi-VN')) : true;

            if (isNewDay || diffInMinutes > 15) {
                const today = new Date().toLocaleDateString('vi-VN');
                const timeStr = `${String(ts.getHours()).padStart(2, '0')}:${String(ts.getMinutes()).padStart(2, '0')}`;
                
                let label = (dateKey === today) ? 'Hôm nay' : dateKey;
                if (!isNewDay && diffInMinutes > 15) {
                    label += ' ' + timeStr;
                }

                html += `<div class="wgt-date-sep"><span>${label}</span></div>`;
            }
            
            lastShownTs = ts;

            const isSent = msg.sender_id == USER_ID;
            const time = `${String(ts.getHours()).padStart(2, '0')}:${String(ts.getMinutes()).padStart(2, '0')}`;
            const avatar = msg.sender_avatar 
                ? `${BASE_URL}public/uploads/avatars/${msg.sender_avatar}` 
                : `https://ui-avatars.com/api/?name=${encodeURIComponent(msg.sender_name)}&background=random`;

            let content = '';
            if (msg.message_type === 'image') {
                content = `<img src="${BASE_URL}${msg.attachment_url}" onclick="window.open(this.src)" alt="Image">`;
            } else if (msg.message_type === 'product') {
                try {
                    // Handle possible HTML-entity encoding from htmlspecialchars
                    let raw = msg.content;
                    if (typeof raw === 'string') {
                        raw = raw.replace(/&quot;/g, '"')
                                 .replace(/&#039;/g, "'")
                                 .replace(/&amp;/g, '&')
                                 .replace(/&lt;/g, '<')
                                 .replace(/&gt;/g, '>');
                    }
                    const p = JSON.parse(raw);
                    const imgSrc = p.image ? fixImageUrl(p.image) : '';
                    content = `
                        <a href="${BASE_URL}index.php?url=Product/show/${p.id}" class="msg-product-card" target="_blank">
                            <div class="msg-product-body">
                                <img src="${imgSrc}" alt="" onerror="this.style.display='none'">
                                <div class="msg-product-info">
                                    <div class="msg-product-name">${escHtml(p.name)}</div>
                                    <div class="msg-product-price">${p.price || ''}</div>
                                </div>
                            </div>
                        </a>`;
                } catch(e) {
                    console.error('Product parse error:', e, msg.content);
                    content = escHtml(msg.content);
                }
            } else {
                content = escHtml(msg.content || '').replace(/\n/g, '<br>');
            }

            html += `
                <div class="wgt-msg-row ${isSent ? 'sent' : 'received'}" data-id="${msg.id}">
                    ${!isSent ? `<img src="${avatar}" class="avatar" alt="">` : ''}
                    <div class="wgt-msg-body">
                        <div class="wgt-bubble type-${msg.message_type}">${content}</div>
                        <div class="wgt-msg-time">${time}</div>
                    </div>
                </div>`;
        });
        return html;
    }

    function scrollToBottom() {
        if (messagesEl) {
            messagesEl.scrollTo({ top: messagesEl.scrollHeight, behavior: 'smooth' });
        }
    }

    /* ── Product Attachment Logic ──────────────────────────────── */
    let attachedProduct = null;
    let currentSellerId = null;

    const stickyBar = document.getElementById('wgtProductSticky');
    const stickyImg = document.getElementById('stickyProdImg');
    const stickyName = document.getElementById('stickyProdName');
    const stickyPrice = document.getElementById('stickyProdPrice');
    const closeSticky = document.getElementById('wgtCloseSticky');
    const changeProd = document.getElementById('wgtChangeProd');
    const productBtn = document.getElementById('wgtProductBtn');
    const productSelector = document.getElementById('wgtProductSelector');
    const selectorList = document.getElementById('wgtSelectorList');
    const selectorSearch = document.getElementById('wgtSelectorSearchInput');

    function setAttachedProduct(p) {
        attachedProduct = p;
        if (p && stickyBar) {
            stickyImg.src = fixImageUrl(p.image);
            stickyName.textContent = p.name;
            stickyPrice.textContent = p.price;
            stickyBar.style.display = 'block';
        } else if (stickyBar) {
            stickyBar.style.display = 'none';
        }
    }

    if (closeSticky) closeSticky.onclick = () => setAttachedProduct(null);
    if (changeProd) changeProd.onclick = () => openProductSelector();
    if (productBtn) productBtn.onclick = () => openProductSelector();

    function openProductSelector() {
        if (productSelector) {
            productSelector.style.display = 'flex';
            loadSelectorProducts();
        }
    }

    function loadSelectorProducts(q = '') {
        if (!selectorList) return;
        selectorList.innerHTML = '<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i></div>';
        
        let url = `${BASE_URL}index.php?url=Chat/productList&q=${encodeURIComponent(q)}`;
        if (currentSellerId) url += `&seller_id=${currentSellerId}`;

        fetch(url)
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                if (data.products.length === 0) {
                    selectorList.innerHTML = '<div class="text-center p-5 text-muted">Không tìm thấy sản phẩm nào</div>';
                    return;
                }
                selectorList.innerHTML = data.products.map(p => {
                    const pJson = JSON.stringify(p).replace(/'/g, "&#39;").replace(/"/g, '&quot;');
                    return `
                    <div class="selector-item">
                        <div class="checkbox-area">
                            <i class="far fa-square"></i>
                        </div>
                        <img src="${fixImageUrl(p.image)}" alt="">
                        <div class="info">
                            <div class="name">${escHtml(p.name)}</div>
                            <div class="price-row">
                                ${p.old_price ? `<span class="old-price">${p.old_price}</span>` : ''}
                                <span class="price">${p.price}</span>
                            </div>
                        </div>
                        <button class="btn-select" onclick="window.chatAction.selectProductForAttach(${pJson})">Gửi</button>
                    </div>
                `}).join('');
            });
    }

    if (selectorSearch) {
        selectorSearch.oninput = (e) => loadSelectorProducts(e.target.value);
    }

    window.chatAction.selectProductForAttach = function(p) {
        if (productSelector) productSelector.style.display = 'none';
        sendProductNow(p);
    };

    function sendProductNow(p) {
        if (!currentConvId || !p) return;
        
        const fd = new FormData();
        fd.append('conversation_id', currentConvId);
        fd.append('type', 'product');
        fd.append('content', JSON.stringify(p));

        fetch(`${BASE_URL}index.php?url=Chat/send`, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    loadMessages();
                    setAttachedProduct(null); // Clear from sticky bar
                }
            })
            .catch(err => console.error('Send product error:', err));
    }

    /* ── Actions ─────────────────────────────────────────────── */
    function sendText() {
        const text = textarea.value.trim();
        // Allow sending if there's text OR an attached product
        if ((!text && !attachedProduct) || !currentConvId) return;

        const fd = new FormData();
        fd.append('conversation_id', currentConvId);
        
        if (attachedProduct) {
            // Send product message
            fd.append('type', 'product');
            fd.append('content', JSON.stringify(attachedProduct));
            // Clear attachment after sending
            setAttachedProduct(null);
        } else {
            fd.append('type', 'text');
            fd.append('content', text);
        }

        fetch(`${BASE_URL}index.php?url=Chat/send`, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    textarea.value = '';
                    loadMessages();
                }
            });
    }

    if (sendBtn) sendBtn.onclick = sendText;
    if (textarea) {
        textarea.onkeydown = e => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendText(); } };
    }

    if (fileInput) {
        fileInput.onchange = function() {
            const file = this.files[0];
            if (!file || !currentConvId) return;
            const fd = new FormData();
            fd.append('conversation_id', currentConvId);
            fd.append('type', 'image');
            fd.append('attachment', file);
            fetch(`${BASE_URL}index.php?url=Chat/send`, { method: 'POST', body: fd })
                .then(r => r.json())
                .then(d => { if (d.success) loadMessages(); });
            this.value = '';
        };
    }

    /* ── Polling ──────────────────────────────────────────────── */
    function startPolling() {
        stopPolling();
        pollTimer = setInterval(() => loadMessages(), 3000);
    }

    function stopPolling() {
        if (pollTimer) clearInterval(pollTimer);
    }

    /* ── Options Menu ────────────────────────────────────────── */
    function showOptionsMenu(btn, id) {
        // Remove existing
        const existing = document.getElementById('chatOptionsMenu');
        if (existing) existing.remove();

        const menu = document.createElement('div');
        menu.id = 'chatOptionsMenu';
        menu.className = 'chat-options-menu';
        menu.innerHTML = `
            <div class="menu-item delete" onclick="window.chatAction.deleteConversation(${id})">
                <i class="fas fa-trash-alt mr-2"></i> Xóa đoạn chat
            </div>
        `;

        document.body.appendChild(menu);

        // Position
        const rect = btn.getBoundingClientRect();
        menu.style.top = (rect.bottom + window.scrollY + 5) + 'px';
        menu.style.left = (rect.right + window.scrollX - 140) + 'px';

        // Close on click outside
        setTimeout(() => {
            const close = () => {
                menu.remove();
                document.removeEventListener('click', close);
            };
            document.addEventListener('click', close);
        }, 0);
    }

    function deleteConv(id) {
        const fd = new FormData();
        fd.append('conv_id', id);
        fd.append('action', 'delete');

        fetch(`${BASE_URL}index.php?url=Chat/manage`, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (currentConvId == id) {
                        currentConvId = null;
                        if (activePane) activePane.style.display = 'none';
                        if (convEmpty) convEmpty.style.display = 'flex';
                    }
                    loadConversations();
                } else {
                    alert('Lỗi khi xóa hội thoại');
                }
            })
            .catch(err => console.error('Delete conv error:', err));
    }

    function escHtml(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
    function escAttr(s) { return escHtml(s); }
    function fixImageUrl(url) {
        if (!url) return '';
        if (url.startsWith('http')) return url;
        // Check if it already has public/
        if (url.startsWith('public/')) return BASE_URL + url;
        return BASE_URL + (url.startsWith('/') ? url.substring(1) : url);
    }
    function updateBubbleBadge(n) {
        if (badge) {
            badge.textContent = n;
            badge.classList.toggle('show', n > 0);
        }
    }
//cậpn nhật số lượng tin nhắn chưa đọc
    function loadUnreadMessages() {
        if (!badge) return;
        fetch(BASE_URL + 'index.php?url=Chat/conversations')
            .then(response => response.json())
            .then(data => {
                if (!data.success) return;
                const totalUnread = (data.conversations || []).reduce((sum, conv) => sum + (conv.unread || 0), 0);
                updateBubbleBadge(totalUnread);
            })
            .catch(() => {
                // Ignore polling errors silently
            });
    }

    if (badge) {
        loadUnreadMessages();
        setInterval(loadUnreadMessages, 3000);
    }

    /* ── Global: open chat from product/order page ───────────── */
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.open-chat-with-product');
        if (!btn) return;

        const sellerId = btn.dataset.sellerId || 0;
        const productData = btn.dataset.product ? JSON.parse(btn.dataset.product) : null;
        
        isStartingNewChat = true; 

        // 1. Open widget
        if (!isOpen) openWidget();
        
        // Clear current state to avoid sending to wrong person during load
        currentConvId = null; 
        if (messagesEl) messagesEl.innerHTML = '<div class="text-center p-4"><i class="fas fa-spinner fa-spin"></i> Đang kết nối với người bán...</div>';

        // 2. Set attached product
        if (productData) {
            setAttachedProduct(productData);
        }

        // 3. Set seller ID and load or create conversation with this seller
        currentSellerId = sellerId;
        fetch(`${BASE_URL}index.php?url=Chat/history&seller_id=${sellerId}`)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.conversation_id) {
                    const existing = convList.find(c => c.id == data.conversation_id);
                    if (existing) {
                        selectConv(existing);
                    } else if (data.conversation) {
                        // Build display_name/avatar if missing
                        if (!data.conversation.display_name) {
                            data.conversation.display_name = data.conversation.shop_name || 'GÌ CŨNG MÓC SHOP';
                            if (data.conversation.shop_logo) {
                                data.conversation.display_avatar_url = `${BASE_URL}${data.conversation.shop_logo}`;
                            } else if (data.conversation.seller_avatar) {
                                data.conversation.display_avatar_url = `${BASE_URL}public/uploads/avatars/${data.conversation.seller_avatar}`;
                            } else {
                                data.conversation.display_avatar_url = BASE_URL + 'public/images/logolen.jpg';
                            }
                        }
                        // Add to list then select
                        convList.unshift(data.conversation);
                        renderConvList(convList);
                        selectConv(data.conversation);
                    } else {
                        // fallback: just set convId and load
                        currentConvId = data.conversation_id;
                        loadMessages(true);
                        startPolling();
                    }
                } else {
                    console.error('Chat/history failed:', data);
                    if (messagesEl) messagesEl.innerHTML = '<div class="text-center p-4 text-muted"><i class="fas fa-exclamation-circle mr-2"></i>Không thể kết nối. Vui lòng thử lại.</div>';
                }
            })
            .catch(err => {
                console.error('Start chat error:', err);
                if (messagesEl) messagesEl.innerHTML = '<div class="text-center p-4 text-muted"><i class="fas fa-exclamation-circle mr-2"></i>Lỗi kết nối.</div>';
            });
    });

})();
