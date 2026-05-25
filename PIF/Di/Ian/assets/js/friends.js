(function() {
    async function api(action, method='GET', data=null) {
        const url = `/api/friends.php${method === 'GET' ? '?action=' + encodeURIComponent(action) : ''}`;
        const init = { method };
        if (method === 'POST' && data) {
            const form = new URLSearchParams();
            form.set('action', action);
            for (const k in data) form.set(k, data[k]);
            init.headers = { 'Content-Type': 'application/x-www-form-urlencoded' };
            init.body = form.toString();
        }
        const res = await fetch(url, init);
        return res.json();
    }

    function createButton(text, cls, onClick) {
        const b = document.createElement('button');
        b.textContent = text;
        b.className = cls || '';
        b.addEventListener('click', onClick);
        return b;
    }

    async function loadLists() {
        const out = await api('list');
        if (out.error) {
            alert('Error loading friend lists: ' + out.error);
            return;
        }

        // Friends
        const friendsDiv = document.getElementById('friends-list');
        friendsDiv.innerHTML = '';
        const friends = out.friends || [];
        if (friends.length === 0) {
            friendsDiv.textContent = 'No friends yet.';
        } else {
            friends.forEach(f => {
                const row = document.createElement('div');
                row.className = 'friend-row';
                row.textContent = f;
                const unfriendBtn = createButton('Unfriend', 'danger-btn', async () => {
                    if (!confirm('Unfriend ' + f + '?')) return;
                    const r = await api('unfriend', 'POST', { username: f });
                    if (r.error) alert('Error: ' + r.error);
                    else loadLists();
                });
                row.appendChild(unfriendBtn);
                friendsDiv.appendChild(row);
            });
        }

        // Incoming
        const incomingDiv = document.getElementById('incoming-list');
        incomingDiv.innerHTML = '';
        const incoming = out.incoming_requests || [];
        if (incoming.length === 0) {
            incomingDiv.textContent = 'No incoming friend requests.';
        } else {
            incoming.forEach(r => {
                const row = document.createElement('div');
                row.className = 'friend-row';
                row.textContent = r.from_username || r.from || '';
                const acceptBtn = createButton('Accept', 'primary-btn', async () => {
                    const resp = await api('accept', 'POST', { from: r.from_username || r.from });
                    if (resp.error) alert('Error: ' + resp.error);
                    else loadLists();
                });
                const declineBtn = createButton('Decline', '', async () => {
                    const resp = await api('decline', 'POST', { from: r.from_username || r.from });
                    if (resp.error) alert('Error: ' + resp.error);
                    else loadLists();
                });
                row.appendChild(acceptBtn);
                row.appendChild(declineBtn);
                incomingDiv.appendChild(row);
            });
        }

        // Outgoing
        const outgoingDiv = document.getElementById('outgoing-list');
        outgoingDiv.innerHTML = '';
        const outgoing = out.outgoing_requests || [];
        if (outgoing.length === 0) {
            outgoingDiv.textContent = 'No outgoing friend requests.';
        } else {
            outgoing.forEach(r => {
                const row = document.createElement('div');
                row.className = 'friend-row';
                const to = r.to_username || r.to || '';
                row.textContent = to;
                const cancelBtn = createButton('Cancel', 'danger-btn', async () => {
                    if (!confirm('Cancel request to ' + to + '?')) return;
                    const resp = await api('cancel', 'POST', { to });
                    if (resp.error) alert('Error: ' + resp.error);
                    else loadLists();
                });
                row.appendChild(cancelBtn);
                outgoingDiv.appendChild(row);
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        loadLists();
        const form = document.getElementById('send-friend-form');
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const to = document.getElementById('to-username').value.trim();
            if (!to) return;
            const msg = document.getElementById('send-friend-message');
            try {
                const res = await api('request', 'POST', { to });
                if (res.error) {
                    msg.textContent = 'Error: ' + (res.error || JSON.stringify(res));
                } else {
                    msg.textContent = res.message || 'Friend request sent';
                    form.reset();
                    setTimeout(() => msg.textContent = '', 5000);
                    loadLists();
                }
            } catch (err) {
                msg.textContent = 'Unexpected error';
            }
        });
    });
})();
