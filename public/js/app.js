// Global Utilities
window.formatCurrency = (amount) => new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN', minimumFractionDigits: 0 }).format(amount || 0);
window.formatDate = (dateStr) => new Date(dateStr + 'T00:00:00').toLocaleDateString('es-ES', { day: '2-digit', month: 'short' });

window.showToast = (message, type = 'success') => {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    const icon = type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle');
    toast.innerHTML = `<i class="fas ${icon}"></i> <span>${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.classList.add('fade-out');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
};

window.toggleNotifications = () => {
    const panel = document.getElementById('notif-panel');
    if (panel) {
        if (panel.style.display === 'none' || panel.style.display === '') {
            panel.style.display = 'block';
            // Pequeño delay para la animación si existiera
            setTimeout(() => panel.style.opacity = '1', 10);
        } else {
            panel.style.display = 'none';
        }
    }
};

window.handleInvitation = async (id, action) => {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    try {
        const res = await fetch(`/invitations/${id}/${action}`, {
            method: 'POST',
            headers: { 
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        });
        if (res.ok) {
            window.showToast(action === 'accept' ? '¡Vinculados!' : 'Invitación rechazada');
            setTimeout(() => location.reload(), 1000);
        } else {
            window.showToast('Error al procesar la invitación', 'error');
        }
    } catch (err) {
        window.showToast('Error de conexión', 'error');
    }
};

// PWA and Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').then(reg => {
            console.log('SW Registered');
        }).catch(err => console.log('SW Error', err));
    });
}

let deferredPrompt;
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    // Mostrar botón de instalación si existe
    const installBtn = document.getElementById('install-pwa');
    if (installBtn) installBtn.style.display = 'flex';
});

window.installApp = async () => {
    if (deferredPrompt) {
        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        if (outcome === 'accepted') {
            const installBtn = document.getElementById('install-pwa');
            if (installBtn) installBtn.style.display = 'none';
        }
        deferredPrompt = null;
    }
};

window.requestNotifications = async () => {
    const permission = await Notification.requestPermission();
    if (permission === 'granted') {
        window.showToast('¡Notificaciones activadas!');
    } else {
        window.showToast('Notificaciones bloqueadas', 'error');
    }
};

let payerChart = null;
let categoryChart = null;

// Global API Functions
window.fetchSummary = async () => {
    try {
        const res = await fetch('/api/expenses/summary');
        const data = await res.json();
        window.currentSalary = data.monthly_salary;
        
        const totalDay = document.getElementById('total-day');
        const totalMonth = document.getElementById('total-month');
        const tShared = document.getElementById('total-shared');
        const tPersonal = document.getElementById('total-personal');
        const tMeDeben = document.getElementById('total-me-deben');
        const tYoDebo = document.getElementById('total-yo-debo');

        const hMeDeben = document.getElementById('history-me-deben');
        const hYoDebo = document.getElementById('history-yo-debo');

        if (totalDay) totalDay.textContent = window.formatCurrency(data.day);
        if (totalMonth) totalMonth.textContent = window.formatCurrency(data.month);
        if (tShared) tShared.textContent = window.formatCurrency(data.shared);
        if (tPersonal) tPersonal.textContent = window.formatCurrency(data.personal);
        if (tMeDeben) tMeDeben.textContent = window.formatCurrency(data.me_deben);
        if (tYoDebo) tYoDebo.textContent = window.formatCurrency(data.yo_debo);
        if (hMeDeben) hMeDeben.textContent = window.formatCurrency(data.me_deben);
        if (hYoDebo) hYoDebo.textContent = window.formatCurrency(data.yo_debo);
        
        // Renderizar Recordatorios / Notificaciones (Dashboard y Panel)
        const remindersArea = document.getElementById('reminders-area');
        const notifList = document.getElementById('notif-list');
        const notifBadge = document.getElementById('notif-badge');
        
        if (data.reminders || data.invitations) {
            const allItems = [...(data.invitations || []), ...(data.reminders || [])];
            if (notifBadge) notifBadge.style.display = allItems.length > 0 ? 'block' : 'none';
            
            const html = allItems.map(r => {
                if (r.type === 'invitation') {
                    return `
                        <div class="stat-card" style="margin-bottom: 10px; border-left: 5px solid var(--primary); padding: 1.2rem;">
                            <p style="font-size:0.55rem; font-weight:800; color:var(--primary); margin-bottom:5px;">SOLICITUD DE VÍNCULO</p>
                            <h4 style="font-size:0.75rem; font-weight:900; margin:0;">${r.sender_name} quiere vincular su cuenta contigo</h4>
                            <div style="display:flex; gap:10px; margin-top:10px;">
                                <button onclick="handleInvitation(${r.id}, 'accept')" style="flex:1; padding:8px; border-radius:8px; border:none; background:var(--primary); color:white; font-size:0.6rem; font-weight:900; cursor:pointer;">ACEPTAR</button>
                                <button onclick="handleInvitation(${r.id}, 'reject')" style="flex:1; padding:8px; border-radius:8px; border:none; background:#f1f5f9; color:var(--text-muted); font-size:0.6rem; font-weight:900; cursor:pointer;">RECHAZAR</button>
                            </div>
                        </div>
                    `;
                }

                const icon = r.type.includes('card') ? 'fa-credit-card' : 'fa-redo';
                return `
                    <div class="stat-card" style="margin-bottom: 5px; border-left: 5px solid ${r.color}; padding: 1rem; display: flex; align-items: center; gap: 12px;">
                        <div style="width:35px; height:35px; border-radius:50%; background:${r.color}; color:white; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <i class="fas ${icon}" style="font-size:0.8rem;"></i>
                        </div>
                        <div style="flex:1;">
                            <h4 style="font-size:0.7rem; font-weight:900; margin:0;">${r.title}</h4>
                            <p style="font-size:0.55rem; margin:0; font-weight:700; color:var(--text-muted);">${r.message}</p>
                        </div>
                    </div>
                `;
            }).join('');

            if (remindersArea) {
                // Solo mostrar recordatorios en el dashboard, no invitaciones
                const remindersHtml = (data.reminders || []).map(r => {
                    const icon = r.type.includes('card') ? 'fa-credit-card' : 'fa-redo';
                    return `
                        <div class="stat-card" style="margin-bottom: 5px; border-left: 5px solid ${r.color}; padding: 1rem; display: flex; align-items: center; gap: 12px;">
                            <div style="width:35px; height:35px; border-radius:50%; background:${r.color}; color:white; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <i class="fas ${icon}" style="font-size:0.8rem;"></i>
                            </div>
                            <div style="flex:1;">
                                <h4 style="font-size:0.7rem; font-weight:900; margin:0;">${r.title}</h4>
                                <p style="font-size:0.55rem; margin:0; font-weight:700; color:var(--text-muted);">${r.message}</p>
                            </div>
                        </div>
                    `;
                }).join('');
                remindersArea.innerHTML = remindersHtml;
            }

            if (notifList) {
                notifList.innerHTML = allItems.length > 0 ? html : '<p style="text-align:center; color:var(--text-muted); font-size:0.7rem; padding:1rem;">No hay alertas pendientes.</p>';
            }
        }

        const pInvList = document.getElementById('pending-invitations-list');
        if (pInvList) {
            if (data.invitations && data.invitations.length > 0) {
                pInvList.innerHTML = `<h4 style="font-size:0.7rem; font-weight:900; margin-bottom:10px; color:var(--primary);">INVITACIONES PENDIENTES</h4>` + 
                data.invitations.map(inv => `
                    <div class="stat-card" style="margin-bottom: 10px; border: 1px solid var(--primary-glow); padding: 1rem; display:flex; align-items:center; justify-content:space-between; gap:10px;">
                        <span style="font-size:0.75rem; font-weight:800;">${inv.sender_name}</span>
                        <div style="display:flex; gap:5px;">
                            <button onclick="handleInvitation(${inv.id}, 'accept')" style="padding:5px 10px; border-radius:6px; border:none; background:var(--primary); color:white; font-size:0.55rem; font-weight:900; cursor:pointer;">ACEPTAR</button>
                            <button onclick="handleInvitation(${inv.id}, 'reject')" style="padding:5px 10px; border-radius:6px; border:none; background:#fff1f2; color:var(--secondary); font-size:0.55rem; font-weight:900; cursor:pointer;">X</button>
                        </div>
                    </div>
                `).join('');
            } else {
                pInvList.innerHTML = '';
            }
        }

        // Lógica de Liquidación
        const sMsg = document.getElementById('settlement-msg');
        const sBal = document.getElementById('settlement-balance');
        if (sMsg && sBal) {
            if (data.net_balance > 0) {
                sMsg.textContent = `${data.partner_name || 'Tu pareja'} te debe`;
                sBal.textContent = window.formatCurrency(data.net_balance);
                sBal.style.color = 'var(--accent)';
            } else if (data.net_balance < 0) {
                sMsg.textContent = `Debes a ${data.partner_name || 'tu pareja'}`;
                sBal.textContent = window.formatCurrency(Math.abs(data.net_balance));
                sBal.style.color = 'var(--secondary)';
            } else {
                sMsg.textContent = 'Están a mano 🤝';
                sBal.textContent = window.formatCurrency(0);
                sBal.style.color = 'var(--text-muted)';
            }
        }

        // Presupuesto General
        const gCard = document.getElementById('general-budget-card');
        if (gCard && data.total_budget_limit > 0) {
            gCard.style.display = 'block';
            const perc = Math.min(100, Math.round((data.total_budget_spent / data.total_budget_limit) * 100));
            const pLabel = document.getElementById('budget-percentage');
            const sLabel = document.getElementById('budget-spent');
            const lLabel = document.getElementById('budget-limit');
            const bar = document.getElementById('budget-progress-bar');

            if(pLabel) pLabel.textContent = perc + '%';
            if(sLabel) sLabel.textContent = window.formatCurrency(data.total_budget_spent);
            if(lLabel) lLabel.textContent = 'Límite: ' + window.formatCurrency(data.total_budget_limit);
            if(bar) {
                bar.style.width = perc + '%';
                bar.style.background = perc > 90 ? 'var(--secondary)' : (perc > 70 ? '#f59e0b' : 'var(--primary)');
            }
        } else if (gCard) {
            gCard.style.display = 'none';
        }

        const payerSelect = document.getElementById('payer-select');
        const filterPayer = document.getElementById('filter-payer');
        let opts = `<option value="${data.user_name}">${data.user_name}</option>`;
        if (data.partner_name) opts += `<option value="${data.partner_name}">${data.partner_name}</option>`;
        
        if (payerSelect) payerSelect.innerHTML = opts;
        if (filterPayer) filterPayer.innerHTML = `<option value="all">TODOS</option>` + opts;

        window.updateCharts(data.by_payer, data.by_category);
        if (window.fetchBudgets) window.fetchBudgets();
        if (window.fetchTrend) window.fetchTrend();
    } catch (err) { console.error('Summary error:', err); }
};

window.fetchTrend = async () => {
    try {
        const res = await fetch('/api/expenses/trend');
        const data = await res.json();
        const ctx = document.getElementById('trendChart')?.getContext('2d');
        if (!ctx) return;

        if (window.trendChartInstance) window.trendChartInstance.destroy();
        window.trendChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(d => d.month),
                datasets: [{
                    label: 'Gastos',
                    data: data.map(d => d.total),
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointBackgroundColor: '#6366f1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { display: false }, ticks: { font: { size: 10, weight: 'bold' } } },
                    x: { grid: { display: false }, ticks: { font: { size: 10, weight: 'bold' } } }
                }
            }
        });
    } catch (err) { console.error('Trend error:', err); }
};

window.fetchBudgets = async () => {
    try {
        const res = await fetch('/api/budgets/report');
        const data = await res.json();
        const budgetArea = document.getElementById('budget-area');
        if (!budgetArea) return;

        if (data.length === 0) {
            budgetArea.innerHTML = '<p style="font-size:0.7rem; color:var(--text-muted); text-align:center; padding:1rem;">No tienes presupuestos configurados.</p>';
            return;
        }

        budgetArea.innerHTML = '';
        data.forEach(b => {
            const color = b.percentage > 90 ? 'var(--secondary)' : (b.percentage > 70 ? '#f59e0b' : 'var(--accent)');
            const div = document.createElement('div');
            div.style.marginBottom = '1.2rem';
            div.innerHTML = `
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <span style="font-weight:800; font-size:0.75rem;">${b.category_icon} ${b.category_name}</span>
                    <span style="font-weight:900; font-size:0.75rem;">${window.formatCurrency(b.spent)} / ${window.formatCurrency(b.limit)}</span>
                </div>
                <div style="height:8px; background:#f1f5f9; border-radius:10px; overflow:hidden;">
                    <div style="height:100%; width:${Math.min(100, b.percentage)}%; background:${color}; border-radius:10px; transition: width 1s ease-out;"></div>
                </div>
            `;
            budgetArea.appendChild(div);
        });
    } catch (err) { console.error('Budgets error:', err); }
};

window.openDetailModal = (exp) => {
    const modal = document.getElementById('detail-modal');
    if (!modal) return;

    document.getElementById('detail-icon').textContent = exp.category?.icon || '💰';
    document.getElementById('detail-name').textContent = exp.name;
    document.getElementById('detail-date').textContent = window.formatDate(exp.date);
    document.getElementById('detail-amount').textContent = window.formatCurrency(exp.amount);
    document.getElementById('detail-payer').textContent = exp.payer;
    document.getElementById('detail-method').textContent = exp.payment_method?.name || 'Efectivo';
    document.getElementById('detail-category').textContent = exp.category?.name || 'S/C';
    document.getElementById('detail-type').textContent = exp.is_paid ? 'PAGADO ✅' : (exp.is_personal ? 'Privado' : 'Compartido');

    const percArea = document.getElementById('detail-perc-salary');
    if (percArea && window.currentSalary > 0) {
        const perc = ((exp.amount / window.currentSalary) * 100).toFixed(1);
        percArea.innerHTML = `<span style="color:var(--secondary); font-weight:900;">${perc}%</span> de tu sueldo mensual`;
        percArea.style.display = 'block';
    } else if (percArea) {
        percArea.style.display = 'none';
    }

    const imgArea = document.getElementById('detail-image-area');
    if (exp.image || exp.payment_proof) {
        imgArea.style.display = 'block';
        document.getElementById('detail-img').src = '/storage/' + (exp.payment_proof || exp.image);
    } else {
        imgArea.style.display = 'none';
    }

    // Botones
    const settleBtn = document.getElementById('detail-settle-btn');
    const editBtn = document.getElementById('detail-edit-btn');
    const delBtn = document.getElementById('detail-delete-btn');
    const settleArea = document.getElementById('detail-settle-area');
    
    settleArea.style.display = 'none';
    settleBtn.style.display = (exp.type === 'deuda' && !exp.is_paid) ? 'block' : 'none';
    
    settleBtn.onclick = () => { settleArea.style.display = 'block'; settleBtn.style.display = 'none'; };
    editBtn.onclick = () => { location.href = `/gastos/nuevo?id=${exp.id}`; };
    delBtn.onclick = () => window.deleteExpenseLocal(exp.id);

    const confirmSettleBtn = document.getElementById('confirm-settle-btn');
    confirmSettleBtn.onclick = () => window.confirmSettle(exp.id);

    modal.style.display = 'flex';
};

window.hideSettleForm = () => {
    document.getElementById('detail-settle-area').style.display = 'none';
    document.getElementById('detail-settle-btn').style.display = 'block';
};

window.confirmSettle = async (id) => {
    const file = document.getElementById('settle-proof').files[0];
    const formData = new FormData();
    if (file) formData.append('payment_proof', file);

    const res = await fetch(`/api/expenses/${id}/settle`, { method: 'POST', body: formData });
    if (res.ok) {
        window.closeDetailModal();
        window.fetchExpenses();
        if (window.fetchSummary) window.fetchSummary();
    }
};

window.closeDetailModal = () => {
    document.getElementById('detail-modal').style.display = 'none';
};

window.deleteExpenseLocal = async (id) => {
    if (!confirm('¿Eliminar este gasto?')) return;
    const res = await fetch(`/api/expenses/${id}`, { method: 'DELETE' });
    if (res.ok) {
        window.closeDetailModal();
        window.fetchExpenses();
        if (window.fetchSummary) window.fetchSummary();
        if (window.fetchBudgetsDetailed) window.fetchBudgetsDetailed();
    }
};

window.fetchExpenses = async () => {
    try {
        const res = await fetch('/api/expenses');
        let expenses = await res.json();
        const mainList = document.getElementById('main-list');
        if (!mainList) return;

        const typeF = document.getElementById('filter-type')?.value || 'all';
        const payerF = document.getElementById('filter-payer')?.value || 'all';
        const methodF = document.getElementById('filter-method')?.value || 'all';
        const searchF = document.getElementById('filter-search')?.value.toLowerCase() || '';
        const startF = document.getElementById('filter-start')?.value || '';
        const endF = document.getElementById('filter-end')?.value || '';

        expenses = expenses.filter(e => {
            if (typeF === 'shared' && (e.is_personal || e.is_recurring)) return false;
            if (typeF === 'personal' && !e.is_personal) return false;
            if (typeF === 'deuda' && e.type !== 'deuda') return false;
            if (payerF !== 'all' && e.payer !== payerF) return false;
            if (methodF !== 'all' && (e.payment_method_id || 0) != methodF) return false;
            if (searchF && !e.name.toLowerCase().includes(searchF)) return false;
            if (startF && e.date < startF) return false;
            if (endF && e.date > endF) return false;
            return true;
        });

        mainList.innerHTML = '';
        expenses.forEach(exp => {
            const item = document.createElement('div');
            item.className = 'history-item';
            item.style.cursor = 'pointer';
            item.style.opacity = exp.is_paid ? '0.5' : '1';
            item.onclick = () => window.openDetailModal(exp);
            
            const isDebt = exp.type === 'deuda';
            const debtIcon = exp.debt_direction === 'to_me' ? 'fa-arrow-down' : 'fa-arrow-up';
            const debtColor = exp.debt_direction === 'to_me' ? 'var(--accent)' : 'var(--secondary)';
            
            item.innerHTML = `
                <div class="history-icon" style="background:${isDebt ? debtColor : ''}; color:${isDebt ? 'white' : ''}">
                    <i class="fas ${isDebt ? debtIcon : (exp.is_personal ? 'fa-lock' : 'fa-shopping-cart')}"></i>
                </div>
                <div class="history-content">
                    <h4>${exp.name} ${exp.is_paid ? '✅' : ''} ${isDebt ? `<span style="font-size:0.5rem; vertical-align:middle; margin-left:5px; padding:2px 5px; border-radius:4px; background:#f1f5f9; color:${debtColor}">${exp.debt_direction === 'to_me' ? 'ME DEBEN' : 'YO DEBO'}</span>` : ''}</h4>
                    <p>${window.formatDate(exp.date)} • ${exp.payer}</p>
                </div>
                <div class="history-amount" style="display:flex; flex-direction:column; align-items:flex-end;">
                    <span style="text-decoration: ${exp.is_paid ? 'line-through' : 'none'}">${window.formatCurrency(exp.amount)}</span>
                    ${window.currentSalary > 0 ? `<span style="font-size:0.5rem; color:var(--secondary); font-weight:900; opacity: ${exp.is_paid ? '0.5' : '1'}">${((exp.amount / window.currentSalary) * 100).toFixed(1)}%</span>` : ''}
                </div>
            `;
            mainList.appendChild(item);
        });
    } catch (err) { console.error('List error:', err); }
};

window.fetchCategories = async () => {
    try {
        const res = await fetch('/api/categories');
        const cats = await res.json();
        const categoriesList = document.getElementById('categories-list');
        const selectors = document.querySelectorAll('#category-selector, .category-selector-all');
        
        selectors.forEach(s => {
            let opts = '';
            cats.forEach(c => opts += `<option value="${c.id}">${c.icon || '💰'} ${c.name}</option>`);
            s.innerHTML = opts;
        });

        if (categoriesList) {
            categoriesList.innerHTML = '';
            cats.forEach(c => {
                const div = document.createElement('div');
                div.className = 'history-item';
                div.style.padding = '0.5rem';
                div.innerHTML = `
                    <div class="history-icon" style="background:transparent; font-size:1.5rem; width:40px;">${c.icon || '💰'}</div>
                    <h4 style="flex:1; font-size:0.85rem;">${c.name}</h4>
                    ${c.user_id ? `
                        <button class="btn-text" style="color:var(--primary); font-size:0.6rem; margin-right:10px;" onclick="editCategory(${c.id}, '${c.name}', '${c.icon || '💰'}')">EDITAR</button>
                        <button class="btn-text" style="color:var(--secondary); font-size:0.6rem;" onclick="deleteCategory(${c.id})">ELIMINAR</button>
                    ` : '<span style="font-size:0.5rem; color:var(--text-muted);">SISTEMA</span>'}
                `;
                categoriesList.appendChild(div);
            });
        }
    } catch (err) { console.error('Categories error:', err); }
};

window.editCategory = (id, name, icon) => {
    const idField = document.getElementById('cat-id');
    const nameField = document.getElementById('cat-name');
    const iconField = document.getElementById('cat-icon');
    if (idField) idField.value = id;
    if (nameField) nameField.value = name;
    if (iconField) iconField.value = icon;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

window.cancelCatEdit = () => {
    const form = document.getElementById('cat-form');
    if (form) form.reset();
    const idField = document.getElementById('cat-id');
    if (idField) idField.value = '';
};

window.fetchMonthly = async () => {
    try {
        const res = await fetch('/api/expenses');
        let expenses = await res.json();
        const monthlyList = document.getElementById('monthly-list');
        if (!monthlyList) return;

        expenses = expenses.filter(e => e.is_recurring);

        monthlyList.innerHTML = '';
        if (expenses.length === 0) {
            monthlyList.innerHTML = '<p style="text-align:center; padding:2rem; color:var(--text-muted); font-size:0.7rem;">No hay cargos mensuales configurados.</p>';
            return;
        }

        expenses.forEach(exp => {
            const div = document.createElement('div');
            div.className = 'history-item';
            div.style.opacity = exp.is_active ? '1' : '0.5';
            
            div.innerHTML = `
                <div class="history-icon" style="background: ${exp.is_active ? 'rgba(99, 102, 241, 0.1)' : '#f1f5f9'}; color: ${exp.is_active ? 'var(--primary)' : 'var(--text-muted)'}">
                    <i class="fas fa-redo"></i>
                </div>
                <div class="history-content">
                    <h4 style="font-size:0.85rem;">${exp.name}</h4>
                    <p style="font-size:0.6rem;">Día ${exp.due_day} • ${exp.is_active ? 'Activo' : 'Pausado'}</p>
                </div>
                <div style="text-align:right;">
                    <div style="font-weight:900; font-size:0.85rem; color:var(--text-main);">${window.formatCurrency(exp.amount)}</div>
                    <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:5px;">
                        <button class="btn-text" style="color:var(--primary); font-size:0.5rem; font-weight:800;" onclick="editMonthly('${encodeURIComponent(JSON.stringify(exp))}')">EDITAR</button>
                        <button class="btn-text" style="color:${exp.is_active ? 'var(--secondary)' : 'var(--accent)'}; font-size:0.5rem; font-weight:800;" onclick="toggleMonthly(${exp.id}, ${exp.is_active})">${exp.is_active ? 'PAUSAR' : 'ACTIVAR'}</button>
                    </div>
                </div>
            `;
            monthlyList.appendChild(div);
        });
    } catch (err) { console.error('Monthly error:', err); }
};

window.toggleMonthly = async (id, currentStatus) => {
    await fetch(`/api/expenses/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ is_active: !currentStatus })
    });
    window.fetchMonthly();
    if (window.fetchSummary) window.fetchSummary();
};

