import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
window.apForm = (floor, apNo, status) => ({ floor, apNo, status });
Alpine.start();

const indicator = document.getElementById('realtime-indicator');
const feed = document.getElementById('realtime-feed');

if (window.Echo && indicator) {
    window.Echo.connector.pusher.connection.bind('connected', () => {
        indicator.textContent = 'Realtime Connected';
        indicator.className = 'rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-bold text-emerald-700';
    });
    window.Echo.connector.pusher.connection.bind('disconnected', () => {
        indicator.textContent = 'Realtime Disconnected';
        indicator.className = 'rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-500';
    });
    window.Echo.channel('ap-records').listen('.ApRecordSaved', (record) => {
        if (!feed) return;
        const blocked = record.status === 'blocked';
        const item = document.createElement('div');
        item.className = `rounded-2xl border p-4 shadow-sm ${blocked ? 'border-amber-200 bg-amber-50' : 'border-emerald-200 bg-emerald-50'}`;
        item.innerHTML = `<strong>${blocked ? 'Vừa nhận sự cố' : 'Vừa nhận dữ liệu'}:</strong><div class="mt-1 text-lg font-black"></div><div class="text-sm"></div>`;
        item.children[1].textContent = record.ap_name;
        item.children[2].textContent = `${record.team_name || 'Không có tổ'} · ${blocked ? record.issue_reason : 'Đã lắp xong'} · ${new Date(record.updated_at).toLocaleTimeString('vi-VN', {hour:'2-digit', minute:'2-digit'})}`;
        feed.prepend(item);
        window.setTimeout(() => window.location.reload(), 1500);
    });
}
