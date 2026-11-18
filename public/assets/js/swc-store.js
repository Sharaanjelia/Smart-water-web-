/**
 * Smart Water Payakumbuh - Centralized Reactive Store & Database Sync Adapter
 * 
 * Arsitektur State Terpusat (Client-Side & MySQL REST API Synchronized)
 * Mengelola sinkronisasi data:
 *  1. Dompet Smart Water & Mutasi Saldo
 *  2. Tagihan Rekening Air (Aktif & Riwayat Lunas)
 *  3. Antrean Validasi Meteran AI-OCR Petugas SCADA (Terhubung ke MySQL via /api/v1/meter-reports)
 *  4. Notifikasi Terpadu Pelanggan
 */

(function () {
    'use strict';

    function getApiUrl(path) {
        const pathname = window.location.pathname;
        if (pathname.includes('/smart-water')) {
            return '/smart-water' + path;
        }
        return path;
    }

    const STORAGE_KEYS = {
        WALLET: 'swc_wallet_v2',
        BILLS: 'swc_bills_v2',
        METER_REPORTS: 'swc_meter_reports_v2',
        NOTIFICATIONS: 'swc_notifications_v2',
    };

    // Data Awal / Seed Default
    const DEFAULT_WALLET = {
        balance: 2500000,
        currency: 'IDR',
        owner: 'Budi Santoso',
        accountNo: '1234567890',
        mutations: [
            {
                id: 'MUT-001',
                title: 'Restitusi Koreksi Angka Meter',
                desc: '10 April 2025 • Verifikasi Kas PDAM',
                amount: 500000,
                type: 'CR', // Credit (Masuk)
                date: '10 April 2025'
            },
            {
                id: 'MUT-002',
                title: 'Deposit Awal Pasang Baru',
                desc: '01 Januari 2025 • Saldo Jaminan',
                amount: 2000000,
                type: 'CR',
                date: '01 Januari 2025'
            }
        ]
    };

    const DEFAULT_BILLS = [
        {
            inv: 'INV-PYK-2025-04-1000',
            period: 'April 2025',
            usage: '19.0 m³',
            amount: 150800,
            status: 'UNPAID', // UNPAID | PAID
            dueDate: '20 April 2025',
            paidAt: null,
            method: null,
            items: [
                { label: 'Pemakaian Air Bersih (19.0 m³)', cost: 148200 },
                { label: 'Biaya Administrasi & Beban Tetap', cost: 2600 },
                { label: 'Biaya Pemeliharaan Meter', cost: 0 },
                { label: 'Denda Keterlambatan', cost: 0 }
            ]
        },
        {
            inv: 'INV-PYK-2025-03-1000',
            period: 'Maret 2025',
            usage: '18.2 m³',
            amount: 144560,
            status: 'PAID',
            dueDate: '20 Maret 2025',
            paidAt: '10 Maret 2025',
            method: 'BCA Virtual Account'
        },
        {
            inv: 'INV-PYK-2025-02-1000',
            period: 'Februari 2025',
            usage: '17.5 m³',
            amount: 139100,
            status: 'PAID',
            dueDate: '20 Februari 2025',
            paidAt: '08 Februari 2025',
            method: 'QRIS Bank Nagari'
        },
        {
            inv: 'INV-PYK-2025-01-1000',
            period: 'Januari 2025',
            usage: '19.0 m³',
            amount: 150800,
            status: 'PAID',
            dueDate: '20 Januari 2025',
            paidAt: '12 Januari 2025',
            method: 'Mandiri Virtual Account'
        }
    ];

    const DEFAULT_METER_REPORTS = [
        {
            id: 'REP-04-1001',
            name: 'Budi Santoso',
            custId: '1234567890',
            meter: '01000',
            zone: 'DIST-A Sektor Utara',
            lastReading: 1000,
            ocrReading: 1019,
            usage: 19.0,
            date: 'Hari ini, 09:15 WIB',
            status: 'PENDING' // PENDING | APPROVED | REJECTED
        },
        {
            id: 'REP-04-1002',
            name: 'Ibu Siti Nurbaya',
            custId: '1234567891',
            meter: '01842',
            zone: 'DIST-B Sektor Timur',
            lastReading: 1420,
            ocrReading: 1435,
            usage: 15.0,
            date: 'Hari ini, 08:45 WIB',
            status: 'PENDING'
        },
        {
            id: 'REP-04-1003',
            name: 'H. Agus Salim',
            custId: '1234567892',
            meter: '02914',
            zone: 'DIST-A Sektor Utara',
            lastReading: 2180,
            ocrReading: 2202,
            usage: 22.0,
            date: 'Kemarin, 16:30 WIB',
            status: 'PENDING'
        }
    ];

    const DEFAULT_NOTIFICATIONS = [
        {
            id: 'NOTIF-001',
            type: 'BILL',
            title: 'Tagihan Air Bulan April 2025 Terbit',
            desc: 'Lembar tagihan rekening air periode April 2025 sebesar Rp 150.800 telah siap dibayarkan sebelum jatuh tempo 20 April 2025.',
            time: 'Hari ini, 08:30 WIB',
            isRead: false,
            link: '/bill'
        },
        {
            id: 'NOTIF-002',
            type: 'METER',
            title: 'Laporan Stand Meter Mandiri Diproses AI-OCR',
            desc: 'Foto odometer meteran air Anda (stand 01019 m³) masuk antrean verifikasi petugas lapangan Ahmad Pratama, S.T.',
            time: 'Hari ini, 09:16 WIB',
            isRead: false,
            link: '/bill'
        },
        {
            id: 'NOTIF-003',
            type: 'SERVICE',
            title: 'Optimalisasi Jaringan Transmisi Pipa Sektor Utara Selesai',
            desc: 'Pemeliharaan katup pipa zona DIST-A telah selesai. Tekanan air terpantau kembali stabil normal di angka 2.8 Bar.',
            time: '12 April 2025, 11:00 WIB',
            isRead: true,
            link: '/water-condition'
        },
        {
            id: 'NOTIF-004',
            type: 'BILL',
            title: 'Pembayaran Rekening Maret 2025 Lunas',
            desc: 'Terima kasih, pembayaran rekening air sebesar Rp 144.560 via BCA Virtual Account telah berhasil dicatat.',
            time: '10 Maret 2025, 09:15 WIB',
            isRead: true,
            link: '/bill-history'
        }
    ];

    // Helper Penyimpanan LocalStorage Aman
    function load(key, defaultData) {
        try {
            const raw = localStorage.getItem(key);
            if (!raw) {
                save(key, defaultData);
                return JSON.parse(JSON.stringify(defaultData));
            }
            return JSON.parse(raw);
        } catch (e) {
            console.warn('[SWC-STORE] Gagal memuat dari storage:', e);
            return JSON.parse(JSON.stringify(defaultData));
        }
    }

    function save(key, data) {
        try {
            localStorage.setItem(key, JSON.stringify(data));
        } catch (e) {
            console.error('[SWC-STORE] Gagal menyimpan ke storage:', e);
        }
    }

    function notifyChange(type, detail) {
        window.dispatchEvent(new CustomEvent('swc:state-change', { detail: { type, detail } }));
        SWC.syncHeaderUI();
    }

    // Objek Store Utama
    const SWC = {
        // --- 1. DOMPET (WALLET) ---
        getWallet() {
            return load(STORAGE_KEYS.WALLET, DEFAULT_WALLET);
        },

        deductWallet(amount, note) {
            const wallet = this.getWallet();
            if (wallet.balance < amount) {
                return { success: false, message: 'Saldo Dompet tidak mencukupi!' };
            }
            wallet.balance -= amount;
            wallet.mutations.unshift({
                id: 'MUT-' + Date.now().toString().slice(-4),
                title: note || 'Pembayaran Tagihan Rekening Air',
                desc: 'Hari ini • Saldo Dompet Smart Water',
                amount: amount,
                type: 'DB', // Debit (Keluar)
                date: 'Hari ini, ' + new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB'
            });
            save(STORAGE_KEYS.WALLET, wallet);
            notifyChange('wallet', wallet);
            return { success: true, balance: wallet.balance };
        },

        addWallet(amount, note) {
            const wallet = this.getWallet();
            wallet.balance += amount;
            wallet.mutations.unshift({
                id: 'MUT-' + Date.now().toString().slice(-4),
                title: note || 'Pengisian / Restitusi Saldo',
                desc: 'Hari ini • Kasir Perumda Tirta Sago',
                amount: amount,
                type: 'CR',
                date: 'Hari ini, ' + new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB'
            });
            save(STORAGE_KEYS.WALLET, wallet);
            notifyChange('wallet', wallet);
            return { success: true, balance: wallet.balance };
        },

        // Helper cek status login pelanggan resmi
        isCustomerLoggedIn() {
            return typeof document !== 'undefined' && document.querySelector('meta[name="auth-check"]')?.content === '1';
        },

        // --- 2. TAGIHAN REKENING (BILLS) ---
        getBills() {
            return load(STORAGE_KEYS.BILLS, DEFAULT_BILLS);
        },

        getBill(inv) {
            const bills = this.getBills();
            return bills.find(b => b.inv === inv) || null;
        },

        isBillPaid(inv = 'INV-PYK-2025-04-1000') {
            if (this.isCustomerLoggedIn() && inv === 'INV-PYK-2025-04-1000') {
                return false;
            }
            const b = this.getBill(inv);
            return b ? b.status === 'PAID' : false;
        },

        payBill(inv, method = 'Saldo Dompet Smart Water') {
            if (this.isCustomerLoggedIn()) {
                // Untuk user terautentikasi, pembayaran resmi melalui verifikasi backend Laravel
                return false;
            }
            const bills = this.getBills();
            const target = bills.find(b => b.inv === inv);
            if (!target) return false;

            target.status = 'PAID';
            target.method = method;
            const now = new Date();
            const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            target.paidAt = `${now.getDate()} ${monthNames[now.getMonth()]} ${now.getFullYear()}, ${now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })} WIB`;

            save(STORAGE_KEYS.BILLS, bills);

            // Tambahkan Notifikasi Pembayaran Berhasil
            this.addNotification({
                type: 'BILL',
                title: `Pembayaran Rekening ${target.period} Lunas`,
                desc: `Terima kasih! Rekening air sebesar Rp ${target.amount.toLocaleString('id-ID')} via ${method} telah lunas resmi terverifikasi.`,
                link: '/payment-success?method=' + encodeURIComponent(method)
            });

            notifyChange('bill_paid', target);
            return true;
        },

        // --- 3. LAPOR METER & VERIFIKASI PETUGAS ---
        getMeterReports() {
            return load(STORAGE_KEYS.METER_REPORTS, DEFAULT_METER_REPORTS);
        },

        getPendingMeterCount() {
            const reports = this.getMeterReports();
            return reports.filter(r => r.status === 'PENDING').length;
        },

        addMeterReport(data) {
            const reports = this.getMeterReports();
            const newReport = {
                id: 'REP-04-' + Date.now().toString().slice(-4),
                name: data.name || 'Budi Santoso',
                custId: data.custId || '1234567890',
                meter: data.meter || '01000',
                zone: data.zone || 'DIST-A Sektor Utara',
                lastReading: data.lastReading || 1000,
                ocrReading: data.ocrReading || 1019,
                usage: data.usage || 19.0,
                date: 'Hari ini, ' + new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB',
                status: 'PENDING'
            };

            reports.unshift(newReport);
            save(STORAGE_KEYS.METER_REPORTS, reports);

            // Simpan ke MySQL Backend Asinkron
            try {
                fetch(getApiUrl('/api/v1/meter-reports'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(newReport)
                }).catch(e => console.warn('[SWC-DB-SYNC] Offline fallback:', e));
            } catch (e) {}

            // Notifikasi untuk Warga
            this.addNotification({
                type: 'METER',
                title: 'Laporan Stand Meter Mandiri Berhasil Terkirim',
                desc: `Foto stand ${newReport.ocrReading} m³ sedang menunggu validasi petugas SCADA di Command Center.`,
                link: '/bill'
            });

            notifyChange('meter_report_added', newReport);
            return newReport;
        },

        approveMeterReport(id) {
            const reports = this.getMeterReports();
            const target = reports.find(r => r.id === id);
            if (!target) return false;

            target.status = 'APPROVED';
            save(STORAGE_KEYS.METER_REPORTS, reports);

            // Simpan ke MySQL Backend Asinkron
            try {
                fetch(getApiUrl('/api/v1/meter-reports/validate'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id, action: 'APPROVE' })
                }).catch(e => console.warn('[SWC-DB-SYNC] Offline fallback:', e));
            } catch (e) {}

            // Tambahkan Notifikasi Persetujuan Petugas
            this.addNotification({
                type: 'METER',
                title: 'Laporan Stand Meter Mandiri Disetujui Petugas',
                desc: `Petugas Ahmad Pratama, S.T. telah memverifikasi stand meter ${target.ocrReading} m³ (Pemakaian: ${target.usage} m³). Tagihan rekening resmi disahkan.`,
                link: '/bill'
            });

            notifyChange('meter_approved', target);
            return true;
        },

        rejectMeterReport(id, reason = 'Foto buram / angka tidak terbaca jelas') {
            const reports = this.getMeterReports();
            const target = reports.find(r => r.id === id);
            if (!target) return false;

            target.status = 'REJECTED';
            save(STORAGE_KEYS.METER_REPORTS, reports);

            // Simpan ke MySQL Backend Asinkron
            try {
                fetch(getApiUrl('/api/v1/meter-reports/validate'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id, action: 'REJECT', reason: reason })
                }).catch(e => console.warn('[SWC-DB-SYNC] Offline fallback:', e));
            } catch (e) {}

            this.addNotification({
                type: 'METER',
                title: 'Permintaan Foto Ulang Meteran Air',
                desc: `Laporan stand meter ${id} membutuhkan foto ulang: ${reason}.`,
                link: '/report-meter'
            });

            notifyChange('meter_rejected', target);
            return true;
        },

        // --- 4. NOTIFIKASI PELANGGAN ---
        getNotifications() {
            return load(STORAGE_KEYS.NOTIFICATIONS, DEFAULT_NOTIFICATIONS);
        },

        getUnreadNotificationCount() {
            const notifs = this.getNotifications();
            return notifs.filter(n => !n.isRead).length;
        },

        addNotification(item) {
            const notifs = this.getNotifications();
            const newNotif = {
                id: 'NOTIF-' + Date.now().toString().slice(-4),
                type: item.type || 'INFO',
                title: item.title,
                desc: item.desc,
                time: 'Baru saja',
                isRead: false,
                link: item.link || '/notification'
            };
            notifs.unshift(newNotif);
            save(STORAGE_KEYS.NOTIFICATIONS, notifs);
            notifyChange('notification_added', newNotif);
            return newNotif;
        },

        markAllNotificationsRead() {
            const notifs = this.getNotifications();
            notifs.forEach(n => n.isRead = true);
            save(STORAGE_KEYS.NOTIFICATIONS, notifs);
            notifyChange('notifications_read', notifs);
            return true;
        },

        // --- 5. SINKRONISASI UI OTOMATIS (Header, Saldo, Badge) ---
        syncHeaderUI() {
            try {
                // Update Saldo di Dropdown Header
                const wallet = this.getWallet();
                const formattedWallet = 'Rp ' + Number(wallet.balance).toLocaleString('id-ID');
                
                const walletEls = document.querySelectorAll('[data-swc="wallet-balance"], #headerWalletBalance');
                walletEls.forEach(el => {
                    el.textContent = formattedWallet;
                });

                // Update Notifikasi Badge di Header
                const unreadCount = this.getUnreadNotificationCount();
                const notifBadges = document.querySelectorAll('[data-swc="notif-badge"], #headerNotifBadge');
                notifBadges.forEach(el => {
                    if (unreadCount > 0) {
                        el.textContent = unreadCount;
                        el.style.display = 'flex';
                    } else {
                        el.style.display = 'none';
                    }
                });

                // Update Status Verifikasi Meter Petugas
                const pendingCount = this.getPendingMeterCount();
                const pendingBadges = document.querySelectorAll('[data-swc="pending-meter-count"]');
                pendingBadges.forEach(el => {
                    el.textContent = pendingCount;
                });
            } catch (err) {
                console.warn('[SWC-STORE] Error syncing header UI:', err);
            }
        },

        // --- 6. RESET DATA DEMO KE DEFAULT ---
        resetDemoData() {
            localStorage.removeItem(STORAGE_KEYS.WALLET);
            localStorage.removeItem(STORAGE_KEYS.BILLS);
            localStorage.removeItem(STORAGE_KEYS.METER_REPORTS);
            localStorage.removeItem(STORAGE_KEYS.NOTIFICATIONS);
            window.location.reload();
        }
    };

    // Pasang ke Window Global
    window.SWC = SWC;

    // Sinkronisasi data dari Backend MySQL saat start
    try {
        fetch(getApiUrl('/api/v1/meter-reports'))
            .then(res => res.json())
            .then(data => {
                if (data.ok && Array.isArray(data.reports) && data.reports.length > 0) {
                    const local = load(STORAGE_KEYS.METER_REPORTS, DEFAULT_METER_REPORTS);
                    // Gabungkan dengan prioritas data database
                    const map = new Map();
                    local.forEach(r => map.set(r.id, r));
                    data.reports.forEach(r => map.set(r.id, r));
                    const merged = Array.from(map.values());
                    save(STORAGE_KEYS.METER_REPORTS, merged);
                    SWC.syncHeaderUI();
                }
            })
            .catch(() => {});
    } catch (e) {}

    // Inisialisasi Sinkronisasi saat DOM siap
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => SWC.syncHeaderUI());
    } else {
        SWC.syncHeaderUI();
    }
})();