window.deleteMonthly = async (id) => {
    if (!confirm('¿Eliminar este cargo mensual?')) return;
    await fetch(`/api/expenses/${id}`, { method: 'DELETE' });
    window.fetchMonthly();
    if (window.fetchSummary) window.fetchSummary();
};

window.fetchMethods = async () => {
    try {
        const res = await fetch('/api/payment-methods');
        const methods = await res.json();
        const methodsList = document.getElementById('methods-list');
        const selectors = document.querySelectorAll('.method-selector');
        
        selectors.forEach(s => {
            let opts = '<option value="">Efectivo / Ninguno</option>';
            methods.forEach(m => opts += `<option value="${m.id}">${m.name}</option>`);
            s.innerHTML = opts;
        });

        if (!methodsList) return;
        methodsList.innerHTML = '';

        // Añadir opción "Efectivo" fija
        const cashDiv = document.createElement('div');
        cashDiv.className = 'card-mockup';
        cashDiv.style.background = 'linear-gradient(135deg, #64748b 0%, #475569 100%)';
        cashDiv.style.cursor = 'pointer';
        cashDiv.onclick = () => location.href = '/gastos?method_id=0';
        cashDiv.innerHTML = `<div class="type">EFECTIVO</div><h4>Efectivo / Otros</h4><p style="font-size:0.55rem; font-weight:800; opacity:0.8;">VER MOVIMIENTOS</p>`;
        methodsList.appendChild(cashDiv);

        methods.forEach(m => {
            const div = document.createElement('div');
            div.className = 'card-mockup';
            div.style.cursor = 'pointer';
            div.onclick = (e) => {
                if (e.target.tagName !== 'SPAN') location.href = `/gastos?method_id=${m.id}`;
            };

            if(m.type === 'credito') div.style.background = 'linear-gradient(135deg, #1e293b 0%, #334155 100%)';
            if(m.type === 'vales') div.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
            
            let info = `<div class="type">${m.type}</div><h4>${m.name}</h4>`;
            if(m.type === 'credito' && m.cut_day) {
                info += `<div style="font-size:0.5rem; font-weight:800; opacity:0.8; margin-top:-10px;">CORTE: DÍA ${m.cut_day} | PAGO: DÍA ${m.payment_day}</div>`;
            }
            info += `<span style="font-size: 0.7rem; text-decoration: underline; cursor: pointer; position:absolute; bottom:15px; right:15px;" onclick="deleteMethod(${m.id})">QUITAR</span>`;
            
            div.innerHTML = info;
            methodsList.appendChild(div);
        });
    } catch (err) { console.error('Methods error:', err); }
};

