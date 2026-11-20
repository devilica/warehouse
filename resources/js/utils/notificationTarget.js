export function notificationTarget(message) {
    const text = (message ?? '').toLowerCase();
    if (text.includes('transfer')) return { icon: 'truck', tone: 'bg-sky-50 text-sky-600', to: '/inventory/transfers' };
    if (text.includes('low stock') || text.includes('below minimum')) return { icon: 'box', tone: 'bg-amber-50 text-amber-600', to: '/inventory/stock-levels' };
    if (text.includes('purchase') || text.includes('order')) return { icon: 'cart', tone: 'bg-indigo-50 text-indigo-600', to: '/purchase-orders' };
    if (text.includes('receipt')) return { icon: 'inbox', tone: 'bg-emerald-50 text-emerald-600', to: '/goods-receipts' };
    if (text.includes('count')) return { icon: 'clipboard', tone: 'bg-violet-50 text-violet-600', to: '/inventory/counts' };
    if (text.includes('expir')) return { icon: 'tag', tone: 'bg-red-50 text-red-600', to: '/products' };
    if (text.includes('supplier')) return { icon: 'building', tone: 'bg-slate-100 text-slate-600', to: '/suppliers' };
    return { icon: 'bell', tone: 'bg-slate-100 text-slate-500', to: '/notifications' };
}
