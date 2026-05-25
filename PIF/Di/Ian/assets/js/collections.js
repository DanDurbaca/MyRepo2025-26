(function() {
    async function api(action, method='GET', data=null) {
        const url = `/api/collections.php${method === 'GET' ? '?action=' + encodeURIComponent(action) : ''}`;
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

    function formatMeta(meta) {
        if (!meta || !meta.station) return '';
        const parts = [
            `Station: ${meta.station}`,
            meta.start ? `Start: ${meta.start}` : null,
            meta.end ? `End: ${meta.end}` : null,
        ].filter(Boolean);
        return parts.join(' | ');
    }

    async function loadCollections() {
        const res = await api('list_owner');
        const div = document.getElementById('collections-list');
        div.innerHTML = '';
        if (res.error) { div.textContent = 'Error: ' + res.error; return; }
        if (!res.collections || res.collections.length === 0) {
            div.textContent = 'No collections yet.'; return;
        }
        res.collections.forEach(c => {
            const card = document.createElement('div');
            card.className = 'card';
            const title = document.createElement('h3');
            title.textContent = c.name;
            card.appendChild(title);

            if (c.metadata) {
                const meta = document.createElement('div');
                meta.className = 'muted';
                meta.textContent = formatMeta(c.metadata);
                card.appendChild(meta);
            }

            const sharesDiv = document.createElement('div');
            sharesDiv.className = 'muted';
            sharesDiv.textContent = 'Shared with: ' + ((c.shared_with && c.shared_with.length) ? c.shared_with.join(', ') : 'No one');
            card.appendChild(sharesDiv);

            const shareForm = document.createElement('form');
            shareForm.style.marginTop = '8px';
            shareForm.innerHTML = '<input name="to" placeholder="Username to share with" class="input-text"> <button class="primary-btn">Share</button>';
            shareForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const to = e.target.to.value.trim();
                if (!to) return;
                const r = await api('share', 'POST', { collectionId: c.pk_collection, to });
                if (r.error) alert('Error: ' + r.error); else loadCollections();
            });
            card.appendChild(shareForm);

            // Unshare items
            if (c.shared_with && c.shared_with.length) {
                const ul = document.createElement('ul');
                c.shared_with.forEach(s => {
                    const li = document.createElement('li');
                    li.textContent = s + ' ';
                    const unshareBtn = document.createElement('button');
                    unshareBtn.className = 'danger-btn';
                    unshareBtn.textContent = 'Unshare';
                    unshareBtn.addEventListener('click', async () => {
                        await api('unshare', 'POST', { collectionId: c.pk_collection, to: s });
                        loadCollections();
                    });
                    li.appendChild(unshareBtn);
                    ul.appendChild(li);
                });
                card.appendChild(ul);
            }

            const deleteBtn = document.createElement('button');
            deleteBtn.className = 'danger-btn';
            deleteBtn.textContent = 'Delete collection';
            deleteBtn.style.marginTop = '8px';
            deleteBtn.addEventListener('click', async () => {
                if (!confirm('Delete this collection?')) return;
                const r = await api('delete', 'POST', { collectionId: c.pk_collection });
                if (r.error) alert('Error: ' + r.error); else loadCollections();
            });
            card.appendChild(deleteBtn);

            div.appendChild(card);
        });
    }

    async function loadShared() {
        const res = await api('list_shared');
        const div = document.getElementById('shared-list');
        div.innerHTML = '';
        if (res.error) { div.textContent = 'Error: ' + res.error; return; }
        if (!res.collections || res.collections.length === 0) {
            div.textContent = 'No collections shared with you.'; return;
        }
        res.collections.forEach(c => {
            const card = document.createElement('div');
            card.className = 'card';
            const title = document.createElement('h3');
            title.textContent = `${c.name} (by ${c.fk_user_creates})`;
            card.appendChild(title);
            const meta = document.createElement('div');
            meta.className = 'muted';
            const decoded = (() => {
                try { return JSON.parse(c.description || '{}'); } catch (_) { return {}; }
            })();
            meta.textContent = formatMeta(decoded) || 'No metadata available';
            card.appendChild(meta);
            div.appendChild(card);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        loadCollections();
        loadShared();
        const createForm = document.getElementById('create-collection-form');
        createForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const name = document.getElementById('collection-name').value.trim();
            const station = document.getElementById('collection-station').value;
            const start = document.getElementById('collection-start').value;
            const end = document.getElementById('collection-end').value;
            if (!name || !station || !start || !end) return;
            const r = await api('create', 'POST', { name, station, start: start.replace('T', ' ')+':00', end: end.replace('T', ' ')+':00' });
            if (r.error) {
                document.getElementById('create-message').textContent = 'Error: ' + r.error;
            } else {
                document.getElementById('create-message').textContent = 'Created';
                createForm.reset();
                loadCollections();
                loadShared();
            }
        });
    });
})();
