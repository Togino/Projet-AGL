let csrfToken = null;

const output = document.getElementById('output');
const logsOutput = document.getElementById('logsOutput');
const rolePermissionsOutput = document.getElementById('rolePermissionsOutput');
const sessionState = document.getElementById('sessionState');
const roleSelect = document.getElementById('roleSelect');
const rolePicker = document.getElementById('rolePicker');
const usersTableBody = document.getElementById('usersTableBody');
const usersCount = document.getElementById('usersCount');
const rolesCount = document.getElementById('rolesCount');
const logsCount = document.getElementById('logsCount');

function show(target, data) {
    target.textContent = typeof data === 'string' ? data : JSON.stringify(data, null, 2);
}

function setSessionState(text) {
    sessionState.textContent = text;
}

async function api(path, options = {}) {
    const headers = options.headers || {};
    if (csrfToken) {
        headers['X-CSRF-Token'] = csrfToken;
    }

    const response = await fetch(path, {
        credentials: 'same-origin',
        ...options,
        headers
    });

    const data = await response.json().catch(() => ({}));
    if (!response.ok) {
        throw data;
    }

    return data;
}

async function loadRoles() {
    const data = await api('/admin/roles');
    roleSelect.innerHTML = '';
    rolePicker.innerHTML = '';

    data.data.forEach((role) => {
        const optionA = document.createElement('option');
        optionA.value = role.id;
        optionA.textContent = `${role.name} (#${role.id})`;
        roleSelect.appendChild(optionA);

        const optionB = document.createElement('option');
        optionB.value = role.id;
        optionB.textContent = `${role.name} (#${role.id})`;
        rolePicker.appendChild(optionB);
    });

    rolesCount.textContent = String(data.data.length);
    return data.data;
}

async function loadUsers() {
    const data = await api('/users');
    usersTableBody.innerHTML = '';

    if (!data.data.length) {
        usersTableBody.innerHTML = '<tr><td colspan="5" class="empty">Aucun utilisateur trouve.</td></tr>';
    } else {
        data.data.forEach((user) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${user.MAT}</td>
                <td>${user.prenom} ${user.nom}</td>
                <td>${user.email}</td>
                <td>${user.role_name}</td>
                <td>${Number(user.statut) ? 'actif' : 'inactif'}</td>
            `;
            usersTableBody.appendChild(row);
        });
    }

    usersCount.textContent = String(data.data.length);
    show(output, data);
}

async function loadRolePermissions() {
    const roleId = rolePicker.value;
    const data = await api(`/admin/roles/${roleId}/permissions`);
    show(rolePermissionsOutput, data);
}

async function loadLogs() {
    const data = await api('/admin/security-logs?limit=20');
    logsCount.textContent = String(data.data.length);
    show(logsOutput, data);
}

document.getElementById('loginForm').addEventListener('submit', async (event) => {
    event.preventDefault();
    try {
        const payload = Object.fromEntries(new FormData(event.target).entries());
        const data = await api('/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        csrfToken = data.data.csrf_token;
        setSessionState(`Connecte en tant que ${data.data.email}`);
        show(output, data);
        await loadRoles();
        await loadUsers();
    } catch (error) {
        show(output, error);
    }
});

document.getElementById('meButton').addEventListener('click', async () => {
    try {
        const data = await api('/me');
        csrfToken = data.data.csrf_token;
        setSessionState(`Session active pour ${data.data.email}`);
        show(output, data);
        await loadRoles();
        await loadUsers();
    } catch (error) {
        show(output, error);
    }
});

document.getElementById('logoutButton').addEventListener('click', async () => {
    try {
        const data = await api('/logout', { method: 'POST' });
        csrfToken = null;
        setSessionState('Session non connectee');
        show(output, data);
    } catch (error) {
        show(output, error);
    }
});

document.getElementById('createUserForm').addEventListener('submit', async (event) => {
    event.preventDefault();
    try {
        const payload = Object.fromEntries(new FormData(event.target).entries());
        const data = await api('/users', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        show(output, data);
        await loadUsers();
    } catch (error) {
        show(output, error);
    }
});

document.getElementById('permissionsForm').addEventListener('submit', async (event) => {
    event.preventDefault();
    try {
        const roleId = rolePicker.value;
        const permissionIds = document.getElementById('permissionIds').value
            .split(',')
            .map((value) => value.trim())
            .filter(Boolean)
            .map((value) => Number(value));

        const data = await api(`/admin/roles/${roleId}/permissions`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ permission_ids: permissionIds })
        });
        show(output, data);
        show(rolePermissionsOutput, data);
    } catch (error) {
        show(output, error);
    }
});

document.getElementById('refreshUsersButton').addEventListener('click', async () => {
    try {
        await loadUsers();
    } catch (error) {
        show(output, error);
    }
});

document.getElementById('loadUsersButton').addEventListener('click', async () => {
    try {
        await loadUsers();
    } catch (error) {
        show(output, error);
    }
});

document.getElementById('viewRolePermissionsButton').addEventListener('click', async () => {
    try {
        await loadRolePermissions();
    } catch (error) {
        show(rolePermissionsOutput, error);
    }
});

document.getElementById('logsButton').addEventListener('click', async () => {
    try {
        await loadLogs();
    } catch (error) {
        show(logsOutput, error);
    }
});
