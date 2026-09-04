'use client';

import { useState } from 'react';
import { Activity, Bell, BookOpenCheck, ChartNoAxesCombined, ChevronRight, CircleDollarSign, CreditCard, Gift, Headphones, LayoutDashboard, Menu, PackageCheck, Plus, Search, Settings, ShieldCheck, Users, X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

const features = [
  { name: 'Overview', icon: LayoutDashboard, color: 'blue' },
  { name: 'Setup Guide', icon: BookOpenCheck, color: 'green' },
  { name: 'Business Plan Guide', icon: ChartNoAxesCombined, color: 'green' },
  { name: 'Notifications', icon: Bell, color: 'pink' },
  { name: 'Accounting System', icon: CircleDollarSign, color: 'green' },
  { name: 'Flexible Payments', icon: CreditCard, color: 'green' },
  { name: 'Loyalty Reward', icon: Gift, color: 'green' },
  { name: 'Content & Guide Management', icon: PackageCheck, color: 'pink' },
  { name: 'User & Account Management', icon: Users, color: 'pink' },
  { name: 'Usage & Performance', icon: Activity, color: 'pink' },
  { name: 'Support Management', icon: Headphones, color: 'pink' },
];

const activities = [
  ['Payment received', 'Invoice #INV-2048', '+₱18,500', '4 min ago'],
  ['New customer joined', 'Northstar Coffee Co.', 'Customer', '32 min ago'],
  ['Reward redeemed', 'Free delivery voucher', '−250 pts', '1 hr ago'],
  ['Support request resolved', 'Ticket #1042', 'Resolved', '2 hrs ago'],
];

export default function Home() {
  const [active, setActive] = useState('Overview');
  const [mobileOpen, setMobileOpen] = useState(false);
  const [toast, setToast] = useState('');
  const choose = (name: string) => { setActive(name); setMobileOpen(false); };
  const quickAction = (label: string) => { setToast(`${label} opened successfully`); window.setTimeout(() => setToast(''), 2400); };

  return (
    <div className="app-shell">
      <aside className={`sidebar ${mobileOpen ? 'sidebar-open' : ''}`}>
        <div className="brand"><div className="brand-mark">S</div><div><strong>Startup:</strong><span>business management system</span></div><button className="mobile-close" onClick={() => setMobileOpen(false)} aria-label="Close menu"><X /></button></div>
        <nav aria-label="Main navigation"><p className="nav-label">Workspace</p>
          {features.map(({ name, icon: Icon, color }) => <button key={name} onClick={() => choose(name)} className={`nav-item ${active === name ? 'active' : ''}`}><span className={`nav-icon ${color}`}><Icon size={17} /></span><span>{name}</span>{active === name && <ChevronRight className="nav-arrow" size={16} />}</button>)}
        </nav>
        <div className="sidebar-foot"><button className="nav-item" onClick={() => choose('Settings')}><span className="nav-icon neutral"><Settings size={17} /></span><span>Settings</span></button><div className="profile"><div className="avatar">JD</div><div><strong>Jamie Dela Cruz</strong><span>Administrator</span></div></div></div>
      </aside>
      {mobileOpen && <button className="scrim" aria-label="Close menu" onClick={() => setMobileOpen(false)} />}
      <main className="main">
        <header className="topbar"><button className="menu-button" onClick={() => setMobileOpen(true)} aria-label="Open menu"><Menu /></button><div className="search"><Search size={18} /><Input aria-label="Search" placeholder="Search customers, invoices, guides..." /></div><div className="top-actions"><button aria-label="Notifications"><Bell size={19} /><span className="dot" /></button><div className="mini-avatar">JD</div></div></header>
        <div className="content">
          {active === 'Overview' ? <>
            <section className="welcome"><div><p className="eyebrow">Friday, September 4</p><h1>Good afternoon, Jamie.</h1><p>Here’s how your business is doing today.</p></div><Button onClick={() => quickAction('Create new')}><Plus size={17} /> Create new</Button></section>
            <section className="metrics" aria-label="Business metrics">
              <article><span className="metric-icon indigo"><CircleDollarSign /></span><p>Revenue this month</p><strong>₱284,500</strong><small className="up">↗ 12.4% <span>from last month</span></small></article>
              <article><span className="metric-icon cyan"><Users /></span><p>Active customers</p><strong>1,248</strong><small className="up">↗ 8.1% <span>from last month</span></small></article>
              <article><span className="metric-icon amber"><CreditCard /></span><p>Pending invoices</p><strong>18</strong><small>₱72,800 outstanding</small></article>
              <article><span className="metric-icon rose"><Headphones /></span><p>Open support tickets</p><strong>7</strong><small><b>2 high priority</b></small></article>
            </section>
            <section className="dashboard-grid">
              <article className="panel revenue-panel"><div className="panel-head"><div><h2>Revenue overview</h2><p>Monthly performance for 2026</p></div><select aria-label="Revenue period"><option>Last 6 months</option><option>This year</option></select></div><div className="chart" aria-label="Revenue bar chart">{[38,52,46,70,61,88].map((h,i) => <div className="bar-col" key={h}><div className="bar" style={{height:`${h}%`}}><span>₱{[112,156,138,210,184,285][i]}k</span></div><small>{['Apr','May','Jun','Jul','Aug','Sep'][i]}</small></div>)}</div></article>
              <article className="panel quick-panel"><div className="panel-head"><div><h2>Quick actions</h2><p>Common tasks, one click away</p></div></div><div className="quick-list">{([['Create invoice', CreditCard],['Add customer', Users],['Publish announcement', Bell],['Open setup guide', BookOpenCheck]] as const).map(([label,Icon]) => <button key={label} onClick={() => quickAction(label)}><span><Icon size={18}/></span>{label}<ChevronRight size={16}/></button>)}</div></article>
              <article className="panel activity-panel"><div className="panel-head"><div><h2>Recent activity</h2><p>Latest updates across your workspace</p></div><button>View all</button></div><div className="activity-list">{activities.map(([title,detail,value,time],i) => <div className="activity-row" key={title}><span className={`activity-dot a${i}`}><ShieldCheck size={17}/></span><div><strong>{title}</strong><p>{detail}</p></div><div><b>{value}</b><small>{time}</small></div></div>)}</div></article>
              <article className="panel health-panel"><div className="panel-head"><div><h2>Business health</h2><p>Your setup progress</p></div></div><div className="score"><strong>82</strong><span>/100</span></div><div className="progress"><i /></div><p>You’re on the right track. Complete two more setup tasks to improve your score.</p><button onClick={() => choose('Setup Guide')}>Continue setup <ChevronRight size={15}/></button></article>
            </section>
          </> : <section className="module-view"><button className="back" onClick={() => choose('Overview')}>← Back to overview</button><div className="module-card">{(() => { const item=features.find(f=>f.name===active); const Icon=item?.icon??Settings; return <span className={`module-icon ${item?.color??'blue'}`}><Icon/></span>; })()}<p className="eyebrow">Business module</p><h1>{active}</h1><p>This workspace is ready for you to manage {active.toLowerCase()} in one organized place.</p><Button onClick={() => quickAction(active)}>Open {active}</Button></div></section>}
        </div>
      </main>
      {toast && <div className="toast" role="status"><ShieldCheck size={18}/>{toast}</div>}
    </div>
  );
}
