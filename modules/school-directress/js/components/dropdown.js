
document.addEventListener('DOMContentLoaded', () => {
    const bellBtn = document.getElementById('bellBtn');
    const bellDropdown = document.getElementById('bellDropdown');
    const bellWrapper = document.getElementById('bellWrapper');

    const userBtn = document.getElementById('userBtn');
    const userDropdown = document.getElementById('userDropdown');
    const userWrapper = document.getElementById('userWrapper');

    const notifBadge = document.getElementById('notifBadge');
    const notifList = document.querySelector('.notif-list');
    const markAllRead = document.querySelector('.mark-all-read');

    // Update this if your application uses a different base URL.
    const notificationApi =
        'controllers/NotificationController.php';

    function closeAll() {
        bellDropdown?.classList.remove('open');
        userDropdown?.classList.remove('open');
    }

    function openDropdown(dropdown, triggerEl) {
        const sidebar = document.querySelector('.sidebar');

        if (!dropdown || !triggerEl || !sidebar) return;

        const rect = triggerEl.getBoundingClientRect();
        const sidebarRect = sidebar.getBoundingClientRect();

        dropdown.style.top = `${rect.top}px`;
        dropdown.style.left = `${sidebarRect.right + 8}px`;
        dropdown.classList.add('open');
    }

    bellBtn?.addEventListener('click', async (e) => {
        e.stopPropagation();

        const isOpen = bellDropdown?.classList.contains('open');
        closeAll();

        if (!isOpen) {
            openDropdown(bellDropdown, bellBtn);
            await loadNotifications();
        }
    });

    userBtn?.addEventListener('click', (e) => {
        e.stopPropagation();

        const isOpen = userDropdown?.classList.contains('open');
        closeAll();

        if (!isOpen) openDropdown(userDropdown, userBtn);
    });

    document.addEventListener('click', (e) => {
        if (
            !bellWrapper?.contains(e.target) &&
            !userWrapper?.contains(e.target)
        ) {
            closeAll();
        }
    });

    async function loadNotifications() {
        if (!notifList) return;

        notifList.innerHTML = '<li class="notif-item">Loading notifications...</li>';

        try {
            const response = await fetch(notificationApi + '?action=list', {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) {
                throw new Error(`Request failed: ${response.status}`);
            }

            const result = await response.json();

            if (!result.success) {
                throw new Error(result.message || 'Unable to load notifications.');
            }

            notifList.replaceChildren();

            if (!Array.isArray(result.notifications) ||
                result.notifications.length === 0) {
                notifList.innerHTML =
                    '<li class="notif-item">No notifications yet.</li>';
            } else {
                result.notifications.forEach((notification) => {
                    const item = document.createElement('li');
                    item.className = 'notif-item';

                    if (Number(notification.is_read) === 0) {
                        item.classList.add('unread');
                    }

                    const title = document.createElement('strong');
                    title.textContent = notification.title;

                    const message = document.createElement('p');
                    message.textContent = notification.message;

                    const date = document.createElement('small');
                    date.textContent = notification.created_at;

                    item.append(title, message, date);
                    notifList.appendChild(item);
                });
            }

            updateBadge(Number(result.unread_count) || 0);
        } catch (error) {
            console.error('Notification loading failed:', error);
            notifList.innerHTML =
                '<li class="notif-item">Could not load notifications.</li>';
        }
    }

    function updateBadge(count) {
        if (!notifBadge) return;

        notifBadge.textContent = count > 99 ? '99+' : String(count);
        notifBadge.classList.toggle('hidden', count <= 0);
    }

    markAllRead?.addEventListener('click', async (e) => {
        e.preventDefault();
        e.stopPropagation();

        try {
            const response = await fetch(notificationApi, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ action: 'mark_all_read' })
            });

            if (!response.ok) {
                throw new Error(`Request failed: ${response.status}`);
            }

            const result = await response.json();

            if (!result.success) {
                throw new Error(result.message || 'Unable to update notifications.');
            }

            document.querySelectorAll('.notif-item.unread').forEach((item) => {
                item.classList.remove('unread');
            });

            updateBadge(0);
        } catch (error) {
            console.error('Mark all as read failed:', error);
            alert('Could not mark notifications as read. Please try again.');
        }
    });
});
