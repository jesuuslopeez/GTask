const apiBase = '/api';

const elSession = document.getElementById('session-info');
const elLast = document.getElementById('last-response');
const elList = document.getElementById('task-list');
const formEdit = document.getElementById('form-edit');
const btnCancelEdit = document.getElementById('btn-cancel-edit');

function showResponse(label, data) {
    elLast.textContent = `${label}\n${JSON.stringify(data, null, 2)}`;
}

async function apiRequest(path, options = {}) {
    const response = await fetch(`${apiBase}${path}`, {
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json',
            ...(options.headers || {}),
        },
        ...options,
    });

    const text = await response.text();
    let payload = {};
    try {
        payload = text ? JSON.parse(text) : {};
    } catch {
        payload = { raw: text };
    }

    if (!response.ok) {
        throw { status: response.status, payload };
    }

    return payload;
}

async function refreshMe() {
    try {
        const data = await apiRequest('/me', { method: 'GET' });
        elSession.textContent = `Autenticado: ${data.user.name} (${data.user.email})`;
        showResponse('GET /api/me', data);
    } catch (err) {
        elSession.textContent = 'No autenticado.';
        showResponse('GET /api/me (error)', err);
    }
}

async function loadTasks() {
    try {
        const data = await apiRequest('/tasks', { method: 'GET' });
        renderTasks(data.tasks || []);
        showResponse('GET /api/tasks', data);
    } catch (err) {
        renderTasks([]);
        showResponse('GET /api/tasks (error)', err);
    }
}

function renderTasks(tasks) {
    elList.innerHTML = '';
    if (tasks.length === 0) {
        elList.innerHTML = '<li class="muted">No hay tareas.</li>';
        return;
    }

    tasks.forEach((task) => {
        const li = document.createElement('li');
        li.className = 'task-item';
        li.dataset.id = task.id;
        li.dataset.title = task.title ?? '';
        li.dataset.description = task.description ?? '';
        li.dataset.status = task.status ?? 'pending';
        li.dataset.due_date = task.due_date ?? '';
        li.dataset.priority = task.priority ?? 0;
        li.innerHTML = `
            <h3>${task.title}</h3>
            <p>${task.description ?? ''}</p>
            <small>Estado: ${task.status} | Prioridad: ${task.priority} | Fecha limite: ${task.due_date ?? '-'} </small>
            <div class="task-actions">
                <button type="button" data-action="edit" data-id="${task.id}">Editar</button>
                <button type="button" data-action="toggle" data-id="${task.id}">
                    ${task.status === 'completed' ? 'Marcar pendiente' : 'Marcar completada'}
                </button>
                <button type="button" class="delete" data-action="delete" data-id="${task.id}">Eliminar</button>
            </div>
        `;
        elList.appendChild(li);
    });
}

async function toggleTask(id, currentStatus) {
    const nextStatus = currentStatus === 'completed' ? 'pending' : 'completed';
    const data = await apiRequest(`/tasks/${id}`, {
        method: 'PATCH',
        body: JSON.stringify({ status: nextStatus }),
    });
    showResponse(`PATCH /api/tasks/${id}`, data);
    await loadTasks();
}

async function deleteTask(id) {
    const data = await apiRequest(`/tasks/${id}`, { method: 'DELETE' });
    showResponse(`DELETE /api/tasks/${id}`, data);
    await loadTasks();
}

function fillEditForm(taskItem) {
    formEdit.elements.id.value = taskItem.dataset.id || '';
    formEdit.elements.title.value = taskItem.dataset.title || '';
    formEdit.elements.description.value = taskItem.dataset.description || '';
    formEdit.elements.status.value = taskItem.dataset.status || 'pending';
    formEdit.elements.due_date.value = taskItem.dataset.due_date || '';
    formEdit.elements.priority.value = taskItem.dataset.priority || 0;
}

function clearEditForm() {
    formEdit.reset();
    formEdit.elements.id.value = '';
}

function attachEvents() {
    document.getElementById('form-register').addEventListener('submit', async (event) => {
        event.preventDefault();
        const form = event.target;
        const body = Object.fromEntries(new FormData(form));
        try {
            const data = await apiRequest('/register', { method: 'POST', body: JSON.stringify(body) });
            showResponse('POST /api/register', data);
            form.reset();
            await refreshMe();
        } catch (err) {
            showResponse('POST /api/register (error)', err);
        }
    });

    document.getElementById('form-login').addEventListener('submit', async (event) => {
        event.preventDefault();
        const form = event.target;
        const body = Object.fromEntries(new FormData(form));
        try {
            const data = await apiRequest('/login', { method: 'POST', body: JSON.stringify(body) });
            showResponse('POST /api/login', data);
            form.reset();
            await refreshMe();
        } catch (err) {
            showResponse('POST /api/login (error)', err);
        }
    });

    document.getElementById('btn-logout').addEventListener('click', async () => {
        try {
            const data = await apiRequest('/logout', { method: 'POST' });
            showResponse('POST /api/logout', data);
            await refreshMe();
        } catch (err) {
            showResponse('POST /api/logout (error)', err);
        }
    });

    document.getElementById('btn-me').addEventListener('click', refreshMe);

    document.getElementById('form-task').addEventListener('submit', async (event) => {
        event.preventDefault();
        const form = event.target;
        const body = Object.fromEntries(new FormData(form));
        body.title = (body.title || '').trim();
        body.description = (body.description || '').trim();
        body.status = (body.status || 'pending').toLowerCase();
        if (body.priority !== '') {
            body.priority = Number(body.priority);
        }
        if (body.due_date === '') {
            body.due_date = null;
        }
        try {
            const data = await apiRequest('/tasks', { method: 'POST', body: JSON.stringify(body) });
            showResponse('POST /api/tasks', data);
            form.reset();
            await loadTasks();
        } catch (err) {
            showResponse('POST /api/tasks (error)', err);
        }
    });

    document.getElementById('btn-load').addEventListener('click', loadTasks);

    formEdit.addEventListener('submit', async (event) => {
        event.preventDefault();
        const body = Object.fromEntries(new FormData(formEdit));
        const id = body.id;
        delete body.id;
        body.title = (body.title || '').trim();
        body.description = (body.description || '').trim();
        body.status = (body.status || 'pending').toLowerCase();
        if (!id) {
            showResponse('PATCH /api/tasks (error)', { error: 'Selecciona una tarea para editar.' });
            return;
        }
        if (body.priority !== '') {
            body.priority = Number(body.priority);
        }
        if (body.due_date === '') {
            body.due_date = null;
        }
        try {
            const data = await apiRequest(`/tasks/${id}`, { method: 'PATCH', body: JSON.stringify(body) });
            showResponse(`PATCH /api/tasks/${id}`, data);
            clearEditForm();
            await loadTasks();
        } catch (err) {
            showResponse(`PATCH /api/tasks/${id} (error)`, err);
        }
    });

    btnCancelEdit.addEventListener('click', clearEditForm);

    elList.addEventListener('click', async (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }
        const action = target.dataset.action;
        const id = target.dataset.id;
        if (!action || !id) {
            return;
        }
        if (action === 'edit') {
            const item = target.closest('.task-item');
            if (item instanceof HTMLElement) {
                fillEditForm(item);
            }
            return;
        }
        if (action === 'toggle') {
            const item = target.closest('.task-item');
            const statusText = item?.querySelector('small')?.textContent || '';
            const currentStatus = statusText.includes('completed') ? 'completed' : 'pending';
            await toggleTask(id, currentStatus);
        }
        if (action === 'delete') {
            await deleteTask(id);
        }
    });
}

attachEvents();
refreshMe();
loadTasks();
