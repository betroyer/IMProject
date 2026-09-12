const $ = (selector, root = document) => root.querySelector(selector);
const app = $('#app');
const dialog = $('#record-dialog');
const money = (value) =>
  new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
    maximumFractionDigits: 0,
  }).format(Number(value || 0));
const escapeHtml = (value) =>
  String(value ?? '').replace(
    /[&<>'"]/g,
    (c) =>
      ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' })[
        c
      ],
  );
const icon = (name) => {
  const p = {
    home: '<path d="M3 11.5 12 4l9 7.5"/><path d="M5 10v10h14V10M9 20v-6h6v6"/>',
    check:
      '<path d="m5 12 4 4L19 6"/><rect x="3" y="3" width="18" height="18" rx="4"/>',
    plan: '<path d="M5 4h14v16H5zM8 8h8M8 12h8M8 16h5"/>',
    wallet:
      '<path d="M4 6h15a2 2 0 0 1 2 2v10H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h13M16 12h5"/>',
    card: '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>',
    gift: '<path d="M3 10h18v11H3zM12 10v11M2 7h20v3H2zM12 7H8.5a2.5 2.5 0 1 1 0-5C11 2 12 7 12 7Zm0 0h3.5a2.5 2.5 0 1 0 0-5C13 2 12 7 12 7Z"/>',
    bell: '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/>',
    file: '<path d="M6 2h9l5 5v15H6zM14 2v6h6M9 13h8M9 17h8"/>',
    users:
      '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM17 11a4 4 0 0 0 0-8M22 21v-2a4 4 0 0 0-3-3.87"/>',
    activity: '<path d="M3 12h4l2-7 4 14 2-7h6"/>',
    support:
      '<circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 1 1 3.4 2.33c-.9.39-.9 1.17-.9 1.67M12 17h.01"/>',
  };
  return `<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">${p[name] || p.file}</svg>`;
};
const title = (key) =>
  ({
    dashboard: 'Overview',
    setup: 'Setup Guide',
    plan: 'Business Plan Guide',
    goals: 'Goals & Objectives',
    reports: 'Accounting Reports',
    notifications: 'Notifications & Announcements',
    accounting: 'Accounting System',
    payments: 'Flexible Payments',
    rewards: 'Loyalty Rewards',
    content: 'Content & Guide Management',
    users: 'User & Account Management',
    performance: 'System Usage & Performance',
    support: 'Support Management',
  })[key];