window.updateCharts = (byPayer, byCategory) => {
    const pCtx = document.getElementById('payerChart')?.getContext('2d');
    const cCtx = document.getElementById('categoryChart')?.getContext('2d');
    if (!pCtx || !cCtx) return;

    if (payerChart) payerChart.destroy();
    payerChart = new Chart(pCtx, {
        type: 'doughnut',
        data: {
            labels: byPayer.map(p => p.payer),
            datasets: [{ data: byPayer.map(p => p.total), backgroundColor: ['#6366f1', '#f43f5e'], borderRadius: 10, spacing: 5 }]
        },
        options: { responsive: true, maintainAspectRatio: false, cutout: '80%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true } } } }
    });

    if (categoryChart) categoryChart.destroy();
    categoryChart = new Chart(cCtx, {
        type: 'doughnut',
        data: {
            labels: byCategory.map(c => c.label),
            datasets: [{ data: byCategory.map(c => c.total), backgroundColor: ['#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#3b82f6'], borderRadius: 10, spacing: 5 }]
        },
        options: { responsive: true, maintainAspectRatio: false, cutout: '80%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } } } }
    });
};

// Initializers
document.addEventListener('DOMContentLoaded', () => {
    // Dark Mode Init
    if (localStorage.getItem('dark-mode') === 'true' || 
        (!localStorage.getItem('dark-mode') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.body.classList.add('dark-mode');
    }

    window.toggleDarkMode = () => {
        const isDark = document.body.classList.toggle('dark-mode');
        localStorage.setItem('dark-mode', isDark);
    };

    // Form handlers
    if (document.getElementById('cat-form')) {
        document.getElementById('cat-form').onsubmit = async (e) => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target));
            const method = data.id ? 'PUT' : 'POST';
            const url = data.id ? `/api/categories/${data.id}` : '/api/categories';
            await fetch(url, { method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
            e.target.reset(); if (window.cancelCatEdit) window.cancelCatEdit(); window.fetchCategories();
            window.showToast('Categoría guardada correctamente');
        };
    }

    if (document.getElementById('card-form')) {
        document.getElementById('card-form').onsubmit = async (e) => {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(e.target));
            await fetch('/api/payment-methods', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
            e.target.reset(); window.fetchMethods();
            window.showToast('Tarjeta añadida correctamente');
        };
    }

    if (document.getElementById('expense-form')) {
        document.getElementById('expense-form').onsubmit = async (e) => {
            e.preventDefault();
            const fData = new FormData(e.target);
            fData.append('type', 'gasto');
            const res = await fetch('/api/expenses', { method: 'POST', body: fData });
            if (res.ok) location.href = "/gastos";
        };
    }
});

// Other Globals
window.deleteCategory = async (id) => {
    if (!confirm('¿Eliminar categoría?')) return;
    await fetch(`/api/categories/${id}`, { method: 'DELETE' });
    window.fetchCategories();
    window.showToast('Categoría eliminada', 'info');
};

window.deleteMethod = async (id) => {
    if (!confirm('¿Eliminar tarjeta?')) return;
    await fetch(`/api/payment-methods/${id}`, { method: 'DELETE' });
    window.fetchMethods();
    window.showToast('Tarjeta eliminada', 'info');
};

window.viewReceipt = (url) => {
    const modal = document.getElementById('photo-modal');
    if(!modal) return;
    document.getElementById('modal-img').src = url;
    modal.style.display = 'flex';
};
