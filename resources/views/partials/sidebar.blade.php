{{-- sidebar.blade.php --}}
<div class="sidebar-overlay" id="sidebar-overlay"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h2 class="sidebar-title">Menú</h2>
    </div>

    <div class="sidebar-profile">
        <div class="sidebar-avatar" style="background:transparent;padding:0;">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
                <circle cx="24" cy="24" r="24" fill="#7c6bc4"/>
                <circle cx="24" cy="17" r="8" fill="#d0c7f0"/>
                <path d="M24 28C16.8203 28 11 33.8203 11 41C14.4758 45.4267 18.9958 48 24 48C29.0042 48 33.5242 45.4267 37 41C37 33.8203 31.1797 28 24 28Z" fill="#d0c7f0"/>
            </svg>
        </div>
        <div class="sidebar-profile-info">
            <span class="sidebar-profile-name" style="font-size: 1.1rem;">{{ Auth::user()->name ?? 'Usuario' }}</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <a href="{{ route('app.profile') }}" class="sidebar-nav-item" id="sidebar-mi-perfil">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#4b5563" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
            <span>Mi perfil</span>
        </a>
        <a href="{{ route('app.vehicles') }}" class="sidebar-nav-item" id="sidebar-mis-vehiculos">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#4b5563" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <rect x="1" y="3" width="15" height="13" rx="2" ry="2"/>
                <path d="M16 8h2a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2h-1"/>
                <path d="M1 16h16"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="13.5" cy="18.5" r="2.5"/>
            </svg>
            <span>Mis vehículos</span>
        </a>

        <a href="{{ route('app.settings') }}" class="sidebar-nav-item" id="sidebar-configuracion">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#4b5563" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
            </svg>
            <span>Configuración</span>
        </a>
        <a href="{{ route('app.share_camera') }}" class="sidebar-nav-item" id="sidebar-camara">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#4b5563" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/>
            </svg>
            <span>Compartir mi cámara</span>
        </a>
        <a href="/logout" class="sidebar-nav-item" id="sidebar-logout" style="color:#ef4444;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            <span>Cerrar sesión</span>
        </a>
    </nav>
</aside>

<script>
(function() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const navMenu = document.getElementById('nav-menu');

    function openSidebar() {
        if (sidebar) sidebar.classList.add('open');
        if (overlay) overlay.classList.add('visible');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        if (sidebar) sidebar.classList.remove('open');
        if (overlay) overlay.classList.remove('visible');
        document.body.style.overflow = '';
    }

    if (navMenu) {
        navMenu.addEventListener('click', (e) => { 
            e.preventDefault(); 
            openSidebar(); 
        });
    }

    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    document.addEventListener('keydown', (e) => { 
        if (e.key === 'Escape') closeSidebar(); 
    });

    const logoutBtn = document.getElementById('sidebar-logout');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            localStorage.removeItem('jwt_token');
        });
    }
})();
</script>