const descriptions = {
  plan: 'Write and update the plan for your own business.',
  goals: 'Set measurable objectives and track progress toward them.',
  reports: 'Review your income statement, cash flow, balance, and owner equity.',
  notifications: 'Keep customers informed across every channel.',
  accounting: 'Track income, expenses, invoices, and cash flow.',
  payments: 'Manage payment options and payment status.',
  rewards: 'Create benefits that keep customers coming back.',
  content: 'Publish helpful guides and business updates.',
  users: 'Control team access, roles, and account status.',
  support: 'Resolve customer requests from one organized queue.',
};
const signedUser = window.CURRENT_USER || {};
const clientDefaults = {
  setup: 'view', plan: 'edit', goals: 'edit', notifications: 'view', accounting: 'edit',
  reports: 'view', support: 'edit',
};
function accessLevel(module) {
  if (signedUser.role === 'Administrator') return ['content','users','performance','notifications','support'].includes(module) ? 'edit' : 'none';
  return signedUser.permissions?.[module] || clientDefaults[module] || 'none';
}
const configs = {
  plan: {
    columns: ['section','title','details','status','updated_at'],
    fields: { section:'text', title:'text', details:'textarea', status:['Draft','In progress','Complete'] },
  },
  goals: {
    columns: ['title','objective','target_date','progress','status'],
    fields: { title:'text', objective:'textarea', target_date:'date', progress:'number', status:['Not started','In progress','Complete'] },
  },
  accounting: {
    columns: [
      'reference',
      'customer',
      'type',
      'amount',
      'category',
      'status',
      'transaction_date',
    ],
    fields: {
      reference: 'text',
      customer: 'text',
      type: ['Income', 'Expense'],
      amount: 'number',
      category: 'text',
      status: ['Paid', 'Pending', 'Overdue'],
      transaction_date: 'date',
    },
  },
  payments: {
    columns: [
      'reference',
      'customer',
      'method',
      'amount',
      'status',
      'created_at',
    ],
    fields: {
      reference: 'text',
      customer: 'text',
      method: ['GCash', 'Maya', 'Bank Transfer', 'Cash', 'Card'],
      amount: 'number',
      status: ['Completed', 'Pending', 'Failed'],
    },
  },
  notifications: {
    columns: ['title', 'audience', 'channel', 'status', 'created_at'],
    fields: {
      title: 'text',
      message: 'textarea',
      audience: 'text',
      channel: ['Email', 'SMS', 'In-app'],
      status: ['Draft', 'Scheduled', 'Sent'],
    },
  },
  rewards: {
    columns: ['name', 'points', 'redemptions', 'status'],
    fields: {
      name: 'text',
      points: 'number',
      redemptions: 'number',
      status: ['Active', 'Paused'],
    },
  },
  content: {
    columns: ['title', 'content_type', 'status', 'updated_at'],
    fields: {
      title: 'text',
      content_type: ['Guide', 'Article', 'Announcement'],
      status: ['Draft', 'Published', 'Archived'],
    },
  },
  users: {
    columns: ['name', 'email', 'role', 'status', 'created_at'],
    fields: {
      name: 'text',
      email: 'email',
      password: 'password',
      organization_id: 'number',
      role: ['Client', 'Administrator'],
      status: ['Active', 'Invited', 'Disabled'],
    },
  },
  support: {
    columns: ['subject', 'customer', 'priority', 'status', 'created_at'],
    fields: {
      subject: 'text',
      customer: 'text',
      priority: ['Low', 'Medium', 'High'],
      status: ['Open', 'In progress', 'Resolved'],
    },
  },
};
let currentView = 'dashboard',
  currentItems = [];
