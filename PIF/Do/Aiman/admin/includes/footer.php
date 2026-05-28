</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= esc(appUrl('/admin/assets/js/app.js')) ?>"></script>
<?php if (isLoggedIn()): ?>
<script>
(() => {
  const friendBadge = document.getElementById("friendRequestBadge");
  const chatDot = document.getElementById("chatUnreadDot");
  if (!friendBadge && !chatDot) return;

  const endpoint = <?= json_encode(appUrl('/user/notifications.php')) ?>;
  let polling = false;

  async function pollNotifications() {
    if (polling) return;
    polling = true;
    try {
      const res = await fetch(endpoint, {
        credentials: "same-origin",
        cache: "no-store"
      });
      if (!res.ok) return;
      const data = await res.json();
      if (!data || !data.ok) return;

      if (friendBadge) {
        const count = Number(data.pending_friend_requests || 0);
        friendBadge.textContent = String(count);
        friendBadge.classList.toggle("d-none", count <= 0);
      }

      if (chatDot) {
        const unreadChats = Number(data.unread_chats || 0);
        chatDot.classList.toggle("d-none", unreadChats <= 0);
        chatDot.setAttribute("aria-label", `${unreadChats} unread chats`);
      }
    } catch (e) {
      // keep polling silent
    } finally {
      polling = false;
    }
  }

  setInterval(pollNotifications, 4000);
})();
</script>
<?php endif; ?>
</body>
</html>