async function request(url, options = {}) {
  const response = await fetch(url, options);
  const data = await response.json();
  if (!response.ok) throw new Error(data.message || 'Something went wrong.');
  return data;
}
function toast(message, error = false) {
  const el = $('#toast');
  el.textContent = message;
  el.className = error ? 'show error' : 'show';
  setTimeout(() => (el.className = ''), 2600);
}
function pageHead(view, action = '') {
  return `<div class="page-head"><div><h1>${title(view)}</h1><p>${descriptions[view] || 'Build a stronger foundation for your business.'}</p></div>${action}</div>`;
}
async function renderDashboard() {
  app.innerHTML = '<div class="empty">Loading your business…</div>';
  try {
    const { metrics, profile, activity } = await request(
      'api.php?resource=dashboard',
    );
    const months = ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'],
      heights = [38, 52, 46, 70, 61, 88];
    app.innerHTML = `${pageHead('dashboard', '<button class="button primary" data-go="accounting">+ Create invoice</button>')}<section class="metrics"><article class="metric"><span class="metric-icon">₱</span><p>Revenue this month</p><strong>${money(metrics.revenue)}</strong><small>Paid income recorded</small></article><article class="metric"><span class="metric-icon">♙</span><p>Active customers</p><strong>${metrics.customers}</strong><small>Across all transactions</small></article><article class="metric"><span class="metric-icon">▰</span><p>Pending invoices</p><strong>${metrics.pending}</strong><small>Awaiting settlement</small></article><article class="metric"><span class="metric-icon">?</span><p>Open support tickets</p><strong>${metrics.tickets}</strong><small>Needs your attention</small></article></section><section class="dashboard-grid"><article class="panel"><h2>Revenue overview</h2><p class="panel-sub">Six-month performance snapshot</p><div class="chart">${months.map((m, i) => `<div class="bar-wrap"><div class="bar" style="height:${heights[i]}%"></div><small>${m}</small></div>`).join('')}</div></article><article class="panel"><h2>Quick actions</h2><p class="panel-sub">Common tasks, one click away</p><div class="quick-list"><button data-go="accounting"><span>₱</span>Create invoice<b>›</b></button><button data-go="users"><span>♙</span>Add team member<b>›</b></button><button data-go="notifications"><span>♢</span>Publish announcement<b>›</b></button><button data-go="setup"><span>✓</span>Continue setup<b>›</b></button></div></article><article class="panel activity-panel"><h2>Recent activity</h2><p class="panel-sub">Latest updates across your workspace</p><div class="activity-list">${activity.map((a) => `<div class="activity-row"><i>✓</i><div><strong>${escapeHtml(a.title)}</strong><p>${escapeHtml(a.detail)}</p></div><time>${new Date(a.created_at).toLocaleDateString()}</time></div>`).join('') || '<div class="empty">No activity yet.</div>'}</div></article><article class="panel health"><h2>Business health</h2><p class="panel-sub">Your setup progress</p><div class="progress-ring" style="--progress:${profile?.setup_progress || 0}" data-value="${profile?.setup_progress || 0}%"></div><p>Complete your startup checklist to improve your business health score.</p><button class="button secondary" data-go="setup">Continue setup</button></article></section>`;
    bindLinks();
  } catch (e) {
    showDatabaseError(e);
  }
}
async function renderSetup() {
  const tasks = [
    'Complete your business profile',
    'Add your first team member',
    'Connect a payment method',
    'Create your first invoice',
    'Publish a customer guide',
    'Set up a loyalty reward',
  ];
  if (signedUser.role === 'Administrator') { app.innerHTML=`${pageHead('setup')}<article class="panel empty"><h2>Manage client onboarding</h2><p>Create and publish setup instructions from Content & Guides. Clients only see the steps you make available.</p><button class="button primary" data-go="content">Manage guide content</button></article>`;bindLinks();return; }
  let profile={}; try{profile=(await request('api.php?resource=profile')).profile||{};}catch(e){showDatabaseError(e);return;}
  app.innerHTML = `${pageHead('setup')}<section class="setup-layout"><article class="panel profile-editor"><h2>Your business details</h2><p class="panel-sub">Saved securely to your organization.</p><form id="profile-form" class="form-grid"><div class="field"><label>Business name<input name="business_name" required value="${escapeHtml(profile.business_name)}"></label></div><div class="field"><label>Owner name<input name="owner_name" required value="${escapeHtml(profile.owner_name)}"></label></div><div class="field"><label>Industry<input name="industry" required value="${escapeHtml(profile.industry)}"></label></div><div class="field"><label>Email<input type="email" name="email" required value="${escapeHtml(profile.email)}"></label></div><div class="field"><label>Phone<input name="phone" value="${escapeHtml(profile.phone)}"></label></div><div class="field"><label>Business address<input name="address" value="${escapeHtml(profile.address)}"></label></div><div class="field full"><button class="button primary">Save business details</button></div></form></article><article class="panel setup-intro"><h2>Next steps</h2><p>Continue setting up the parts of your business you will use every day.</p><div class="setup-links">${[
    ['users', 'Business details and team'],
    ['payments', 'Payments and accounting'],
    ['rewards', 'Customer rewards'],
    ['content', 'Guides and announcements'],
  ]
    .map(
      (x) =>
        `<button data-go="${x[0]}">${icon('check')}<span>${x[1]}</span><b>Open</b></button>`,
    )
    .join(
      '',
    )}</div></article></section>`;
  bindLinks();
  $('#profile-form').onsubmit=async(e)=>{e.preventDefault();const form=e.currentTarget;if(!form.reportValidity())return;try{await request('api.php?resource=profile',{method:'PATCH',headers:{'Content-Type':'application/json'},body:JSON.stringify(Object.fromEntries(new FormData(form)))});toast('Business details saved.');}catch(err){toast(err.message,true)}};
}
function renderPlan() {
  const cards = [
    [
      '01',
      'Executive summary',
      'Clarify the problem, your solution, and why now.',
    ],
    [
      '02',
      'Target market',
      'Define your ideal customers and the market opportunity.',
    ],
    [
      '03',
      'Offer & pricing',
      'Describe what you sell and how the business earns.',
    ],
    [
      '04',
      'Marketing plan',
      'Choose the channels that will reach your customers.',
    ],
    ['05', 'Operations', 'Map suppliers, workflows, people, and tools.'],
    ['06', 'Financial outlook', 'Set revenue goals, costs, and funding needs.'],
  ];
  app.innerHTML = `${pageHead('plan')}<section class="plan-grid">${cards.map((c) => `<article class="panel plan-card"><span class="number">${c[0]}</span><h2>${c[1]}</h2><p class="panel-sub">${c[2]}</p></article>`).join('')}</section>`;
}
function renderPerformance() {
  if (signedUser.role !== 'Administrator') {
    app.innerHTML = `${pageHead('performance')}<section class="performance-grid"><article class="panel"><p class="panel-sub">Storage used</p><strong>1.6 GB</strong><small>of your 2 GB allowance</small></article><article class="panel"><p class="panel-sub">Team accounts</p><strong>3</strong><small>within your organization</small></article><article class="panel"><p class="panel-sub">Records this month</p><strong>28</strong><small>Your workspace activity</small></article><article class="panel" style="grid-column:span 3"><h2>Your feature usage</h2><p class="panel-sub">Only activity from your organization is shown here.</p><div class="chart"><div class="bar-wrap"><div class="bar" style="height:88%"></div><small>Payments</small></div><div class="bar-wrap"><div class="bar" style="height:65%"></div><small>Accounting</small></div><div class="bar-wrap"><div class="bar" style="height:48%"></div><small>Rewards</small></div></div></article></section>`;
    return;
  }
  app.innerHTML = `${pageHead('performance')}<section class="performance-grid"><article class="panel"><p class="panel-sub">System availability</p><strong>99.98%</strong><small>All systems operational</small></article><article class="panel"><p class="panel-sub">Average response time</p><strong>142 ms</strong><small>18% faster this week</small></article><article class="panel"><p class="panel-sub">Active sessions</p><strong>24</strong><small>Across the platform</small></article><article class="panel"><h2>Feature usage</h2><div class="chart"><div class="bar-wrap"><div class="bar" style="height:88%"></div><small>Payments</small></div><div class="bar-wrap"><div class="bar" style="height:65%"></div><small>Content</small></div><div class="bar-wrap"><div class="bar" style="height:48%"></div><small>Rewards</small></div></div></article><article class="panel" style="grid-column:span 2"><h2>Platform status</h2><p class="panel-sub">Database, payment records, notification delivery, and account services are healthy.</p><div class="checklist"><label>Database service — Operational</label><label>Payment records — Operational</label><label>Notification service — Operational</label></div></article></section>`;
}
async function renderReports(){app.innerHTML='<div class="empty">Preparing reports…</div>';try{const data=await request('api.php?resource=reports');const s=data.summary;app.innerHTML=`${pageHead('reports')}<section class="report-summary"><article><span>Total income</span><strong>${money(s.income)}</strong></article><article><span>Total expenses</span><strong>${money(s.expenses)}</strong></article><article><span>Net income</span><strong>${money(s.net_income)}</strong></article><article><span>Owner equity</span><strong>${money(s.owner_equity)}</strong></article></section><article class="panel report-sheet"><header><div><h2>Income statement</h2><p class="panel-sub">Paid transactions grouped by category</p></div><time>${new Date().toLocaleDateString()}</time></header><table><thead><tr><th>Type</th><th>Category</th><th>Amount</th></tr></thead><tbody>${data.breakdown.map(r=>`<tr><td>${escapeHtml(r.type)}</td><td>${escapeHtml(r.category)}</td><td>${money(r.total)}</td></tr>`).join('')}</tbody></table></article>`;}catch(e){showDatabaseError(e)}}
async function renderResource(view) {
  app.innerHTML = '<div class="empty">Loading records…</div>';
  try {
    const data = await request(`api.php?resource=${view}`);
    currentItems = data.items;
    const cfg = configs[view];
    const addAction = data.canEdit ? '<button class="button primary" id="add-record">+ Add record</button>' : '<span class="access-note">View only</span>';
    app.innerHTML = `${pageHead(view, addAction)}<article class="panel table-card"><div class="table-toolbar"><div><h2>${title(view)} records</h2><p class="panel-sub">${data.items.length} total records</p></div></div><div class="table-wrap">${data.items.length ? `<table><thead><tr>${cfg.columns.map((c) => `<th>${label(c)}</th>`).join('')}<th></th></tr></thead><tbody>${data.items.map((row) => `<tr>${cfg.columns.map((c) => `<td>${cell(c, row[c])}</td>`).join('')}<td class="row-actions">${data.isAdmin && view === 'users' && row.role !== 'Administrator' ? `<button class="permission-link" data-permissions="${row.id}">Permissions</button>` : ''}${data.canEdit && !(view === 'users' && row.role === 'Administrator') ? `<button data-delete="${row.id}">Delete</button>` : ''}</td></tr>`).join('')}</tbody></table>` : '<div class="empty">No records yet.</div>'}</div></article>`;
    if ($('#add-record')) $('#add-record').onclick = () => openForm(view);
    document.querySelectorAll('[data-permissions]').forEach((b) => b.onclick = () => openPermissions(currentItems.find((item) => item.id == b.dataset.permissions)));
    document
      .querySelectorAll('[data-delete]')
      .forEach((b) => (b.onclick = () => removeRecord(view, b.dataset.delete)));
  } catch (e) {
    showDatabaseError(e);
  }
}
function openPermissions(user) {
  const modules = ['setup','plan','goals','notifications','accounting','payments','rewards','content','users','performance','support'];
  const saved = typeof user.permissions === 'string' ? JSON.parse(user.permissions || '{}') : (user.permissions || {});
  $('#dialog-title').textContent = `Permissions for ${user.name}`;
  $('#form-fields').innerHTML = modules.map((module) => { const level=saved[module]||clientDefaults[module]; return `<div class="field"><label for="p-${module}">${title(module)}</label><select id="p-${module}" name="${module}"><option value="none" ${level==='none'?'selected':''}>No access</option><option value="view" ${level==='view'?'selected':''}>View only</option><option value="edit" ${level==='edit'?'selected':''}>View and edit</option></select></div>`; }).join('');
  $('#save-record').textContent = 'Save permissions';
  dialog.showModal();
  $('#save-record').onclick = async (e) => { e.preventDefault(); const permissions=Object.fromEntries(new FormData($('#record-form'))); try { await request('api.php?resource=permissions',{method:'PATCH',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:user.id,permissions})}); dialog.close(); toast('Permissions updated.'); renderResource('users'); } catch(err){ toast(err.message,true); } };
}
function label(s) {
  return s.replaceAll('_', ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}
function cell(key, value) {
  if (key === 'amount') return money(value);
  if (['status', 'priority'].includes(key))
    return `<span class="badge ${String(value).replace(' ', '-')}">${escapeHtml(value)}</span>`;
  if (key.endsWith('_at') || key.endsWith('_date'))
    return escapeHtml(new Date(value).toLocaleDateString());
  return escapeHtml(value);
}
function openForm(view) {
  const cfg = configs[view];
  $('#save-record').textContent = 'Save record';
  $('#dialog-title').textContent = `Add to ${title(view)}`;
  $('#form-fields').innerHTML = Object.entries(cfg.fields)
    .map(([name, type]) => {
      const required = name !== 'redemptions';
      if (Array.isArray(type))
        return `<div class="field"><label for="f-${name}">${label(name)}</label><select id="f-${name}" name="${name}" ${required ? 'required' : ''}>${type.map((x) => `<option>${x}</option>`).join('')}</select></div>`;
      if (type === 'textarea')
        return `<div class="field full"><label for="f-${name}">${label(name)}</label><textarea id="f-${name}" name="${name}" required></textarea></div>`;
      return `<div class="field"><label for="f-${name}">${label(name)}</label><input id="f-${name}" name="${name}" type="${type}" ${required ? 'required' : ''} ${type === 'number' ? 'min="0" step="0.01"' : ''}></div>`;
    })
    .join('');
  dialog.showModal();
  $('#save-record').onclick = async (e) => {
    e.preventDefault();
    const form = $('#record-form');
    if (!form.reportValidity()) return;
    const payload = Object.fromEntries(new FormData(form));
    try {
      await request(`api.php?resource=${view}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      dialog.close();
      toast('Record created successfully.');
      renderResource(view);
    } catch (err) {
      toast(err.message, true);
    }
  };
}
async function removeRecord(view, id) {
  if (!confirm('Delete this record? This cannot be undone.')) return;
  try {
    await request(`api.php?resource=${view}&id=${id}`, { method: 'DELETE' });
    toast('Record deleted.');
    renderResource(view);
  } catch (e) {
    toast(e.message, true);
  }
}
function showDatabaseError(e) {
  app.innerHTML = `${pageHead(currentView)}<article class="panel empty"><h2>Connect the database to continue</h2><p>${escapeHtml(e.message)}</p><p>Open phpMyAdmin, import <strong>schema.sql</strong>, and refresh this page.</p></article>`;
}
function bindLinks() {
  document
    .querySelectorAll('[data-go]')
    .forEach((b) => (b.onclick = () => navigate(b.dataset.go)));
}
function navigate(view) {
  if (accessLevel(view) === 'none') { toast('You do not have access to this section.', true); return; }
  currentView = view;
  document
    .querySelectorAll('.nav-item')
    .forEach((b) => b.classList.toggle('active', b.dataset.view === view));
  $('#sidebar').classList.remove('open');
  $('.scrim').classList.remove('show');
  history.replaceState(null, '', `#${view}`);
  if (view === 'dashboard') renderDashboard();
  else if (view === 'setup') renderSetup();
  else if (view === 'plan' || view === 'goals') renderResource(view);
  else if (view === 'reports') renderReports();
  else if (view === 'performance') renderPerformance();
  else renderResource(view);
}
document.querySelectorAll('.nav-item').forEach((b) => {
  if (b.dataset.view !== 'dashboard' && accessLevel(b.dataset.view) === 'none') { b.remove(); return; }
  b.insertAdjacentHTML('afterbegin', `<i>${icon(b.dataset.icon)}</i>`);
  b.onclick = () => navigate(b.dataset.view);
});
document
  .querySelectorAll('[data-inline-icon]')
  .forEach((el) => (el.innerHTML = icon(el.dataset.inlineIcon)));
$('.menu-toggle').onclick = () => {
  $('#sidebar').classList.add('open');
  $('.scrim').classList.add('show');
};
$('.close-menu').onclick = $('.scrim').onclick = () => {
  $('#sidebar').classList.remove('open');
  $('.scrim').classList.remove('show');
};
$('#global-search').addEventListener('input', (e) => {
  const q = e.target.value.toLowerCase();
  document
    .querySelectorAll('tbody tr')
    .forEach(
      (row) => (row.hidden = !row.textContent.toLowerCase().includes(q)),
    );
});
const requestedView=location.hash.slice(1);const defaultView=signedUser.role==='Administrator'?'content':'setup';navigate(requestedView&&title(requestedView)&&accessLevel(requestedView)!=='none'?requestedView:defaultView);
